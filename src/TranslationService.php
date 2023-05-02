<?php

namespace JobMetric\Translation;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use JobMetric\Metadata\MetadataService;
use JobMetric\Translation\Models\Translation;

class TranslationService
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * The metadata instance.
     *
     * @var MetadataService
     */
    protected MetadataService $metadataService;

    /**
     * Create a new Translation instance.
     *
     * @param Application $app
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->metadataService = $app->make('MetadataService');
    }

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

                /**
                 * @var Translation $translation
                 */
                $translation = $model->translations()->updateOrCreate([
                    'locale' => $locale
                ], [
                    'title' => $title
                ]);

                foreach($value as $key => $item) {
                    $this->metadataService->store($translation, $key, $item);
                }

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
    public function get(Model $model, string $key = 'title'): mixed
    {
        $cache_time = config('translation.cache_time');

        return Cache::remember($this->cacheKey($model::class, $model->id, $key, app()->getLocale()), $cache_time, function () use ($model, $key) {
            if($key == 'title') {
                return $model?->translation->title;
            } else {
                return $this->metadataService->get($model?->translation, $key);
            }
        });
    }

    /**
     * generate cache key
     *
     * @param string $type
     * @param int    $id
     * @param string $key
     * @param string $local
     *
     * @return string
     */
    private function cacheKey(string $type, int $id, string $key, string $local): string
    {
        return 'translated:'.class_basename($type).':'.$id.':'.$key.':'.$local;
    }
}
