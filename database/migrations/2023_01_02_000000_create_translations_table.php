<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * CreateTranslationsTable
 *
 * Defines the portable translations table and installs driver-specific indexes
 * for efficient lookups and text search across MySQL/MariaDB, PostgreSQL, and SQL Server.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the translations table with core lookup indexes and then applies
     * optional, driver-aware text-search indexes (FULLTEXT / GIN / FTS).
     *
     * @return void
     */
    public function up(): void
    {
        $tableName = config('translation.tables.translation');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();

            // Polymorphic relation to translatable models
            $table->morphs('translatable');

            // Locale and field
            $table->string('locale')->index();
            $table->string('field')->index();

            // Translation value
            $table->text('value')->nullable();

            // Optional versioning
            $table->unsignedBigInteger('version')->default(1)->index();

            $table->softDeletes()->index();
            $table->timestamps();

            // Uniqueness per model/field/locale/version
            $table->unique([
                'translatable_type',
                'translatable_id',
                'locale',
                'field',
                'version'
            ], 'UNIQUE_TRANSLATION');

            // Fast lookup by model + locale + field (soft-delete aware)
            $table->index([
                'translatable_type',
                'translatable_id',
                'locale',
                'field',
                'deleted_at'
            ], 'IDX_TRANSLATION_MODEL_LOCALE_FIELD_DEL');
        });

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            // MySQL/MariaDB: prefix index + FULLTEXT
            try {
                DB::statement("CREATE INDEX `IDX_TRANSLATION_VALUE_191` ON `{$tableName}` (`value`(191))");
            } catch (Throwable $e) {
                // ignore if exists or unsupported
            }

            try {
                DB::statement("CREATE FULLTEXT INDEX `FT_TRANSLATION_VALUE` ON `{$tableName}` (`value`)");
            } catch (Throwable $e) {
                // ignore if exists or unsupported
            }
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: expression GIN index for full-text (no extra column/trigger)
            try {
                DB::statement(
                    "CREATE INDEX idx_{$tableName}_value_tsv ON {$tableName} " .
                    "USING GIN (to_tsvector('simple', coalesce(value, '')))"
                );
            } catch (Throwable $e) {
                // ignore if exists or unsupported
            }

            // Optional substring acceleration for LIKE/ILIKE via pg_trgm
            try {
                DB::statement("CREATE EXTENSION IF NOT EXISTS pg_trgm");
                DB::statement("CREATE INDEX idx_{$tableName}_value_trgm ON {$tableName} USING GIN (value gin_trgm_ops)");
            } catch (Throwable $e) {
                // ignore if extension not allowed or index exists
            }
        } elseif ($driver === 'sqlsrv') {
            // SQL Server: full-text catalog + stable unique index on id for KEY INDEX
            try {
                DB::unprepared("
                    IF NOT EXISTS (SELECT * FROM sys.fulltext_catalogs WHERE name = 'FT_{$tableName}_CATALOG')
                        CREATE FULLTEXT CATALOG FT_{$tableName}_CATALOG AS DEFAULT;
                ");

                DB::unprepared("
                    IF NOT EXISTS (SELECT name FROM sys.indexes WHERE name = 'UX_{$tableName}_id_for_fts')
                        CREATE UNIQUE INDEX UX_{$tableName}_id_for_fts ON {$tableName} (id);
                ");

                DB::unprepared("
                    IF NOT EXISTS (
                        SELECT 1
                        FROM sys.fulltext_indexes fi
                        JOIN sys.objects o ON fi.object_id = o.object_id
                        WHERE o.name = '{$tableName}'
                    )
                    CREATE FULLTEXT INDEX ON {$tableName}(value LANGUAGE 1033)
                    KEY INDEX UX_{$tableName}_id_for_fts
                    ON FT_{$tableName}_CATALOG;
                ");
            } catch (Throwable $e) {
                // ignore if not supported or exists
            }
        }

        // SQLite: no FTS setup here
    }

    /**
     * Reverse the migrations.
     *
     * Drops the translations table; associated indexes are removed with the table.
     * Database-level extensions (e.g., pg_trgm) remain installed by design.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('translation.tables.translation'));
    }
};
