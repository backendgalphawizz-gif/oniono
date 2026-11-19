<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use App\Services\CmsStaticPageService;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    public function index(CmsStaticPageService $cms)
    {
        return view('admin.settings.cms.index', [
            'pages' => $cms->orderedPages(),
        ]);
    }

    public function edit(string $slug, CmsStaticPageService $cms)
    {
        if (! $cms->isAllowed($slug)) {
            abort(404);
        }

        $cms->ensurePagesExist();
        $staticPage = StaticPage::query()->where('slug', $slug)->firstOrFail();
        $definition = $cms->definition($slug);

        return view('admin.settings.cms.edit', compact('staticPage', 'definition'));
    }

    public function update(Request $request, string $slug, CmsStaticPageService $cms)
    {
        if (! $cms->isAllowed($slug)) {
            abort(404);
        }

        $staticPage = StaticPage::query()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $staticPage->update([
            'title' => $data['title'],
            'content' => $data['content'] ?? '',
            'is_active' => $request->boolean('is_active', true),
        ]);

        $label = $cms->definition($slug)['nav_label'] ?? $staticPage->title;

        return redirect()
            ->route('admin.settings.cms.index')
            ->with('success', $label.' updated.');
    }
}
