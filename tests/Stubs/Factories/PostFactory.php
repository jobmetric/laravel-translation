<?php

namespace JobMetric\Translation\Tests\Stubs\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Translation\Tests\Stubs\Models\Post;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => $this->faker->slug,
            'translation' => [],
        ];
    }

    /**
     * set slug
     *
     * @param string $slug
     *
     * @return static
     */
    public function setSlug(string $slug): static
    {
        return $this->state(fn(array $attributes) => [
            'slug' => $slug,
        ]);
    }

    /**
     * set translation
     *
     * @param array $translation
     *
     * @return static
     */
    public function setTranslation(array $translation): static
    {
        return $this->state(fn(array $attributes) => [
            'translation' => $translation,
        ]);
    }
}
