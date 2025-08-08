<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $tableName = config('translation.tables.translation');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();

            // Polymorphic relation to translatable models
            $table->morphs('translatable'); // Adds translatable_id + translatable_type + index

            // Language code, e.g. 'en', 'fa'
            $table->string('locale')->index();

            // Field name of the model being translated (e.g. 'title', 'body')
            $table->string('field')->index();

            // The translated value (can be long text)
            $table->text('value')->nullable();

            // Version number of the translation
            $table->unsignedBigInteger('version')->default(1)->index();

            $table->softDeletes()->index();
            $table->timestamps();

            // Prevent duplicate translations for same field/language/model
            $table->unique([
                'translatable_type',
                'translatable_id',
                'locale',
                'field',
                'version'
            ], 'UNIQUE_TRANSLATION');

            // Index for efficient querying by translatable model and locale
            $table->index([
                'translatable_type',
                'translatable_id',
                'locale',
                'field',
                'deleted_at'
            ], 'IDX_TRANSLATION_MODEL_LOCALE_FIELD_DEL');
        });

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            // Index on first 191 characters of value
            DB::statement("CREATE INDEX IDX_TRANSLATION_VALUE_191 ON $tableName (value(191))");

            // Fulltext index for MySQL
            DB::statement("CREATE FULLTEXT INDEX FT_TRANSLATION_VALUE ON $tableName (value)");
        }

        elseif ($driver === 'pgsql') {
            // Add tsvector column for fulltext search
            DB::statement("ALTER TABLE $tableName ADD COLUMN value_vector tsvector");

            // Populate initial data
            DB::statement("UPDATE $tableName SET value_vector = to_tsvector('simple', coalesce(value, ''))");

            // Trigger function to keep tsvector up to date
            DB::statement("CREATE FUNCTION {$tableName}_value_vector_trigger_func() RETURNS trigger AS $$
                begin
                    new.value_vector := to_tsvector('simple', coalesce(new.value, ''));
                    return new;
                end
                $$ LANGUAGE plpgsql;");

            // Trigger itself
            DB::statement("CREATE TRIGGER {$tableName}_value_vector_trigger BEFORE INSERT OR UPDATE
            ON $tableName FOR EACH ROW EXECUTE FUNCTION {$tableName}_value_vector_trigger_func();");

            // GIN index for fulltext search
            DB::statement("CREATE INDEX idx_{$tableName}_value_vector ON $tableName USING GIN (value_vector);");
        }

        elseif ($driver === 'sqlsrv') {
            // Create fulltext catalog (if not already exists)
            DB::statement("IF NOT EXISTS (SELECT * FROM sys.fulltext_catalogs WHERE name = 'FT_{$tableName}_CATALOG')
            CREATE FULLTEXT CATALOG FT_{$tableName}_CATALOG AS DEFAULT;");

            // Create fulltext index on the value column
            DB::statement("CREATE FULLTEXT INDEX ON $tableName(value) KEY INDEX PK_$tableName ON FT_{$tableName}_CATALOG;");
        }

        // SQLite: No fulltext setup (requires virtual table with FTS5)
    }

    public function down(): void
    {
        Schema::dropIfExists(config('translation.tables.translation'));
    }
};
