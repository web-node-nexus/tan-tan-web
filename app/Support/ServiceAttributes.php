<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Validation\Rule;

final class ServiceAttributes
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(?Category $category): array
    {
        $slug = $category?->slug;

        return match ($slug) {
            'service-electrician' => self::wrap([
                'work_types' => ['required', 'array', 'min:1'],
                'work_types.*' => ['string', Rule::in(['wiring', 'fan', 'inverter', 'switchboard', 'appliance'])],
                'same_day' => ['required', 'boolean'],
                'experience_years' => ['required', 'integer', 'min:0', 'max:40'],
            ]),
            'service-plumber' => self::wrap([
                'work_types' => ['required', 'array', 'min:1'],
                'work_types.*' => ['string', Rule::in(['leak', 'tap', 'drain', 'bathroom', 'tank'])],
                'same_day' => ['required', 'boolean'],
                'brings_material' => ['required', 'boolean'],
            ]),
            'service-carpenter' => self::wrap([
                'work_types' => ['required', 'array', 'min:1'],
                'work_types.*' => ['string', Rule::in(['repair', 'new_furniture', 'doors', 'modular'])],
                'home_visit' => ['required', 'boolean'],
            ]),
            'service-packers-movers' => self::wrap([
                'move_type' => ['required', Rule::in(['local', 'intercity'])],
                'vehicle' => ['required', Rule::in(['tempo', 'pickup', 'truck'])],
                'helpers' => ['required', 'integer', 'min:1', 'max:8'],
                'packing_included' => ['required', 'boolean'],
            ]),
            'service-driver' => self::wrap([
                'duty' => ['required', Rule::in(['local', 'outstation'])],
                'shift' => ['required', Rule::in(['part_time', 'full_time'])],
                'own_vehicle' => ['required', 'boolean'],
            ]),
            'service-maid' => self::wrap([
                'duties' => ['required', 'array', 'min:1'],
                'duties.*' => ['string', Rule::in(['cleaning', 'cooking', 'babysitting', 'elderly'])],
                'stay' => ['required', Rule::in(['live_out', 'live_in'])],
                'timing' => ['required', Rule::in(['morning', 'full_day'])],
            ]),
            'service-daily-needs' => self::wrap([
                'need' => ['required', Rule::in(['laundry', 'grocery', 'tuition', 'pest_control', 'other'])],
                'available_today' => ['required', 'boolean'],
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
            'service-electrician' => ['work_types' => ['wiring', 'fan'], 'same_day' => true, 'experience_years' => 6],
            'service-plumber' => ['work_types' => ['leak', 'drain'], 'same_day' => true, 'brings_material' => false],
            'service-carpenter' => ['work_types' => ['repair', 'doors'], 'home_visit' => true],
            'service-packers-movers' => ['move_type' => 'local', 'vehicle' => 'tempo', 'helpers' => 3, 'packing_included' => true],
            'service-driver' => ['duty' => 'local', 'shift' => 'full_time', 'own_vehicle' => true],
            'service-maid' => ['duties' => ['cleaning', 'cooking'], 'stay' => 'live_out', 'timing' => 'morning'],
            'service-daily-needs' => ['need' => 'laundry', 'available_today' => true],
        ];
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
