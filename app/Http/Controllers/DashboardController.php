<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function stats()
    {
        $companyId = auth()->user()->company_id;

        // Cache key is per company so tenants never see each other's numbers.
        return Cache::remember('dashboard.stats.' . $companyId, 3600, function () {
            $totalQuotations = Quotation::count();
            $pendingApprovals = Quotation::whereIn('status', ['Draft', 'Sent'])->count();
            $activeProjects = Quotation::where('status', 'Approved')->count();
            $pipelineValue = Quotation::where('status', '!=', 'Rejected')->sum('grand_total');

            $recentQuotations = Quotation::with('customer')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($q) {
                    return [
                        'id' => $q->quotation_number,
                        'client' => $q->client_name,
                        'date' => $q->quotation_date ? $q->quotation_date->format('d M Y') : $q->created_at->format('d M Y'),
                        'amount' => '₹ ' . number_format($q->grand_total, 2),
                        'status' => $q->status ?? 'Draft',
                        'color' => match ($q->status) {
                            'Approved' => 'bg-emerald-100 text-emerald-700',
                            'Rejected' => 'bg-rose-100 text-rose-700',
                            'Sent' => 'bg-blue-100 text-blue-700',
                            default => 'bg-amber-100 text-amber-700',
                        },
                    ];
                });

            return response()->json([
                'totalQuotations' => $totalQuotations,
                'pendingApprovals' => $pendingApprovals,
                'activeProjects' => $activeProjects,
                'pipelineValue' => $pipelineValue,
                'recentQuotations' => $recentQuotations,
            ]);
        });
    }
}
