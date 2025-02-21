<?php

namespace JobMetric\Translation\Http\Requests;

use JobMetric\Translation\Rules\TranslationFieldExistRule;

trait TranslationArrayRequest
{
    public function renderTranslationFiled(
        array    &$rules,
        array    $data,
        string   $model_name,
        string   $field_name = 'name',
        int|null $object_id = null,
        int|null $parent_id = -1,
        array    $parent_where = []
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
            'required',
            'string',
            new TranslationFieldExistRule($model_name, $field_name, $locale, $object_id, $parent_id, $parent_where)
        ];

        $translations = (new $model_name)->translationAllowFields();

        foreach ($translations as $item) {
            if ($item !== $field_name) {
                $rules["translation.$locale.$item"] = 'string|nullable|sometimes';
            }
        }
    }

    public function renderTranslationAttribute(
        array  &$params,
        array  $data,
        string $model_name,
        string $trans_scope = null,
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

        $translations = (new $model_name)->translationAllowFields();

        foreach ($translations as $item) {
            if ($item == 'meta_title' || $item == 'meta_description' || $item == 'meta_keywords') {
                $params["translation.$locale.$item"] = trans('translation::base.components.translation_card.fields.'.$item.'.label');
            } else {
                $params["translation.$locale.$item"] = trans(str_replace('{field}', $item, $trans_scope));
            }
        }
    }
}
