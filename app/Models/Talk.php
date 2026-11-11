<?php

namespace App\Models;

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Talk extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'speaker_id' => 'integer',
            'new_talk' => 'boolean',
            'status' => TalkStatus::class,
            'length' => TalkLength::class,
        ];
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    public function conferences(): BelongsToMany
    {
        return $this->belongsToMany(Conference::class);
    }

    /**
     * The rich text abstract as plain text, with block elements separated by spaces.
     */
    public function plainAbstract(): string
    {
        $text = preg_replace('/<\/(p|li|blockquote|h[1-6])>|<br\s*\/?>/i', ' ', (string) $this->abstract);

        return Str::squish(html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5));
    }

    public function isApproved(): bool
    {
        return $this->status === TalkStatus::Approved;
    }

    public function approve(): void
    {
        $this->update(['status' => TalkStatus::Approved]);
    }

    public function isRejected(): bool
    {
        return $this->status === TalkStatus::Rejected;
    }

    public function reject(): void
    {
        $this->update(['status' => TalkStatus::Rejected]);
    }
}
