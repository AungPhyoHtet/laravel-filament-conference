<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert plain-text abstracts into HTML paragraphs so the rich editor can load them.
     */
    public function up(): void
    {
        DB::table('talks')
            ->where('abstract', 'not like', '<%')
            ->orderBy('id')
            ->chunkById(100, function ($talks): void {
                foreach ($talks as $talk) {
                    DB::table('talks')
                        ->where('id', $talk->id)
                        ->update(['abstract' => self::toParagraphs((string) $talk->abstract)]);
                }
            });
    }

    /**
     * Plain-text abstracts are not restored, as HTML abstracts are valid content.
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
