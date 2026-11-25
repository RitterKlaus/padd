<?php

namespace App\Http\Controllers;

use App\Enums\LcarsTheme;
use App\Exceptions\LcarsTemplateException;
use App\Models\Handout;
use App\Models\User;
use App\Services\LcarsRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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
    public function show(User $user, LcarsRenderer $renderer): Response
    {
        $handout = $user->activeHandout();
        $theme = $handout->theme ?? $user->latestTheme();

        try {
            $html = $renderer->render(
                $theme,
                $handout->title ?? 'PADD',
                view('padd.content', ['handout' => $handout])->render(),
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
     * Calculate a hash that changes whenever the displayed content changes.
     */
    private function versionOf(?Handout $handout, LcarsTheme $theme): string
    {
        return hash('sha256', serialize([$theme->value, $handout?->id, $handout?->title, $handout?->body]));
    }
}
