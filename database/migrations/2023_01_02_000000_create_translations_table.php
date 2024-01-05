<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('translation.tables.translation'), function (Blueprint $table) {
            $table->id();

            $table->morphs('translatable');
            /**
             * translatable for any tables for any data
             */

            $table->string('locale', 20)->index();

            $table->string('title')->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('translation.tables.translation'));
    }
};
