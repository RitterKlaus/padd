<?php

namespace App\Http\Controllers;

use App\Services\QrCodeGenerator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the dashboard.
     */
    public function __invoke(Request $request, QrCodeGenerator $qrCodeGenerator): Response
    {
        $paddUrl = route('padd.show', $request->user()->padd_token);

        return Inertia::render('Dashboard', [
            'handoutCount' => $request->user()->handouts()->count(),
            'paddUrl' => $paddUrl,
            'paddQrCode' => $qrCodeGenerator->dataUri($paddUrl),
        ]);
    }
}
