<?php

use App\Enums\LcarsTheme;
use App\Models\Handout;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('handouts.index'))->assertRedirect(route('login'));
});

test('users only see their own handouts', function () {
    $handout = Handout::factory()->create();
    Handout::factory()->create();

    $this->actingAs($handout->user)
        ->get(route('handouts.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('handouts/Index')
            ->where('handouts', [['id' => $handout->id, 'title' => $handout->title, 'is_active' => false]]));
});

test('users can open the form for a new handout', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('handouts.create'))
        ->assertInertia(fn (Assert $page) => $page->component('handouts/Create')
            ->where('defaultTheme', 'classic')
            ->has('themes', 6)
            ->where('themes.0', ['value' => 'classic', 'label' => 'Classic']));
});

test('the design of the own handout created last is preselected', function () {
    $user = User::factory()->create();
    $first = Handout::factory()->for($user)->create(['theme' => LcarsTheme::Voyager]);
    Handout::factory()->for($user)->create(['theme' => LcarsTheme::Picard]);
    Handout::factory()->create(['theme' => LcarsTheme::LowerDecks]);
    $first->update(['title' => 'Edited later']);

    $this->actingAs($user)
        ->get(route('handouts.create'))
        ->assertInertia(fn (Assert $page) => $page->where('defaultTheme', 'picard'));
});

test('users can create a handout with the maximum lengths', function () {
    $user = User::factory()->create();
    $data = ['title' => str_repeat('ä', 300), 'body' => str_repeat('ä', 3000), 'theme' => 'voyager'];

    $this->actingAs($user)
        ->post(route('handouts.store'), $data)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('handouts.index'));

    $this->assertDatabaseHas('handouts', ['user_id' => $user->id, ...$data]);
});

test('users can edit their own handout', function () {
    $handout = Handout::factory()->create();

    $this->actingAs($handout->user)
        ->get(route('handouts.edit', $handout))
        ->assertInertia(fn (Assert $page) => $page
            ->component('handouts/Edit')
            ->where('handout', [...$handout->only(['id', 'title', 'body']), 'theme' => 'classic'])
            ->has('themes', 6));

    $this->patch(route('handouts.update', $handout), ['title' => 'New title', 'body' => 'New text', 'theme' => 'picard'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('handouts.index'));

    expect($handout->refresh()->only(['title', 'body', 'theme']))
        ->toBe(['title' => 'New title', 'body' => 'New text', 'theme' => LcarsTheme::Picard]);
});

test('users can delete their own handout', function () {
    $handout = Handout::factory()->create();

    $this->actingAs($handout->user)
        ->delete(route('handouts.destroy', $handout))
        ->assertRedirect(route('handouts.index'));

    $this->assertModelMissing($handout);
});

test('other users, including the admin, cannot access a handout', function (int $userId) {
    $handout = Handout::factory()->for(User::factory()->state(['id' => 10]))->create();

    $this->actingAs(User::factory()->create(['id' => $userId]));

    $this->get(route('handouts.edit', $handout))->assertNotFound();
    $this->patch(route('handouts.update', $handout), ['title' => 'New title', 'body' => 'New text', 'theme' => 'classic'])->assertNotFound();
    $this->delete(route('handouts.destroy', $handout))->assertNotFound();

    $this->assertDatabaseHas('handouts', $handout->only(['id', 'user_id', 'title', 'body']));
})->with([2, User::ADMIN_ID]);

test('invalid handouts are rejected', function (array $override, string $field, string $message) {
    app()->setLocale('en');

    $handout = Handout::factory()->create();
    $data = ['title' => 'New title', 'body' => 'New text', 'theme' => 'classic', ...$override];

    $this->actingAs($handout->user);

    $this->post(route('handouts.store'), $data)->assertSessionHasErrors([$field => $message]);
    $this->patch(route('handouts.update', $handout), $data)->assertSessionHasErrors([$field => $message]);

    $this->assertDatabaseCount('handouts', 1);
    $this->assertDatabaseHas('handouts', $handout->only(['id', 'title', 'body']));
})->with([
    'missing title' => [['title' => ''], 'title', 'The title field is required.'],
    'long title' => [['title' => str_repeat('a', 301)], 'title', 'The title field must not be greater than 300 characters.'],
    'missing body' => [['body' => ''], 'body', 'The body field is required.'],
    'missing design' => [['theme' => ''], 'theme', 'The theme field is required.'],
    'unknown design' => [['theme' => '../classic'], 'theme', 'The selected theme is invalid.'],
    'long body' => [['body' => str_repeat('a', 3001)], 'body', 'The body field must not be greater than 3000 characters.'],
]);

test('handouts are deleted together with their user', function () {
    $handout = Handout::factory()->create();

    $handout->user->delete();

    $this->assertModelMissing($handout);
});
