<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TrustController extends Controller
{
    public function index(): View
    {
        return view('site.trust', [
            'title' => 'Trust and contact',
            'description' => 'Verify your identity, open a dealer account, and see how Tan Tan blocks scam ads.',
        ]);
    }
}
