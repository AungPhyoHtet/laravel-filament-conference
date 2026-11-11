<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert plain-text bios into HTML paragraphs so the rich editor can load them.
     */
    public function up(): void
    {
        DB::table('speakers')
            ->where('bio', 'not like', '<%')
            ->orderBy('id')
            ->chunkById(100, function ($speakers): void {
                foreach ($speakers as $speaker) {
                    DB::table('speakers')
                        ->where('id', $speaker->id)
                        ->update(['bio' => self::toParagraphs((string) $speaker->bio)]);
                }
            });
    }

    /**
     * Plain-text bios are not restored, as HTML bios are valid content.
     */
    public function down(): void
    {
        //
    }

    /**
     * Wrap each blank-line-separated block of text in an escaped paragraph.
     */
    private static function toParagraphs(string $text): string
    {
        return collect(preg_split('/\R{2,}/', trim($text)))
            ->filter(fn (string $paragraph): bool => trim($paragraph) !== '')
            ->map(fn (string $paragraph): string => '<p>'.e(trim($paragraph)).'</p>')
            ->implode('');
    }
};
