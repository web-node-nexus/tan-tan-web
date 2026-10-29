<?php

namespace Database\Seeders;

use App\Models\CatalogNode;
use App\Models\Category;
use App\Support\CatalogTree;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $children = [
            'mobiles' => [
                ['mobile-phones', 'Mobile phones', 'मोबाइल फ़ोन'],
                ['mobile-tablets', 'Tablets', 'टैबलेट'],
                ['mobile-accessories', 'Accessories', 'एक्सेसरीज़'],
            ],
            'vehicles' => [
                ['vehicle-cars', 'Cars', 'कार'],
                ['vehicle-bikes', 'Bikes', 'बाइक'],
                ['vehicle-spare', 'Spare parts', 'स्पेयर पार्ट्स'],
            ],
            'properties' => [
                ['property-apartments', 'Apartments', 'अपार्टमेंट'],
                ['property-houses', 'Houses', 'मकान'],
                ['property-plots', 'Plots', 'प्लॉट'],
            ],
            'electronics' => [
                ['electronics-laptops', 'Laptops', 'लैपटॉप'],
                ['electronics-tv', 'TVs', 'टीवी'],
                ['electronics-appliances', 'Appliances', 'उपकरण'],
            ],
            'furniture' => [
                ['furniture-sofa', 'Sofa', 'सोफा'],
                ['furniture-bed', 'Bed', 'बेड'],
                ['furniture-tables', 'Tables', 'मेज़'],
            ],
            'fashion' => [
                ['fashion-men', 'Men', 'पुरुष'],
                ['fashion-women', 'Women', 'महिला'],
                ['fashion-kids', 'Kids', 'बच्चे'],
            ],
        ];

        foreach ($children as $parentSlug => $rows) {
            $parent = Category::query()->where('slug', $parentSlug)->first();
            if (! $parent) {
                continue;
            }
            foreach ($rows as $index => [$slug, $en, $hi]) {
                Category::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'parent_id' => $parent->id,
                        'kind' => $parent->kind ?? 'product',
                        'name_en' => $en,
                        'name_hi' => $hi,
                        'icon' => $parent->icon,
                        'tint' => $parent->tint,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ],
                );
            }
        }

        foreach (CatalogTree::trees() as $root => $brands) {
            $brandOrder = 0;
            foreach ($brands as $brand => $models) {
                $brandNode = CatalogNode::query()->updateOrCreate(
                    ['root' => $root, 'parent_id' => null, 'name' => $brand],
                    ['sort_order' => ++$brandOrder],
                );
                $modelOrder = 0;
                foreach ($models as $model => $variants) {
                    $modelNode = CatalogNode::query()->updateOrCreate(
                        ['root' => $root, 'parent_id' => $brandNode->id, 'name' => $model],
                        ['sort_order' => ++$modelOrder],
                    );
                    $variantOrder = 0;
                    foreach ($variants as $variant) {
                        CatalogNode::query()->updateOrCreate(
                            ['root' => $root, 'parent_id' => $modelNode->id, 'name' => $variant],
                            ['sort_order' => ++$variantOrder],
                        );
                    }
                }
            }
        }
    }
}
