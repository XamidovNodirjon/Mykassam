<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use App\Services\SubscriptionService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(StatisticsService $statistics, SubscriptionService $subscriptions): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'overview' => $statistics->platformOverview(),
            'subscriptions' => $subscriptions->summary(),
        ]);
    }
}
