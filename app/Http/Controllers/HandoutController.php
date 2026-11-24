<?php

namespace App\Http\Controllers;

use App\Http\Requests\HandoutRequest;
use App\Models\Handout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class HandoutController extends Controller
{
    /**
     * Show the user's handouts.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('handouts/Index', [
            'handouts' => $request->user()->handouts()->latest('id')->get(['id', 'title']),
        ]);
    }

    /**
     * Show the form for creating a handout.
     */
    public function create(): Response
    {
        return Inertia::render('handouts/Create');
    }

    /**
     * Store a new handout.
     */
    public function store(HandoutRequest $request): RedirectResponse
    {
        $request->user()->handouts()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Handout created.')]);

        return to_route('handouts.index');
    }

    /**
     * Show the form for editing the handout.
     */
    public function edit(Handout $handout): Response
    {
        Gate::authorize('update', $handout);

        return Inertia::render('handouts/Edit', [
            'handout' => $handout->only(['id', 'title', 'body']),
        ]);
    }

    /**
     * Update the handout.
     */
    public function update(HandoutRequest $request, Handout $handout): RedirectResponse
    {
        Gate::authorize('update', $handout);

        $handout->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Handout updated.')]);

        return to_route('handouts.index');
    }

    /**
     * Delete the handout.
     */
    public function destroy(Handout $handout): RedirectResponse
    {
        Gate::authorize('delete', $handout);

        $handout->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Handout deleted.')]);

        return to_route('handouts.index');
    }
}
