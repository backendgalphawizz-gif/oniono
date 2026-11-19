<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminGeoScope;

class AdminGeoScopeSyncService
{
  /**
   * @param  array<int, array{state?: string, city?: string|null}>  $rows
   */
  public function sync(Admin $admin, bool $allRegions, array $rows): void
  {
    $admin->scope_all_regions = $allRegions;
    $admin->save();

    AdminGeoScope::query()->where('admin_id', $admin->id)->delete();

    if ($allRegions) {
      return;
    }

    $seen = [];

    foreach ($rows as $row) {
      $state = trim((string) ($row['state'] ?? ''));
      if ($state === '') {
        continue;
      }

      $cityRaw = $row['city'] ?? null;
      $city = is_string($cityRaw) ? trim($cityRaw) : '';
      $city = $city !== '' ? $city : null;

      $key = strtolower($state).'|'.($city !== null ? strtolower($city) : '*');
      if (isset($seen[$key])) {
        continue;
      }
      $seen[$key] = true;

      AdminGeoScope::query()->create([
        'admin_id' => $admin->id,
        'state' => $state,
        'city' => $city,
      ]);
    }
  }

  /**
   * @return list<array{state: string, city: ?string}>
   */
  public function rowsForAdmin(Admin $admin): array
  {
    $admin->loadMissing('geoScopes');

    return $admin->geoScopes
      ->map(fn (AdminGeoScope $scope) => [
        'state' => $scope->state,
        'city' => $scope->city,
      ])
      ->values()
      ->all();
  }
}
