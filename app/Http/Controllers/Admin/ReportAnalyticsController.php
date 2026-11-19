<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportAnalyticsService;
use Illuminate\Http\Request;

class ReportAnalyticsController extends Controller
{
    public function __construct(private ReportAnalyticsService $reports) {}

    public function orders(Request $request)
    {
        [$from, $to] = $this->reports->dateRange($request);
        $data = $this->reports->orderReport($from, $to);

        return view('admin.reports-analytics.orders', array_merge($data, compact('from', 'to')));
    }

    public function revenue(Request $request)
    {
        [$from, $to] = $this->reports->dateRange($request);
        $data = $this->reports->revenueReport($from, $to);

        return view('admin.reports-analytics.revenue', array_merge($data, compact('from', 'to')));
    }

    public function userActivity(Request $request)
    {
        [$from, $to] = $this->reports->dateRange($request);
        $data = $this->reports->userActivityReport($from, $to);

        return view('admin.reports-analytics.user-activity', array_merge($data, compact('from', 'to')));
    }

    public function restaurantPerformance(Request $request)
    {
        [$from, $to] = $this->reports->dateRange($request);
        $data = $this->reports->restaurantPerformanceReport($from, $to);

        return view('admin.reports-analytics.restaurant-performance', array_merge($data, compact('from', 'to')));
    }

    public function deliveryPartnerPerformance(Request $request)
    {
        [$from, $to] = $this->reports->dateRange($request);
        $data = $this->reports->deliveryPartnerPerformanceReport($from, $to);

        return view('admin.reports-analytics.delivery-partner-performance', array_merge($data, compact('from', 'to')));
    }

    public function popular(Request $request)
    {
        [$from, $to] = $this->reports->dateRange($request);
        $data = $this->reports->popularItemsAndRestaurants($from, $to);

        return view('admin.reports-analytics.popular', array_merge($data, compact('from', 'to')));
    }

    public function orderTrends(Request $request)
    {
        [$from, $to] = $this->reports->dateRange($request);
        $trends = $this->reports->orderTrends($from, $to);

        return view('admin.reports-analytics.order-trends', compact('from', 'to', 'trends'));
    }
}
