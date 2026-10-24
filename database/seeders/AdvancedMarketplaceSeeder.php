<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Package;
use Illuminate\Database\Seeder;

class AdvancedMarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Sell faster this week',
                'subtitle' => 'Post clear photos + fair price — buyers nearby are active now.',
                'image_path' => '/images/listings/iphone.jpg',
                'cta_label' => 'Post ad',
                'cta_href' => '/sell',
                'sort_order' => 1,
            ],
            [
                'title' => 'Featured near you',
                'subtitle' => 'Boost your ad to the top of Home for 7 days.',
                'image_path' => '/images/listings/macbook.jpg',
                'cta_label' => 'See packages',
                'cta_href' => '/packages',
                'sort_order' => 2,
            ],
            [
                'title' => 'Safe deals only',
                'subtitle' => 'Meet in public places. Never share OTP or bank OTPs.',
                'image_path' => '/images/listings/scooter.jpg',
                'cta_label' => 'Safety tips',
                'cta_href' => '/safety',
                'sort_order' => 3,
            ],
        ];

        foreach ($banners as $row) {
            Banner::query()->updateOrCreate(
                ['title' => $row['title']],
                $row + ['is_active' => true],
            );
        }

        $packages = [
            [
                'code' => 'boost_3',
                'name_en' => 'Quick Boost',
                'name_hi' => 'क्विक बूस्ट',
                'description_en' => '3 days featured placement on Home.',
                'description_hi' => 'होम पर 3 दिन फीचर्ड।',
                'price' => 99,
                'duration_days' => 3,
                'is_best_value' => false,
                'benefits' => ['Featured badge', 'Home carousel slot', '3 days'],
                'sort_order' => 1,
            ],
            [
                'code' => 'boost_7',
                'name_en' => 'Pro Boost',
                'name_hi' => 'प्रो बूस्ट',
                'description_en' => 'Best value — 7 days top visibility.',
                'description_hi' => 'बेस्ट वैल्यू — 7 दिन टॉप विज़िबिलिटी।',
                'price' => 199,
                'duration_days' => 7,
                'is_best_value' => true,
                'benefits' => ['Featured badge', 'Priority in search', '7 days', 'Seller highlight'],
                'sort_order' => 2,
            ],
            [
                'code' => 'boost_15',
                'name_en' => 'Max Reach',
                'name_hi' => 'मैक्स रीच',
                'description_en' => '15 days premium push for high-ticket items.',
                'description_hi' => 'महँगे आइटम के लिए 15 दिन प्रीमियम पुश।',
                'price' => 349,
                'duration_days' => 15,
                'is_best_value' => false,
                'benefits' => ['Featured badge', 'Priority in search', '15 days', 'Category spotlight'],
                'sort_order' => 3,
            ],
        ];

        foreach ($packages as $row) {
            Package::query()->updateOrCreate(
                ['code' => $row['code']],
                $row + ['is_active' => true, 'is_featured_boost' => true],
            );
        }
    }
}
