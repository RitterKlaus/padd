<?php

use App\Models\Setting;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration screen redirects to login when registration is disabled', function () {
    Setting::setRegistrationEnabled(false);

    $this->get(route('register'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Registration is currently disabled.');
});

test('new users cannot register when registration is disabled', function () {
    Setting::setRegistrationEnabled(false);

    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors(['email' => 'Registration is currently disabled.']);

    $this->assertGuest();
    $this->assertDatabaseEmpty('users');
});

test('register links follow the registration setting', function (string $routeName, bool $enabled) {
    Setting::setRegistrationEnabled($enabled);

    $this->get(route($routeName))
        ->assertInertia(fn (Assert $page) => $page->where('canRegister', $enabled));
})->with(['home', 'login'])->with([true, false]);
