<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuisine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CuisineController extends Controller
{
    public function index(Request $request)
    {
        $cuisines = Cuisine::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search')->trim();
                $q->where('name', 'like', "%{$s}%");
            })
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(admin_listing_per_page())
            ->withQueryString();

        return view('admin.cuisines.index', compact('cuisines'));
    }

    public function create()
    {
        return view('admin.cuisines.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('cuisines', 'public');
        }

        Cuisine::query()->create($data);

        return redirect()->route('admin.cuisines.index')->with('success', 'Cuisine created.');
    }

    public function edit(Cuisine $cuisine)
    {
        return view('admin.cuisines.edit', compact('cuisine'));
    }

    public function update(Request $request, Cuisine $cuisine)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('cuisines', 'public');
        }

        $cuisine->update($data);

        return redirect()->route('admin.cuisines.index')->with('success', 'Cuisine updated.');
    }

    public function destroy(Cuisine $cuisine)
    {
        $cuisine->delete();

        return back()->with('success', 'Cuisine deleted.');
    }
}
