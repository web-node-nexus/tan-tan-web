<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            'Lucknow' => [
                ['Hazratganj', 26.8508, 80.9462],
                ['Gomti Nagar', 26.8550, 81.0010],
                ['Aliganj', 26.8860, 80.9430],
                ['Indira Nagar', 26.8770, 80.9960],
                ['Aminabad', 26.8460, 80.9200],
            ],
            'Kanpur' => [
                ['Civil Lines', 26.4720, 80.3500],
                ['Swaroop Nagar', 26.4790, 80.3150],
                ['Kakadeo', 26.4765, 80.2910],
            ],
            'Varanasi' => [
                ['Lanka', 25.2820, 82.9990],
                ['Assi', 25.2825, 83.0060],
                ['Sigra', 25.3170, 82.9910],
            ],
            'Mumbai' => [
                ['Andheri', 19.1136, 72.8697],
                ['Bandra', 19.0596, 72.8295],
                ['Dadar', 19.0178, 72.8478],
            ],
            'Pune' => [
                ['Koregaon Park', 18.5362, 73.8939],
                ['Kothrud', 18.5074, 73.8077],
                ['Hinjewadi', 18.5912, 73.7389],
            ],
            'New Delhi' => [
                ['Connaught Place', 28.6315, 77.2167],
                ['Karol Bagh', 28.6514, 77.1907],
                ['Saket', 28.5244, 77.2066],
            ],
            'Bengaluru' => [
                ['Indiranagar', 12.9784, 77.6408],
                ['Koramangala', 12.9352, 77.6245],
                ['Whitefield', 12.9698, 77.7500],
            ],
        ];

        foreach ($areas as $cityName => $rows) {
            $city = City::query()->where('name', $cityName)->first();
            if (! $city) {
                continue;
            }
            foreach ($rows as [$name, $lat, $lng]) {
                Area::query()->updateOrCreate(
                    ['city_id' => $city->id, 'name' => $name],
                    ['latitude' => $lat, 'longitude' => $lng],
                );
            }
        }
    }
}
