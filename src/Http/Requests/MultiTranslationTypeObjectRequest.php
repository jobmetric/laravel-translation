<?php

namespace JobMetric\Translation\Http\Requests;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JobMetric\Language\Facades\Language;
use JobMetric\Translation\Exceptions\ModelHasTranslationNotFoundException;
use JobMetric\Translation\Rules\TranslationFieldExistRule;
use JobMetric\Translation\Typeify\Translation;

/**
 * Trait MultiTranslationTypeObjectRequest
 *
 * Builds validation rules and attribute labels for multi-locale "translation" payloads.
 *
 * Role:
 * - Resolve locales via Language::all().
 * - Enforce uniqueness on the primary translatable field per locale.
 * - Apply per-field custom validation rules and optional uniqueness for schema-marked fields.
 * - Produce human-friendly attribute labels for validator messages.
 */
trait MultiTranslationTypeObjectRequest
{
    /**
     * Build validation rules for multi-locale translations under the "translation" root.
     *
     * Shape (per locale):
     *   translation.{locale}                => array
     *   translation.{locale}.{primary}      => string + TranslationFieldExistRule
     *   translation.{locale}.{custom}       => {custom validation or 'string|nullable|sometimes'}
     *
     * @param array<string, mixed> $rules Rules array (passed by reference).
     * @param Collection<int, Translation> $translations Typeify items defining fields.
     * @param class-string $class_name FQCN of the model that owns translations.
     * @param string $field_name Primary field name (e.g., "name") that must be unique by locale.
     * @param int|null $object_id Current object id for update scenarios (ignored on create).
     * @param int|null $parent_id Optional parent id for scoped uniqueness.
     * @param array<string, mixed> $parent_where Additional parent scoping for uniqueness queries.
     *
     * @return void
     * @throws ModelHasTranslationNotFoundException
     */
    public function renderMultiTranslationFiled(
        array      &$rules,
        Collection $translations,
        string     $class_name,
        string     $field_name = 'name',
        ?int       $object_id = null,
        ?int       $parent_id = null,
        array      $parent_where = []
    ): void
    {
        $rules['translation'] = 'array';

        $languages = Language::all();

        foreach ($languages as $language) {
            $locale = $language->locale;

            $rules["translation.$locale"] = 'array';

            // Primary field: always string + uniqueness rule
            $rules["translation.$locale.$field_name"] = [
                'string',
                new TranslationFieldExistRule($class_name, $field_name, $locale, $object_id, $parent_id, $parent_where),
            ];

            /** @var Translation $item */
            foreach ($translations as $item) {
                $uniqName = Arr::get($item->customField->params ?? [], 'uniqName');

                if (!$uniqName || $uniqName === $field_name) {
                    continue;
                }

                $validation = $item->customField->validation ?? 'string|nullable|sometimes';
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
     * Build human-friendly attribute names for validation messages.
     *
     * Role: Maps "translation.{locale}.{field}" to translatable labels.
     *
     * @param array<string, string> $params Attribute names (passed by reference).
     * @param Collection<int, Translation> $translations Typeify items defining fields.
     *
     * @return void
     */
    public function renderMultiTranslationAttribute(
        array      &$params,
        Collection $translations
    ): void
    {
        $languages = Language::all();

        foreach ($languages as $language) {
            $locale = $language->locale;

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
