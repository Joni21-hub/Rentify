<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;

class AdminAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminAuditLog::with('admin')->latest('id');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('auditable_id', $search)
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('admin', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(25);

        $actions = AdminAuditLog::select('action')->distinct()->pluck('action');

        return view('admin.audit_logs.index', compact('logs', 'actions'));
    }
}
