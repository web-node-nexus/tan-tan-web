<?php

namespace App\Services\Geo;

use App\Models\Area;
use App\Models\City;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

final class GooglePlacesService
{
    /**
     * @return array<string, mixed>|null
     */
    public function reverse(float $lat, float $lng): ?array
    {
        $fromGoogle = $this->reverseGoogle($lat, $lng);

        return $fromGoogle ?? $this->nearestArea($lat, $lng);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function suggest(string $query, ?float $lat, ?float $lng): array
    {
        $seen = [];
        $out = [];
        foreach ([...$this->autocomplete($query, $lat, $lng), ...$this->localMatches($query)] as $row) {
            $key = Str::lower($row['name'].'|'.($row['city_name'] ?? ''));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $row;
        }

        return array_slice($out, 0, 12);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function reverseGoogle(float $lat, float $lng): ?array
    {
        $key = $this->key();
        if ($key === null) {
            return null;
        }

        $body = $this->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => $lat.','.$lng,
            'key' => $key,
            'language' => 'en',
        ]);
        $result = $body['results'][0] ?? null;
        if (! is_array($result)) {
            return null;
        }

        return $this->storeResult($result, $lat, $lng);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function autocomplete(string $query, ?float $lat, ?float $lng): array
    {
        $key = $this->key();
        if ($key === null || mb_strlen($query) < 2) {
            return [];
        }

        $params = [
            'input' => $query,
            'key' => $key,
            'components' => 'country:in',
            'types' => '(regions)',
            'language' => 'en',
        ];
        if ($lat !== null && $lng !== null) {
            $params['location'] = $lat.','.$lng;
            $params['radius'] = 40000;
        }

        $body = $this->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', $params);
        $predictions = array_slice($body['predictions'] ?? [], 0, 4);
        $rows = [];
        foreach ($predictions as $prediction) {
            $placeId = $prediction['place_id'] ?? null;
            if (! is_string($placeId) || $placeId === '') {
                continue;
            }
            $details = $this->get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'place_id,name,geometry,address_components',
                'key' => $key,
                'language' => 'en',
            ]);
            $result = $details['result'] ?? null;
            if (! is_array($result)) {
                continue;
            }
            $stored = $this->storeResult($result, $lat ?? 0, $lng ?? 0);
            if ($stored) {
                $rows[] = $stored;
            }
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>|null
     */
    private function storeResult(array $result, float $fallbackLat, float $fallbackLng): ?array
    {
        $components = is_array($result['address_components'] ?? null) ? $result['address_components'] : [];
        $neighbourhood = $this->component($components, ['neighborhood', 'sublocality_level_1', 'sublocality']);
        $cityName = $this->component($components, ['locality', 'administrative_area_level_2']);
        $name = $neighbourhood ?: (is_string($result['name'] ?? null) ? $result['name'] : $cityName);
        if ($name === null || $name === '') {
            return null;
        }

        $lat = (float) ($result['geometry']['location']['lat'] ?? $fallbackLat);
        $lng = (float) ($result['geometry']['location']['lng'] ?? $fallbackLng);
        if ($lat === 0.0 && $lng === 0.0) {
            return null;
        }

        $city = $cityName
            ? City::query()->where('is_active', true)->whereRaw('lower(name) = ?', [Str::lower($cityName)])->first()
            : null;

        $placeId = is_string($result['place_id'] ?? null) ? $result['place_id'] : null;
        $area = null;
        if ($neighbourhood || $placeId) {
            $area = $placeId
                ? Area::query()->updateOrCreate(
                    ['google_place_id' => $placeId],
                    [
                        'city_id' => $city?->id,
                        'name' => $name,
                        'latitude' => $lat,
                        'longitude' => $lng,
                    ],
                )
                : Area::query()->updateOrCreate(
                    ['city_id' => $city?->id, 'name' => $name],
                    ['latitude' => $lat, 'longitude' => $lng],
                );
        }

        return $this->payload($name, $lat, $lng, $city, $area);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function localMatches(string $query): array
    {
        return Area::query()
            ->with('city:id,name')
            ->where('name', 'like', $query.'%')
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(fn (Area $area): array => $this->payload($area->name, (float) $area->latitude, (float) $area->longitude, $area->city, $area))
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function nearestArea(float $lat, float $lng): ?array
    {
        $area = Area::query()
            ->with('city:id,name')
            ->whereBetween('latitude', [$lat - 0.45, $lat + 0.45])
            ->whereBetween('longitude', [$lng - 0.45, $lng + 0.45])
            ->get()
            ->sortBy(fn (Area $row): float => (($row->latitude - $lat) ** 2) + (($row->longitude - $lng) ** 2))
            ->first();

        if (! $area) {
            return null;
        }

        $km = $this->km($lat, $lng, (float) $area->latitude, (float) $area->longitude);
        if ($km > 40) {
            return null;
        }

        return $this->payload($area->name, (float) $area->latitude, (float) $area->longitude, $area->city, $area);
    }

    /**
     * @param  list<array<string, mixed>>  $components
     * @param  list<string>  $types
     */
    private function component(array $components, array $types): ?string
    {
        foreach ($components as $component) {
            $componentTypes = $component['types'] ?? [];
            if (! is_array($componentTypes)) {
                continue;
            }
            foreach ($types as $type) {
                if (in_array($type, $componentTypes, true) && is_string($component['long_name'] ?? null)) {
                    return $component['long_name'];
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $name, float $lat, float $lng, ?City $city, ?Area $area): array
    {
        $cityName = $city?->name;
        $label = $cityName && ! Str::contains(Str::lower($name), Str::lower($cityName))
            ? $name.', '.$cityName
            : $name;

        return [
            'area_id' => $area?->id,
            'name' => $name,
            'city_id' => $city?->id,
            'city_name' => $cityName,
            'latitude' => $lat,
            'longitude' => $lng,
            'label' => $label,
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function get(string $url, array $query): array
    {
        try {
            $response = Http::timeout(8)->acceptJson()->get($url, $query);
        } catch (Throwable) {
            return [];
        }

        if (! $response->ok()) {
            return [];
        }

        $body = $response->json();
        if (! is_array($body)) {
            return [];
        }

        $status = $body['status'] ?? 'OK';
        if (! in_array($status, ['OK', 'ZERO_RESULTS'], true)) {
            return [];
        }

        return $body;
    }

    private function key(): ?string
    {
        $key = trim((string) config('services.google.maps_key'));

        return $key !== '' ? $key : null;
    }

    private function km(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earth * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
