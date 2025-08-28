<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as CollectionSupport;
use JobMetric\Translation\Exceptions\ModelHasTranslationNotFoundException;
use JobMetric\Translation\HasTranslation;

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
        if (!is_subclass_of($model, HasTranslation::class)) {
            throw new ModelHasTranslationNotFoundException($model::class);
        }

        foreach ($data as $locale => $item) {
            $model->translate($locale, $item);
        }
    }
}

if (!function_exists('translationResourceData')) {
    /**
     * translation resource data
     *
     * @param mixed $relations
     * @param string|null $locale
     *
     * @return array
     */
    function translationResourceData(mixed $relations, string $locale = null): array
    {
        $data = [];
        foreach ($relations as $relation) {
            if ($locale && $relation->locale !== $locale) {
                continue;
            }

            $data[$relation->locale][$relation->key] = $relation->value;
        }

        return $data;
    }
}

if (!function_exists('translationDataSelect')) {
    /**
     * translation data for select
     *
     * @param Collection $objects
     * @param string $field
     * @param string|null $locale
     *
     * @return CollectionSupport|Collection
     */
    function translationDataSelect(Collection $objects, string $field, string $locale = null): Collection|CollectionSupport
    {
        return $objects->mapWithKeys(function ($object) use ($field, $locale) {
            return [
                $object->id => $object->translations()->select('value')->where([
                    'locale' => $locale ?: app()->getLocale(),
                    'key' => $field
                ])->first()->toArray()['value']
            ];
        });
    }
}
