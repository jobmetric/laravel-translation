<?php

namespace JobMetric\Translation\Http\Requests;

use Illuminate\Support\Collection;
use JobMetric\Translation\Rules\TranslationFieldExistRule;
use JobMetric\Translation\ServiceType\Translation;

trait TranslationTypeObjectRequest
{
    public function renderTranslationFiled(
        array      &$rules,
        array      $data,
        Collection $translations,
        string     $class_name,
        string     $field_name = 'name',
        int|null   $object_id = null,
        int|null   $parent_id = -1,
        array      $parent_where = []
    ): void
    {
        if (array_key_exists('translation', $data)) {
            $translation = $data['translation'];

            $locale = '';
            foreach ($translation as $key => $value) {
                $locale = $key;
                break;
            }
        } else {
            $locale = app()->getLocale();
        }

        $rules["translation"] = 'array';
        $rules["translation.$locale"] = 'array';
        $rules["translation.$locale.$field_name"] = [
            'string',
            new TranslationFieldExistRule($class_name, $field_name, $locale, $object_id, $parent_id, $parent_where)
        ];

        foreach ($translations as $item) {
            /**
             * @var Translation $item
             */
            $uniqName = $item->customField->params['uniqName'] ?? null;

            if ($uniqName !== $field_name) {
                $rules["translation.$locale.$uniqName"] = $item->customField->validation ?? 'string|nullable|sometimes';
            }
        }
    }

    public function renderTranslationAttribute(
        array      &$params,
        array      $data,
        Collection $translations
    ): void
    {
        if (array_key_exists('translation', $data)) {
            $translation = $data['translation'];

            $locale = '';
            foreach ($translation as $key => $value) {
                $locale = $key;
                break;
            }
        } else {
            $locale = app()->getLocale();
        }

        foreach ($translations as $item) {
            /**
             * @var Translation $item
             */
            $uniqName = $item->customField->params['uniqName'];

            $params["translation.$locale.$uniqName"] = trans($item->customField->label);
        }
    }
}
