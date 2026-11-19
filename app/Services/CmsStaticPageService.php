<?php

namespace App\Services;

use App\Models\StaticPage;
use Illuminate\Support\Collection;

class CmsStaticPageService
{
    /**
     * @return array<string, array{title: string, nav_label: string, default_content?: string}>
     */
    public function definitions(): array
    {
        return config('cms_pages.pages', []);
    }

    public function isAllowed(string $slug): bool
    {
        return array_key_exists($slug, $this->definitions());
    }

    /**
     * @return array{title: string, nav_label: string, default_content?: string}|null
     */
    public function definition(string $slug): ?array
    {
        return $this->definitions()[$slug] ?? null;
    }

    /** @return list<string> */
    public function allowedSlugs(): array
    {
        return array_keys($this->definitions());
    }

    public function ensurePagesExist(): void
    {
        foreach ($this->definitions() as $slug => $meta) {
            StaticPage::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $meta['title'],
                    'content' => $meta['default_content'] ?? '',
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * @return Collection<int, StaticPage>
     */
    public function orderedPages(): Collection
    {
        $this->ensurePagesExist();

        $pages = StaticPage::query()
            ->whereIn('slug', $this->allowedSlugs())
            ->get()
            ->keyBy('slug');

        return collect($this->allowedSlugs())
            ->map(fn (string $slug) => $pages->get($slug))
            ->filter();
    }

    public function resolvePage(string $slug): StaticPage
    {
        abort_unless($this->isAllowed($slug), 404);

        $this->ensurePagesExist();

        return StaticPage::query()->where('slug', $slug)->firstOrFail();
    }
}
