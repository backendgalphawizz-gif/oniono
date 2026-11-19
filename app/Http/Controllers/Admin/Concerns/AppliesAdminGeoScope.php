<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\FoodItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\Settlement;
use App\Services\AdminGeoScopeService;
use Illuminate\Database\Eloquent\Builder;

trait AppliesAdminGeoScope
{
  protected function geoScope(): AdminGeoScopeService
  {
    return app(AdminGeoScopeService::class);
  }

  protected function scopeRestaurants(Builder $query): Builder
  {
    return $this->geoScope()->constrainRestaurants($query);
  }

  protected function scopeOrders(Builder $query): Builder
  {
    return $this->geoScope()->constrainOrders($query);
  }

  protected function scopeFoodItems(Builder $query): Builder
  {
    return $this->geoScope()->constrainFoodItems($query);
  }

  protected function scopeReviews(Builder $query): Builder
  {
    return $this->geoScope()->constrainReviews($query);
  }

  protected function scopePayments(Builder $query): Builder
  {
    return $this->geoScope()->constrainPayments($query);
  }

  protected function scopeSettlements(Builder $query): Builder
  {
    return $this->geoScope()->constrainSettlements($query);
  }

  protected function ensureRestaurantInScope(Restaurant $restaurant): void
  {
    $this->geoScope()->authorizeRestaurant($restaurant);
  }

  protected function ensureOrderInScope(Order $order): void
  {
    $this->geoScope()->authorizeOrder($order);
  }

  protected function ensureFoodItemInScope(FoodItem $foodItem): void
  {
    $this->geoScope()->authorizeFoodItem($foodItem);
  }

  protected function ensureReviewInScope(Review $review): void
  {
    $this->geoScope()->authorizeReview($review);
  }

  protected function ensurePaymentInScope(Payment $payment): void
  {
    $this->geoScope()->authorizePayment($payment);
  }

  protected function ensureSettlementInScope(Settlement $settlement): void
  {
    $this->geoScope()->authorizeSettlement($settlement);
  }
}
