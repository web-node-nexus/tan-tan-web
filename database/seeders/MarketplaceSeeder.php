<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $up = State::query()->firstOrCreate(['code' => 'UP'], ['name' => 'Uttar Pradesh']);
        $mh = State::query()->firstOrCreate(['code' => 'MH'], ['name' => 'Maharashtra']);
        $dl = State::query()->firstOrCreate(['code' => 'DL'], ['name' => 'Delhi']);
        $ka = State::query()->firstOrCreate(['code' => 'KA'], ['name' => 'Karnataka']);

        $cities = collect([
            ['state_id' => $up->id, 'name' => 'Lucknow', 'latitude' => 26.8467, 'longitude' => 80.9462],
            ['state_id' => $up->id, 'name' => 'Kanpur', 'latitude' => 26.4499, 'longitude' => 80.3319],
            ['state_id' => $up->id, 'name' => 'Varanasi', 'latitude' => 25.3176, 'longitude' => 82.9739],
            ['state_id' => $mh->id, 'name' => 'Mumbai', 'latitude' => 19.0760, 'longitude' => 72.8777],
            ['state_id' => $mh->id, 'name' => 'Pune', 'latitude' => 18.5204, 'longitude' => 73.8567],
            ['state_id' => $dl->id, 'name' => 'New Delhi', 'latitude' => 28.6139, 'longitude' => 77.2090],
            ['state_id' => $ka->id, 'name' => 'Bengaluru', 'latitude' => 12.9716, 'longitude' => 77.5946],
        ])->map(fn (array $row) => City::query()->firstOrCreate(
            ['state_id' => $row['state_id'], 'name' => $row['name']],
            $row + ['is_active' => true],
        ));

        $categoryDefs = [
            ['slug' => 'mobiles', 'name_en' => 'Mobiles', 'name_hi' => 'मोबाइल', 'icon' => 'DeviceMobile', 'tint' => '#E8F1FF', 'sort_order' => 1, 'kind' => 'product'],
            ['slug' => 'electronics', 'name_en' => 'Electronics', 'name_hi' => 'इलेक्ट्रॉनिक्स', 'icon' => 'Laptop', 'tint' => '#EAF8F4', 'sort_order' => 2, 'kind' => 'product'],
            ['slug' => 'vehicles', 'name_en' => 'Vehicles', 'name_hi' => 'वाहन', 'icon' => 'Motorcycle', 'tint' => '#FFF3E8', 'sort_order' => 3, 'kind' => 'product'],
            ['slug' => 'furniture', 'name_en' => 'Furniture', 'name_hi' => 'फर्नीचर', 'icon' => 'Armchair', 'tint' => '#F3EEFF', 'sort_order' => 4, 'kind' => 'product'],
            ['slug' => 'fashion', 'name_en' => 'Fashion', 'name_hi' => 'फैशन', 'icon' => 'TShirt', 'tint' => '#FFEAF1', 'sort_order' => 5, 'kind' => 'product'],
            ['slug' => 'properties', 'name_en' => 'Properties', 'name_hi' => 'प्रॉपर्टी', 'icon' => 'House', 'tint' => '#EEF8E8', 'sort_order' => 6, 'kind' => 'product'],
            ['slug' => 'jobs', 'name_en' => 'Jobs', 'name_hi' => 'जॉब्स', 'icon' => 'Briefcase', 'tint' => '#E7F0FF', 'sort_order' => 7, 'kind' => 'job'],
            ['slug' => 'services', 'name_en' => 'Local Services', 'name_hi' => 'लोकल सर्विसेज', 'icon' => 'Wrench', 'tint' => '#FFF4D6', 'sort_order' => 8, 'kind' => 'service'],
        ];

        $categories = collect($categoryDefs)->map(fn (array $row) => Category::query()->updateOrCreate(
            ['slug' => $row['slug']],
            $row + ['is_active' => true, 'parent_id' => null],
        ));

        $childDefs = [
            'jobs' => [
                ['slug' => 'job-full-time', 'name_en' => 'Full-time', 'name_hi' => 'फुल-टाइम'],
                ['slug' => 'job-part-time', 'name_en' => 'Part-time', 'name_hi' => 'पार्ट-टाइम'],
                ['slug' => 'job-data-entry', 'name_en' => 'Data entry', 'name_hi' => 'डेटा एंट्री'],
                ['slug' => 'job-sales', 'name_en' => 'Sales', 'name_hi' => 'सेल्स'],
                ['slug' => 'job-marketing', 'name_en' => 'Marketing', 'name_hi' => 'मार्केटिंग'],
                ['slug' => 'job-other', 'name_en' => 'Other local jobs', 'name_hi' => 'अन्य स्थानीय नौकरियाँ'],
            ],
            'services' => [
                ['slug' => 'service-electrician', 'name_en' => 'Electrician', 'name_hi' => 'इलेक्ट्रीशियन'],
                ['slug' => 'service-plumber', 'name_en' => 'Plumber', 'name_hi' => 'प्लंबर'],
                ['slug' => 'service-carpenter', 'name_en' => 'Carpenter', 'name_hi' => 'कारपेंटर'],
                ['slug' => 'service-packers-movers', 'name_en' => 'Packers & Movers', 'name_hi' => 'पैकर्स एंड मूवर्स'],
                ['slug' => 'service-driver', 'name_en' => 'Driver', 'name_hi' => 'ड्राइवर'],
                ['slug' => 'service-maid', 'name_en' => 'Maid', 'name_hi' => 'मेड'],
                ['slug' => 'service-daily-needs', 'name_en' => 'Daily needs', 'name_hi' => 'अन्य डेली नीड्स'],
            ],
        ];

        foreach ($childDefs as $parentSlug => $children) {
            $parent = $categories->firstWhere('slug', $parentSlug);
            if (! $parent) {
                continue;
            }
            foreach ($children as $index => $child) {
                $categories->push(Category::query()->updateOrCreate(
                    ['slug' => $child['slug']],
                    $child + [
                        'parent_id' => $parent->id,
                        'kind' => $parent->kind,
                        'icon' => $parent->icon,
                        'tint' => $parent->tint,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ],
                ));
            }
        }

        $seller = User::query()->firstOrCreate(
            ['phone' => '9876543210'],
            [
                'name' => 'Demo Seller',
                'email' => '9876543210@phone.tantan.local',
                'password' => Hash::make(Str::random(32)),
                'phone_verified_at' => now(),
                'locale' => 'en',
                'theme' => 'system',
                'is_verified' => true,
                'show_phone' => false,
                'referral_code' => 'DEMOSELL',
                'city_id' => $cities->firstWhere('name', 'Lucknow')?->id,
            ],
        );

        $samples = [
            [
                'title' => 'iPhone 13 128GB Blue — Excellent condition',
                'description' => 'Single owner iPhone 13 with original box and charger. Battery health 89%. No dents, always used with cover and tempered glass. Local meetup preferred in Hazratganj.',
                'price' => 38999,
                'condition' => 'like_new',
                'category' => 'mobiles',
                'city' => 'Lucknow',
                'image' => '/images/listings/iphone.jpg',
                'featured' => true,
            ],
            [
                'title' => 'MacBook Air M1 8/256 — Light office use',
                'description' => '2020 MacBook Air M1, 8GB RAM, 256GB SSD. Perfect for students and WFH. Keyboard backlight working, charger included. Can demo before purchase.',
                'price' => 52000,
                'condition' => 'used',
                'category' => 'electronics',
                'city' => 'Bengaluru',
                'image' => '/images/listings/macbook.jpg',
                'featured' => true,
            ],
            [
                'title' => 'Honda Activa 6G 2022 — Well maintained',
                'description' => '2022 Activa 6G, ~12,000 km, single owner, all papers clear, recent service done. Ideal for daily commute. Test ride available with valid licence.',
                'price' => 68500,
                'condition' => 'used',
                'category' => 'vehicles',
                'city' => 'Kanpur',
                'image' => '/images/listings/scooter.jpg',
                'featured' => false,
            ],
            [
                'title' => '3-seater fabric sofa — Almost new',
                'description' => 'Comfortable 3-seater sofa in beige fabric. Bought 8 months ago, shifting abroad so selling. No stains or tears. Pickup from Indiranagar.',
                'price' => 12500,
                'condition' => 'like_new',
                'category' => 'furniture',
                'city' => 'Bengaluru',
                'image' => '/images/listings/sofa.jpg',
                'featured' => false,
            ],
            [
                'title' => 'Samsung Galaxy S23 256GB Phantom Black',
                'description' => 'Barely used S23 with warranty left. Comes with box, cable, and unused earbuds case. Screen protector applied from day one.',
                'price' => 44999,
                'condition' => 'like_new',
                'category' => 'mobiles',
                'city' => 'Mumbai',
                'image' => '/images/listings/iphone.jpg',
                'featured' => false,
            ],
            [
                'title' => 'Wooden study table with drawer',
                'description' => 'Solid wood study table, 4ft width, one drawer, smooth finish. Perfect for WFH setup. Buyer to arrange transport within city.',
                'price' => 4500,
                'condition' => 'used',
                'category' => 'furniture',
                'city' => 'New Delhi',
                'image' => '/images/listings/sofa.jpg',
                'featured' => false,
            ],
            [
                'title' => 'Sales executive — full time, Lucknow',
                'description' => 'Local retail brand hiring a full-time sales executive. Field visits in Lucknow, fixed salary plus incentive. No registration fee.',
                'price' => 18000,
                'condition' => 'new',
                'category' => 'job-sales',
                'city' => 'Lucknow',
                'image' => '/images/site/hero-city.jpg',
                'featured' => false,
            ],
            [
                'title' => 'Home electrician — same day visit in Kanpur',
                'description' => 'Fan, switchboard and wiring repairs. Visit charge is the starting price. Material extra. Share a photo of the fault in chat.',
                'price' => 299,
                'condition' => 'new',
                'category' => 'service-electrician',
                'city' => 'Kanpur',
                'image' => '/images/site/hero-safe.jpg',
                'featured' => false,
            ],
        ];

        foreach ($samples as $sample) {
            $category = $categories->firstWhere('slug', $sample['category']);
            $city = $cities->firstWhere('name', $sample['city']);
            if (! $category || ! $city) {
                continue;
            }

            $listing = Listing::query()->firstOrCreate(
                ['slug' => Str::slug($sample['title']).'-demo'],
                [
                    'user_id' => $seller->id,
                    'category_id' => $category->id,
                    'city_id' => $city->id,
                    'title' => $sample['title'],
                    'description' => $sample['description'],
                    'price' => $sample['price'],
                    'condition' => $sample['condition'],
                    'status' => 'active',
                    'is_featured' => $sample['featured'],
                    'location_label' => $city->name,
                    'latitude' => $city->latitude,
                    'longitude' => $city->longitude,
                    'published_at' => now()->subHours(rand(2, 72)),
                    'views_count' => rand(20, 400),
                ],
            );

            if ($listing->images()->count() === 0) {
                ListingImage::query()->create([
                    'listing_id' => $listing->id,
                    'path' => $sample['image'],
                    'sort_order' => 1,
                    'is_cover' => true,
                ]);
            }
        }
    }
}
