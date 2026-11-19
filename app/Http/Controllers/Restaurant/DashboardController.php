<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Services\RestaurantDashboardService;

class DashboardController extends Controller
{
    public function index()
    {
        $dashboard = RestaurantDashboardService::forAuthenticatedRestaurant();
        $stats = $dashboard->overviewStats();
        $recentOrders = $dashboard->recentOrders();

        $restaurant = auth('restaurant')->user()->restaurant;
        $documentChecklist = app(\App\Services\RestaurantDocumentService::class)->checklist($restaurant);
        $profileSummary = [
            'is_open' => (bool) $restaurant->is_open,
            'is_blocked' => (bool) $restaurant->is_blocked,
            'account_status' => $restaurant->accountStatusLabel(),
            'missing_documents' => collect($documentChecklist)->where('uploaded', false)->count(),
            'pending_documents' => collect($documentChecklist)
                ->filter(fn (array $item) => $item['uploaded'] && ($item['status'] ?? 'pending') === 'pending')
                ->count(),
        ];

        return view('restaurant.dashboard', compact('stats', 'recentOrders', 'restaurant', 'profileSummary'));
    }
}
