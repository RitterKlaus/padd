<?php

namespace App\Models;

use App\Enums\LcarsTheme;
use Database\Factories\HandoutFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $body
 * @property LcarsTheme $theme
 * @property string|null $image_path
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'body', 'theme'])]
class Handout extends Model
{
    /** @use HasFactory<HandoutFactory> */
    use HasFactory;

    protected $attributes = [
        'theme' => LcarsTheme::Classic->value,
    ];

    /**
     * The private disk on which the images are stored.
     */
    private const IMAGE_DISK = 'local';

    /**
     * Delete the image together with its handout.
     */
    protected static function booted(): void
    {
        static::deleted(function (Handout $handout): void {
            self::deleteImageFile($handout->image_path);
        });
    }

    /**
     * Delete the images of all handouts of a user.
     */
    public static function deleteImagesOf(User $user): void
    {
        Storage::disk(self::IMAGE_DISK)->deleteDirectory('handouts/'.$user->id);
    }

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
     * Store the image and delete the previous one.
     */
    public function replaceImage(UploadedFile $image): void
    {
        $path = $image->store('handouts/'.$this->user_id, self::IMAGE_DISK);

        if ($path === false) {
            throw new RuntimeException('The image of the handout could not be stored.');
        }

        $previous = $this->image_path;
        $this->image_path = $path;
        $this->save();

        self::deleteImageFile($previous);
    }

    /**
     * Remove the image from the handout.
     */
    public function removeImage(): void
    {
        $previous = $this->image_path;
        $this->image_path = null;
        $this->save();

        self::deleteImageFile($previous);
    }

    /**
     * Get a short version of the image for URLs, so that browsers notice a new image.
     */
    public function imageVersion(): string
    {
        return substr(hash('sha256', (string) $this->image_path), 0, 12);
    }

    /**
     * Create a response with the image.
     *
     * @param  array<string, string>  $headers
     */
    public function imageResponse(array $headers = []): StreamedResponse
    {
        $disk = Storage::disk(self::IMAGE_DISK);

        abort_if($this->image_path === null || ! $disk->exists($this->image_path), 404);

        return $disk->response($this->image_path, headers: [
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
            ...$headers,
        ]);
    }

    /**
     * Delete an image file that is no longer used.
     */
    private static function deleteImageFile(?string $path): void
    {
        if ($path !== null) {
            Storage::disk(self::IMAGE_DISK)->delete($path);
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'theme' => LcarsTheme::class,
            'is_active' => 'boolean',
        ];
    }
}
