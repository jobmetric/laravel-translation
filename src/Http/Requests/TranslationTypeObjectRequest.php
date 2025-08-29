<?php

namespace JobMetric\Translation\Http\Requests;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JobMetric\Translation\Exceptions\ModelHasTranslationNotFoundException;
use JobMetric\Translation\Rules\TranslationFieldExistRule;
use JobMetric\Translation\Typeify\Translation;

/**
 * Trait TranslationTypeObjectRequest
 *
 * Builds validation rules and attribute labels for a single "translation" object
 * based on Typeify\Translation schema, across all provided locales in the payload.
 *
 * Role:
 * - Use all locales present under "translation" or fall back to app()->getLocale().
 * - Enforce uniqueness on the primary translatable field per locale.
 * - Apply per-field custom validation rules and optional uniqueness for schema-marked fields.
 * - Produce human-friendly attribute labels for validator messages.
 */
trait TranslationTypeObjectRequest
{
    /**
     * Compose validation rules for the translation payload (per provided locale).
     *
     * @param array<string, mixed> $rules Rules array (passed by reference).
     * @param array<string, mixed> $data Incoming request data.
     * @param Collection<int,Translation> $translations Typeify items defining fields.
     * @param class-string $class_name FQCN of the model that owns translations.
     * @param string $field_name Primary field name, defaults to 'name'.
     * @param int|null $object_id Current object id for update scenarios.
     * @param int|null $parent_id Optional parent id for scoped uniqueness.
     * @param array<string, mixed> $parent_where Additional parent scoping for uniqueness.
     *
     * @return void
     * @throws ModelHasTranslationNotFoundException
     */
    public function renderTranslationFiled(
        array      &$rules,
        array      $data,
        Collection $translations,
        string     $class_name,
        string     $field_name = 'name',
        ?int       $object_id = null,
        ?int       $parent_id = null,
        array      $parent_where = []
    ): void
    {
        $rules['translation'] = 'array';

        // Determine locales: use all keys under translation, otherwise app locale.
        $locales = [];
        if (array_key_exists('translation', $data) && is_array($data['translation'])) {
            foreach ($data['translation'] as $key => $_) {
                $locales[] = (string)$key;
            }
        } else {
            $locales[] = app()->getLocale();
        }

        foreach ($locales as $locale) {
            $rules["translation.$locale"] = 'array';

            // Primary field: string + uniqueness rule (not forcing required here).
            $rules["translation.$locale.$field_name"] = [
                'string',
                new TranslationFieldExistRule($class_name, $field_name, $locale, $object_id, $parent_id, $parent_where),
            ];

            /** @var Translation $item */
            foreach ($translations as $item) {
                $uniqName = Arr::get($item->customField->params ?? [], 'uniqName');

                // Skip if missing or equals the primary field
                if (!$uniqName || $uniqName === $field_name) {
                    continue;
                }

                // Base validation for custom field
                $validation = $item->customField->validation ?? 'string|nullable|sometimes';

                // Optional uniqueness if schema declares it
                $isUnique = (bool)Arr::get($item->customField->params ?? [], 'unique', false);

                if ($isUnique) {
                    $rules["translation.$locale.$uniqName"] = [
                        $validation,
                        new TranslationFieldExistRule($class_name, $uniqName, $locale, $object_id, $parent_id, $parent_where),
                    ];
                } else {
                    $rules["translation.$locale.$uniqName"] = $validation;
                }
            }
        }
    }

    /**
     * Compose human-friendly attribute labels for validator messages.
     *
     * @param array<string, string> $params Attribute names (passed by reference).
     * @param array<string, mixed> $data Incoming request data.
     * @param Collection<int,Translation> $translations Typeify items defining fields.
     *
     * @return void
     */
    public function renderTranslationAttribute(
        array      &$params,
        array      $data,
        Collection $translations
    ): void
    {
        // Determine locales: use all keys under translation, otherwise app locale.
        $locales = [];
        if (array_key_exists('translation', $data) && is_array($data['translation'])) {
            foreach ($data['translation'] as $key => $_) {
                $locales[] = (string)$key;
            }
        } else {
            $locales[] = app()->getLocale();
        }

        foreach ($locales as $locale) {
            /** @var Translation $item */
            foreach ($translations as $item) {
                $uniqName = Arr::get($item->customField->params ?? [], 'uniqName');

                if (!$uniqName) {
                    continue;
                }

                $labelKey = $item->customField->label ?? $uniqName;
                $params["translation.$locale.$uniqName"] = trans($labelKey);
            }
        }
    }
}
