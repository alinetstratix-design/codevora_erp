<?php

return [
    // Days of read-only access after a trial or plan expires, before full lockout.
    'grace_days' => (int) env('SUBSCRIPTION_GRACE_DAYS', 3),

    // Plans shown on the landing page. A null price shows "Contact us".
    'plans' => [
        [
            'name' => 'Free Trial',
            'price' => '₹0',
            'period' => '3 days',
            'highlight' => false,
            'cta' => 'trial',
            'features' => ['Full access for 3 days', 'Up to 3 team members', 'Quotations, PDF and sharing'],
        ],
        [
            'name' => 'Standard',
            'price' => env('PLAN_STANDARD_PRICE'),
            'period' => 'per month',
            'highlight' => true,
            'cta' => 'contact',
            'features' => ['Up to 10 team members', 'Unlimited quotations', 'Email support'],
        ],
        [
            'name' => 'Enterprise',
            'price' => env('PLAN_ENTERPRISE_PRICE'),
            'period' => 'custom',
            'highlight' => false,
            'cta' => 'contact',
            'features' => ['Custom team size', 'Priority support', 'Onboarding help'],
        ],
    ],
];
