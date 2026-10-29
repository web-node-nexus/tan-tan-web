<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Validation\Rule;

final class ProductAttributes
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(?Category $category): array
    {
        return match (self::fieldSlug($category)) {
            'mobiles' => self::wrap([
                'brand' => ['required', Rule::in(['apple', 'samsung', 'xiaomi', 'vivo', 'other'])],
                'storage' => ['required', Rule::in(['64', '128', '256', '512'])],
                'bill' => ['required', 'boolean'],
            ]),
            'mobile-phones' => self::wrap([
                'brand' => ['required', 'string', 'min:2', 'max:40'],
                'model' => ['required', 'string', 'min:2', 'max:60'],
                'variant' => ['nullable', 'string', 'max:40'],
                'bill' => ['required', 'boolean'],
            ]),
            'vehicle-cars' => self::wrap([
                'brand' => ['required', 'string', 'min:2', 'max:40'],
                'model' => ['required', 'string', 'min:2', 'max:60'],
                'variant' => ['nullable', 'string', 'max:40'],
                'year' => ['required', 'integer', 'min:1990', 'max:2027'],
                'km' => ['required', 'integer', 'min:0', 'max:500000'],
                'fuel' => ['required', Rule::in(['petrol', 'diesel', 'cng', 'electric'])],
                'transmission' => ['required', Rule::in(['manual', 'automatic'])],
            ]),
            'vehicle-bikes' => self::wrap([
                'brand' => ['required', 'string', 'min:2', 'max:40'],
                'model' => ['required', 'string', 'min:2', 'max:60'],
                'variant' => ['nullable', 'string', 'max:40'],
                'year' => ['required', 'integer', 'min:1990', 'max:2027'],
                'km' => ['required', 'integer', 'min:0', 'max:500000'],
            ]),
            'electronics' => self::wrap([
                'brand' => ['required', 'string', 'min:2', 'max:40'],
                'warranty' => ['required', 'boolean'],
                'working' => ['required', 'boolean'],
            ]),
            'vehicles' => self::wrap([
                'year' => ['required', 'integer', 'min:1990', 'max:2027'],
                'km' => ['required', 'integer', 'min:0', 'max:500000'],
                'fuel' => ['required', Rule::in(['petrol', 'diesel', 'cng', 'electric'])],
            ]),
            'furniture' => self::wrap([
                'material' => ['required', Rule::in(['wood', 'metal', 'fabric', 'plastic'])],
                'assembly' => ['required', 'boolean'],
            ]),
            'fashion' => self::wrap([
                'size' => ['required', Rule::in(['xs', 's', 'm', 'l', 'xl', 'free'])],
                'gender' => ['required', Rule::in(['men', 'women', 'unisex'])],
            ]),
            'properties' => self::wrap([
                'purpose' => ['required', Rule::in(['rent', 'sale'])],
                'bhk' => ['required', Rule::in(['1', '2', '3', '4'])],
                'furnishing' => ['required', Rule::in(['unfurnished', 'semi', 'full'])],
                'bathrooms' => ['nullable', Rule::in(['1', '2', '3', '4'])],
                'area_sqft' => ['nullable', 'integer', 'min:50', 'max:20000'],
            ]),
            default => [
                'attributes' => ['nullable', 'array'],
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function onlyKnown(?Category $category, array $attributes): array
    {
        $allowed = [];
        foreach (array_keys(self::rules($category)) as $key) {
            if (! str_starts_with($key, 'attributes.')) {
                continue;
            }
            $name = explode('.', substr($key, strlen('attributes.')))[0];
            $allowed[$name] = true;
        }

        return array_intersect_key($attributes, $allowed);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function examples(): array
    {
        return [
            'mobiles' => ['brand' => 'apple', 'storage' => '128', 'bill' => true],
            'electronics' => ['brand' => 'Dell', 'warranty' => false, 'working' => true],
            'vehicles' => ['year' => 2019, 'km' => 32000, 'fuel' => 'petrol'],
            'furniture' => ['material' => 'wood', 'assembly' => true],
            'fashion' => ['size' => 'm', 'gender' => 'unisex'],
            'properties' => ['purpose' => 'rent', 'bhk' => '2', 'furnishing' => 'semi'],
            'mobile-phones' => ['brand' => 'Apple', 'model' => 'iPhone 13', 'variant' => '128 GB', 'bill' => true],
            'vehicle-cars' => ['brand' => 'Maruti Suzuki', 'model' => 'Swift', 'variant' => 'VXi', 'year' => 2020, 'km' => 28000, 'fuel' => 'petrol', 'transmission' => 'manual'],
            'vehicle-bikes' => ['brand' => 'Honda', 'model' => 'Activa', 'variant' => 'Disc', 'year' => 2022, 'km' => 12000],
        ];
    }

    private static function fieldSlug(?Category $category): ?string
    {
        $slug = $category?->slug;
        if ($slug === null) {
            return null;
        }
        if (str_starts_with($slug, 'property-')) {
            return 'properties';
        }
        if (str_starts_with($slug, 'electronics-')) {
            return 'electronics';
        }
        if (str_starts_with($slug, 'furniture-')) {
            return 'furniture';
        }
        if (str_starts_with($slug, 'fashion-')) {
            return 'fashion';
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private static function wrap(array $fields): array
    {
        $rules = ['attributes' => ['required', 'array']];
        foreach ($fields as $key => $rule) {
            $rules['attributes.'.$key] = $rule;
        }

        return $rules;
    }
}
