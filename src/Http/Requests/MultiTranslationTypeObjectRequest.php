<?php

namespace JobMetric\Translation\Http\Requests;

use Illuminate\Support\Collection;
use JobMetric\Language\Facades\Language;
use JobMetric\Translation\Rules\TranslationFieldExistRule;
use JobMetric\Translation\ServiceType\Translation;

trait MultiTranslationTypeObjectRequest
{
    public function renderMultiTranslationFiled(
        array      &$rules,
        Collection $translations,
        string     $class_name,
        string     $field_name = 'name',
        int|null   $object_id = null,
        int|null   $parent_id = -1,
        array      $parent_where = []
    ): void
    {
        $rules["translation"] = 'array';

        $languages = Language::all();
        foreach ($languages as $language) {
            $rules["translation.$language->locale"] = 'array';
            $rules["translation.$language->locale.$field_name"] = [
                'string',
                new TranslationFieldExistRule($class_name, $field_name, $language->locale, $object_id, $parent_id, $parent_where),
            ];

            foreach ($translations as $item) {
                /**
                 * @var Translation $item
                 */
                $uniqName = $item->customField->params['uniqName'] ?? null;

                if ($uniqName !== $field_name) {
                    $rules["translation.$language->locale.$uniqName"] = $item->customField->validation ?? 'string|nullable|sometimes';
                }
            }
        }
    }

    public function renderMultiTranslationAttribute(
        array      &$params,
        Collection $translations
    ): void
    {
        $languages = Language::all();
        foreach ($languages as $language) {
            foreach ($translations as $item) {
                /**
                 * @var Translation $item
                 */
                $uniqName = $item->customField->params['uniqName'];

                $params["translation.$language->locale.$uniqName"] = trans($item->customField->label);
            }
        }
    }
}
