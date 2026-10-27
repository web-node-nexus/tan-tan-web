<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Validation\Rule;

final class JobAttributes
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(?Category $category): array
    {
        return match ($category?->slug) {
            'job-full-time' => self::wrap([
                'experience' => ['required', Rule::in(['fresher', '1_2', '3_5', '5_plus'])],
                'work_mode' => ['required', Rule::in(['office', 'remote', 'hybrid'])],
                'openings' => ['required', 'integer', 'min:1', 'max:50'],
                'education' => ['required', Rule::in(['tenth', 'twelfth', 'graduate', 'any'])],
            ]),
            'job-part-time' => self::wrap([
                'shift' => ['required', Rule::in(['morning', 'evening', 'weekend', 'flexible'])],
                'hours_per_week' => ['required', 'integer', 'min:4', 'max:48'],
                'pay_cycle' => ['required', Rule::in(['weekly', 'monthly'])],
                'openings' => ['required', 'integer', 'min:1', 'max:20'],
            ]),
            'job-data-entry' => self::wrap([
                'work_mode' => ['required', Rule::in(['office', 'home', 'hybrid'])],
                'english' => ['required', Rule::in(['basic', 'fluent'])],
                'computer' => ['required', 'boolean'],
                'openings' => ['required', 'integer', 'min:1', 'max:30'],
            ]),
            'job-sales' => self::wrap([
                'sales_type' => ['required', Rule::in(['field', 'shop', 'phone'])],
                'incentive' => ['required', 'boolean'],
                'experience' => ['required', Rule::in(['fresher', 'experienced'])],
                'target' => ['required', Rule::in(['none', 'weekly', 'monthly'])],
            ]),
            'job-marketing' => self::wrap([
                'channels' => ['required', 'array', 'min:1'],
                'channels.*' => ['string', Rule::in(['social', 'content', 'ads', 'offline'])],
                'work_mode' => ['required', Rule::in(['office', 'remote', 'hybrid'])],
                'experience' => ['required', Rule::in(['fresher', '1_2', '3_plus'])],
            ]),
            'job-other' => self::wrap([
                'workplace' => ['required', Rule::in(['shop', 'home', 'field'])],
                'shift' => ['required', Rule::in(['day', 'night', 'flexible'])],
                'openings' => ['required', 'integer', 'min:1', 'max:20'],
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
            'job-full-time' => ['experience' => '1_2', 'work_mode' => 'office', 'openings' => 2, 'education' => 'graduate'],
            'job-part-time' => ['shift' => 'evening', 'hours_per_week' => 20, 'pay_cycle' => 'monthly', 'openings' => 1],
            'job-data-entry' => ['work_mode' => 'home', 'english' => 'basic', 'computer' => true, 'openings' => 3],
            'job-sales' => ['sales_type' => 'field', 'incentive' => true, 'experience' => 'fresher', 'target' => 'monthly'],
            'job-marketing' => ['channels' => ['social', 'content'], 'work_mode' => 'hybrid', 'experience' => '1_2'],
            'job-other' => ['workplace' => 'shop', 'shift' => 'day', 'openings' => 1],
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
