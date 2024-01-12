<?php

use Illuminate\Database\Eloquent\Model;
use JobMetric\Translation\Exceptions\ModelHasTranslationNotFoundException;

if (!function_exists('translation')) {
    /**
     * store translation
     *
     * @param Model $model
     * @param array $data
     *
     * @return void
     * @throws Throwable
     */
    function translation(Model $model, array $data = []): void
    {
        if (!in_array('JobMetric\Translation\HasTranslation', class_uses($model))) {
            throw new ModelHasTranslationNotFoundException($model::class);
        }

        foreach ($data as $locale => $item) {
            $model->translate($locale, $item);
        }
    }
}
