<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Main categories and sub-categories for menu structure.
     *
     * @return array{main: int, sub: int}
     */
    public function run(): array
    {
        $structure = [
            'Fast Food' => ['Burgers', 'Pizza', 'Sandwiches', 'Sides', 'Meal combos'],
            'North Indian' => ['Starters', 'Curries', 'Breads', 'Rice & biryani', 'Thali'],
            'South Indian' => ['Dosas', 'Idlis & vadas', 'Meals', 'Chettinad', 'Beverages'],
            'Chinese' => ['Soups', 'Starters', 'Noodles', 'Rice', 'Main course'],
            'Desserts' => ['Cakes', 'Ice cream', 'Indian sweets', 'Shakes', 'Pastries'],
        ];

        $mainCount = 0;
        $subCount = 0;

        foreach ($structure as $mainName => $subNames) {
            $main = $this->upsertCategory(
                Str::slug($mainName),
                [
                    'name' => $mainName,
                    'parent_id' => null,
                    'sort_order' => $mainCount + 1,
                    'is_active' => true,
                ]
            );
            $mainCount++;

            foreach ($subNames as $j => $subName) {
                $this->upsertCategory(
                    Str::slug($main->slug.'-'.Str::slug($subName)),
                    [
                        'name' => $subName,
                        'parent_id' => $main->id,
                        'sort_order' => $j + 1,
                        'is_active' => true,
                    ]
                );
                $subCount++;
            }
        }

        $this->command?->info("Categories seeded: {$mainCount} main, {$subCount} sub-categories.");

        return ['main' => $mainCount, 'sub' => $subCount];
    }

    private function upsertCategory(string $slug, array $values): Category
    {
        $category = Category::withTrashed()->where('slug', $slug)->first();

        if ($category) {
            if ($category->trashed()) {
                $category->restore();
            }
            $category->update($values);

            return $category->fresh();
        }

        return Category::query()->create(array_merge(['slug' => $slug], $values));
    }
}
