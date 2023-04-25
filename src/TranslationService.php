<?php

namespace JobMetric\Translation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    /**
     * store translation
     *
     * @param Model $model
     * @param array $data
     *
     * @return void
     */
    public function store(Model $model, array $data = []): void
    {
        foreach($data as $locale => $value) {
            if(isset($value['title']) && $value['title'] != '') {
                $title = $value['title'];

                unset($value['title']);

                $model->translations()->updateOrCreate([
                    'locale' => $locale
                ], [
                    'title' => $title,
                    'data'  => $value
                ]);

                foreach($value as $key => $val) {
                    Cache::forget($this->cacheKey($model::class, $model->id, $key, $locale));
                }
            }
        }
    }

    /**
     * get translation filed
     *
     * @param Model  $model
     * @param string $key
     *
     * @return mixed
     */
    public function get(Model $model, string $key): mixed
    {
        return Cache::rememberForever($this->cacheKey($model::class, $model->id, $key, app()->getLocale()), function () use ($model, $key) {
            if($key == 'title') {
                return $model?->translation->title;
            } else {
                return $model?->translation?->data?->{$key};
            }
        });
    }

    /**
     * generate cache key
     *
     * @param string $type
     * @param int $id
     * @param string $key
     * @param string $local
     *
     * @return string
     */
    private function cacheKey(string $type, int $id, string $key, string $local): string
    {
        return 'translated:' . class_basename($type) . ':' . $id . ':' . $key . ':' . $local;
    }
}
