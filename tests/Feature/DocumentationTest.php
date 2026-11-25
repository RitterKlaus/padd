<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('documentation'))->assertRedirect(route('login'));
});

test('users can read the documentation', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('documentation'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Documentation'));
});
