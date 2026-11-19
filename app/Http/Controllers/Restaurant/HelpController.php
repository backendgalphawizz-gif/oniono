<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\CmsStaticPageService;
use Illuminate\View\View;

class HelpController extends Controller
{
    public function index(CmsStaticPageService $cms): View
    {
        $restaurantUserId = auth('restaurant')->id();

        $openTickets = SupportTicket::query()
            ->where('creator_type', 'restaurant_user')
            ->where('creator_id', $restaurantUserId)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        $recentTickets = SupportTicket::query()
            ->where('creator_type', 'restaurant_user')
            ->where('creator_id', $restaurantUserId)
            ->latest()
            ->limit(5)
            ->get();

        $featured = $this->featuredPageCards($cms);
        $legal = $this->legalPageCards($cms);

        return view('restaurant.help.index', compact(
            'openTickets',
            'recentTickets',
            'featured',
            'legal',
        ));
    }

    public function contact(): View
    {
        $contact = [
            'support_email' => site_setting('support_email'),
            'site_email' => site_setting('site_email'),
            'site_phone' => site_setting('site_phone'),
            'site_address' => site_setting('site_address'),
            'platform_name' => site_name(),
        ];

        return view('restaurant.help.contact', compact('contact'));
    }

    public function page(string $slug, CmsStaticPageService $cms): View
    {
        abort_unless(
            $this->isRestaurantHelpSlug($slug),
            404
        );

        $page = $cms->resolvePage($slug);
        $definition = $cms->definition($slug);

        return view('restaurant.help.page', compact('page', 'definition', 'slug'));
    }

    private function isRestaurantHelpSlug(string $slug): bool
    {
        if (array_key_exists($slug, config('restaurant_help.featured_pages', []))) {
            return true;
        }

        return in_array($slug, config('restaurant_help.legal_pages', []), true);
    }

    /**
     * @return list<array{slug: string, title: string, description: string, icon: string, active: bool}>
     */
    private function featuredPageCards(CmsStaticPageService $cms): array
    {
        $cards = [];

        foreach (config('restaurant_help.featured_pages', []) as $slug => $meta) {
            if (! $cms->isAllowed($slug)) {
                continue;
            }

            $page = $cms->resolvePage($slug);
            $def = $cms->definition($slug);

            $cards[] = [
                'slug' => $slug,
                'title' => $def['nav_label'] ?? $page->title,
                'description' => $meta['description'] ?? '',
                'icon' => $meta['icon'] ?? 'file-text',
                'active' => (bool) $page->is_active,
            ];
        }

        return $cards;
    }

    /**
     * @return list<array{slug: string, title: string, active: bool}>
     */
    private function legalPageCards(CmsStaticPageService $cms): array
    {
        $cards = [];

        foreach (config('restaurant_help.legal_pages', []) as $slug) {
            if (! $cms->isAllowed($slug)) {
                continue;
            }

            $page = $cms->resolvePage($slug);
            $def = $cms->definition($slug);

            $cards[] = [
                'slug' => $slug,
                'title' => $def['nav_label'] ?? $page->title,
                'active' => (bool) $page->is_active,
            ];
        }

        return $cards;
    }
}
