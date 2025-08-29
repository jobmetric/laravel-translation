<?php

namespace JobMetric\Translation\Tests\Feature\Http\Requests;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use JobMetric\Language\Facades\Language;
use JobMetric\Translation\Exceptions\ModelHasTranslationNotFoundException;
use JobMetric\Translation\Rules\TranslationFieldExistRule;
use JobMetric\Translation\Tests\Stubs\Models\Post;
use JobMetric\Translation\Tests\Stubs\Models\VersionedPost;
use JobMetric\Translation\Tests\Stubs\Requests\DummyTranslationRequest;
use JobMetric\Translation\Tests\TestCase;
use Mockery;

/**
 * Rules builder tests for MultiTranslationArrayRequest.
 *
 * We mock the underlying 'Language' service binding so that Language::all()
 * returns a predictable list of locales without touching DB.
 */
class MultiTranslationArrayRequestRulesTest extends TestCase
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
    public function test_build_rules_for_model_with_wildcard_fields(): void
    {
        $this->bindLanguageMock(['fa', 'en']);

        $languages = Language::all();
        $this->assertInstanceOf(EloquentCollection::class, $languages);
        $this->assertNotEmpty($languages);

        $req = new DummyTranslationRequest();

        $rules = [];
        $req->renderMultiTranslationFiled(
            $rules,
            Post::class, // Post uses HasTranslation with default ['*']
            'title',
            null,
            -1,
            []
        );

        // Root container
        $this->assertArrayHasKey('translation', $rules);
        $this->assertSame('array', $rules['translation']);

        foreach ($languages as $lang) {
            $locale = $lang->locale;

            // Each locale container
            $this->assertArrayHasKey("translation.$locale", $rules);
            $this->assertSame('array', $rules["translation.$locale"]);

            // Primary unique field includes string + rule instance
            $this->assertArrayHasKey("translation.$locale.title", $rules);
            $this->assertIsArray($rules["translation.$locale.title"]);
            $this->assertContains('string', $rules["translation.$locale.title"]);
            $this->assertTrue(
                collect($rules["translation.$locale.title"])
                    ->contains(fn($r) => $r instanceof TranslationFieldExistRule)
            );

            // Wildcard acceptance for other fields
            $this->assertArrayHasKey("translation.$locale.*", $rules);
            $this->assertSame('string|nullable|sometimes', $rules["translation.$locale.*"]);
        }
    }

    /**
     * @throws ModelHasTranslationNotFoundException
     */
    public function test_build_rules_for_model_with_explicit_allowed_fields(): void
    {
        $this->bindLanguageMock(['fa', 'en']);

        $languages = Language::all();
        $this->assertInstanceOf(EloquentCollection::class, $languages);
        $this->assertNotEmpty($languages);

        $req = new DummyTranslationRequest();

        $rules = [];
        $req->renderMultiTranslationFiled(
            $rules,
            VersionedPost::class, // ['title','summary']
            'title'
        );

        // Root
        $this->assertArrayHasKey('translation', $rules);
        $this->assertSame('array', $rules['translation']);

        foreach ($languages as $lang) {
            $locale = $lang->locale;

            $this->assertSame('array', $rules["translation.$locale"]);

            // Primary field rule
            $this->assertIsArray($rules["translation.$locale.title"]);
            $this->assertContains('string', $rules["translation.$locale.title"]);
            $this->assertTrue(
                collect($rules["translation.$locale.title"])
                    ->contains(fn($r) => $r instanceof TranslationFieldExistRule)
            );

            // Other allowed fields present, wildcard should NOT exist
            $this->assertArrayHasKey("translation.$locale.summary", $rules);
            $this->assertSame('string|nullable|sometimes', $rules["translation.$locale.summary"]);
            $this->assertArrayNotHasKey("translation.$locale.*", $rules);
        }
    }

    /**
     * @throws ModelHasTranslationNotFoundException
     */
    public function test_update_flow_excludes_current_object_id(): void
    {
        $this->bindLanguageMock(['fa', 'en']);

        $languages = Language::all();
        $this->assertNotEmpty($languages);

        $req = new DummyTranslationRequest();

        $rules = [];
        $req->renderMultiTranslationFiled(
            $rules,
            Post::class,
            'title',
            123 // simulate update: exclude current record
        );

        $locale = $languages->first()->locale;

        $this->assertIsArray($rules["translation.$locale.title"]);
        $rule = collect($rules["translation.$locale.title"])
            ->first(fn($r) => $r instanceof TranslationFieldExistRule);

        $this->assertInstanceOf(TranslationFieldExistRule::class, $rule);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
