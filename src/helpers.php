<?php

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use JobMetric\Translation\Exceptions\ModelHasTranslationNotFoundException;
use JobMetric\Translation\HasTranslation;
use JobMetric\Translation\Models\Translation;

if (!function_exists('translation_model_uses_has_translation')) {
    /**
     * Check whether a model class/object uses the HasTranslation trait (recursively).
     *
     * @param object|string $model
     * @return bool
     */
    function translation_model_uses_has_translation(object|string $model): bool
    {
        $class = is_object($model) ? get_class($model) : $model;
        if (!class_exists($class)) {
            return false;
        }

        if (function_exists('class_uses_recursive')) {
            return in_array(HasTranslation::class, class_uses_recursive($class), true);
        }

        // Fallback recursive trait inspection
        $results = [];
        $cursor = $class;
        do {
            $results = array_merge($results, class_uses($cursor) ?: []);
        } while ($cursor = get_parent_class($cursor));

        $stack = $results;
        while (!empty($stack)) {
            $trait = array_pop($stack);
            $nested = class_uses($trait) ?: [];
            foreach ($nested as $t) {
                if (!in_array($t, $results, true)) {
                    $results[] = $t;
                    $stack[] = $t;
                }
            }
        }

        return in_array(HasTranslation::class, array_values(array_unique($results)), true);
    }
}

if (!function_exists('translation')) {
    /**
     * Store translations on a model using translateBatch semantics.
     * Input shape: ['fa' => ['title' => '...'], 'en' => [...]]
     *
     * @param Model $model
     * @param array $data
     * @return void
     * @throws Throwable
     */
    function translation(Model $model, array $data = []): void
    {
        if (!translation_model_uses_has_translation($model)) {
            throw new ModelHasTranslationNotFoundException($model::class);
        }

        // Prefer translateBatch if available; otherwise iterate.
        if (method_exists($model, 'translateBatch')) {
            $model->translateBatch($data);
            return;
        }

        foreach ($data as $locale => $fields) {
            if (is_array($fields)) {
                $model->translate($locale, $fields);
            }
        }
    }
}

if (!function_exists('translationResourceData')) {
    /**
     * Normalize translation relations into an array suitable for API resources.
     * Accepts iterable of translation rows (active rows recommended).
     * Returns: ['fa' => ['title' => '...', ...], 'en' => [...]]
     *
     * @param iterable $relations
     * @param string|null $locale
     * @return array<string, array<string, mixed>>
     */
    function translationResourceData(iterable $relations, string $locale = null): array
    {
        $data = [];

        foreach ($relations as $relation) {
            if ($locale !== null && $relation->locale !== $locale) {
                continue;
            }
            // New schema uses 'field' (not 'key')
            $data[$relation->locale][$relation->field] = $relation->value;
        }

        return $data;
    }
}

if (!function_exists('translationDataSelect')) {
    /**
     * Build an id => translated value map for a field across many models.
     * Efficient single-query implementation against the translations table.
     *
     * @param EloquentCollection $objects Collection of the same model type
     * @param string $field
     * @param string|null $locale
     * @return Collection          key: parent id, value: translation value
     */
    function translationDataSelect(EloquentCollection $objects, string $field, string $locale = null): Collection
    {
        $first = $objects->first();
        if (!$first) {
            return collect();
        }
        if (!translation_model_uses_has_translation($first)) {
            throw new ModelHasTranslationNotFoundException(get_class($first));
        }

        $ids = $objects->pluck($first->getKeyName())->filter()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        $locale = $locale ?: app()->getLocale();
        $table = (string)config('translation.tables.translation', (new Translation)->getTable());
        $type = get_class($first);
        $versioned = method_exists($first, 'usesTranslationVersioning') ? (bool)$first->usesTranslationVersioning() : false;

        $rows = DB::table($table)
            ->select(['translatable_id as id', 'value'])
            ->where('translatable_type', $type)
            ->whereIn('translatable_id', $ids->all())
            ->where('locale', $locale)
            ->where('field', $field)
            ->when(
                $versioned,
                fn($q) => $q->whereNull('deleted_at'),
                fn($q) => $q->where('version', 1)->whereNull('deleted_at')
            )
            ->get();

        // Map to id => value. Only include rows that actually exist.
        return $rows->keyBy('id')->map(fn($r) => $r->value);
    }
}

if (!function_exists('translation_value')) {
    /**
     * Convenience wrapper to fetch a single translated field from a model.
     *
     * @param Model $model
     * @param string $field
     * @param string|null $locale
     * @param int|null $version
     * @return string|null
     */
    function translation_value(Model $model, string $field, ?string $locale = null, ?int $version = null): ?string
    {
        if (!translation_model_uses_has_translation($model)) {
            throw new ModelHasTranslationNotFoundException($model::class);
        }

        return $model->getTranslation($field, $locale, $version);
    }
}

if (!function_exists('translation_values')) {
    /**
     * Convenience wrapper to fetch all translated fields for a locale, or grouped by locale.
     *
     * @param Model $model
     * @param string|null $locale
     * @return array
     */
    function translation_values(Model $model, ?string $locale = null): array
    {
        if (!translation_model_uses_has_translation($model)) {
            throw new ModelHasTranslationNotFoundException($model::class);
        }

        return $model->getTranslations($locale);
    }
}
