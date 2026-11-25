<?php

namespace App\Services;

use App\Enums\LcarsTheme;
use App\Exceptions\LcarsTemplateException;

/**
 * Fills the separately downloaded LCARS templates in public/LCARS without
 * changing the files. The attribution in their footer must not be removed.
 */
class LcarsRenderer
{
    /**
     * Render the template. The content and script must be safe HTML.
     *
     * @throws LcarsTemplateException
     */
    public function render(LcarsTheme $theme, string $title, string $content, string $script): string
    {
        $fileName = $theme->fileName();
        $path = public_path('LCARS/'.$fileName);

        if (! is_file($path)) {
            throw LcarsTemplateException::missing($fileName);
        }

        $html = (string) file_get_contents($path);

        $html = str_replace(
            ['src="assets/', 'href="assets/'],
            ['src="/LCARS/assets/', 'href="/LCARS/assets/'],
            $html,
        );
        $html = (string) preg_replace('~Content &copy;[^<]*(<br\s*/?>)?~i', '', $html);

        $html = $this->replace('~<title>.*?</title>~is', '<meta charset="utf-8"><title>'.e($title).'</title>', $html, $fileName);
        $html = $this->replace('~</body>~i', $script.'</body>', $html, $fileName);

        return $this->replace('~<main\b[^>]*>.*?</main>~is', '<main style="overflow-wrap: anywhere">'.$content.'</main>', $html, $fileName);
    }

    /**
     * Replace the first match literally, so that "$1" in a handout stays as it is.
     *
     * @throws LcarsTemplateException
     */
    private function replace(string $pattern, string $replacement, string $html, string $fileName): string
    {
        $result = preg_replace_callback($pattern, fn (): string => $replacement, $html, 1, $count);

        if ($result === null || $count !== 1) {
            throw LcarsTemplateException::invalid($fileName);
        }

        return $result;
    }
}
