<?php

namespace App\Support;

final class CatalogTree
{
    /**
     * Brand => model => variants. An empty variant list means the model has no sub-variant.
     *
     * @return array<string, array<string, array<string, list<string>>>>
     */
    public static function trees(): array
    {
        return [
            'mobiles' => [
                'Apple' => [
                    'iPhone 13' => ['128 GB', '256 GB', '512 GB'],
                    'iPhone 14' => ['128 GB', '256 GB', '512 GB'],
                    'iPhone 15' => ['128 GB', '256 GB', '512 GB'],
                    'iPhone 16' => ['128 GB', '256 GB', '512 GB'],
                ],
                'Samsung' => [
                    'Galaxy A15' => ['128 GB', '256 GB'],
                    'Galaxy A35' => ['128 GB', '256 GB'],
                    'Galaxy A55' => ['128 GB', '256 GB'],
                    'Galaxy S23' => ['128 GB', '256 GB'],
                    'Galaxy S24' => ['128 GB', '256 GB', '512 GB'],
                    'Galaxy M35' => ['128 GB', '256 GB'],
                ],
                'Xiaomi' => [
                    'Redmi Note 12' => ['128 GB', '256 GB'],
                    'Redmi Note 13' => ['128 GB', '256 GB'],
                    'Redmi Note 14' => ['128 GB', '256 GB'],
                ],
                'Vivo' => [
                    'V30' => ['128 GB', '256 GB'],
                    'Y28' => ['128 GB'],
                ],
                'OnePlus' => [
                    'Nord CE 4' => ['128 GB', '256 GB'],
                    'OnePlus 12' => ['256 GB', '512 GB'],
                ],
                'Oppo' => [
                    'Reno 11' => ['128 GB', '256 GB'],
                    'A79' => ['128 GB'],
                ],
                'Realme' => [
                    'Narzo 70' => ['128 GB'],
                    'Realme 12' => ['128 GB', '256 GB'],
                ],
                'Google' => [
                    'Pixel 8' => ['128 GB', '256 GB'],
                    'Pixel 8a' => ['128 GB', '256 GB'],
                ],
                'Nothing' => [
                    'Phone 2a' => ['128 GB', '256 GB'],
                ],
            ],
            'cars' => [
                'Maruti Suzuki' => [
                    'Swift' => ['LXi', 'VXi', 'ZXi'],
                    'Baleno' => ['Sigma', 'Delta', 'Zeta', 'Alpha'],
                    'Wagon R' => ['LXi', 'VXi'],
                    'Brezza' => ['LXi', 'VXi', 'ZXi'],
                    'Alto' => ['LXi', 'VXi'],
                ],
                'Hyundai' => [
                    'Grand i10' => ['Era', 'Magna', 'Sportz', 'Asta'],
                    'i20' => ['Magna', 'Sportz', 'Asta'],
                    'Venue' => ['E', 'S', 'SX'],
                    'Creta' => ['E', 'S', 'SX', 'SX(O)'],
                ],
                'Honda' => [
                    'Amaze' => ['E', 'S', 'VX'],
                    'City' => ['V', 'VX', 'ZX'],
                ],
                'Tata' => [
                    'Punch' => ['Pure', 'Adventure', 'Accomplished'],
                    'Altroz' => ['XE', 'XM', 'XZ'],
                    'Nexon' => ['Smart', 'Creative', 'Fearless'],
                ],
                'Mahindra' => [
                    'Scorpio' => ['S', 'Z'],
                    'XUV700' => ['MX', 'AX'],
                ],
                'Toyota' => [
                    'Innova' => ['G', 'V', 'ZX'],
                    'Fortuner' => ['4x2', '4x4'],
                ],
                'Kia' => [
                    'Sonet' => ['HTE', 'HTK', 'HTX'],
                    'Seltos' => ['HTE', 'HTK', 'HTX', 'GTX'],
                ],
            ],
            'bikes' => [
                'Hero' => [
                    'Splendor Plus' => ['Drum', 'Disc'],
                    'HF Deluxe' => ['Drum', 'Disc'],
                ],
                'Honda' => [
                    'Activa' => ['Drum', 'Disc'],
                    'Shine' => ['Drum', 'Disc'],
                    'Unicorn' => [],
                ],
                'TVS' => [
                    'Jupiter' => ['Drum', 'Disc'],
                    'Apache RTR' => [],
                ],
                'Bajaj' => [
                    'Platina' => ['Drum', 'Disc'],
                    'Pulsar 150' => [],
                ],
                'Royal Enfield' => [
                    'Classic 350' => [],
                    'Hunter 350' => [],
                ],
                'Yamaha' => [
                    'FZ' => [],
                    'R15' => [],
                ],
                'Suzuki' => [
                    'Access' => ['Drum', 'Disc'],
                    'Gixxer' => [],
                ],
                'Ola' => [
                    'S1' => [],
                ],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function words(): array
    {
        $words = [];
        foreach (self::trees() as $brands) {
            foreach ($brands as $brand => $models) {
                self::pushWords($words, $brand);
                foreach ($models as $model => $variants) {
                    self::pushWords($words, $model);
                    foreach ($variants as $variant) {
                        self::pushWords($words, $variant);
                    }
                }
            }
        }

        return array_keys($words);
    }

    /**
     * @param  array<string, true>  $words
     */
    private static function pushWords(array &$words, string $name): void
    {
        foreach (preg_split('/[^a-z0-9]+/i', mb_strtolower($name)) ?: [] as $word) {
            if (is_string($word) && mb_strlen($word) >= 4) {
                $words[$word] = true;
            }
        }
    }
}
