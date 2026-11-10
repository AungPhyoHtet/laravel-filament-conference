<?php

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('talks', function (Blueprint $table) {
            $table->boolean('new_talk')->default(true)->after('abstract');
            $table->string('status')->default(TalkStatus::Submitted->value)->after('new_talk');
            $table->string('length')->default(TalkLength::Normal->value)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('talks', function (Blueprint $table) {
            $table->dropColumn(['new_talk', 'status', 'length']);
        });
    }
};
