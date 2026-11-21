<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->isCustomer()) {
            abort(403, 'Akses laporan dibatasi khusus untuk personel operasional dan eksekutif Bio Farma.');
        }

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subMonths(3)->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $query = Shipment::with(['carrier', 'origin', 'destination', 'customer', 'items'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }

        $totalFiltered = (clone $query)->count();
        $deliveredFiltered = (clone $query)->where('current_status', 'DELIVERED')->count();
        $delayedFiltered = (clone $query)->where('is_delayed', true)->count();
        $inTransitFiltered = (clone $query)->where('current_status', 'IN_TRANSIT')->count();

        // Temperature integrity in period
        $shipmentIds = (clone $query)->pluck('id');
        $tempEvents = TrackingEvent::whereIn('shipment_id', $shipmentIds)->whereNotNull('container_temperature')->with('shipment')->get();
        $totalReadings = $tempEvents->count();
        $safeReadings = $tempEvents->filter(fn ($ev) => $ev->shipment && $ev->container_temperature >= $ev->shipment->target_temp_min && $ev->container_temperature <= $ev->shipment->target_temp_max)->count();
        $tempComplianceRate = $totalReadings > 0 ? round(($safeReadings / $totalReadings) * 100, 1) : null;

        // Carrier breakdown
        $carriers = Carrier::all();
        $carrierStats = [];
        foreach ($carriers as $c) {
            $cShipments = Shipment::where('carrier_id', $c->id)->whereBetween('created_at', [$startDate, $endDate])->count();
            $cDelayed = Shipment::where('carrier_id', $c->id)->whereBetween('created_at', [$startDate, $endDate])->where('is_delayed', true)->count();
            if ($cShipments > 0) {
                $carrierStats[] = [
                    'name' => $c->name,
                    'type' => $c->transport_type,
                    'total' => $cShipments,
                    'delayed' => $cDelayed,
                    'delay_rate' => round(($cDelayed / $cShipments) * 100, 1),
                ];
            }
        }

        $recentShipments = $query->latest()->paginate(15)->withQueryString();
        $openExceptions = ShipmentException::whereIn('shipment_id', $shipmentIds)->whereIn('status', ['OPEN', 'INVESTIGATING'])->count();

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'totalFiltered',
            'deliveredFiltered',
            'delayedFiltered',
            'inTransitFiltered',
            'tempComplianceRate',
            'totalReadings',
            'safeReadings',
            'carrierStats',
            'recentShipments',
            'openExceptions',
            'carriers'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $user = Auth::user();
        if ($user->isCustomer()) {
            abort(403);
        }

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subMonths(6)->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $query = Shipment::with(['carrier', 'origin', 'destination', 'customer', 'items'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }

        $shipments = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="cargopharma-report-'.date('Ymd-His').'.csv"',
        ];

        return response()->stream(function () use ($shipments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Shipment Number (AWB)',
                'Current Status',
                'Is Delayed',
                'Delay Hours',
                'Carrier Name',
                'Transport Mode',
                'Origin Location',
                'Destination Location',
                'Consignee Organization',
                'Vaccine Product',
                'Batch Number',
                'Total Vials',
                'Target Temp Min (C)',
                'Target Temp Max (C)',
                'Expected Arrival',
                'Actual Arrival',
                'Created At',
            ]);

            foreach ($shipments as $s) {
                $item = $s->items->first();
                fputcsv($file, [
                    $s->shipment_number,
                    $s->current_status,
                    $s->is_delayed ? 'YES' : 'NO',
                    $s->delay_duration_hours,
                    $s->carrier?->name ?? 'N/A',
                    $s->carrier?->transport_type ?? 'N/A',
                    $s->origin?->name ?? 'N/A',
                    $s->destination?->name ?? 'N/A',
                    $s->customer?->organization ?? 'N/A',
                    $item?->product_name ?? $s->cargo_type,
                    $item?->batch_number ?? 'N/A',
                    $item?->quantity_vials ?? 0,
                    $s->target_temp_min,
                    $s->target_temp_max,
                    $s->expected_arrival?->format('Y-m-d H:i:s'),
                    $s->actual_arrival?->format('Y-m-d H:i:s'),
                    $s->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}
