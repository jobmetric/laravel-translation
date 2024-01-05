<?php

use Illuminate\Database\Eloquent\Model;
use JobMetric\Translation\Translation;

if(!function_exists('translation')) {
    /**
     * Service store translation
     *
     * @param Model $model
     * @param array $data
     *
     * @return void
     * @throws Throwable
     */
    function translation(Model $model, array $data = []): void
    {
        Translation::store($model, $data);
    }
}
