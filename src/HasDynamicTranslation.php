<?php

namespace JobMetric\Translation;

use JobMetric\Translation\Typeify\Translation;

/**
 * HasDynamicTranslation
 *
 * @property static array $dynamicTranslation
 */
trait HasDynamicTranslation
{
    protected static array $dynamicTranslation = [];

    /**
     * Boot Has Dynamic Translation
     *
     * @return void
     */
    public static function bootHasDynamicTranslation(): void
    {
        $serviceType = getServiceTypeClass(static::class);

        $types = $serviceType->getTypes();

        foreach ($types as $type) {
            $innerType = $serviceType->type($type);

            foreach ($innerType->getTranslation() as $translation) {
                /**
                 * @var Translation $translation
                 */
                self::$dynamicTranslation[$type][] = $translation->customField->params['uniqName'];
            }
        }
    }

    /**
     * translation allow fields.
     *
     * @return array
     */
    public function translationAllowFields(): array
    {
        return self::$dynamicTranslation[$this->{$this->dynamicTranslationFieldTypeName()}] ?? [];
    }

    /**
     * translation filed type name.
     *
     * @return string
     */
    public function dynamicTranslationFieldTypeName(): string
    {
        return 'type';
    }
}
