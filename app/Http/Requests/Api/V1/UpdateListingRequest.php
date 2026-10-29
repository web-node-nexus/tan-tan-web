<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Category;
use App\Models\Listing;
use App\Support\JobAttributes;
use App\Support\ProductAttributes;
use App\Support\ServiceAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('attributes');
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $this->merge(['attributes' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        $routeListing = $this->route('listing');
        $categoryId = $this->input('category_id', $routeListing instanceof Listing ? $routeListing->category_id : null);
        $category = Category::query()->find($categoryId);

        $attributeRules = ! $this->exists('attributes') ? [] : match ($category?->kind) {
            'job' => JobAttributes::rules($category),
            'service' => ServiceAttributes::rules($category),
            'product' => ProductAttributes::rules($category),
            default => ['attributes' => ['nullable', 'array']],
        };

        return $attributeRules + [
            'title' => ['sometimes', 'string', 'min:8', 'max:120'],
            'description' => ['sometimes', 'string', 'min:20', 'max:5000'],
            'price' => ['sometimes', 'numeric', 'min:1', 'max:99999999.99'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'condition' => ['sometimes', Rule::in(Listing::CONDITIONS)],
            'is_negotiable' => ['sometimes', 'boolean'],
            'location_label' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['sometimes', Rule::in(['draft', 'active', 'pending', 'sold'])],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }
}
