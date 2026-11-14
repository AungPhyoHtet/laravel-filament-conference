<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Speaker extends Model
{
    use HasFactory;

    /**
     * A speaker linked to an account always takes its name and email from that account.
     */
    protected static function booted(): void
    {
        static::saving(function (Speaker $speaker): void {
            if ($speaker->user_id === null) {
                return;
            }

            $speaker->fill($speaker->user()->sole()->only(['name', 'email']));
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'qualifications' => 'array',
        ];
    }

    /**
     * The account the speaker signs in to the speaker panel with, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Conference, $this>
     */
    public function conferences(): BelongsToMany
    {
        return $this->belongsToMany(Conference::class);
    }

    public function talks(): HasMany
    {
        return $this->hasMany(Talk::class);
    }

    /**
     * Uploaded avatar URL, or a generated initials avatar when none is uploaded.
     */
    public function avatarUrl(int $size = 240): string
    {
        if (filled($this->avatar)) {
            return Storage::disk('public')->url($this->avatar);
        }

        return 'https://ui-avatars.com/api/?'.http_build_query([
            'name' => $this->name,
            'background' => '0D8ABC',
            'color' => 'fff',
            'size' => $size,
        ]);
    }

    /**
     * Profile link for the speaker's Twitter / X handle, or null when no handle is set.
     */
    public function twitterUrl(): ?string
    {
        $handle = ltrim(trim((string) $this->twitter_handle), '@');

        return $handle === '' ? null : 'https://x.com/'.rawurlencode($handle);
    }
}
