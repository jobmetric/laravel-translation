<?php

namespace JobMetric\Translation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use JobMetric\Translation\Exceptions\ModelTranslationContractNotFoundException;
use JobMetric\Translation\Exceptions\TranslationDisallowFieldException;
use JobMetric\Translation\Models\Translation;
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
        return $this->morphOne(Translation::class, 'translatable');
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
     * translation has many relationship
     *
     * @return MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
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
     * add translate
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
            if(in_array($key, $this->translationAllowFields())) {
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

        return $this;
    }
}
