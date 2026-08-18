<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function stats()
    {
        // Cache for 60 minutes
        return Cache::remember('dashboard.stats', 3600, function () {
            $totalQuotations = Quotation::count();
            $pendingApprovals = Quotation::whereIn('status', ['draft', 'sent', 'pending'])->count();
            $activeProjects = Quotation::where('status', 'approved')->count();
            $pipelineValue = Quotation::where('status', '!=', 'rejected')->sum('grand_total');

            $recentQuotations = Quotation::with('customer')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($q) {
                    return [
                        'id' => $q->quote_no,
                        'client' => $q->client_name,
                        'date' => $q->date ? $q->date->format('d M Y') : $q->created_at->format('d M Y'),
                        'amount' => '₹ ' . number_format($q->grand_total, 2),
                        'status' => ucfirst($q->status ?? 'Draft'),
                        'color' => match (strtolower($q->status ?? 'draft')) {
                            'approved' => 'bg-emerald-100 text-emerald-700',
                            'rejected' => 'bg-rose-100 text-rose-700',
                            'sent' => 'bg-blue-100 text-blue-700',
                            default => 'bg-amber-100 text-amber-700',
                        }
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
