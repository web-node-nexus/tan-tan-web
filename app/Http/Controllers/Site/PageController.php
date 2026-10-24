<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('site.home', [
            'title' => 'Buy & sell near you',
            'description' => 'Tan Tan — India\'s friendly classifieds marketplace. Post free ads, chat safely, and discover deals nearby.',
        ]);
    }

    public function about(): View
    {
        return view('site.about', [
            'title' => 'About Tan Tan',
            'description' => 'Learn why Tan Tan exists, who we serve, and how we make buying and selling near you simpler and safer.',
        ]);
    }

    public function howItWorks(): View
    {
        return view('site.how-it-works', [
            'title' => 'How Tan Tan works',
            'description' => 'From OTP signup to posting ads, chatting, and marking sold — the complete Tan Tan walkthrough.',
        ]);
    }

    public function safetyTips(): View
    {
        return view('site.safety', [
            'title' => 'Safety tips',
            'description' => 'Practical safety advice for buyers and sellers on Tan Tan — meetings, payments, scams, and reporting.',
        ]);
    }

    public function packages(): View
    {
        return view('site.packages', [
            'title' => 'Boost packages & pricing',
            'description' => 'Compare Tan Tan bump, featured and top-ad packages. Sell faster with more visibility.',
        ]);
    }

    public function faq(): View
    {
        return view('site.faq', [
            'title' => 'Frequently asked questions',
            'description' => 'Detailed answers about accounts, ads, chat, payments, privacy, and support on Tan Tan.',
        ]);
    }

    public function contact(): View
    {
        return view('site.contact', [
            'title' => 'Contact Tan Tan',
            'description' => 'Contact Tan Tan support for account help, listing issues, partnerships, or press.',
        ]);
    }

    public function contactSubmit(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:20'],
            'topic' => ['required', 'string', 'max:80'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // Persisted to support_tickets in Phase 3 admin. For now, acknowledge receipt.
        return back()->with('success', 'Thanks '.$request->string('name').'! Your message was received. Our team typically replies within 1–2 business days.');
    }

    public function terms(): View
    {
        return view('site.terms', [
            'title' => 'Terms of use',
            'description' => 'Terms of use governing your access to the Tan Tan marketplace app and website.',
        ]);
    }

    public function privacy(): View
    {
        return view('site.privacy', [
            'title' => 'Privacy policy',
            'description' => 'How Tan Tan collects, uses, stores and shares personal data, and your rights as a user in India.',
        ]);
    }

    public function deleteAccount(): View
    {
        return view('site.delete-account', [
            'title' => 'Delete your account',
            'description' => 'Step-by-step guide to permanently delete your Tan Tan account and related data.',
        ]);
    }

    public function download(): View
    {
        return view('site.download', [
            'title' => 'Download Tan Tan',
            'description' => 'Download Tan Tan for Android and iOS. Start buying and selling near you today.',
        ]);
    }

    public function categories(): View
    {
        return view('site.categories', [
            'title' => 'Browse categories',
            'description' => 'Explore Tan Tan categories — vehicles, property, mobiles, electronics, furniture, fashion, jobs, services and more.',
        ]);
    }

    public function forSellers(): View
    {
        return view('site.for-sellers', [
            'title' => 'Sell on Tan Tan',
            'description' => 'A practical guide for sellers — photos, pricing, chat etiquette, and how to close deals faster.',
        ]);
    }

    public function forBuyers(): View
    {
        return view('site.for-buyers', [
            'title' => 'Buy on Tan Tan',
            'description' => 'How to search, filter, chat, make offers and buy safely on Tan Tan.',
        ]);
    }
}
