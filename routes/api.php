<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\BlockController;
use App\Http\Controllers\Api\V1\BoostController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\FavouriteController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ListingController;
use App\Http\Controllers\Api\V1\PackageController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SavedSearchController;
use App\Http\Controllers\Api\V1\SellerController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class)->name('api.v1.health');

    Route::get('/categories', [CategoryController::class, 'index'])->name('api.v1.categories.index');
    Route::get('/cities', [CityController::class, 'index'])->name('api.v1.cities.index');
    Route::get('/banners', [BannerController::class, 'index'])->name('api.v1.banners.index');
    Route::get('/packages', [PackageController::class, 'index'])->name('api.v1.packages.index');
    Route::get('/listings', [ListingController::class, 'index'])->name('api.v1.listings.index');
    Route::get('/listings/{listing}', [ListingController::class, 'show'])->name('api.v1.listings.show');
    Route::get('/users/{user}/reviews', [ReviewController::class, 'forUser'])->name('api.v1.reviews.for_user');
    Route::get('/sellers/{user}', [SellerController::class, 'show'])->name('api.v1.sellers.show');

    Route::prefix('auth')->group(function (): void {
        Route::post('/otp/send', [AuthController::class, 'sendOtp'])
            ->middleware('throttle:otp')
            ->name('api.v1.auth.otp.send');

        Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])
            ->middleware('throttle:login')
            ->name('api.v1.auth.otp.verify');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
            Route::patch('/me', [AuthController::class, 'updateProfile'])->name('api.v1.auth.profile');
            Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        });
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me/listings', [ListingController::class, 'mine'])->name('api.v1.listings.mine');
        Route::get('/me/favourites', [FavouriteController::class, 'index'])->name('api.v1.favourites.index');
        Route::get('/me/recently-viewed', [ListingController::class, 'recentlyViewed'])
            ->name('api.v1.listings.recent');
        Route::get('/me/listing-quota', [ListingController::class, 'quota'])->name('api.v1.listings.quota');
        Route::post('/me/listing-quota/purchase', [ListingController::class, 'purchaseExtraSlot'])
            ->middleware('throttle:ad-post')
            ->name('api.v1.listings.quota.purchase');

        Route::post('/listings', [ListingController::class, 'store'])
            ->middleware('throttle:ad-post')
            ->name('api.v1.listings.store');
        Route::match(['put', 'post'], '/listings/{listing}', [ListingController::class, 'update'])
            ->name('api.v1.listings.update');
        Route::delete('/listings/{listing}', [ListingController::class, 'destroy'])->name('api.v1.listings.destroy');
        Route::post('/listings/{listing}/sold', [ListingController::class, 'markSold'])->name('api.v1.listings.sold');
        Route::post('/listings/{listing}/renew', [ListingController::class, 'renew'])->name('api.v1.listings.renew');
        Route::post('/listings/{listing}/reserve', [ListingController::class, 'reserve'])->name('api.v1.listings.reserve');
        Route::get('/listings/{listing}/insights', [ListingController::class, 'insights'])
            ->name('api.v1.listings.insights');
        Route::post('/listings/{listing}/favourite', [ListingController::class, 'toggleFavourite'])
            ->name('api.v1.listings.favourite');
        Route::post('/listings/{listing}/boost', [BoostController::class, 'store'])->name('api.v1.listings.boost');

        Route::post('/sellers/{user}/follow', [SellerController::class, 'follow'])->name('api.v1.sellers.follow');
        Route::delete('/sellers/{user}/follow', [SellerController::class, 'unfollow'])->name('api.v1.sellers.unfollow');

        Route::post('/reports', [ReportController::class, 'store'])->name('api.v1.reports.store');
        Route::post('/reviews', [ReviewController::class, 'store'])->name('api.v1.reviews.store');

        Route::get('/blocks', [BlockController::class, 'index'])->name('api.v1.blocks.index');
        Route::post('/blocks', [BlockController::class, 'store'])->name('api.v1.blocks.store');
        Route::delete('/blocks/{user}', [BlockController::class, 'destroy'])->name('api.v1.blocks.destroy');

        Route::get('/saved-searches', [SavedSearchController::class, 'index'])->name('api.v1.saved_searches.index');
        Route::post('/saved-searches', [SavedSearchController::class, 'store'])->name('api.v1.saved_searches.store');
        Route::delete('/saved-searches/{savedSearch}', [SavedSearchController::class, 'destroy'])
            ->name('api.v1.saved_searches.destroy');

        Route::get('/conversations', [ConversationController::class, 'index'])->name('api.v1.conversations.index');
        Route::post('/conversations', [ConversationController::class, 'store'])->name('api.v1.conversations.store');
        Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('api.v1.conversations.show');
        Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'messages'])
            ->name('api.v1.conversations.messages');
        Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'send'])
            ->middleware('throttle:chat')
            ->name('api.v1.conversations.send');
        Route::post('/conversations/{conversation}/typing', [ConversationController::class, 'typing'])
            ->middleware('throttle:chat')
            ->name('api.v1.conversations.typing');
        Route::post('/conversations/{conversation}/offers', [ConversationController::class, 'offer'])
            ->name('api.v1.conversations.offer');
        Route::post('/offers/{offer}/respond', [ConversationController::class, 'respondOffer'])
            ->name('api.v1.offers.respond');
    });
});
