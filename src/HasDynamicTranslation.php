<?php

namespace JobMetric\Translation;

use JobMetric\Translation\Typeify\Translation;
use Throwable;

/**
 * Trait HasDynamicTranslation
 *
 * Provides dynamic translation-allowed fields per model "type" based on
 * the service type registry returned by getServiceTypeClass(static::class).
 *
 * Role:
 * - On boot, inspects all registered types and collects Translation fields' uniqName
 *   into a per-class static cache keyed by type name.
 * - Exposes translationAllowFields() to retrieve allowed fields for the instance's current type.
 * - Offers helper methods to refresh and fetch fields for specific types.
 *
 * Requirements:
 * - A global/helper function getServiceTypeClass(string $fqcn) must return a service type registry
 *   exposing: getTypes(): array<string>, and type(string $typeName) with getTranslation(): iterable<Translation>.
 */
trait HasDynamicTranslation
{
    /**
     * Per-class cache of allowed translation fields indexed by type name.
     *
     * @var array<string, array<int, string>>
     */
    protected static array $dynamicTranslation = [];

    /**
     * Internal flag to avoid rebuilding cache on repeated boot cycles.
     *
     * @var bool
     */
    protected static bool $dynamicTranslationBooted = false;

    /**
     * Boot hook for the trait.
     *
     * Role: Build/refresh the dynamic translation cache once per class.
     *
     * @return void
     */
    public static function bootHasDynamicTranslation(): void
    {
        if (static::$dynamicTranslationBooted) {
            return;
        }

        static::refreshDynamicTranslationCache();
        static::$dynamicTranslationBooted = true;
    }

    /**
     * Return the list of allowed translation fields for the current instance's type.
     *
     * Role: Reads the type from the model using dynamicTranslationFieldTypeName(), and
     *       returns the precomputed field list for that type.
     *
     * @return array<int, string>
     */
    public function translationAllowFields(): array
    {
        $fieldName = $this->dynamicTranslationFieldTypeName();

        if (!isset($this->{$fieldName})) {
            return [];
        }

        $type = (string) $this->{$fieldName};

        return static::$dynamicTranslation[$type] ?? [];
    }

    /**
     * Refresh the dynamic translation cache from the service type registry.
     *
     * Role: Rebuilds the per-class cache by scanning all types and collecting uniqName for each.
     *
     * @return void
     */
    public static function refreshDynamicTranslationCache(): void
    {
        static::$dynamicTranslation = [];

        try {
            $serviceType = getServiceTypeClass(static::class);
        } catch (Throwable) {
            // If the service type registry cannot be resolved, keep cache empty.
            return;
        }

        $types = (array) $serviceType->getTypes();

        foreach ($types as $type) {
            $innerType = $serviceType->type($type);

            $fields = [];

            foreach ($innerType->getTranslation() as $translation) {
                /** @var Translation $translation */
                $uniqName = $translation->customField->params['uniqName'] ?? null;

                if (is_string($uniqName) && $uniqName !== '') {
                    $fields[] = $uniqName;
                }
            }

            // De-duplicate while preserving order
            $fields = array_values(array_unique($fields));

            static::$dynamicTranslation[$type] = $fields;
        }
    }

    /**
     * Get allowed translation fields for a specific type.
     *
     * Role: Useful when you need fields for a type different from the current instance type.
     *
     * @param string $type The type identifier to look up.
     *
     * @return array<int, string>
     */
    public static function getDynamicTranslationFieldsFor(string $type): array
    {
        return static::$dynamicTranslation[$type] ?? [];
    }

    /**
     * Name of the attribute on the model that stores the "type" discriminator.
     *
     * Role: Override this method in your model if your type field is not "type".
     *
     * @return string
     */
    public function dynamicTranslationFieldTypeName(): string
    {
        return 'type';
    }
}
