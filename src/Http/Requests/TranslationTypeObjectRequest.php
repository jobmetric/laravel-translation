<?php

namespace JobMetric\Translation\Http\Requests;

use JobMetric\Translation\Rules\TranslationFieldExistRule;

trait TranslationTypeObjectRequest
{
    public function renderTranslationFiled(
        array       &$rules,
        array       $object_type,
        string      $class_name,
        string      $field_name = 'title',
        string|null $locale = null,
        int|null    $object_id = null,
        int|null    $parent_id = -1,
        array       $parent_where = []
    ): void
    {
        $rules['translation'] = 'array';
        $rules["translation.$locale.$field_name"] = [
            'string',
            new TranslationFieldExistRule($class_name, $field_name, $locale, $object_id, $parent_id, $parent_where),
        ];

        foreach ($object_type['translation']['fields'] ?? [] as $translation_key => $translation_value) {
            if ($translation_key === $field_name && !isset($translation_value['validation'])) {
                continue;
            }

            $rules["translation.$locale.$translation_key"] = $translation_value['validation'] ?? 'string|nullable|sometimes';
        }

        if ($object_type['translation']['seo'] ?? false) {
            $rules["translation.$locale.meta_title"] = 'string|nullable|sometimes';
            $rules["translation.$locale.meta_description"] = 'string|nullable|sometimes';
            $rules["translation.$locale.meta_keywords"] = 'string|nullable|sometimes';
        }
    }
}
