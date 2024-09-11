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
        $rules['translation.name'] = [
            'string',
            new TranslationFieldExistRule($class_name, $field_name, $locale, $object_id, $parent_id, $parent_where),
        ];

        foreach ($object_type['translation'] ?? [] as $translation_key => $translation_value) {
            $rules['translation.' . $translation_key] = $translation_value['validation'] ?? 'string|nullable|sometimes';
        }
    }
}
