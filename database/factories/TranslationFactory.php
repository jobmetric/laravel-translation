<?php

namespace JobMetric\Translation\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Translation\Models\Translation;

/**
 * TranslationFactory
 *
 * Provides default fake data for the Translation model aligned with the latest schema:
 * - Uses 'field' instead of the deprecated 'key'
 * - Supplies a textual 'value'
 * - Includes an explicit 'version' (defaults to 1)
 *
 * @extends Factory<Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * The associated model class for the factory.
     *
     * @var class-string<Translation>
     */
    protected $model = Translation::class;

    /**
     * Define the model's default state.
     *
     * Generates a portable default payload for a translation row.
     * - 'translatable_id' and 'translatable_type' are nullable to allow polymorphic assignment later.
     * - 'locale' defaults to 'en'.
     * - 'field' defaults to 'title'.
     * - 'value' is a coherent text string (not an array).
     * - 'version' defaults to 1 and can be adjusted via a state helper.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'translatable_type' => null,
            'translatable_id' => null,
            'locale' => 'en',
            'field' => 'title',
            'value' => $this->faker->paragraph(), // ensure string, not array
            'version' => 1,
            'deleted_at' => null,
        ];
    }

    /**
     * Set the polymorphic translatable target.
     *
     * @param int $translatableId The target model primary key.
     * @param string $translatableType The target model morph class (FQCN).
     *
     * @return static
     */
    public function setTranslatable(int $translatableId, string $translatableType): static
    {
        return $this->state(fn(array $attributes) => [
            'translatable_id' => $translatableId,
            'translatable_type' => $translatableType,
        ]);
    }

    /**
     * Set the locale code (e.g., 'en', 'fa').
     *
     * @param string $locale The IETF/BCP47-like short code you store (e.g., 'en', 'fa').
     *
     * @return static
     */
    public function setLocale(string $locale): static
    {
        return $this->state(fn(array $attributes) => [
            'locale' => $locale,
        ]);
    }

    /**
     * Set the translated field name (e.g., 'title', 'body').
     *
     * @param string $field The model attribute key being translated.
     *
     * @return static
     */
    public function setField(string $field): static
    {
        return $this->state(fn(array $attributes) => [
            'field' => $field,
        ]);
    }

    /**
     * Set the translation value (text).
     *
     * @param string $value The translated content.
     *
     * @return static
     */
    public function setValue(string $value): static
    {
        return $this->state(fn(array $attributes) => [
            'value' => $value,
        ]);
    }

    /**
     * Set the version number for the translation.
     *
     * @param int $version The version sequence (>=1).
     *
     * @return static
     */
    public function setVersion(int $version): static
    {
        return $this->state(fn(array $attributes) => [
            'version' => $version,
        ]);
    }

    /**
     * Mark the translation as soft-deleted.
     *
     * @param string|null $deletedAt The deletion timestamp (null for current time).
     *
     * @return static
     */
    public function markAsDeleted(?string $deletedAt = null): static
    {
        return $this->state(fn(array $attributes) => [
            'deleted_at' => $deletedAt ?? now(),
        ]);
    }
}
