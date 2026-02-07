<?php

namespace JobMetric\Translation;

use Illuminate\Database\Eloquent\Model;
use ReflectionProperty;
use Throwable;

/**
 * Trait HasServiceTranslation
 *
 * Provides reusable translation management methods for CRUD services.
 * Automatically reads translatable fields from the model's $translatables
 * property and provides methods to sync and normalize translation payloads.
 *
 * Requirements:
 * - Service must define `protected static string $modelClass` pointing to a model
 *   that uses HasTranslation trait and defines `protected array $translatables`.
 *
 * Usage:
 * ```php
 * class MyService extends AbstractCrudService
 * {
 *     use HasServiceTranslation;
 *
 *     protected static string $modelClass = MyModel::class;
 *
 *     protected function afterStore(Model $model, array &$data): void
 *     {
 *         $this->syncTranslations($model, $data['translation'] ?? null, false);
 *     }
 *
 *     protected function afterUpdate(Model $model, array &$data): void
 *     {
 *         if (array_key_exists('translation', $data)) {
 *             $this->syncTranslations($model, $data['translation'], true);
 *         }
 *     }
 * }
 * ```
 */
trait HasServiceTranslation
{
    /**
     * Static cache for translatable fields per model class.
     * Using static to avoid re-computation across instances.
     *
     * @var array<class-string, array<int, string>>
     */
    protected static array $translatableFieldsCache = [];

    /**
     * Default values for translatable fields on store.
     * Override in child service to customize defaults.
     *
     * @var array<string, mixed>
     */
    protected array $translationDefaults = [];

    /**
     * Get translatable fields from the model class.
     * Results are cached statically per model class for performance.
     *
     * @return array<int, string>
     */
    protected function getTranslatableFields(): array
    {
        if (! property_exists(static::class, 'modelClass')) {
            return [];
        }

        $modelClass = static::$modelClass;

        // Return from static cache if available
        if (isset(self::$translatableFieldsCache[$modelClass])) {
            return self::$translatableFieldsCache[$modelClass];
        }

        self::$translatableFieldsCache[$modelClass] = $this->resolveTranslatableFields($modelClass);

        return self::$translatableFieldsCache[$modelClass];
    }

    /**
     * Resolve translatable fields from model class.
     *
     * @param class-string $modelClass
     *
     * @return array<int, string>
     */
    private function resolveTranslatableFields(string $modelClass): array
    {
        if (! class_exists($modelClass)) {
            return [];
        }

        $model = new $modelClass();

        // First try: read from model's getTranslatableFields method (if available from trait)
        if (method_exists($model, 'getTranslatableFields')) {
            $fields = $model->getTranslatableFields();
            if ($this->isValidTranslatableFields($fields)) {
                return array_values($fields);
            }
        }

        // Second try: read directly from $translatables property via reflection
        if (property_exists($model, 'translatables')) {
            try {
                $reflection = new ReflectionProperty($model, 'translatables');
                $translatables = $reflection->getValue($model);

                if ($this->isValidTranslatableFields($translatables)) {
                    return array_values($translatables);
                }
            } catch (Throwable) {
                // Reflection failed, return empty
            }
        }

        return [];
    }

    /**
     * Check if fields array is valid (non-empty array, not wildcard).
     *
     * @param mixed $fields
     *
     * @return bool
     */
    private function isValidTranslatableFields(mixed $fields): bool
    {
        return is_array($fields) && ! empty($fields) && $fields !== ['*'];
    }

    /**
     * Ensure `translations` relation is present in eager-load list.
     *
     * @param array<int, string> $with
     *
     * @return array<int, string>
     */
    protected function ensureTranslationsRelation(array $with): array
    {
        if (! in_array('translations', $with, true)) {
            $with[] = 'translations';
        }

        return $with;
    }

    /**
     * Upsert translations for a model.
     *
     * Supported formats:
     * - Locale-keyed: ['en' => ['name' => ...], 'fa' => [...]]
     * - Legacy (current locale): ['name' => ..., 'code' => ...]
     *
     * @param Model $model
     * @param mixed $translation
     * @param bool $isUpdate If true, only provided keys are updated
     *
     * @return void
     * @throws Throwable
     */
    protected function syncTranslations(Model $model, mixed $translation, bool $isUpdate): void
    {
        if (! is_array($translation) || empty($translation)) {
            return;
        }

        if (! method_exists($model, 'translate')) {
            return;
        }

        $translatableFields = $this->getTranslatableFields();

        if ($this->isLocaleKeyedFormat($translation, $translatableFields)) {
            $this->syncLocaleKeyedTranslations($model, $translation, $isUpdate, $translatableFields);

            return;
        }

        $this->syncSingleLocaleTranslation($model, $translation, $isUpdate, $translatableFields);
    }

    /**
     * Sync translations in locale-keyed format.
     *
     * @param Model $model
     * @param array<string, array<string, mixed>> $translation
     * @param bool $isUpdate
     * @param array<int, string> $translatableFields
     *
     * @return void
     * @throws Throwable
     */
    private function syncLocaleKeyedTranslations(
        Model $model,
        array $translation,
        bool $isUpdate,
        array $translatableFields
    ): void {
        foreach ($translation as $locale => $fields) {
            if (! is_string($locale) || ! is_array($fields) || empty($fields)) {
                continue;
            }

            $payload = $this->buildPayload($fields, $isUpdate, $translatableFields);
            if (! empty($payload)) {
                $model->translate($locale, $payload);
            }
        }
    }

    /**
     * Sync translation for current locale.
     *
     * @param Model $model
     * @param array<string, mixed> $translation
     * @param bool $isUpdate
     * @param array<int, string> $translatableFields
     *
     * @return void
     * @throws Throwable
     */
    private function syncSingleLocaleTranslation(
        Model $model,
        array $translation,
        bool $isUpdate,
        array $translatableFields
    ): void {
        $payload = $this->buildPayload($translation, $isUpdate, $translatableFields);
        if (! empty($payload)) {
            $model->translate(app()->getLocale(), $payload);
        }
    }

    /**
     * Check if translation array is in locale-keyed format.
     *
     * @param array<string, mixed> $translation
     * @param array<int, string> $translatableFields
     *
     * @return bool
     */
    private function isLocaleKeyedFormat(array $translation, array $translatableFields): bool
    {
        $firstKey = array_key_first($translation);
        $firstValue = $translation[$firstKey] ?? null;

        // Must have string key with array value
        if (! is_string($firstKey) || ! is_array($firstValue)) {
            return false;
        }

        // Key length check (locale codes are typically 2-5 chars)
        $keyLength = strlen($firstKey);
        if ($keyLength < 2 || $keyLength > 5) {
            return false;
        }

        // If key matches a translatable field name, it's not locale-keyed
        if (! empty($translatableFields) && in_array($firstKey, $translatableFields, true)) {
            return false;
        }

        return true;
    }

    /**
     * Build translation payload based on operation type.
     *
     * @param array<string, mixed> $fields           Input fields
     * @param bool $isUpdate                         True for update, false for store
     * @param array<int, string> $translatableFields Allowed fields
     *
     * @return array<string, mixed>
     */
    private function buildPayload(array $fields, bool $isUpdate, array $translatableFields): array
    {
        // If no translatable fields defined, pass through all fields
        if (empty($translatableFields)) {
            return $fields;
        }

        $payload = [];

        foreach ($translatableFields as $key) {
            if ($isUpdate) {
                // On update: only include fields present in input
                if (array_key_exists($key, $fields)) {
                    $payload[$key] = $fields[$key];
                }
            }
            else {
                // On store: include all fields with defaults
                $payload[$key] = $fields[$key] ?? ($this->translationDefaults[$key] ?? null);
            }
        }

        return $payload;
    }

    /**
     * Normalize translation input into a payload suitable for translate().
     * Public wrapper for buildPayload for backward compatibility.
     *
     * @param array<string, mixed> $fields
     * @param bool $isUpdate
     *
     * @return array<string, mixed>
     */
    protected function normalizeTranslationPayload(array $fields, bool $isUpdate): array
    {
        return $this->buildPayload($fields, $isUpdate, $this->getTranslatableFields());
    }

    /**
     * Set default values for translatable fields.
     *
     * @param array<string, mixed> $defaults
     *
     * @return static
     */
    public function setTranslationDefaults(array $defaults): static
    {
        $this->translationDefaults = $defaults;

        return $this;
    }

    /**
     * Get the default value for a translatable field.
     *
     * @param string $field
     *
     * @return mixed
     */
    protected function getTranslationDefault(string $field): mixed
    {
        return $this->translationDefaults[$field] ?? null;
    }

    /**
     * Clear the translatable fields cache.
     * Useful for testing or when model configuration changes.
     *
     * @return void
     */
    public static function clearTranslatableFieldsCache(): void
    {
        self::$translatableFieldsCache = [];
    }
}
