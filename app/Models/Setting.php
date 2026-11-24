<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $key
 * @property mixed $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    private const REGISTRATION_ENABLED = 'registration_enabled';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Determine whether new users may register (allowed unless disabled).
     */
    public static function registrationEnabled(): bool
    {
        return self::find(self::REGISTRATION_ENABLED)?->value ?? true;
    }

    /**
     * Enable or disable the registration of new users.
     */
    public static function setRegistrationEnabled(bool $enabled): void
    {
        self::updateOrCreate(['key' => self::REGISTRATION_ENABLED], ['value' => $enabled]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }
}
