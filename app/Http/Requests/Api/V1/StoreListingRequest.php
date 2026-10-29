<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Category;
use App\Models\Listing;
use App\Support\JobAttributes;
use App\Support\ProductAttributes;
use App\Support\ServiceAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreListingRequest extends FormRequest
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
        $category = Category::query()->find($this->input('category_id'));

        $attributeRules = match ($category?->kind) {
            'job' => JobAttributes::rules($category),
            'service' => ServiceAttributes::rules($category),
            'product' => ProductAttributes::rules($category),
            default => ['attributes' => ['nullable', 'array']],
        };

        return $attributeRules + [
            'title' => ['required', 'string', 'min:8', 'max:120'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'price' => ['required', 'numeric', 'min:1', 'max:99999999.99'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'condition' => ['nullable', Rule::in(Listing::CONDITIONS)],
            'is_negotiable' => ['sometimes', 'boolean'],
            'location_label' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['nullable', Rule::in(['draft', 'active', 'pending'])],
            'payment_id' => ['nullable', 'integer', 'exists:listing_payments,id'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:30720'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }
}
