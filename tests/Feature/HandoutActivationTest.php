<?php

use App\Models\Handout;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->put(route('handouts.activation.update', Handout::factory()->create()))
        ->assertRedirect(route('login'));
});

test('activating a handout replaces the active one of the same user only', function () {
    $user = User::factory()->create();
    $previous = Handout::factory()->for($user)->create(['is_active' => true]);
    $next = Handout::factory()->for($user)->create();
    $foreign = Handout::factory()->create(['is_active' => true]);

    $this->actingAs($user)
        ->put(route('handouts.activation.update', $next))
        ->assertRedirect(route('handouts.index'));

    expect($previous->refresh()->is_active)->toBeFalse()
        ->and($next->refresh()->is_active)->toBeTrue()
        ->and($foreign->refresh()->is_active)->toBeTrue();
});

test('users can hide the active handout', function () {
    $handout = Handout::factory()->create(['is_active' => true]);

    $this->actingAs($handout->user)
        ->delete(route('handouts.activation.destroy', $handout))
        ->assertRedirect(route('handouts.index'));

    expect($handout->refresh()->is_active)->toBeFalse();
});

test('other users cannot change the activation', function () {
    $active = Handout::factory()->create(['is_active' => true]);
    $inactive = Handout::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->delete(route('handouts.activation.destroy', $active))->assertNotFound();
    $this->put(route('handouts.activation.update', $inactive))->assertNotFound();

    expect($active->refresh()->is_active)->toBeTrue()
        ->and($inactive->refresh()->is_active)->toBeFalse();
});
