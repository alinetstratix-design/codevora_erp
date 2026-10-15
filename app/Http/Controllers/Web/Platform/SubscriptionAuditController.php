<?php

namespace App\Http\Controllers\Web\Platform;

use App\Http\Controllers\Controller;
use App\Models\Company;

class SubscriptionAuditController extends Controller
{
    public function index(Company $company)
    {
        $logs = $company->subscriptionAuditLogs()->with('actor')->paginate(50);

        return view('platform.audit', compact('company', 'logs'));
    }
}
