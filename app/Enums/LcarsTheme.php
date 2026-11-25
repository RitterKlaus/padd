<?php

namespace App\Enums;

enum LcarsTheme: string
{
    case Classic = 'classic';

    /**
     * Get the name of the template file in public/LCARS.
     */
    public function fileName(): string
    {
        return $this->value.'.html';
    }
}
