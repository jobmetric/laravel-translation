<?php

namespace JobMetric\Translation\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Translation\Models\Translation;

/**
 * @extends Factory<Translation>
 */
class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'translatable_id' => null,
            'translatable_type' => null,
            'locale' => 'en',
            'key' => 'title',
            'value' => $this->faker->words
        ];
    }

    /**
     * set translatable
     *
     * @param int $translatable_id
     * @param string $translatable_type
     *
     * @return static
     */
    public function setTranslatable(int $translatable_id, string $translatable_type): static
    {
        return $this->state(fn(array $attributes) => [
            'translatable_id' => $translatable_id,
            'translatable_type' => $translatable_type,
        ]);
    }

    /**
     * set locale
     *
     * @param string $locale
     *
     * @return static
     */
    public function setLocale(string $locale): static
    {
        return $this->state(fn(array $attributes) => [
            'locale' => $locale
        ]);
    }

    /**
     * set key
     *
     * @param string $key
     *
     * @return static
     */
    public function setKey(string $key): static
    {
        return $this->state(fn(array $attributes) => [
            'key' => $key
        ]);
    }

    /**
     * set value
     *
     * @param string $value
     *
     * @return static
     */
    public function setValue(string $value): static
    {
        return $this->state(fn(array $attributes) => [
            'value' => $value
        ]);
    }
}
