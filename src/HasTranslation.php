<?php

namespace JobMetric\Translation;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use JobMetric\Translation\Events\TranslationStoredEvent;
use JobMetric\Translation\Exceptions\ModelTranslationContractNotFoundException;
use JobMetric\Translation\Exceptions\TranslationDisallowFieldException;
use JobMetric\Translation\Models\Translation as TranslationModel;
use Throwable;

/**
 * @method morphOne(string $class, string $string)
 * @method morphMany(string $class, string $string)
 */
trait HasTranslation
{
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
     * translation has many relationship
     *
     * @return MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(TranslationModel::class, 'translatable');
    }

    /**
     * scope locale for select translations relationship
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
     * @throws TranslationDisallowFieldException
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
     * has translation
     *
     * @param string $key
     * @param string|null $locale
     *
     * @return bool
     */
    public function hasTranslation(string $key, string $locale = null): bool
    {
        $locale = $locale ?: app()->getLocale();

        return $this->translationsTo($locale)->where('key', $key)->exists();
    }
}
