<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdministrationUpdateRequest;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdministrationController extends Controller
{
    /**
     * Show the administration page.
     */
    public function edit(): Response
    {
        return Inertia::render('Administration', [
            'registrationEnabled' => Setting::registrationEnabled(),
            'users' => User::query()->orderBy('id')->get(['id', 'email']),
        ]);
    }

    /**
     * Update the administration settings.
     */
    public function update(AdministrationUpdateRequest $request): RedirectResponse
    {
        Setting::setRegistrationEnabled($request->boolean('registration_enabled'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Settings updated.')]);

        return to_route('administration.edit');
    }
}
