<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\City;
use App\Models\FoodItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\State;
use App\Models\Review;
use App\Models\Settlement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdminGeoScopeService
{
  protected ?Admin $admin;

  /** @var list<array{state: string, city: ?string}>|null */
  protected ?array $rules = null;

  public function __construct(?Admin $admin = null)
  {
    $this->admin = $admin ?? auth('admin')->user();
  }

  public function forAdmin(Admin $admin): self
  {
    $clone = clone $this;
    $clone->admin = $admin;
    $clone->rules = null;

    return $clone;
  }

  /**
   * Employee is limited to specific states / cities (super admin is never restricted).
   */
  public function restricts(): bool
  {
    if (! $this->admin || $this->admin->isSuperAdmin()) {
      return false;
    }

    if ($this->admin->scope_all_regions) {
      return false;
    }

    return count($this->rules()) > 0;
  }

  /**
   * @return list<array{state: string, city: ?string}>
   */
  public function rules(): array
  {
    if ($this->rules !== null) {
      return $this->rules;
    }

    if (! $this->admin) {
      return $this->rules = [];
    }

    $this->admin->loadMissing('geoScopes');

    $this->rules = $this->admin->geoScopes
      ->map(fn ($scope) => [
        'state' => $this->normalize($scope->state),
        'city' => $scope->city !== null && trim($scope->city) !== ''
          ? $this->normalize($scope->city)
          : null,
      ])
      ->filter(fn ($row) => $row['state'] !== '')
      ->values()
      ->all();

    return $this->rules;
  }

  public function scopeSummary(): string
  {
    if (! $this->admin || $this->admin->isSuperAdmin() || $this->admin->scope_all_regions) {
      return 'All regions';
    }

    $rules = $this->rules();
    if ($rules === []) {
      return 'No region assigned';
    }

    return collect($rules)
      ->map(function (array $rule) {
        if ($rule['city']) {
          return $rule['city'].', '.$rule['state'];
        }

        return 'All cities · '.$rule['state'];
      })
      ->take(3)
      ->implode('; ')
      .(count($rules) > 3 ? '…' : '');
  }

  public function allowsLocation(string $state, ?string $city = null): bool
  {
    if (! $this->restricts()) {
      return true;
    }

    $stateNorm = $this->normalize($state);
    $cityNorm = $city !== null && trim($city) !== '' ? $this->normalize($city) : null;

    foreach ($this->rules() as $rule) {
      if ($rule['state'] !== $stateNorm) {
        continue;
      }

      if ($rule['city'] === null) {
        return true;
      }

      if ($cityNorm !== null && $rule['city'] === $cityNorm) {
        return true;
      }
    }

    return false;
  }

  public function constrainRestaurants(Builder $query): Builder
  {
    if (! $this->restricts()) {
      return $query;
    }

    return $query->where(function (Builder $outer) {
      foreach ($this->rules() as $rule) {
        $outer->orWhere(function (Builder $inner) use ($rule) {
          $inner->whereRaw('LOWER(TRIM(state)) = ?', [$rule['state']]);
          if ($rule['city'] !== null) {
            $inner->whereRaw('LOWER(TRIM(city)) = ?', [$rule['city']]);
          }
        });
      }
    });
  }

  public function constrainOrders(Builder $query): Builder
  {
    if (! $this->restricts()) {
      return $query;
    }

    return $query->whereHas('restaurant', fn (Builder $restaurant) => $this->constrainRestaurants($restaurant));
  }

  public function constrainFoodItems(Builder $query): Builder
  {
    if (! $this->restricts()) {
      return $query;
    }

    $restaurantIds = $this->constrainRestaurants(Restaurant::query()->select('id'));

    return $query->whereIn('restaurant_id', $restaurantIds);
  }

  public function constrainReviews(Builder $query): Builder
  {
    if (! $this->restricts()) {
      return $query;
    }

    return $query->whereHas('restaurant', fn (Builder $restaurant) => $this->constrainRestaurants($restaurant));
  }

  public function constrainPayments(Builder $query): Builder
  {
    if (! $this->restricts()) {
      return $query;
    }

    return $query->whereHas('order', fn (Builder $order) => $this->constrainOrders($order));
  }

  public function constrainSettlements(Builder $query): Builder
  {
    if (! $this->restricts()) {
      return $query;
    }

    return $query->whereHas('restaurant', fn (Builder $restaurant) => $this->constrainRestaurants($restaurant));
  }

  public function authorizeRestaurant(Restaurant $restaurant): void
  {
    if ($this->allowsLocation((string) $restaurant->state, $restaurant->city)) {
      return;
    }

    abort(404);
  }

  public function authorizeOrder(Order $order): void
  {
    $order->loadMissing('restaurant');
    if ($order->restaurant) {
      $this->authorizeRestaurant($order->restaurant);
    } elseif ($this->restricts()) {
      abort(404);
    }
  }

  public function authorizeFoodItem(FoodItem $foodItem): void
  {
    $foodItem->loadMissing('restaurant');
    if ($foodItem->restaurant) {
      $this->authorizeRestaurant($foodItem->restaurant);
    } elseif ($this->restricts()) {
      abort(404);
    }
  }

  public function authorizeReview(Review $review): void
  {
    $review->loadMissing('restaurant');
    if ($review->restaurant) {
      $this->authorizeRestaurant($review->restaurant);
    } elseif ($this->restricts()) {
      abort(404);
    }
  }

  public function authorizePayment(Payment $payment): void
  {
    $payment->loadMissing('order.restaurant');
    if ($payment->order) {
      $this->authorizeOrder($payment->order);
    } elseif ($this->restricts()) {
      abort(404);
    }
  }

  public function authorizeSettlement(Settlement $settlement): void
  {
    $settlement->loadMissing('restaurant');
    if ($settlement->restaurant) {
      $this->authorizeRestaurant($settlement->restaurant);
    } elseif ($this->restricts()) {
      abort(404);
    }
  }

  /**
   * @return list<string>
   */
    public function stateOptions(): array
    {
        return State::query()
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * @return array<string, list<string>>
     */
    public function citiesByState(): array
    {
        return State::query()
            ->with(['cities' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (State $state) => [
                $state->name => $state->cities->pluck('name')->all(),
            ])
            ->all();
    }

  protected function normalize(string $value): string
  {
    return strtolower(trim($value));
  }
}
