<?php

namespace JobMetric\Translation\Http\Requests;

use Illuminate\Support\Arr;
use JobMetric\Language\Facades\Language;
use JobMetric\Translation\Exceptions\ModelHasTranslationNotFoundException;
use JobMetric\Translation\HasTranslation;
use JobMetric\Translation\Rules\TranslationFieldExistRule;

trait MultiTranslationArrayRequest
{
    /**
     * Build validation rules for multi-locale translations under the "translation" root.
     *
     * Shape produced (per locale):
     *   translation.{locale}               => array
     *   translation.{locale}.{field_name}  => string + uniqueness rule
     *   translation.{locale}.{otherField}  => string|nullable|sometimes (for allowed fields)
     * If the model allows all fields (['*']), a wildcard rule will be used:
     *   translation.{locale}.*             => string|nullable|sometimes
     *
     * @param array $rules Rules array (passed by reference)
     * @param string $model_name FQCN of the model using HasTranslation
     * @param string $field_name Primary field to enforce uniqueness on (default: name)
     * @param int|null $object_id Current record id to exclude (update)
     * @param int|null $parent_id Optional: constrain parent_id on parent table (-1 to ignore)
     * @param array $parent_where Optional: extra where constraints on parent table
     *
     * @return void
     *
     * @throws ModelHasTranslationNotFoundException
     */
    public function renderMultiTranslationFiled(
        array  &$rules,
        string $model_name,
        string $field_name = 'name',
        ?int   $object_id = null,
        ?int   $parent_id = -1,
        array  $parent_where = []
    ): void
    {
        if (!class_exists($model_name) || !in_array(HasTranslation::class, class_uses_recursive($model_name), true)) {
            throw new ModelHasTranslationNotFoundException($model_name);
        }

        $model = new $model_name;

        // Determine allowed fields from trait API
        $allowed = method_exists($model, 'getTranslatableFields')
            ? (array)$model->getTranslatableFields()
            : ['*'];

        // Ensure "translation" is validated as an array container
        $rules['translation'] = Arr::get($rules, 'translation', 'array');

        $languages = Language::all();
        foreach ($languages as $language) {
            $locale = $language->locale;

            // translation.{locale} must be an array
            $rules["translation.{$locale}"] = 'array';

            // Primary unique field rule
            $rules["translation.{$locale}.{$field_name}"] = [
                'string',
                new TranslationFieldExistRule($model_name, $field_name, $locale, $object_id, $parent_id, $parent_where),
            ];

            // Other allowed fields
            if ($allowed === ['*']) {
                // Accept any additional fields as string/nullable/sometimes
                $rules["translation.{$locale}.*"] = 'string|nullable|sometimes';
            } else {
                foreach ($allowed as $item) {
                    if ($item === $field_name) {
                        continue;
                    }
                    $rules["translation.{$locale}.{$item}"] = 'string|nullable|sometimes';
                }
            }
        }
    }

    /**
     * Build custom attribute names for translation inputs.
     *
     * If the model whitelists explicit fields, attributes are built per field.
     * If it allows all fields (['*']), a wildcard attribute is added: translation.{locale}.*
     *
     * The $trans_scope may contain a "{field}" token which will be replaced by the field name.
     * Example: $trans_scope = 'validation.attributes.translation_field' where
     * the string is like "Translation : {field}".
     *
     * @param array $params Attributes array (passed by reference)
     * @param string $model_name FQCN of the model using HasTranslation
     * @param string|null $trans_scope Translation key used to build readable labels (may contain {field})
     *
     * @return void
     *
     * @throws ModelHasTranslationNotFoundException
     */
    public function renderMultiTranslationAttribute(
        array   &$params,
        string  $model_name,
        ?string $trans_scope = null
    ): void
    {
        if (!class_exists($model_name) || !in_array(HasTranslation::class, class_uses_recursive($model_name), true)) {
            throw new ModelHasTranslationNotFoundException($model_name);
        }

        $model = new $model_name;

        $allowed = method_exists($model, 'getTranslatableFields')
            ? (array)$model->getTranslatableFields()
            : ['*'];

        $languages = Language::all();
        foreach ($languages as $language) {
            $locale = $language->locale;

            if ($allowed === ['*']) {
                // Wildcard attribute label per locale
                $params["translation.{$locale}.*"] = $trans_scope
                    ? trans($trans_scope)
                    : "translation {$locale}";
                continue;
            }

            foreach ($allowed as $item) {
                $label = $trans_scope
                    ? trans(str_replace('{field}', $item, $trans_scope))
                    : $item;

                $params["translation.{$locale}.{$item}"] = $label;
            }
        }
    }
}
