<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardWebController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::count();
        $totalQuotations = Quotation::count();
        $draftQuotations = Quotation::where('status', 'Draft')->count();
        $approvedQuotations = Quotation::whereIn('status', ['Approved', 'Accepted'])->count();
        $rejectedQuotations = Quotation::where('status', 'Rejected')->count();
        
        $monthlySales = Quotation::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('grand_total');

        $totalQuotationValue = Quotation::sum('grand_total');
        $pendingFollowups = Quotation::whereIn('status', ['Draft', 'Sent', 'Pending'])->count();

        $recentQuotations = Quotation::with('customer')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $recentCustomers = Customer::orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalCustomers',
            'totalQuotations',
            'draftQuotations',
            'approvedQuotations',
            'rejectedQuotations',
            'monthlySales',
            'totalQuotationValue',
            'pendingFollowups',
            'recentQuotations',
            'recentCustomers'
        ));
    }
}
