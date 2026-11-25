<?php

namespace App\Http\Controllers;

use App\Models\Handout;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HandoutImageController extends Controller
{
    /**
     * Show the image of the handout to its owner.
     */
    public function __invoke(Handout $handout): StreamedResponse
    {
        Gate::authorize('update', $handout);

        return $handout->imageResponse();
    }
}
