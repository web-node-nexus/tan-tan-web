<?php

use App\Http\Controllers\Site\PageController;
use Illuminate\Support\Facades\Route;

Route::controller(PageController::class)->group(function (): void {
    Route::get('/', 'home')->name('site.home');
    Route::get('/about', 'about')->name('site.about');
    Route::get('/how-it-works', 'howItWorks')->name('site.how');
    Route::get('/safety-tips', 'safetyTips')->name('site.safety');
    Route::get('/packages', 'packages')->name('site.packages');
    Route::get('/faq', 'faq')->name('site.faq');
    Route::get('/contact', 'contact')->name('site.contact');
    Route::post('/contact', 'contactSubmit')->name('site.contact.submit');
    Route::get('/terms', 'terms')->name('site.terms');
    Route::get('/privacy', 'privacy')->name('site.privacy');
    Route::get('/delete-account', 'deleteAccount')->name('site.delete-account');
    Route::get('/download', 'download')->name('site.download');
    Route::get('/categories', 'categories')->name('site.categories');
    Route::get('/for-sellers', 'forSellers')->name('site.sellers');
    Route::get('/for-buyers', 'forBuyers')->name('site.buyers');
});
