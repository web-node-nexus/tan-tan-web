<?php

/**
 * Client-ready smoke: run 3 times.
 * Usage: php scripts/qa_smoke.php
 */

use App\Models\Category;
use App\Models\City;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$base = rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/').'/api/v1';

function req(string $method, string $url, ?string $token = null, array $json = [], array $multipart = []): array
{
    $ch = curl_init($url);
    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer '.$token;
    }

    if ($multipart) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart);
    } elseif ($json !== []) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
    }

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
    ]);

    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    $data = json_decode((string) $raw, true);

    return [
        'code' => $code,
        'err' => $err,
        'raw' => $raw,
        'json' => is_array($data) ? $data : null,
    ];
}

function assertOk(string $name, array $res, bool $expectSuccess = true): void
{
    $ok = $res['code'] >= 200 && $res['code'] < 300;
    if ($expectSuccess) {
        $ok = $ok && (($res['json']['success'] ?? false) === true);
    }
    $msg = $res['json']['message'] ?? ($res['err'] ?: 'no message');
    echo ($ok ? '[PASS] ' : '[FAIL] ').$name.' HTTP '.$res['code'].' — '.$msg.PHP_EOL;
    if (! $ok) {
        echo '       '.substr((string) $res['raw'], 0, 280).PHP_EOL;
    }
    if (! $ok) {
        $GLOBALS['failures']++;
    } else {
        $GLOBALS['passes']++;
    }
}

$failures = 0;
$passes = 0;

$buyer = User::query()->updateOrCreate(
    ['phone' => '9111111111'],
    ['name' => 'Buyer QA', 'email' => 'buyer.qa@tantan.test', 'password' => bcrypt('x'), 'is_verified' => true],
);
$seller = User::query()->updateOrCreate(
    ['phone' => '9222222222'],
    ['name' => 'Seller QA', 'email' => 'seller.qa@tantan.test', 'password' => bcrypt('x'), 'is_verified' => true],
);
$buyer->tokens()->delete();
$seller->tokens()->delete();
$buyerToken = $buyer->createToken('qa')->plainTextToken;
$sellerToken = $seller->createToken('qa')->plainTextToken;

$cat = Category::query()->firstOrFail();
$city = City::query()->firstOrFail();
$listing = Listing::query()->updateOrCreate(
    ['slug' => 'qa-seller-phone-demo'],
    [
        'user_id' => $seller->id,
        'category_id' => $cat->id,
        'city_id' => $city->id,
        'title' => 'QA Seller iPhone 13 — Test Deal',
        'description' => 'Automated QA listing for chat, favourite, offer, boost and review flows. Excellent condition with box.',
        'price' => 32999,
        'condition' => 'like_new',
        'status' => 'active',
        'location_label' => $city->name,
        'published_at' => now(),
        'is_featured' => false,
    ],
);

echo "QA users buyer={$buyer->id} seller={$seller->id} listing={$listing->id}".PHP_EOL;

assertOk('health', req('GET', $base.'/health'));
assertOk('categories', req('GET', $base.'/categories'));
assertOk('cities', req('GET', $base.'/cities'));
assertOk('listings', req('GET', $base.'/listings?per_page=5'));
assertOk('banners', req('GET', $base.'/banners'));
assertOk('packages', req('GET', $base.'/packages'));
assertOk('me buyer', req('GET', $base.'/auth/me', $buyerToken));
assertOk('me seller', req('GET', $base.'/auth/me', $sellerToken));
assertOk('listing show', req('GET', $base.'/listings/'.$listing->id));
assertOk('favourite on', req('POST', $base.'/listings/'.$listing->id.'/favourite', $buyerToken));
assertOk('favourites list', req('GET', $base.'/me/favourites', $buyerToken));
assertOk('favourite off', req('POST', $base.'/listings/'.$listing->id.'/favourite', $buyerToken));

$chat = req('POST', $base.'/conversations', $buyerToken, [
    'listing_id' => $listing->id,
    'message' => 'Hi QA — is this available?',
]);
assertOk('chat start', $chat);
$convId = $chat['json']['data']['id'] ?? null;

assertOk('chat message', req('POST', $base.'/conversations/'.$convId.'/messages', $buyerToken, [
    'body' => 'Can you share more photos?',
]));

Storage::disk('public')->makeDirectory('qa');
$path = storage_path('app/public/qa/qa.txt');
file_put_contents($path, 'QA attachment body');
$attach = req('POST', $base.'/conversations/'.$convId.'/messages', $buyerToken, [], [
    'body' => 'File attached',
    'attachment' => new CURLFile($path, 'text/plain', 'qa.txt'),
]);
assertOk('chat attachment', $attach);

assertOk('typing', req('POST', $base.'/conversations/'.$convId.'/typing', $buyerToken));
$offer = req('POST', $base.'/conversations/'.$convId.'/offers', $buyerToken, ['amount' => 29500]);
assertOk('offer', $offer);
$offerId = $offer['json']['data']['id'] ?? null;
assertOk('offer accept', req('POST', $base.'/offers/'.$offerId.'/respond', $sellerToken, ['status' => 'accepted']));
assertOk('messages seller', req('GET', $base.'/conversations/'.$convId.'/messages', $sellerToken));
assertOk('inbox', req('GET', $base.'/conversations', $sellerToken));

assertOk('report', req('POST', $base.'/reports', $buyerToken, [
    'listing_id' => $listing->id,
    'reason' => 'spam',
]));
assertOk('review', req('POST', $base.'/reviews', $buyerToken, [
    'reviewee_id' => $seller->id,
    'listing_id' => $listing->id,
    'rating' => 5,
    'comment' => 'Great seller QA',
]));
assertOk('reviews public', req('GET', $base.'/users/'.$seller->id.'/reviews'));
assertOk('save search', req('POST', $base.'/saved-searches', $buyerToken, [
    'name' => 'Phones under 40k',
    'filters' => ['q' => 'iPhone', 'max_price' => 40000, 'sort' => 'price_asc'],
    'notify' => true,
]));
assertOk('saved searches', req('GET', $base.'/saved-searches', $buyerToken));

$packages = req('GET', $base.'/packages');
$pkgId = $packages['json']['data'][0]['id'] ?? null;
assertOk('boost', req('POST', $base.'/listings/'.$listing->id.'/boost', $sellerToken, ['package_id' => $pkgId]));

assertOk('block', req('POST', $base.'/blocks', $buyerToken, ['user_id' => $seller->id]));
assertOk('blocks list', req('GET', $base.'/blocks', $buyerToken));
assertOk('unblock', req('DELETE', $base.'/blocks/'.$seller->id, $buyerToken));

$created = req('POST', $base.'/listings', $sellerToken, [
    'title' => 'QA New Sofa Set Comfort',
    'description' => 'Soft fabric sofa, barely used, pickup from home only. Perfect for small living room.',
    'price' => 12500,
    'category_id' => $cat->id,
    'city_id' => $city->id,
    'condition' => 'used',
]);
assertOk('create listing', $created);
assertOk('mine listings', req('GET', $base.'/me/listings', $sellerToken));

$auth = req('POST', rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/').'/broadcasting/auth', $buyerToken, [
    'socket_id' => '1234.5678',
    'channel_name' => 'private-conversation.'.$convId,
]);
// broadcasting/auth returns auth signature JSON, not ApiResponse envelope
$broadcastOk = $auth['code'] === 200 && isset($auth['json']['auth']);
echo ($broadcastOk ? '[PASS] ' : '[FAIL] ').'broadcasting auth HTTP '.$auth['code'].PHP_EOL;
if (! $broadcastOk) {
    echo '       '.substr((string) $auth['raw'], 0, 280).PHP_EOL;
    $failures++;
} else {
    $passes++;
}

echo PHP_EOL."RESULT passes={$passes} failures={$failures}".PHP_EOL;
exit($failures > 0 ? 1 : 0);

// Extra advanced checks (appended once — guard)
if (!defined('TANTAN_EXTRA_SMOKE')) {
define('TANTAN_EXTRA_SMOKE', true);
}
