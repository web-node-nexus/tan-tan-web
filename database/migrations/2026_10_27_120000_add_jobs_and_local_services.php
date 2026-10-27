<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('kind', 16)->default('product')->after('slug');
            $table->index('kind');
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('note');
            $table->string('status', 32)->default('applied');
            $table->timestamps();

            $table->unique(['listing_id', 'user_id']);
            $table->index(['listing_id', 'status']);
        });

        $this->seedCategories();
        $this->seedSamples();
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');

        $ids = Category::query()->whereIn('slug', [
            'jobs', 'job-full-time', 'job-part-time', 'job-data-entry', 'job-sales', 'job-marketing', 'job-other',
            'services', 'service-electrician', 'service-plumber', 'service-carpenter', 'service-packers-movers',
            'service-driver', 'service-maid', 'service-daily-needs',
        ])->pluck('id');
        Listing::query()->whereIn('category_id', $ids)->forceDelete();

        Category::query()->whereIn('parent_id', $ids)->delete();
        Category::query()->whereIn('id', $ids)->delete();

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['kind']);
            $table->dropColumn('kind');
        });
    }

    private function seedCategories(): void
    {
        $jobs = Category::query()->updateOrCreate(
            ['slug' => 'jobs'],
            [
                'parent_id' => null,
                'kind' => 'job',
                'name_en' => 'Jobs',
                'name_hi' => 'जॉब्स',
                'icon' => 'Briefcase',
                'tint' => '#E7F0FF',
                'sort_order' => 7,
                'is_active' => true,
            ],
        );

        $jobChildren = [
            ['slug' => 'job-full-time', 'name_en' => 'Full-time', 'name_hi' => 'फुल-टाइम', 'sort_order' => 1],
            ['slug' => 'job-part-time', 'name_en' => 'Part-time', 'name_hi' => 'पार्ट-टाइम', 'sort_order' => 2],
            ['slug' => 'job-data-entry', 'name_en' => 'Data entry', 'name_hi' => 'डेटा एंट्री', 'sort_order' => 3],
            ['slug' => 'job-sales', 'name_en' => 'Sales', 'name_hi' => 'सेल्स', 'sort_order' => 4],
            ['slug' => 'job-marketing', 'name_en' => 'Marketing', 'name_hi' => 'मार्केटिंग', 'sort_order' => 5],
            ['slug' => 'job-other', 'name_en' => 'Other local jobs', 'name_hi' => 'अन्य स्थानीय नौकरियाँ', 'sort_order' => 6],
        ];

        foreach ($jobChildren as $row) {
            Category::query()->updateOrCreate(
                ['slug' => $row['slug']],
                $row + ['parent_id' => $jobs->id, 'kind' => 'job', 'icon' => 'Briefcase', 'tint' => '#E7F0FF', 'is_active' => true],
            );
        }

        $services = Category::query()->updateOrCreate(
            ['slug' => 'services'],
            [
                'parent_id' => null,
                'kind' => 'service',
                'name_en' => 'Local Services',
                'name_hi' => 'लोकल सर्विसेज',
                'icon' => 'Wrench',
                'tint' => '#FFF4D6',
                'sort_order' => 8,
                'is_active' => true,
            ],
        );

        $serviceChildren = [
            ['slug' => 'service-electrician', 'name_en' => 'Electrician', 'name_hi' => 'इलेक्ट्रीशियन', 'sort_order' => 1],
            ['slug' => 'service-plumber', 'name_en' => 'Plumber', 'name_hi' => 'प्लंबर', 'sort_order' => 2],
            ['slug' => 'service-carpenter', 'name_en' => 'Carpenter', 'name_hi' => 'कारपेंटर', 'sort_order' => 3],
            ['slug' => 'service-packers-movers', 'name_en' => 'Packers & Movers', 'name_hi' => 'पैकर्स एंड मूवर्स', 'sort_order' => 4],
            ['slug' => 'service-driver', 'name_en' => 'Driver', 'name_hi' => 'ड्राइवर', 'sort_order' => 5],
            ['slug' => 'service-maid', 'name_en' => 'Maid', 'name_hi' => 'मेड', 'sort_order' => 6],
            ['slug' => 'service-daily-needs', 'name_en' => 'Daily needs', 'name_hi' => 'अन्य डेली नीड्स', 'sort_order' => 7],
        ];

        foreach ($serviceChildren as $row) {
            Category::query()->updateOrCreate(
                ['slug' => $row['slug']],
                $row + ['parent_id' => $services->id, 'kind' => 'service', 'icon' => 'Wrench', 'tint' => '#FFF4D6', 'is_active' => true],
            );
        }
    }

    private function seedSamples(): void
    {
        $seller = User::query()->where('phone', '9876543210')->first() ?? User::query()->orderBy('id')->first();
        if (! $seller) {
            return;
        }

        $samples = [
            [
                'slug' => 'sales-executive-lucknow-demo',
                'title' => 'Sales executive — full time, Lucknow',
                'description' => 'Local retail brand hiring a full-time sales executive. Field visits in Lucknow, fixed salary plus incentive. Walk-in interview this week. No registration fee.',
                'price' => 18000,
                'category' => 'job-sales',
                'city' => 'Lucknow',
            ],
            [
                'slug' => 'data-entry-part-time-delhi-demo',
                'title' => 'Part-time data entry — work from home',
                'description' => 'Evening data entry for a small accounts team. Hindi and English typing. Laptop required. Monthly payout after 15 days of trial. Apply with your typing speed.',
                'price' => 8000,
                'category' => 'job-data-entry',
                'city' => 'New Delhi',
            ],
            [
                'slug' => 'electrician-kanpur-demo',
                'title' => 'Home electrician — same day visit in Kanpur',
                'description' => 'Fan, switchboard, inverter and wiring repairs. Rate starts at the visit charge. Material extra. Share photos of the fault in chat before booking.',
                'price' => 299,
                'category' => 'service-electrician',
                'city' => 'Kanpur',
            ],
            [
                'slug' => 'packers-pune-demo',
                'title' => 'Packers and movers — local shifting in Pune',
                'description' => '1 BHK and 2 BHK local shifting with packing. Two helpers and a tempo. Quote depends on floor and distance. Share society name in chat.',
                'price' => 4500,
                'category' => 'service-packers-movers',
                'city' => 'Pune',
            ],
        ];

        foreach ($samples as $sample) {
            $category = Category::query()->where('slug', $sample['category'])->first();
            if (! $category) {
                continue;
            }

            $listing = Listing::query()->firstOrCreate(
                ['slug' => $sample['slug']],
                [
                    'user_id' => $seller->id,
                    'category_id' => $category->id,
                    'city_id' => $seller->city_id,
                    'title' => $sample['title'],
                    'description' => $sample['description'],
                    'price' => $sample['price'],
                    'condition' => 'new',
                    'status' => 'active',
                    'location_label' => $sample['city'],
                    'published_at' => now(),
                ],
            );

            if ($listing->images()->count() === 0) {
                ListingImage::query()->create([
                    'listing_id' => $listing->id,
                    'path' => '/images/site/hero-city.jpg',
                    'sort_order' => 1,
                    'is_cover' => true,
                ]);
            }
        }
    }
};
