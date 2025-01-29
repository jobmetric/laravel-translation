<?php

namespace JobMetric\Translation\Http\Requests;

use JobMetric\Language\Facades\Language;
use JobMetric\Translation\Rules\TranslationFieldExistRule;

trait MultiTranslationArrayRequest
{
    public function renderMultiTranslationFiled(
        array    &$rules,
        string   $model_name,
        string   $field_name = 'name',
        int|null $object_id = null,
        int|null $parent_id = -1,
        array    $parent_where = []
    ): void
    {
        $rules["translation"] = 'array';

        $languages = Language::all();
        foreach ($languages as $language) {
            $rules["translation.$language->locale"] = 'array';
            $rules["translation.$language->locale.$field_name"] = [
                'string',
                new TranslationFieldExistRule($model_name, $field_name, $language->locale, $object_id, $parent_id, $parent_where),
            ];

            $translations = (new $model_name)->translationAllowFields();

            foreach ($translations as $item) {
                if ($item !== $field_name) {
                    $rules["translation.$language->locale.$item"] = 'string|nullable|sometimes';
                }
            }
        }
    }

    public function renderMultiTranslationAttribute(
        array  &$params,
        string $model_name,
        string $trans_scope = null,
    ): void
    {
        $translations = (new $model_name)->translationAllowFields();

        $languages = Language::all();
        foreach ($languages as $language) {
            foreach ($translations as $item) {
                $params["translation.$language->locale.$item"] = trans(str_replace('{field}', $item, $trans_scope));
            }
        }
    }
}
