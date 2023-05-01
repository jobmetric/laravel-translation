<?php

use Illuminate\Database\Eloquent\Model;
use JobMetric\Translation\TranslationService;

if(!function_exists('translation')) {
    /**
     * Service store translation
     *
     * @param Model $model
     * @param array $data
     *
     * @return void
     */
    function translation(Model $model, array $data = []): string
    {
        return TranslationService::store($model, $data);
    }
}
