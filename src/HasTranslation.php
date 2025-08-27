<?php

namespace JobMetric\Translation;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use JobMetric\Translation\Events\TranslationForgetEvent;
use JobMetric\Translation\Events\TranslationStoredEvent;
use JobMetric\Translation\Exceptions\TranslationDisallowFieldException;
use JobMetric\Translation\Models\Translation as TranslationModel;
use Throwable;

/**
 * Trait HasTranslation
 *
 * Provides per-field translations via a morphMany relation with optional versioning.
 * Mirrors HasMeta design by using base* properties that can be overridden from model-defined props.
 *
 * Model may define (optional):
 *   protected array $translatables = ['title', 'summary']; // ['*'] to allow all
 *   protected bool  $translationVersioning = true;         // enable history
 *
 * Table contract:
 *  id, translatable_type, translatable_id, locale, field, value, version, deleted_at, timestamps
 *  unique (translatable_type, translatable_id, locale, field, version)
 *
 * @property-read Collection<int, TranslationModel> $translations
 *
 * @method static Builder|static whereTranslationEquals(string $field, string $value, ?string $locale = null)
 * @method static Builder|static whereTranslationLike(string $field, string $needle, ?string $locale = null)
 * @method static Builder|static searchTranslation(string $field, string $needle, ?string $locale = null)
 */
trait HasTranslation
{
    /**
     * Base whitelist and versioning flag (source of truth inside the trait).
     * Initialized from model properties (if present) in initializeHasTranslation().
     *
     * @var array<int, string>
     */
    private array $baseTranslatables = ['*'];

    /**
     * @var bool
     */
    private bool $baseTranslationVersioning = false;

    /**
     * Buffer for incoming "translation" attribute.
     * Format: ['fa' => ['title' => '...', 'summary' => '...'], ...]
     *
     * @var array<string, array<string, string|null>>
     */
    protected array $innerTranslations = [];

    /**
     * Append "translation" to fillable and import model-level config into base*.
     *
     * @return void
     * @throws Throwable
     */
    public function initializeHasTranslation(): void
    {
        // Import from model properties (if defined) into base*, mirroring HasMeta.
        if (function_exists('hasPropertyInClass')) {
            if (hasPropertyInClass($this, 'translatables')) {
                /** @var array $this ->translatables */
                $this->baseTranslatables = (is_array($this->translatables) && $this->translatables !== [])
                    ? array_values($this->translatables)
                    : ['*'];
            }
            if (hasPropertyInClass($this, 'translationVersioning')) {
                /** @var bool $this ->translationVersioning */
                $this->baseTranslationVersioning = (bool)$this->translationVersioning;
            }
        } else {
            if (property_exists($this, 'translatables')) {
                /** @var array $this ->translatables */
                $this->baseTranslatables = (is_array($this->translatables) && $this->translatables !== [])
                    ? array_values($this->translatables)
                    : ['*'];
            }
            if (property_exists($this, 'translationVersioning')) {
                /** @var bool $this ->translationVersioning */
                $this->baseTranslationVersioning = (bool)$this->translationVersioning;
            }
        }

        $this->mergeFillable(['translation']);
    }

    /**
     * Wire up attribute interception, persistence, and cleanup.
     *
     * @return void
     * @throws Throwable
     */
    public static function bootHasTranslation(): void
    {
        // Intercept the virtual "translation" attribute
        static::saving(function (Model $model) {
            if (!isset($model->attributes['translation']) || !is_array($model->attributes['translation'])) {
                return;
            }

            $payload = $model->attributes['translation'];

            foreach ($payload as $locale => $data) {
                if (!is_array($data)) {
                    continue;
                }

                $keys = array_keys($data);
                if (!$model->translatablesAllowAll()) {
                    $diff = array_diff($keys, $model->getTranslatableFields());
                    if (!empty($diff)) {
                        throw new TranslationDisallowFieldException($model::class, $diff);
                    }
                }
            }

            $model->innerTranslations = $payload;
            unset($model->attributes['translation']);
        });

        // Flush buffered translations after save
        static::saved(function (Model $model) {
            if (empty($model->innerTranslations)) {
                return;
            }

            foreach ($model->innerTranslations as $locale => $fields) {
                if (!is_array($fields)) {
                    continue;
                }
                $model->translate($locale, $fields);
            }

            $model->innerTranslations = [];
        });

        // Parent without SoftDeletes: on deleted -> soft-delete child translations (no restore path here)
        static::deleted(function (Model $model) {
            if (!in_array(SoftDeletes::class, class_uses_recursive($model))) {
                $model->translations()->delete();
            }
        });

        // Parent with SoftDeletes
        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            // On soft-deleting the parent, soft-delete active child translations
            static::deleted(function (Model $model) {
                $model->translations()->delete();
            });

            // On restoring the parent, restore only the latest version per (locale, field)
            static::restored(function (Model $model) {
                $latest = $model->translations()
                    ->withTrashed()
                    ->select('locale', 'field')
                    ->selectRaw('MAX(version) AS max_version')
                    ->groupBy('locale', 'field')
                    ->get();

                foreach ($latest as $row) {
                    $model->translations()
                        ->withTrashed()
                        ->where('locale', $row->locale)
                        ->where('field', $row->field)
                        ->where('version', $row->max_version)
                        ->restore();
                }
            });

            // On force-deleting the parent, remove all translation history
            static::forceDeleted(function (Model $model) {
                $model->translations()->withTrashed()->forceDelete();
            });
        }
    }

    /**
     * Relation: translations.
     *
     * @return MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(TranslationModel::class, 'translatable');
    }

    /**
     * Relation filtered by locale.
     *
     * @param string $locale
     * @return MorphMany
     */
    public function translationsTo(string $locale): MorphMany
    {
        return $this->translations()->where('locale', $locale);
    }

    /**
     * Scope: models that have a translation for a field (optionally locale).
     *
     * @param Builder $query
     * @param string $field
     * @param string|null $locale
     * @return Builder
     */
    public function scopeHasTranslationField(Builder $query, string $field, ?string $locale = null): Builder
    {
        return $query->whereHas('translations', function (Builder $q) use ($field, $locale) {
            $q->where('field', $field);
            if ($locale !== null) {
                $q->where('locale', $locale);
            }
        });
    }

    /**
     * Create/update translations for locale.
     * versioning OFF: upsert version=1
     * versioning ON:  soft delete active(s) + insert version=last+1
     *
     * @param string $locale
     * @param array<string, string|null> $data
     * @return static
     * @throws Throwable
     */
    public function translate(string $locale, array $data): static
    {
        $fields = array_keys($data);

        if (!$this->translatablesAllowAll()) {
            $disallowed = array_diff($fields, $this->getTranslatableFields());
            if (!empty($disallowed)) {
                throw new TranslationDisallowFieldException(self::class, $disallowed);
            }
        }

        if ($this->usesTranslationVersioning()) {
            foreach ($data as $field => $value) {
                $this->translationsTo($locale)->where('field', $field)->get()->each(function ($row) {
                    $row->delete();
                });

                $last = $this->translations()
                    ->withTrashed()
                    ->where('locale', $locale)
                    ->where('field', $field)
                    ->max('version');

                $nextVersion = $last ? ((int)$last + 1) : 1;

                $this->translations()->create([
                    'locale' => $locale,
                    'field' => $field,
                    'value' => $value,
                    'version' => $nextVersion,
                ]);

                event(new TranslationStoredEvent($this, $locale, [$field => $value]));
            }

            return $this;
        }

        foreach ($data as $field => $value) {
            $this->translations()->updateOrCreate(
                ['locale' => $locale, 'field' => $field, 'version' => 1],
                ['value' => $value]
            );

            event(new TranslationStoredEvent($this, $locale, [$field => $value]));
        }

        return $this;
    }

    /**
     * Sugar for setting a single field.
     *
     * @param string $locale
     * @param string $field
     * @param string|null $value
     * @return static
     * @throws Throwable
     */
    public function setTranslation(string $locale, string $field, ?string $value): static
    {
        return $this->translate($locale, [$field => $value]);
    }

    /**
     * Batch translate: ['fa' => [...], 'en' => [...]].
     *
     * @param array<string, array<string, string|null>> $payload
     * @return static
     * @throws Throwable
     */
    public function translateBatch(array $payload): static
    {
        foreach ($payload as $locale => $data) {
            if (!is_array($data)) {
                continue;
            }
            $this->translate($locale, $data);
        }

        return $this;
    }

    /**
     * Check field existence (active row) for a locale.
     *
     * @param string $field
     * @param string|null $locale
     * @return bool
     */
    public function hasTranslationField(string $field, ?string $locale = null): bool
    {
        $locale = $locale ?: app()->getLocale();

        return $this->translationsTo($locale)->where('field', $field)->exists();
    }

    /**
     * Get one translation value.
     * If $version is null: returns active; if none, falls back to latest by version.
     * If $version is provided: returns that exact version (with trashed if needed).
     *
     * @param string $field
     * @param string|null $locale
     * @param int|null $version
     * @return string|null
     */
    public function getTranslation(string $field, ?string $locale = null, ?int $version = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        if ($version === null) {
            $active = $this->translationsTo($locale)->where('field', $field)->first();
            if ($active) {
                return $active->value;
            }

            $latest = $this->translations()
                ->withTrashed()
                ->where('locale', $locale)
                ->where('field', $field)
                ->orderByDesc('version')
                ->first();

            return $latest?->value;
        }

        $row = $this->translations()
            ->withTrashed()
            ->where('locale', $locale)
            ->where('field', $field)
            ->where('version', $version)
            ->first();

        return $row?->value;
    }

    /**
     * Get translations:
     * - with $locale: ['field' => 'value']
     * - without $locale: ['fa' => [...], 'en' => [...]]
     *
     * @param string|null $locale
     * @return array<string, mixed>
     */
    public function getTranslations(?string $locale = null): array
    {
        if ($locale !== null) {
            return $this->translations()
                ->where('locale', $locale)
                ->pluck('value', 'field')
                ->toArray();
        }

        return $this->translations()
            ->select(['locale', 'field', 'value'])
            ->get()
            ->groupBy('locale')
            ->map(fn($rows) => $rows->pluck('value', 'field')->toArray())
            ->toArray();
    }

    /**
     * Latest version number for (field, locale).
     *
     * @param string $field
     * @param string|null $locale
     * @return int
     */
    public function latestTranslationVersion(string $field, ?string $locale = null): int
    {
        $locale = $locale ?: app()->getLocale();

        $max = $this->translations()
            ->withTrashed()
            ->where('locale', $locale)
            ->where('field', $field)
            ->max('version');

        return (int)($max ?: 0);
    }

    /**
     * All versions (latest first), including soft-deleted.
     *
     * @param string $field
     * @param string|null $locale
     * @return array<int, array{version:int, value:string|null, deleted_at:string|null}>
     */
    public function getTranslationVersions(string $field, ?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();

        return $this->translations()
            ->withTrashed()
            ->where('locale', $locale)
            ->where('field', $field)
            ->orderByDesc('version')
            ->get(['version', 'value', 'deleted_at'])
            ->map(static fn($row) => [
                'version' => (int)$row->version,
                'value' => $row->value,
                'deleted_at' => optional($row->deleted_at)?->toDateTimeString(),
            ])
            ->toArray();
    }

    /**
     * Forget one field/locale (soft by default; force to hard delete).
     *
     * @param string $field
     * @param string $locale
     * @param bool $force
     * @return static
     */
    public function forgetTranslation(string $field, string $locale, bool $force = false): static
    {
        $query = $this->translationsTo($locale)->where('field', $field);
        $records = $force ? $query->withTrashed()->get() : $query->get();

        $records->each(function ($translation) use ($force) {
            $force ? $translation->forceDelete() : $translation->delete();
            event(new TranslationForgetEvent($translation));
        });

        return $this;
    }

    /**
     * Forget all translations, optionally for a specific locale.
     * Soft-deletes by default; pass $force=true to force-delete history.
     *
     * @param string|null $locale
     * @param bool $force
     * @return static
     */
    public function forgetTranslations(?string $locale = null, bool $force = false): static
    {
        $query = $locale === null ? $this->translations() : $this->translationsTo($locale);
        $records = $force ? $query->withTrashed()->get() : $query->get();

        $records->each(function ($translation) use ($force) {
            $force ? $translation->forceDelete() : $translation->delete();
            event(new TranslationForgetEvent($translation));
        });

        return $this;
    }

    /**
     * Allowed fields. If ['*'], returns ['*'].
     *
     * @return array<int, string>
     */
    public function getTranslatableFields(): array
    {
        if ($this->translatablesAllowAll()) {
            return ['*'];
        }

        return array_values(array_unique($this->baseTranslatables));
    }

    /**
     * Merge fields into whitelist. Removes '*' if present to narrow scope.
     *
     * @param array<int, string> $fields
     * @return void
     */
    public function mergeTranslatables(array $fields): void
    {
        if ($this->translatablesAllowAll()) {
            $this->baseTranslatables = [];
        }

        $this->baseTranslatables = array_values(array_unique(array_merge($this->baseTranslatables, $fields)));

        if ($this->baseTranslatables === []) {
            $this->baseTranslatables = ['*'];
        }
    }

    /**
     * Remove a field from whitelist. If becomes empty, reverts to ['*'].
     *
     * @param string $field
     * @return void
     */
    public function removeTranslatableField(string $field): void
    {
        $idx = array_search($field, $this->baseTranslatables, true);
        if ($idx !== false) {
            unset($this->baseTranslatables[$idx]);
        }

        $this->baseTranslatables = array_values($this->baseTranslatables);

        if (empty($this->baseTranslatables)) {
            $this->baseTranslatables = ['*'];
        }
    }

    /**
     * Returns true if all fields are allowed.
     *
     * @return bool
     */
    public function translatablesAllowAll(): bool
    {
        return in_array('*', $this->baseTranslatables, true);
    }

    /**
     * Whether versioning is enabled.
     *
     * @return bool
     */
    public function usesTranslationVersioning(): bool
    {
        return (bool)$this->baseTranslationVersioning;
    }

    /**
     * Scope: equals (ANY-locale if $locale=null).
     *
     * @param Builder $query
     * @param string $field
     * @param string $value
     * @param string|null $locale
     * @return Builder
     */
    public function scopeWhereTranslationEquals(
        Builder $query,
        string  $field,
        string  $value,
        ?string $locale = null
    ): Builder
    {
        return $query->whereHas('translations', function (Builder $q) use ($field, $value, $locale) {
            $q->where('field', $field);

            if ($locale !== null) {
                $q->where('locale', $locale);
            }

            $q->where('value', '=', $value);
        });
    }

    /**
     * Scope: LIKE/ILIKE (driver-aware) for active rows.
     *
     * @param Builder $query
     * @param string $field
     * @param string $needle
     * @param string|null $locale
     * @return Builder
     */
    public function scopeWhereTranslationLike(
        Builder $query,
        string  $field,
        string  $needle,
        ?string $locale = null
    ): Builder
    {
        $locale = $locale ?: app()->getLocale();
        $driver = $query->getModel()->getConnection()->getDriverName();
        $pattern = '%' . $needle . '%';

        return $query->whereHas('translations', function (Builder $q) use ($driver, $field, $locale, $pattern) {
            $q->where('field', $field)->where('locale', $locale);

            if ($driver === 'pgsql') {
                $q->where('value', 'ILIKE', $pattern);
                return;
            }

            $q->where('value', 'LIKE', $pattern);
        });
    }

    /**
     * Scope: FullText + fallback LIKE (driver-aware) for active rows.
     *
     * @param Builder $query
     * @param string $field
     * @param string $needle
     * @param string|null $locale
     * @return Builder
     */
    public function scopeSearchTranslation(
        Builder $query,
        string  $field,
        string  $needle,
        ?string $locale = null
    ): Builder
    {
        $locale = $locale ?: app()->getLocale();
        $driver = $query->getModel()->getConnection()->getDriverName();
        $likePattern = '%' . $needle . '%';

        return $query->whereHas('translations', function (Builder $q) use ($driver, $field, $locale, $needle, $likePattern) {
            $q->where('field', $field)->where('locale', $locale);

            $q->where(function (Builder $inner) use ($driver, $needle, $likePattern) {
                if ($driver === 'mysql' || $driver === 'mariadb') {
                    $inner->whereRaw('MATCH(`value`) AGAINST (? IN NATURAL LANGUAGE MODE)', [$needle])
                        ->orWhere('value', 'LIKE', $likePattern);
                    return;
                }

                if ($driver === 'pgsql') {
                    $inner->whereRaw(
                        "to_tsvector('simple', coalesce(value, '')) @@ plainto_tsquery('simple', ?)",
                        [$needle]
                    )->orWhere('value', 'ILIKE', $likePattern);
                    return;
                }

                if ($driver === 'sqlsrv') {
                    $contains = '"' . str_replace('"', '""', $needle) . '*"';
                    $inner->whereRaw('CONTAINS([value], ?)', [$contains])
                        ->orWhere('value', 'LIKE', $likePattern);
                    return;
                }

                $inner->where('value', 'LIKE', $likePattern);
            });
        });
    }
}
