<?php

namespace JobMetric\Translation;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use JobMetric\Translation\Events\TranslationForgetEvent;
use JobMetric\Translation\Events\TranslationStoredEvent;
use JobMetric\Translation\Exceptions\ModelTranslationContractNotFoundException;
use JobMetric\Translation\Exceptions\TranslationDisallowFieldException;
use JobMetric\Translation\Models\Translation as TranslationModel;
use Throwable;

/**
 * Trait HasTranslation
 *
 * @package JobMetric\Translation
 *
 * @property TranslationModel[] $translation
 * @property array $translation
 * @method morphOne(string $class, string $string)
 * @method morphMany(string $class, string $string)
 */
trait HasTranslation
{
    private array $innerTranslations = [];

    public function initializeHasTranslation(): void
    {
        $this->mergeFillable(['translation']);
    }

    /**
     * boot has translation
     *
     * @return void
     * @throws Throwable
     */
    public static function bootHasTranslation(): void
    {
        if (!in_array('JobMetric\Translation\Contracts\TranslationContract', class_implements(self::class))) {
            throw new ModelTranslationContractNotFoundException(self::class);
        }

        // translation key in all models must be the same in input
        // means translation [ locale => [ key => value ] ]
        $checkerClosure = function ($model) {

            if (isset($model->attributes['translation'])) {
                $translation = $model->attributes['translation'];
                foreach ($translation as $locale => $translationData) { //TODO locale must be checked here 
                    $keys = array_keys($translationData);
                    $fieldsThatAreNotExistsInAllowedFields = array_diff($keys, $model->translationAllowFields());
                    if (count($fieldsThatAreNotExistsInAllowedFields) > 0) {
                        throw new TranslationDisallowFieldException($model::class, $fieldsThatAreNotExistsInAllowedFields);
                    }
                }
                
                $model->innerTranslations = $translation;
                unset($model->attributes['translation']);
            }
        };
        static::creating($checkerClosure);
        static::updating($checkerClosure);
        static::saving($checkerClosure);


        $savingAndUpdatingClosure = function($model){

            if (count($model->innerTranslations) > 0) {
                $translation = $model->innerTranslations;
                foreach ($translation as $locale => $translationData) {
                    $model->translate($locale, $translationData);
                }
                $model->innerTranslations = [];
            }
        };

        static::created($savingAndUpdatingClosure);
        static::updated($savingAndUpdatingClosure);
        static::saved($savingAndUpdatingClosure);

    }

    /**
     * translation has one relationship
     *
     * @return MorphOne
     */
    public function translation(): MorphOne
    {
        return $this->morphOne(TranslationModel::class, 'translatable');
    }

    /**
     * translation has many relationships
     *
     * @return MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(TranslationModel::class, 'translatable');
    }

    /**
     * scope locale for select translation relationship
     *
     * @param string $locale
     *
     * @return MorphOne
     */
    public function translationTo(string $locale): MorphOne
    {
        return $this->translation()->where('locale', $locale);
    }

    /**
     * scope locale for select translations relationship
     *
     * @param string $locale
     *
     * @return MorphMany
     */
    public function translationsTo(string $locale): MorphMany
    {
        return $this->translations()->where('locale', $locale);
    }

    /**
     * store translate
     *
     * @param string $locale
     * @param array $data
     *
     * @return static
     * @throws Throwable
     */
    public function translate(string $locale, array $data): static
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->translationAllowFields())) {
                $this->translation()->updateOrCreate([
                    'locale' => $locale,
                    'key' => $key,
                ], [
                    'value' => $value,
                ]);
            } else {
                throw new TranslationDisallowFieldException(self::class, $key);
            }
        }

        event(new TranslationStoredEvent($this, $locale, $data));

        return $this;
    }

    /**
     * load translation after model loaded
     *
     * @param string $locale
     * @param string $key
     *
     * @return static
     */
    public function withTranslation(string $locale, string $key): static
    {
        $this->load(['translation' => function ($query) use ($locale, $key) {
            $query->where([
                'locale' => $locale,
                'key' => $key
            ]);
        }]);

        return $this;
    }

    /**
     * load translations after model loaded
     *
     * @param string|null $locale
     *
     * @return static
     */
    public function withTranslations(string $locale = null): static
    {
        if (is_null($locale)) {
            $this->load('translations');
        } else {
            $this->load(['translations' => function ($query) use ($locale) {
                $query->where('locale', $locale);
            }]);
        }

        return $this;
    }

    /**
     * has translation field
     *
     * @param string $key
     * @param string|null $locale
     *
     * @return bool
     */
    public function hasTranslationField(string $key = 'title', string $locale = null): bool
    {
        $locale = $locale ?: app()->getLocale();

        return $this->translationsTo($locale)->where('key', $key)->exists();
    }

    /**
     * get translation
     *
     * @param string $key
     * @param string|null $locale
     *
     * @return string|null
     */
    public function getTranslation(string $key, string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return $this->translationsTo($locale)->where('key', $key)->first()?->value;
    }

    /**
     * get translations
     *
     * @param string|null $locale
     *
     * @return array
     */
    public function getTranslations(string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();

        return $this->translationsTo($locale)->pluck('value', 'key')->toArray();
    }

    /**
     * forget translation
     *
     * @param string $key
     * @param string $locale
     *
     * @return static
     */
    public function forgetTranslation(string $key, string $locale): static
    {
        $this->translationsTo($locale)->where('key', $key)->get()->each(function ($translation) {
            $translation->delete();

            event(new TranslationForgetEvent($translation));
        });

        return $this;
    }

    /**
     * forget translations
     *
     * @param string|null $locale
     *
     * @return static
     */
    public function forgetTranslations(string $locale = null): static
    {
        if (is_null($locale)) {
            $this->translations()->get()->each(function ($translation) {
                $translation->delete();

                event(new TranslationForgetEvent($translation));
            });
        } else {
            $this->translationsTo($locale)->get()->each(function ($translation) {
                $translation->delete();

                event(new TranslationForgetEvent($translation));
            });
        }

        return $this;
    }
}
