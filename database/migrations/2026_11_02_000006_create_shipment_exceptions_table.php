<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('tracking_event_id')->nullable()->constrained('tracking_events')->nullOnDelete();
            
            $table->enum('exception_type', [
                'TEMPERATURE_BREACH',
                'CUSTOMS_HOLD',
                'WEATHER_DELAY',
                'PORT_CONGESTION',
                'DAMAGED',
                'DOCUMENTATION_ISSUE',
                'MISROUTED'
            ])->index();
            
            $table->enum('severity', ['LOW', 'MEDIUM', 'HIGH'])->default('MEDIUM');
            $table->enum('status', ['OPEN', 'INVESTIGATING', 'RESOLVED'])->default('OPEN')->index();
            
            $table->dateTime('detected_at');
            $table->dateTime('resolved_at')->nullable();
            
            $table->text('root_cause')->nullable();
            $table->text('resolution_notes')->nullable();
            
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_exceptions');
    }
};
