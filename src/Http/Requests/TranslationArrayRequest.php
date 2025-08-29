<?php

namespace JobMetric\Translation\Http\Requests;

use JobMetric\Translation\Exceptions\ModelHasTranslationNotFoundException;
use JobMetric\Translation\Rules\TranslationFieldExistRule;

/**
 * Trait TranslationArrayRequest
 *
 * Builds validation rules and attribute labels for a "translation" payload.
 * If multiple locales are provided under translation, rules are generated for all.
 * Otherwise falls back to app()->getLocale().
 */
trait TranslationArrayRequest
{
    /**
     * Compose validation rules for the translation payload.
     *
     * Role: For each provided locale, enforce the primary field (required|string + unique rule)
     *       and allow other fields returned by translationAllowFields() as optional strings.
     *
     * @param array<string, mixed> $rules Rules array (passed by reference).
     * @param array<string, mixed> $data Incoming request data.
     * @param class-string $model_name Model FQCN exposing translationAllowFields().
     * @param string $field_name Primary translatable field name. Defaults to 'name'.
     * @param int|null $object_id Current object id for update scenarios.
     * @param int|null $parent_id Optional parent id for scoping uniqueness.
     * @param array<string, mixed> $parent_where Additional parent scoping for uniqueness queries.
     *
     * @return void
     * @throws ModelHasTranslationNotFoundException
     */
    public function renderTranslationFiled(
        array  &$rules,
        array  $data,
        string $model_name,
        string $field_name = 'name',
        ?int   $object_id = null,
        ?int   $parent_id = null,
        array  $parent_where = []
    ): void
    {
        $rules['translation'] = 'array';

        // Determine locales to validate: all keys under translation, or app locale.
        $locales = [];
        if (array_key_exists('translation', $data) && is_array($data['translation'])) {
            foreach ($data['translation'] as $key => $_value) {
                $locales[] = (string)$key;
            }
        } else {
            $locales[] = app()->getLocale();
        }

        $translations = (new $model_name)->translationAllowFields();

        foreach ($locales as $locale) {
            $rules["translation.$locale"] = 'array';

            // Primary field must be present and unique per locale.
            $rules["translation.$locale.$field_name"] = [
                'required',
                'string',
                new TranslationFieldExistRule($model_name, $field_name, $locale, $object_id, $parent_id, $parent_where),
            ];

            // Other allowed fields are optional strings.
            foreach ($translations as $item) {
                if ($item === $field_name) {
                    continue;
                }

                $rules["translation.$locale.$item"] = 'string|nullable|sometimes';
            }
        }
    }

    /**
     * Compose human-friendly attribute labels for validator messages.
     *
     * Role: For each provided locale (or app locale), map translation.{locale}.{field}
     *       to a translatable label. Meta fields use a fixed label path; others use a template.
     *
     * @param array<string, string> $params Attribute names (passed by reference).
     * @param array<string, mixed> $data Incoming request data.
     * @param class-string $model_name Model FQCN exposing translationAllowFields().
     * @param string|null $trans_scope Translation key template containing `{field}` placeholder.
     *
     * @return void
     */
    public function renderTranslationAttribute(
        array   &$params,
        array   $data,
        string  $model_name,
        ?string $trans_scope = null,
    ): void
    {
        // Determine locales to label: all keys under translation, or app locale.
        $locales = [];
        if (array_key_exists('translation', $data) && is_array($data['translation'])) {
            foreach ($data['translation'] as $key => $_value) {
                $locales[] = (string)$key;
            }
        } else {
            $locales[] = app()->getLocale();
        }

        $translations = (new $model_name)->translationAllowFields();

        foreach ($locales as $locale) {
            foreach ($translations as $item) {
                if (in_array($item, ['meta_title', 'meta_description', 'meta_keywords'], true)) {
                    $params["translation.$locale.$item"] = trans('translation::base.components.translation_card.fields.' . $item . '.label');
                    continue;
                }

                if ($trans_scope) {
                    $params["translation.$locale.$item"] = trans(str_replace('{field}', $item, $trans_scope));
                } else {
                    // Sensible fallback: use the item key itself if no scope provided.
                    $params["translation.$locale.$item"] = ucfirst(str_replace('_', ' ', $item));
                }
            }
        }
    }
}
