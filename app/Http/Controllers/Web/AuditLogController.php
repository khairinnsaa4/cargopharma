<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Access restricted to system administrators.');
        }

        $logs = AuditLog::with('user')->latest()->paginate(20);
        return view('audit-logs.index', compact('logs'));
    }
}
