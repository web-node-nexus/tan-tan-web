<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Support\JobAttributes;
use App\Support\ProductAttributes;
use App\Support\ServiceAttributes;
use Illuminate\View\View;

class SellController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()->where('is_active', true)->orderBy('name_en')->get(['id', 'name_en', 'kind', 'slug']);
        $required = [];
        foreach ($categories as $category) {
            $rules = match ($category->kind) {
                'job' => JobAttributes::rules($category),
                'service' => ServiceAttributes::rules($category),
                default => ProductAttributes::rules($category),
            };
            $keys = [];
            foreach ($rules as $field => $rule) {
                if (! is_string($field) || ! str_starts_with($field, 'attributes.') || ! is_array($rule)) {
                    continue;
                }
                if (in_array('required', $rule, true)) {
                    $keys[] = substr($field, 11);
                }
            }
            $required[$category->id] = $keys;
        }

        return view('site.sell', [
            'categories' => $categories,
            'cities' => City::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'required' => $required,
        ]);
    }
}
