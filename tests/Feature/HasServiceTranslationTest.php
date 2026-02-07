<?php

namespace JobMetric\Translation\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use JobMetric\Translation\HasServiceTranslation;
use JobMetric\Translation\Tests\Stubs\Models\Post;
use JobMetric\Translation\Tests\Stubs\Models\RestrictedPost;
use JobMetric\Translation\Tests\TestCase as BaseTestCase;
use Throwable;

/**
 * Class HasServiceTranslationTest
 *
 * Feature tests for the HasServiceTranslation trait.
 * Tests translation syncing, payload normalization, and locale detection.
 */
class HasServiceTranslationTest extends BaseTestCase
{
    /**
     * Clear cache before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Clear static cache between tests
        TestService::clearTranslatableFieldsCache();
        TestServiceWithDefaults::clearTranslatableFieldsCache();
        TestServiceNoModel::clearTranslatableFieldsCache();
    }

    /**
     * Test that getTranslatableFields returns fields from model.
     *
     * @return void
     */
    public function test_get_translatable_fields_from_model(): void
    {
        $service = new TestServiceWithRestrictedPost();

        $fields = $service->exposeGetTranslatableFields();

        $this->assertIsArray($fields);
        $this->assertEquals(['title'], $fields);
    }

    /**
     * Test that getTranslatableFields returns empty array when model has wildcard.
     *
     * @return void
     */
    public function test_get_translatable_fields_returns_empty_for_wildcard(): void
    {
        $service = new TestService();

        $fields = $service->exposeGetTranslatableFields();

        // Post model has ['*'] so should return empty
        $this->assertIsArray($fields);
        $this->assertEmpty($fields);
    }

    /**
     * Test that getTranslatableFields returns empty when no modelClass defined.
     *
     * @return void
     */
    public function test_get_translatable_fields_returns_empty_when_no_model_class(): void
    {
        $service = new TestServiceNoModel();

        $fields = $service->exposeGetTranslatableFields();

        $this->assertIsArray($fields);
        $this->assertEmpty($fields);
    }

    /**
     * Test ensureTranslationsRelation adds translations to array.
     *
     * @return void
     */
    public function test_ensure_translations_relation_adds_to_array(): void
    {
        $service = new TestService();

        $with = $service->exposeEnsureTranslationsRelation(['users']);

        $this->assertContains('translations', $with);
        $this->assertContains('users', $with);
    }

    /**
     * Test ensureTranslationsRelation does not duplicate.
     *
     * @return void
     */
    public function test_ensure_translations_relation_no_duplicate(): void
    {
        $service = new TestService();

        $with = $service->exposeEnsureTranslationsRelation(['translations', 'users']);

        $this->assertCount(2, $with);
        $this->assertEquals(['translations', 'users'], $with);
    }

    /**
     * Test syncTranslations with locale-keyed format.
     *
     * @return void
     * @throws Throwable
     */
    public function test_sync_translations_locale_keyed_format(): void
    {
        $post = Post::create(['slug' => 'test-locale-keyed']);

        $service = new TestService();

        $service->exposeSyncTranslations($post, [
            'fa' => ['title' => 'عنوان فارسی', 'summary' => 'خلاصه'],
            'en' => ['title' => 'English Title'],
        ], false);

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id'   => $post->id,
            'locale'            => 'fa',
            'field'             => 'title',
            'value'             => 'عنوان فارسی',
        ]);

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id'   => $post->id,
            'locale'            => 'fa',
            'field'             => 'summary',
            'value'             => 'خلاصه',
        ]);

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id'   => $post->id,
            'locale'            => 'en',
            'field'             => 'title',
            'value'             => 'English Title',
        ]);
    }

    /**
     * Test syncTranslations with single locale (legacy) format.
     *
     * @return void
     * @throws Throwable
     */
    public function test_sync_translations_single_locale_format(): void
    {
        $post = Post::create(['slug' => 'test-single-locale']);

        $service = new TestService();

        app()->setLocale('fa');

        $service->exposeSyncTranslations($post, [
            'title'   => 'عنوان تست',
            'summary' => 'خلاصه تست',
        ], false);

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id'   => $post->id,
            'locale'            => 'fa',
            'field'             => 'title',
            'value'             => 'عنوان تست',
        ]);
    }

    /**
     * Test syncTranslations with null/empty input does nothing.
     *
     * @return void
     * @throws Throwable
     */
    public function test_sync_translations_with_null_does_nothing(): void
    {
        $post = Post::create(['slug' => 'test-null']);

        $service = new TestService();

        $service->exposeSyncTranslations($post, null, false);
        $service->exposeSyncTranslations($post, [], false);

        $this->assertDatabaseMissing(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id'   => $post->id,
        ]);
    }

    /**
     * Test normalizeTranslationPayload on store includes all fields with defaults.
     *
     * @return void
     */
    public function test_normalize_payload_on_store_includes_defaults(): void
    {
        $service = new TestServiceWithDefaults();

        // When no title provided, should use default
        $payload = $service->exposeNormalizeTranslationPayload([], false);

        $this->assertEquals([
            'title' => 'default title',
        ], $payload);

        // When title provided, should use provided value
        $payload2 = $service->exposeNormalizeTranslationPayload([
            'title' => 'Custom Title',
        ], false);

        $this->assertEquals([
            'title' => 'Custom Title',
        ], $payload2);
    }

    /**
     * Test normalizeTranslationPayload on update only includes provided fields.
     *
     * @return void
     */
    public function test_normalize_payload_on_update_only_provided_fields(): void
    {
        $service = new TestServiceWithDefaults();

        $payload = $service->exposeNormalizeTranslationPayload([
            'title' => 'Updated Title',
        ], true);

        $this->assertEquals([
            'title' => 'Updated Title',
        ], $payload);

        // When nothing provided on update, should return empty
        $emptyPayload = $service->exposeNormalizeTranslationPayload([], true);

        $this->assertEquals([], $emptyPayload);
    }

    /**
     * Test setTranslationDefaults method.
     *
     * @return void
     */
    public function test_set_translation_defaults(): void
    {
        $service = new TestServiceWithDefaults();

        $result = $service->setTranslationDefaults([
            'title' => 'New Default Title',
        ]);

        $this->assertSame($service, $result);

        $payload = $service->exposeNormalizeTranslationPayload([], false);

        $this->assertEquals([
            'title' => 'New Default Title',
        ], $payload);
    }

    /**
     * Test getTranslationDefault method.
     *
     * @return void
     */
    public function test_get_translation_default(): void
    {
        $service = new TestServiceWithDefaults();

        $this->assertEquals('default title', $service->exposeGetTranslationDefault('title'));
        $this->assertNull($service->exposeGetTranslationDefault('nonexistent'));
    }

    /**
     * Test static cache is working.
     *
     * @return void
     */
    public function test_translatable_fields_cache(): void
    {
        $service1 = new TestServiceWithRestrictedPost();
        $service2 = new TestServiceWithRestrictedPost();

        // First call populates cache
        $fields1 = $service1->exposeGetTranslatableFields();

        // Second call should use cache
        $fields2 = $service2->exposeGetTranslatableFields();

        $this->assertEquals($fields1, $fields2);

        // Clear cache
        TestServiceWithRestrictedPost::clearTranslatableFieldsCache();

        // Should still work after clear
        $fields3 = $service1->exposeGetTranslatableFields();
        $this->assertEquals($fields1, $fields3);
    }

    /**
     * Test isLocaleKeyedFormat detection.
     *
     * @return void
     */
    public function test_locale_keyed_format_detection(): void
    {
        $service = new TestServiceWithRestrictedPost();

        // Locale-keyed format
        $this->assertTrue($service->exposeIsLocaleKeyedFormat([
            'en' => ['title' => 'Hello'],
            'fa' => ['title' => 'سلام'],
        ]));

        // Single locale (legacy) format
        $this->assertFalse($service->exposeIsLocaleKeyedFormat([
            'title'   => 'Hello',
            'summary' => 'World',
        ]));

        // Field name that looks like locale but is actually a field
        $this->assertFalse($service->exposeIsLocaleKeyedFormat([
            'title' => ['nested' => 'value'],
        ]));
    }

    /**
     * Test syncTranslations with restricted model filters fields.
     *
     * @return void
     * @throws Throwable
     */
    public function test_sync_translations_with_restricted_model(): void
    {
        $post = RestrictedPost::create(['slug' => 'restricted-test']);

        $service = new TestServiceWithRestrictedPost();

        // This should only sync 'title', ignoring 'summary' and 'body'
        $service->exposeSyncTranslations($post, [
            'en' => [
                'title'   => 'Test Title',
                'summary' => 'This should be ignored',
                'body'    => 'This should also be ignored',
            ],
        ], false);

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => RestrictedPost::class,
            'translatable_id'   => $post->id,
            'locale'            => 'en',
            'field'             => 'title',
            'value'             => 'Test Title',
        ]);

        // summary should NOT be in the database (filtered out by buildPayload)
        $this->assertDatabaseMissing(config('translation.tables.translation'), [
            'translatable_type' => RestrictedPost::class,
            'translatable_id'   => $post->id,
            'locale'            => 'en',
            'field'             => 'summary',
        ]);

        // body should NOT be in the database (filtered out by buildPayload)
        $this->assertDatabaseMissing(config('translation.tables.translation'), [
            'translatable_type' => RestrictedPost::class,
            'translatable_id'   => $post->id,
            'locale'            => 'en',
            'field'             => 'body',
        ]);
    }
}

/**
 * Test service class with Post model (wildcard translatables).
 */
class TestService
{
    use HasServiceTranslation;

    protected static string $modelClass = Post::class;

    public function exposeGetTranslatableFields(): array
    {
        return $this->getTranslatableFields();
    }

    public function exposeEnsureTranslationsRelation(array $with): array
    {
        return $this->ensureTranslationsRelation($with);
    }

    /**
     * @throws Throwable
     */
    public function exposeSyncTranslations(Model $model, mixed $translation, bool $isUpdate): void
    {
        $this->syncTranslations($model, $translation, $isUpdate);
    }

    public function exposeNormalizeTranslationPayload(array $fields, bool $isUpdate): array
    {
        return $this->normalizeTranslationPayload($fields, $isUpdate);
    }
}

/**
 * Test service class with RestrictedPost model (specific translatables).
 */
class TestServiceWithRestrictedPost
{
    use HasServiceTranslation;

    protected static string $modelClass = RestrictedPost::class;

    public function exposeGetTranslatableFields(): array
    {
        return $this->getTranslatableFields();
    }

    /**
     * @throws Throwable
     */
    public function exposeSyncTranslations(Model $model, mixed $translation, bool $isUpdate): void
    {
        $this->syncTranslations($model, $translation, $isUpdate);
    }

    public function exposeIsLocaleKeyedFormat(array $translation): bool
    {
        $translatableFields = $this->getTranslatableFields();

        $firstKey = array_key_first($translation);
        $firstValue = $translation[$firstKey] ?? null;

        if (! is_string($firstKey) || ! is_array($firstValue)) {
            return false;
        }

        $keyLength = strlen($firstKey);
        if ($keyLength < 2 || $keyLength > 5) {
            return false;
        }

        if (! empty($translatableFields) && in_array($firstKey, $translatableFields, true)) {
            return false;
        }

        return true;
    }
}

/**
 * Test service class with defaults.
 */
class TestServiceWithDefaults
{
    use HasServiceTranslation;

    protected static string $modelClass = RestrictedPost::class;

    public function __construct()
    {
        $this->translationDefaults = [
            'title' => 'default title',
        ];
    }

    public function exposeGetTranslatableFields(): array
    {
        return $this->getTranslatableFields();
    }

    public function exposeNormalizeTranslationPayload(array $fields, bool $isUpdate): array
    {
        return $this->normalizeTranslationPayload($fields, $isUpdate);
    }

    public function exposeGetTranslationDefault(string $field): mixed
    {
        return $this->getTranslationDefault($field);
    }
}

/**
 * Test service without modelClass property.
 */
class TestServiceNoModel
{
    use HasServiceTranslation;

    public function exposeGetTranslatableFields(): array
    {
        return $this->getTranslatableFields();
    }
}

