<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Validation\Rule;

final class BookingDetails
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(?Category $category): array
    {
        return match ($category?->slug) {
            'service-electrician' => self::wrap([
                'issue' => ['required', Rule::in(['wiring', 'fan', 'inverter', 'switchboard', 'appliance'])],
                'urgent' => ['required', 'boolean'],
            ]),
            'service-plumber' => self::wrap([
                'issue' => ['required', Rule::in(['leak', 'tap', 'drain', 'bathroom', 'tank'])],
                'water_off' => ['required', 'boolean'],
            ]),
            'service-carpenter' => self::wrap([
                'job' => ['required', Rule::in(['repair', 'new_furniture', 'doors', 'modular'])],
                'rooms' => ['required', 'integer', 'min:1', 'max:6'],
            ]),
            'service-packers-movers' => self::wrap([
                'from_area' => ['required', 'string', 'min:2', 'max:80'],
                'to_area' => ['required', 'string', 'min:2', 'max:80'],
                'floors' => ['required', 'integer', 'min:0', 'max:30'],
                'lift' => ['required', 'boolean'],
            ]),
            'service-driver' => self::wrap([
                'pickup' => ['required', 'string', 'min:2', 'max:80'],
                'drop' => ['required', 'string', 'min:2', 'max:80'],
                'trip' => ['required', Rule::in(['local', 'outstation'])],
            ]),
            'service-maid' => self::wrap([
                'duty' => ['required', Rule::in(['cleaning', 'cooking', 'babysitting', 'elderly'])],
                'frequency' => ['required', Rule::in(['once', 'weekly', 'monthly'])],
            ]),
            'service-daily-needs' => self::wrap([
                'need' => ['required', Rule::in(['laundry', 'grocery', 'tuition', 'pest_control', 'other'])],
                'today' => ['required', 'boolean'],
            ]),
            default => [
                'details' => ['nullable', 'array'],
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    public static function onlyKnown(?Category $category, array $details): array
    {
        $allowed = [];
        foreach (array_keys(self::rules($category)) as $key) {
            if (! str_starts_with($key, 'details.')) {
                continue;
            }
            $name = explode('.', substr($key, strlen('details.')))[0];
            $allowed[$name] = true;
        }

        return array_intersect_key($details, $allowed);
    }

    /**
     * @return list<array{key: string, type: string, options?: list<string>}>
     */
    public static function form(?Category $category): array
    {
        return match ($category?->slug) {
            'service-electrician' => [
                ['key' => 'issue', 'type' => 'select', 'options' => ['wiring', 'fan', 'inverter', 'switchboard', 'appliance']],
                ['key' => 'urgent', 'type' => 'checkbox'],
            ],
            'service-plumber' => [
                ['key' => 'issue', 'type' => 'select', 'options' => ['leak', 'tap', 'drain', 'bathroom', 'tank']],
                ['key' => 'water_off', 'type' => 'checkbox'],
            ],
            'service-carpenter' => [
                ['key' => 'job', 'type' => 'select', 'options' => ['repair', 'new_furniture', 'doors', 'modular']],
                ['key' => 'rooms', 'type' => 'number'],
            ],
            'service-packers-movers' => [
                ['key' => 'from_area', 'type' => 'text'],
                ['key' => 'to_area', 'type' => 'text'],
                ['key' => 'floors', 'type' => 'number'],
                ['key' => 'lift', 'type' => 'checkbox'],
            ],
            'service-driver' => [
                ['key' => 'pickup', 'type' => 'text'],
                ['key' => 'drop', 'type' => 'text'],
                ['key' => 'trip', 'type' => 'select', 'options' => ['local', 'outstation']],
            ],
            'service-maid' => [
                ['key' => 'duty', 'type' => 'select', 'options' => ['cleaning', 'cooking', 'babysitting', 'elderly']],
                ['key' => 'frequency', 'type' => 'select', 'options' => ['once', 'weekly', 'monthly']],
            ],
            'service-daily-needs' => [
                ['key' => 'need', 'type' => 'select', 'options' => ['laundry', 'grocery', 'tuition', 'pest_control', 'other']],
                ['key' => 'today', 'type' => 'checkbox'],
            ],
            default => [],
        };
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function examples(): array
    {
        return [
            'service-electrician' => ['issue' => 'fan', 'urgent' => true],
            'service-plumber' => ['issue' => 'leak', 'water_off' => true],
            'service-carpenter' => ['job' => 'doors', 'rooms' => 2],
            'service-packers-movers' => ['from_area' => 'Kothrud', 'to_area' => 'Baner', 'floors' => 3, 'lift' => false],
            'service-driver' => ['pickup' => 'Station', 'drop' => 'Airport', 'trip' => 'local'],
            'service-maid' => ['duty' => 'cleaning', 'frequency' => 'weekly'],
            'service-daily-needs' => ['need' => 'laundry', 'today' => true],
        ];
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private static function wrap(array $fields): array
    {
        $rules = ['details' => ['required', 'array']];
        foreach ($fields as $key => $rule) {
            $rules['details.'.$key] = $rule;
        }

        return $rules;
    }
}
