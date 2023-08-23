<?php

namespace JobMetric\Translation;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use JobMetric\Translation\Exceptions\ModelHasTranslationTraitNotFoundException;
use JobMetric\Metadata\JMetadata;
use JobMetric\Translation\Http\Resources\TranslationResource;
use JobMetric\Translation\Models\Translation;
use Throwable;

class JTranslation
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
     * @var JMetadata
     */
    protected JMetadata $JMetadata;

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

        $this->JMetadata = $app->make('JMetadata');
    }

    /**
     * get translation
     *
     * @param Model       $model
     * @param string|null $key
     * @param string|null $locale
     *
     * @return mixed
     * @throws Throwable
     */
    public function get(Model $model, string $key = null, string $locale = null): mixed
    {
        if(!in_array('JobMetric\Translation\Traits\HasTranslation', class_uses($model))) {
            throw new ModelHasTranslationTraitNotFoundException($model::class);
        }

        $cache_time = config('j-translation.cache_time');

        if(is_null($key)) {
            return Cache::remember($this->cacheKey($model::class, $model->id, $locale), $cache_time, function () use ($model, $key, $locale) {
                if(is_null($locale)) {
                    $object = $model->with('translations.metaable')->first();
                    return TranslationResource::collection($object->translations)->toArray(request());
                }

                $object = $model->translationTo($locale)->first()?->load('metaable');
                return TranslationResource::make($object)->toArray(request());
            });
        }

        if(is_null($locale)) {
            $locale = app()->getLocale();
        }

        return Cache::remember($this->cacheKey($model::class, $model->id, $locale, $key), $cache_time, function () use ($model, $key, $locale) {
            if($key == 'title') {
                return $model->translationTo($locale)->first()?->title;
            } else {
                return $this->JMetadata->get($model->translationTo($locale)->first(), $key);
            }
        });
    }

    /**
     * store translation
     *
     * @param Model $model
     * @param array $data
     *
     * @return void
     * @throws Throwable
     */
    public function store(Model $model, array $data = []): void
    {
        if(!in_array('JobMetric\Translation\Traits\HasTranslation', class_uses($model))) {
            throw new ModelHasTranslationTraitNotFoundException($model::class);
        }

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
                    $this->JMetadata->store($translation, $key, $item);

                    Cache::forget($this->cacheKey($model::class, $model->id, $locale));
                    Cache::forget($this->cacheKey($model::class, $model->id, $locale, $key));
                }
            }
        }
    }

    /**
     * delete translation
     *
     * @param Model       $model
     * @param string|null $locale
     *
     * @return Model
     * @throws Throwable
     */
    public function delete(Model $model, string $locale = null): Model
    {
        if(!in_array('JobMetric\Translation\Traits\HasTranslation', class_uses($model))) {
            throw new ModelHasTranslationTraitNotFoundException($model::class);
        }

        if(is_null($locale)) {
            Cache::forget($this->cacheKey($model::class, $model->id));

            $model->translations()->get()->each(function($item){
                $item->delete();
            });
        } else {
            Cache::forget($this->cacheKey($model::class, $model->id, $locale));

            $model->translationTo($locale)->get()->each(function($item){
                $item->delete();
            });
        }

        return $model;
    }

    /**
     * generate cache key
     *
     * @param string      $type
     * @param int         $id
     * @param string|null $locale
     * @param string|null $key
     *
     * @return string
     */
    private function cacheKey(string $type, int $id, string $locale = null, string $key = null): string
    {
        return 'translation:'.class_basename($type).':'.$id.(is_null($locale) ?: ':'.$locale).(is_null($key) ?: ':'.$key);
    }
}
