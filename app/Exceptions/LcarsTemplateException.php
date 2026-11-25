<?php

namespace App\Exceptions;

use RuntimeException;

class LcarsTemplateException extends RuntimeException
{
    public static function missing(string $fileName): self
    {
        return new self("The LCARS template [{$fileName}] is missing in public/LCARS.");
    }

    public static function invalid(string $fileName): self
    {
        return new self("The LCARS template [{$fileName}] has an unexpected structure.");
    }
}
