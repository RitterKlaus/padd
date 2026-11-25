<?php

use App\Models\Handout;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
});

function handoutImage(string $name = 'image.png'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, match (pathinfo($name, PATHINFO_EXTENSION)) {
        'png' => base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='),
        'webp' => base64_decode('UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AAAAAA'),
        default => "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xD9",
    });
}

/**
 * @param  array<string, mixed>  $override
 * @return array<string, mixed>
 */
function handoutData(array $override = []): array
{
    return ['title' => 'Title', 'body' => 'Text', 'theme' => 'classic', ...$override];
}

test('users can create a handout with an image', function (string $name) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('handouts.store'), handoutData(['image' => handoutImage($name)]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('handouts.index'));

    $handout = $user->handouts()->sole();

    expect($handout->image_path)->toStartWith("handouts/{$user->id}/");
    Storage::disk('local')->assertExists($handout->image_path);

    $this->get(route('handouts.edit', $handout))
        ->assertInertia(fn (Assert $page) => $page
            ->where('imageUrl', route('handouts.image', ['handout' => $handout, 'v' => $handout->imageVersion()], absolute: false)));
})->with(['image.png', 'image.jpg', 'image.webp']);

test('a new image replaces the previous one', function () {
    $handout = Handout::factory()->create();
    $handout->replaceImage(handoutImage());
    $previous = $handout->image_path;

    $this->actingAs($handout->user)
        ->put(route('handouts.update', $handout), handoutData(['image' => handoutImage('new.jpg'), 'remove_image' => '1']))
        ->assertSessionHasNoErrors();

    Storage::disk('local')->assertMissing($previous);
    Storage::disk('local')->assertExists($handout->refresh()->image_path);
});

test('the image is kept unless it is removed', function () {
    $handout = Handout::factory()->create();
    $handout->replaceImage(handoutImage());
    $path = $handout->image_path;

    $this->actingAs($handout->user);

    $this->put(route('handouts.update', $handout), handoutData(['remove_image' => '0']));
    Storage::disk('local')->assertExists($path);

    $this->put(route('handouts.update', $handout), handoutData(['remove_image' => '1']));
    Storage::disk('local')->assertMissing($path);
    expect($handout->refresh()->image_path)->toBeNull();
});

test('invalid images are rejected', function (Closure $image, string $message) {
    app()->setLocale('en');

    $handout = Handout::factory()->create();
    $handout->replaceImage(handoutImage());

    $this->actingAs($handout->user)
        ->put(route('handouts.update', $handout), handoutData(['image' => $image()]))
        ->assertSessionHasErrors(['image' => $message]);

    Storage::disk('local')->assertExists($handout->refresh()->image_path);
})->with([
    'too large' => [fn () => UploadedFile::fake()->create('image.png', 2049, 'image/png'), 'The image field must not be greater than 2048 kilobytes.'],
    'other type' => [fn () => UploadedFile::fake()->create('image.gif', 1, 'image/gif'), 'The image field must be a file of type: jpg, png, webp.'],
    'disguised file' => [fn () => new UploadedFile(base_path('artisan'), 'image.png', 'image/png', test: true), 'The image field must be a file of type: jpg, png, webp.'],
    'no file' => [fn () => 'text', 'The image field must be a file.'],
]);

test('images are deleted together with the handout or the user', function (string $deleted) {
    $handout = Handout::factory()->create();
    $handout->replaceImage(handoutImage());

    ($deleted === 'handout' ? $handout : $handout->user)->delete();

    Storage::disk('local')->assertMissing($handout->image_path);
})->with(['handout', 'user']);

test('only the owner can open the image of a handout', function () {
    $handout = Handout::factory()->create();
    $handout->replaceImage(handoutImage());
    $url = route('handouts.image', $handout);

    $this->get($url)->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get($url)->assertNotFound();
    $this->actingAs($handout->user)->get($url)
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('the PADD only shows the image of the active handout', function () {
    $handout = Handout::factory()->create();
    $handout->replaceImage(handoutImage());
    $url = route('padd.image', $handout->user->padd_token);

    $this->get($url)->assertNotFound();

    $handout->activate();
    $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/png');

    $handout->removeImage();
    $this->get($url)->assertNotFound();
});
