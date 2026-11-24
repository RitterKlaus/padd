<?php

use App\Models\Setting;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('administration.edit'))->assertRedirect(route('login'));
});

test('the admin can visit the administration page', function () {
    $this->actingAs(User::factory()->create(['id' => User::ADMIN_ID]))
        ->get(route('administration.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Administration')
            ->where('auth.isAdmin', true)
            ->where('registrationEnabled', true));
});

test('other users have no access to the administration', function () {
    $this->actingAs(User::factory()->create(['id' => 2]));

    $this->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.isAdmin', false));
    $this->get(route('administration.edit'))->assertForbidden();
    $this->patch(route('administration.update'), ['registration_enabled' => false])->assertForbidden();

    expect(Setting::registrationEnabled())->toBeTrue();
});

test('the admin can toggle registration', function () {
    $this->actingAs(User::factory()->create(['id' => User::ADMIN_ID]));

    foreach ([false, true] as $enabled) {
        $this->patch(route('administration.update'), ['registration_enabled' => $enabled])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('administration.edit'));

        expect(Setting::registrationEnabled())->toBe($enabled);
    }
});

test('the registration setting must be a boolean', function (mixed $value) {
    $this->actingAs(User::factory()->create(['id' => User::ADMIN_ID]))
        ->patch(route('administration.update'), ['registration_enabled' => $value])
        ->assertSessionHasErrors('registration_enabled');

    expect(Setting::registrationEnabled())->toBeTrue();
})->with([null, 'invalid']);
