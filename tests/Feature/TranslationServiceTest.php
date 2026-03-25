<?php

namespace JobMetric\Translation\Tests\Feature;

use JobMetric\PackageCore\Output\Response;
use JobMetric\Translation\Facades\Translation as TranslationFacade;
use JobMetric\Translation\Tests\Stubs\Models\RequestablePost;
use JobMetric\Translation\Tests\TestCase as BaseTestCase;
use Throwable;

class TranslationServiceTest extends BaseTestCase
{
    /**
     * @return array<string, mixed>
     */
    protected function requestContext(): array
    {
        return [
            'model_class' => RequestablePost::class,
            'table' => 'posts',
            'attribute_path' => 'translation.fields.{field}',
        ];
    }

    /**
     * @throws Throwable
     */
    public function test_service_set_translation_uses_internal_request(): void
    {
        $post = RequestablePost::query()->create(['slug' => 'service-translation']);

        $response = app('translation')->setTranslation(
            data: [
                'locale' => 'fa',
                'translatable_id' => $post->id,
                'translation' => [
                    'fa' => [
                        'name' => 'نام از سرویس',
                    ],
                ],
            ],
            requestContext: $this->requestContext(),
            modelClass: RequestablePost::class,
            message: 'translated by service'
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->ok);
        $this->assertSame('translated by service', $response->message);
        $this->assertSame(200, $response->status);

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => RequestablePost::class,
            'translatable_id' => $post->id,
            'locale' => 'fa',
            'field' => 'name',
            'value' => 'نام از سرویس',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_translation_helper_sets_translation(): void
    {
        $post = RequestablePost::query()->create(['slug' => 'helper-translation']);

        $response = translation_set(
            data: [
                'locale' => 'en',
                'translatable_id' => $post->id,
                'translation' => [
                    'en' => [
                        'name' => 'Name from helper',
                        'summary' => 'Summary from helper',
                    ],
                ],
            ],
            requestContext: $this->requestContext(),
            modelClass: RequestablePost::class,
            message: 'translated by helper'
        );

        $this->assertTrue($response->ok);
        $this->assertSame('translated by helper', $response->message);

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => RequestablePost::class,
            'translatable_id' => $post->id,
            'locale' => 'en',
            'field' => 'name',
            'value' => 'Name from helper',
        ]);

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => RequestablePost::class,
            'translatable_id' => $post->id,
            'locale' => 'en',
            'field' => 'summary',
            'value' => 'Summary from helper',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_translation_facade_sets_translation(): void
    {
        $post = RequestablePost::query()->create(['slug' => 'facade-translation']);

        $response = TranslationFacade::setTranslation(
            data: [
                'locale' => 'en',
                'translatable_id' => $post->id,
                'translation' => [
                    'en' => [
                        'name' => 'Name from facade',
                    ],
                ],
            ],
            requestContext: $this->requestContext(),
            modelClass: RequestablePost::class,
            message: 'translated by facade'
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->ok);
        $this->assertSame('translated by facade', $response->message);

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => RequestablePost::class,
            'translatable_id' => $post->id,
            'locale' => 'en',
            'field' => 'name',
            'value' => 'Name from facade',
        ]);
    }
}
