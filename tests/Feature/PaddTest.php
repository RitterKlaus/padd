<?php

use App\Enums\LcarsTheme;
use App\Models\Handout;
use App\Models\User;
use App\Services\QrCodeGenerator;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->publicPath = sys_get_temp_dir().'/padd-'.uniqid();
    File::ensureDirectoryExists($this->publicPath.'/LCARS');
    File::put($this->publicPath.'/LCARS/classic.html', <<<'HTML'
        <html><head><title>Classic</title><link href="assets/classic.css"></head><body>
        <button onclick="playSoundAndRedirect('audio2', '')">LCARS</button>
        <main><h1>Hello</h1></main>
        <footer>Content &copy; 2026 *replace this text* <br> LCARS Inspired Website Template</footer>
        <script src="assets/lcars.js"></script></body></html>
        HTML);
    $this->app->usePublicPath($this->publicPath);
    $this->withoutVite();
});

afterEach(function () {
    File::deleteDirectory($this->publicPath);
});

test('guests see the active handout in the template', function () {
    $user = User::factory()->create();
    Handout::factory()->for($user)->create(['title' => 'Hidden title']);
    Handout::factory()->create(['title' => 'Foreign title', 'is_active' => true]);
    Handout::factory()->for($user)->create([
        'title' => 'Geheime Übertragung',
        'body' => "Line 1\nLine 2\r\n\r\nCosts $1 and \\1",
        'is_active' => true,
    ]);

    $this->get(route('padd.show', $user->padd_token))
        ->assertOk()
        ->assertHeader('Referrer-Policy', 'no-referrer')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('<meta charset="utf-8"><title>Geheime Übertragung</title>', false)
        ->assertSee("<p>Line 1<br />\nLine 2</p>", false)
        ->assertSee('<p>Costs $1 and \\1</p>', false)
        ->assertSee('href="/LCARS/assets/classic.css"', false)
        ->assertSee('src="/LCARS/assets/lcars.js"', false)
        ->assertSee('LCARS Inspired Website Template')
        ->assertDontSee('replace this text')
        ->assertDontSee('Hello')
        ->assertDontSee('Hidden title')
        ->assertDontSee('Foreign title');
});

test('handout content is escaped', function () {
    $handout = Handout::factory()->create([
        'title' => '</title><script>alert(1)</script>',
        'body' => '</main><img src=x onerror=alert(2)>',
        'is_active' => true,
    ]);

    $this->get(route('padd.show', $handout->user->padd_token))
        ->assertOk()
        ->assertDontSee('<script>alert(1)', false)
        ->assertDontSee('<img src=x', false)
        ->assertSee('</main><img src=x onerror=alert(2)>');
});

test('the PADD is on standby without an active handout', function () {
    $handout = Handout::factory()->create();

    $this->get(route('padd.show', $handout->user->padd_token))
        ->assertOk()
        ->assertSee('Standby')
        ->assertDontSee($handout->title);
});

test('unknown tokens are not found', function (string $routeName) {
    $user = User::factory()->create();

    $this->get(route($routeName, 'unknown'))->assertNotFound();
    $this->get(route($routeName, $user->id))->assertNotFound();
})->with(['padd.show', 'padd.version']);

test('a hint is shown when the template cannot be used', function (?string $template) {
    $path = $this->publicPath.'/LCARS/classic.html';
    $template === null ? File::delete($path) : File::put($path, $template);

    $this->get(route('padd.show', User::factory()->create()->padd_token))
        ->assertServiceUnavailable()
        ->assertSee('PADD unavailable');
})->with([
    'missing file' => [null],
    'without main element' => ['<html><head><title>Classic</title></head><body></body></html>'],
]);

test('the version changes whenever the displayed content changes', function () {
    $handout = Handout::factory()->create();
    $other = Handout::factory()->for($handout->user)->create();
    $version = fn (): string => $this->get(route('padd.version', $handout->user->padd_token))
        ->assertOk()
        ->json('version');

    $versions = [$version()];
    $handout->activate();
    $versions[] = $version();
    $handout->update(['body' => 'New text']);
    $versions[] = $version();
    $handout->update(['theme' => LcarsTheme::Voyager]);
    $versions[] = $version();
    $other->activate();
    $versions[] = $version();

    expect(array_unique($versions))->toHaveCount(5)
        ->and($version())->toBe($versions[4]);

    $this->get(route('padd.show', $handout->user->padd_token))->assertSee($versions[4]);
});

test('the dashboard links to the PADD without exposing the token in the user data', function () {
    $user = User::create(['name' => 'Test', 'email' => 'test@example.com', 'password' => 'password']);
    $user->markEmailAsVerified();

    expect($user->padd_token)->toHaveLength(40);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('paddUrl', route('padd.show', $user->padd_token))
            ->where('paddQrCode', app(QrCodeGenerator::class)->dataUri(route('padd.show', $user->padd_token)))
            ->missing('auth.user.padd_token'));
});

test('each handout is shown in its design', function (LcarsTheme $theme) {
    File::put($this->publicPath.'/LCARS/'.$theme->fileName(), "<html><head><title>x</title></head><body>Design {$theme->value}.<main></main></body></html>");
    $handout = Handout::factory()->create(['theme' => $theme, 'is_active' => true]);

    $this->get(route('padd.show', $handout->user->padd_token))
        ->assertOk()
        ->assertSee("Design {$theme->value}.")
        ->assertSee($handout->title);
})->with(LcarsTheme::cases());

test('standby uses the design of the handout created last', function () {
    File::put($this->publicPath.'/LCARS/voyager.html', '<html><head><title>x</title></head><body>Design voyager<main></main></body></html>');
    $handout = Handout::factory()->create(['theme' => LcarsTheme::Voyager]);

    $this->get(route('padd.show', $handout->user->padd_token))
        ->assertOk()
        ->assertSee('Design voyager')
        ->assertSee('Standby');
});
