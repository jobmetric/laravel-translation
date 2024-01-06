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
             * The translatable field is used to store the model of the translation.
             */

            $table->string('locale', 20)->index();
            /**
             * The locale field is used to store the language of the translation.
             * For example, the locale field of the English translation is "en".
             */

            $table->string('key')->index();
            /**
             * The key field is used to store the key of the translation.
             * For example, the key field of the title translation is "title".
             */

            $table->longText('value')->fulltext();
            /**
             * The value field is used to store the value of the translation.
             * For example, the value field of the title translation is "Product title".
             */

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
