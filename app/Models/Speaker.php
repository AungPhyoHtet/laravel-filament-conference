<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Speaker extends Model
{
    use HasFactory;

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
