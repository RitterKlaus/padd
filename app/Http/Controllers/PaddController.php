<?php

namespace App\Http\Controllers;

use App\Enums\LcarsTheme;
use App\Exceptions\LcarsTemplateException;
use App\Models\Handout;
use App\Models\User;
use App\Services\HandoutFormatter;
use App\Services\LcarsRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaddController extends Controller
{
    private const HEADERS = [
        'Cache-Control' => 'no-store',
        'Referrer-Policy' => 'no-referrer',
        'X-Robots-Tag' => 'noindex, nofollow',
    ];

    /**
     * Show the user's active handout in the LCARS design.
     */
    public function show(User $user, LcarsRenderer $renderer, HandoutFormatter $formatter): Response
    {
        $handout = $user->activeHandout();
        $theme = $handout->theme ?? $user->latestTheme();

        try {
            $html = $renderer->render(
                $theme,
                $handout->title ?? 'PADD',
                view('padd.content', [
                    'handout' => $handout,
                    'body' => $formatter->toHtml($handout->body ?? '', $this->imageHtml($user, $handout)),
                ])->render(),
                view('padd.poll', [
                    'versionUrl' => route('padd.version', $user->padd_token, absolute: false),
                    'version' => $this->versionOf($handout, $theme),
                ])->render(),
            );
        } catch (LcarsTemplateException $exception) {
            report($exception);

            return response()->view('padd.unavailable', [], 503, self::HEADERS);
        }

        return response($html, 200, self::HEADERS);
    }

    /**
     * Show the image of the user's active handout.
     */
    public function image(User $user): StreamedResponse
    {
        $handout = $user->activeHandout();

        abort_if($handout === null, 404);

        return $handout->imageResponse(self::HEADERS);
    }

    /**
     * Get the version of the displayed content, so that the PADD notices changes.
     */
    public function version(User $user): JsonResponse
    {
        $handout = $user->activeHandout();
        $theme = $handout->theme ?? $user->latestTheme();

        return response()->json(
            ['version' => $this->versionOf($handout, $theme)],
            headers: self::HEADERS,
        );
    }

    /**
     * Get the image element of the handout as safe HTML.
     */
    private function imageHtml(User $user, ?Handout $handout): ?string
    {
        if ($handout?->image_path === null) {
            return null;
        }

        $url = route('padd.image', ['user' => $user->padd_token, 'v' => $handout->imageVersion()], absolute: false);

        return view('padd.image', ['url' => $url, 'title' => $handout->title])->render();
    }

    /**
     * Calculate a hash that changes whenever the displayed content changes.
     */
    private function versionOf(?Handout $handout, LcarsTheme $theme): string
    {
        return hash('sha256', serialize([$theme->value, $handout?->id, $handout?->title, $handout?->body, $handout?->image_path]));
    }
}
