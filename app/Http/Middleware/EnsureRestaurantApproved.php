<?php

namespace App\Http\Middleware;

use App\Enums\RestaurantStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRestaurantApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('restaurant')->user();

        if (! $user || ! $user->is_active) {
            auth('restaurant')->logout();

            return redirect()->route('restaurant.login')->with('error', 'Restaurant account is inactive.');
        }

        $restaurant = $user->restaurant()->first();

        $statusValue = $restaurant?->status instanceof RestaurantStatus
            ? $restaurant->status->value
            : (string) ($restaurant?->status ?? '');

        if ($restaurant?->is_blocked) {
            auth('restaurant')->logout();

            return redirect()->route('restaurant.login')->with('error', 'Your restaurant account has been blocked.');
        }

        if (! $restaurant || $statusValue !== RestaurantStatus::Approved->value) {
            auth('restaurant')->logout();

            $message = match ($restaurant?->status) {
                RestaurantStatus::Pending => 'Your restaurant registration is pending approval.',
                RestaurantStatus::Rejected => 'Your restaurant registration was rejected.',
                RestaurantStatus::Suspended => 'Your restaurant account is suspended.',
                default => 'Restaurant access denied.',
            };

            return redirect()->route('restaurant.login')->with('error', $message);
        }

        return $next($request);
    }
}
