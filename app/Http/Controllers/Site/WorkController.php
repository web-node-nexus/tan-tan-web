<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class WorkController extends Controller
{
    public function index(): View
    {
        return view('site.work', [
            'title' => 'Applications and bookings',
            'description' => 'Track job applications and service bookings. Status stays on the request, not on a generic ad.',
        ]);
    }
}
