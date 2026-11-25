<?php

namespace App\Http\Controllers;

use App\Models\Handout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class HandoutActivationController extends Controller
{
    /**
     * Show the handout on the PADD.
     */
    public function update(Handout $handout): RedirectResponse
    {
        Gate::authorize('update', $handout);

        $handout->activate();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Handout is shown on the PADD.')]);

        return to_route('handouts.index');
    }

    /**
     * Hide the handout from the PADD.
     */
    public function destroy(Handout $handout): RedirectResponse
    {
        Gate::authorize('update', $handout);

        $handout->deactivate();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Handout is hidden from the PADD.')]);

        return to_route('handouts.index');
    }
}
