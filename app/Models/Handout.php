<?php

namespace App\Models;

use App\Enums\LcarsTheme;
use Database\Factories\HandoutFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $body
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'body'])]
class Handout extends Model
{
    /** @use HasFactory<HandoutFactory> */
    use HasFactory;

    /**
     * Get the user who owns the handout.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Show this handout, and no other one of the same user, on the PADD.
     */
    public function activate(): void
    {
        DB::transaction(function (): void {
            $user = $this->user()->lockForUpdate()->firstOrFail();

            $user->handouts()->where('is_active', true)->update(['is_active' => false]);
            $user->handouts()->whereKey($this->id)->update(['is_active' => true]);
        });

        $this->refresh();
    }

    /**
     * Hide this handout from the PADD.
     */
    public function deactivate(): void
    {
        $this->is_active = false;
        $this->save();
    }

    /**
     * Get the design in which the handout is displayed.
     */
    public function theme(): LcarsTheme
    {
        return LcarsTheme::Classic;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
