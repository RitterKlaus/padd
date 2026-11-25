<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the dashboard.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Dashboard', [
            'handoutCount' => $request->user()->handouts()->count(),
            'paddUrl' => route('padd.show', $request->user()->padd_token),
        ]);
    }
}
