<?php

namespace JobMetric\Translation\Tests\Feature\Http\Requests;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use JobMetric\Language\Facades\Language;
use JobMetric\Translation\Exceptions\ModelHasTranslationNotFoundException;
use JobMetric\Translation\Tests\Stubs\Models\Post;
use JobMetric\Translation\Tests\Stubs\Models\VersionedPost;
use JobMetric\Translation\Tests\Stubs\Requests\DummyTranslationRequest;
use JobMetric\Translation\Tests\TestCase;
use Mockery;

/**
 * Attributes builder tests for MultiTranslationArrayRequest.
 *
 * Language service is mocked so that Language::all() returns stable locales.
 */
class MultiTranslationArrayRequestAttributesTest extends TestCase
{
    /**
     * Bind a mock for the Language service so that Language::all()
     * returns a collection of language-like objects with a 'locale' property.
     *
     * @param array<int, string> $locales
     * @return void
     */
    protected function bindLanguageMock(array $locales = ['fa', 'en']): void
    {
        $langs = array_map(function (string $loc) {
            return (object)['locale' => $loc];
        }, $locales);

        $mock = Mockery::mock(\JobMetric\Language\Language::class);
        $mock->shouldReceive('all')
            ->byDefault()
            ->andReturn(new EloquentCollection($langs));

        $this->app->instance('Language', $mock);
    }

    /**
     * @throws ModelHasTranslationNotFoundException
     */
    public function test_attributes_for_model_with_wildcard_fields_without_scope(): void
    {
        $this->bindLanguageMock(['fa', 'en']);

        $languages = Language::all();
        $this->assertInstanceOf(EloquentCollection::class, $languages);
        $this->assertNotEmpty($languages);

        $req = new DummyTranslationRequest();

        $attrs = [];
        $req->renderMultiTranslationAttribute(
            $attrs,
            Post::class, // wildcard ['*']
            null
        );

        foreach ($languages as $lang) {
            $locale = $lang->locale;
            $this->assertArrayHasKey("translation.$locale.*", $attrs);
            $this->assertSame("translation $locale", $attrs["translation.$locale.*"]);
        }
    }

    /**
     * @throws ModelHasTranslationNotFoundException
     */
    public function test_attributes_for_model_with_explicit_fields_without_scope(): void
    {
        $this->bindLanguageMock(['fa', 'en']);

        $languages = Language::all();
        $this->assertNotEmpty($languages);

        $req = new DummyTranslationRequest();

        $attrs = [];
        $req->renderMultiTranslationAttribute(
            $attrs,
            VersionedPost::class, // ['title','summary']
            null
        );

        foreach ($languages as $lang) {
            $locale = $lang->locale;
            $this->assertSame('title', $attrs["translation.$locale.title"]);
            $this->assertSame('summary', $attrs["translation.$locale.summary"]);
        }
    }

    /**
     * @throws ModelHasTranslationNotFoundException
     */
    public function test_attributes_with_trans_scope_and_explicit_fields(): void
    {
        $this->bindLanguageMock(['fa', 'en']);

        $languages = Language::all();
        $this->assertNotEmpty($languages);

        $req = new DummyTranslationRequest();

        $attrs = [];
        // When no translation line exists, trans() returns the key itself
        $req->renderMultiTranslationAttribute(
            $attrs,
            VersionedPost::class,
            'labels.translation.{field}'
        );

        foreach ($languages as $lang) {
            $locale = $lang->locale;
            $this->assertSame('labels.translation.title', $attrs["translation.$locale.title"]);
            $this->assertSame('labels.translation.summary', $attrs["translation.$locale.summary"]);
        }
    }

    /**
     * @throws ModelHasTranslationNotFoundException
     */
    public function test_attributes_with_trans_scope_and_wildcard_fields(): void
    {
        $this->bindLanguageMock(['fa', 'en']);

        $languages = Language::all();
        $this->assertNotEmpty($languages);

        $req = new DummyTranslationRequest();

        $attrs = [];
        $req->renderMultiTranslationAttribute(
            $attrs,
            Post::class, // wildcard model
            'labels.translation.any'
        );

        foreach ($languages as $lang) {
            $locale = $lang->locale;
            $this->assertSame('labels.translation.any', $attrs["translation.$locale.*"]);
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
