<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * Company activity trail (read-only). Company Admin only; always limited to the admin's own company.
 */
class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::where('company_id', $request->user()->company_id)
            ->orderByDesc('id');

        if ($request->filled('action')) {
            $query->where('action', $request->string('action')->toString());
        }

        $logs = $query->paginate(25)->withQueryString();

        $actions = AuditLog::where('company_id', $request->user()->company_id)
            ->distinct()->orderBy('action')->pluck('action');

        return view('activity.index', compact('logs', 'actions'));
    }
}
