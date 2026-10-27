<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Listing;
use App\Services\Listings\ListingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrowseController extends Controller
{
    public function __construct(private readonly ListingService $listings) {}

    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'category_id' => $request->integer('category_id') ?: null,
            'city_id' => $request->integer('city_id') ?: null,
            'kind' => $request->string('kind')->toString(),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'sort' => $request->string('sort')->toString() ?: 'newest',
            'negotiable' => $request->boolean('negotiable') ? '1' : '',
            'price_drop' => $request->boolean('price_drop') ? '1' : '',
            'work_mode' => $request->string('work_mode')->toString(),
            'experience' => $request->string('experience')->toString(),
            'same_day' => $request->boolean('same_day') ? '1' : '',
            'brings_material' => $request->boolean('brings_material') ? '1' : '',
            'home_visit' => $request->boolean('home_visit') ? '1' : '',
            'available_today' => $request->boolean('available_today') ? '1' : '',
            'move_type' => $request->string('move_type')->toString(),
            'verified' => $request->boolean('verified') ? '1' : '',
        ];

        return view('site.browse', [
            'title' => 'Browse ads',
            'description' => 'Search products, jobs and local services on Tan Tan. Filter by city, category and price.',
            'listings' => $this->listings->search(array_filter($filters, fn ($value) => $value !== null && $value !== ''), 12),
            'categories' => Category::query()->whereNull('parent_id')->where('is_active', true)->orderBy('sort_order')->get(),
            'cities' => City::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function show(Listing $listing): View
    {
        abort_unless($listing->status === 'active', 404);

        $listing->load(['category.parent', 'city.state', 'images', 'user']);
        $listing->increment('views_count');

        $similar = Listing::query()
            ->active()
            ->with(['category', 'city', 'images'])
            ->where('category_id', $listing->category_id)
            ->whereKeyNot($listing->id)
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('site.ad', [
            'title' => $listing->title,
            'description' => str($listing->description)->limit(160)->toString(),
            'listing' => $listing,
            'similar' => $similar,
        ]);
    }
}
