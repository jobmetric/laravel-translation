<?php

namespace JobMetric\Translation\Tests\Feature;

use Illuminate\Support\Facades\App;
use JobMetric\Translation\Exceptions\TranslationDisallowFieldException;
use JobMetric\Translation\Models\Translation as TranslationModel;
use JobMetric\Translation\Tests\Stubs\Models\Post;
use JobMetric\Translation\Tests\Stubs\Models\RestrictedPost;
use JobMetric\Translation\Tests\Stubs\Models\VersionedPost;
use JobMetric\Translation\Tests\TestCase as BaseTestCase;
use Throwable;

/**
 * Class HasTranslationTest
 *
 * Feature tests for the HasTranslation trait on SQLite (LIKE fallback).
 * Verifies auto-saving, whitelisting, helpers, versioning, read APIs, forgetting APIs, and scopes.
 */
class HasTranslationTest extends BaseTestCase
{
    /**
     * Ensure the translations table exists via the provider migration.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue(
            $this->app['db']->connection()->getSchemaBuilder()->hasTable(
                config('translation.tables.translation', 'translations')
            ),
            'Translations table must exist via provider migration.'
        );
    }

    /**
     * Autosaving on create/update with versioning OFF (upserts version=1).
     *
     * @return void
     * @throws Throwable
     */
    public function test_autosaving_on_create_and_update_versioning_off(): void
    {
        $post = Post::factory()
            ->setSlug('hello-world')
            ->setTranslation([
                'fa' => ['title' => 'سلام دنیا', 'summary' => 'خلاصه ۱'],
                'en' => ['title' => 'Hello World'],
            ])
            ->create();

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id' => $post->id,
            'locale' => 'fa',
            'field' => 'title',
            'value' => 'سلام دنیا',
            'version' => 1,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id' => $post->id,
            'locale' => 'fa',
            'field' => 'summary',
            'value' => 'خلاصه ۱',
            'version' => 1,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id' => $post->id,
            'locale' => 'en',
            'field' => 'title',
            'value' => 'Hello World',
            'version' => 1,
            'deleted_at' => null,
        ]);

        // Update via virtual "translation" attribute
        $post->translation = [
            'fa' => ['title' => 'سلام دنیا ۲', 'summary' => 'خلاصه ۲'],
        ];
        $post->save();

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id' => $post->id,
            'locale' => 'fa',
            'field' => 'title',
            'value' => 'سلام دنیا ۲',
            'version' => 1,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id' => $post->id,
            'locale' => 'fa',
            'field' => 'summary',
            'value' => 'خلاصه ۲',
            'version' => 1,
            'deleted_at' => null,
        ]);

        // Parent model does not use SoftDeletes; trait should hard-delete child rows on delete
        $post->delete();

        $this->assertDatabaseMissing(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id' => $post->id,
            'locale' => 'fa',
            'field' => 'title',
            'value' => 'سلام دنیا ۲',
            'version' => 1,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseMissing(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id' => $post->id,
            'locale' => 'fa',
            'field' => 'summary',
            'value' => 'خلاصه ۲',
            'version' => 1,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseMissing(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id' => $post->id,
            'locale' => 'en',
            'field' => 'title',
            'value' => 'Hello World',
            'version' => 1,
            'deleted_at' => null,
        ]);
    }

    /**
     * Whitelist must throw TranslationDisallowFieldException for disallowed fields.
     *
     * @return void
     * @throws Throwable
     */
    public function test_whitelist_disallows_fields(): void
    {
        $this->expectException(TranslationDisallowFieldException::class);

        RestrictedPost::create([
            'slug' => 'disallowed',
            'translation' => [
                'en' => [
                    'title' => 'OK',
                    'body' => 'not allowed', // not in whitelist
                ],
            ],
        ]);
    }

    /**
     * Helpers: setTranslation and translateBatch.
     *
     * @return void
     * @throws Throwable
     */
    public function test_helpers_set_and_batch(): void
    {
        $post = Post::factory()->setSlug('helpers')->create();

        $post->setTranslation('fa', 'title', 'عنوان ۱');
        $this->assertSame('عنوان ۱', $post->getTranslation('title', 'fa'));

        $post->translateBatch([
            'fa' => ['summary' => 'خلاصه ۱'],
            'en' => ['title' => 'Title 1'],
        ]);

        $this->assertSame('خلاصه ۱', $post->getTranslation('summary', 'fa'));
        $this->assertSame('Title 1', $post->getTranslation('title', 'en'));
    }

    /**
     * Reading: active value and fallback to latest (with trashed) when active is missing.
     *
     * @return void
     * @throws Throwable
     */
    public function test_get_translation_active_and_fallback_to_latest(): void
    {
        $post = VersionedPost::create([
            'slug' => 'ver',
            'translation' => ['fa' => ['title' => 'نسخه ۱']],
        ]);

        $post->setTranslation('fa', 'title', 'نسخه ۲');

        $this->assertSame('نسخه ۲', $post->getTranslation('title', 'fa'));

        // Soft-delete active to check fallback to latest by version
        $post->forgetTranslation('title', 'fa');
        $this->assertSame('نسخه ۲', $post->getTranslation('title', 'fa'));
    }

    /**
     * Reading: getTranslations by locale and grouped for all locales.
     *
     * @return void
     * @throws Throwable
     */
    public function test_get_translations_single_locale_and_all(): void
    {
        $post = Post::factory()->setTranslation([
            'fa' => ['title' => 'سلام', 'summary' => 'متن'],
            'en' => ['title' => 'Hello'],
        ])->create();

        $fa = $post->getTranslations('fa');
        $this->assertSame('سلام', $fa['title'] ?? null);
        $this->assertSame('متن', $fa['summary'] ?? null);

        $all = $post->getTranslations();
        $this->assertSame('سلام', $all['fa']['title'] ?? null);
        $this->assertSame('Hello', $all['en']['title'] ?? null);
    }

    /**
     * Versioning: v1 then v2 with soft-deleted previous version and incremented version number.
     *
     * @return void
     * @throws Throwable
     */
    public function test_versioning_flow(): void
    {
        $post = VersionedPost::create([
            'slug' => 'versioning',
            'translation' => ['fa' => ['title' => 'v1']],
        ]);

        $this->assertSame(1, $post->latestTranslationVersion('title', 'fa'));
        $this->assertSame('v1', $post->getTranslation('title', 'fa'));

        $post->setTranslation('fa', 'title', 'v2');

        $this->assertSame(2, $post->latestTranslationVersion('title', 'fa'));
        $this->assertSame('v2', $post->getTranslation('title', 'fa'));

        $versions = $post->getTranslationVersions('title', 'fa');
        $this->assertSame(2, $versions[0]['version']); // latest first
        $this->assertSame(1, $versions[1]['version']);
        $this->assertNotNull($versions[1]['deleted_at']); // v1 soft-deleted
    }

    /**
     * Forget APIs: soft by default and force when requested.
     *
     * @return void
     * @throws Throwable
     */
    public function test_forget_single_and_all(): void
    {
        $post = VersionedPost::create([
            'slug' => 'forget',
            'translation' => ['fa' => ['title' => 't1', 'summary' => 's1']],
        ]);

        $this->assertTrue($post->hasTranslationField('title', 'fa'));
        $this->assertTrue($post->hasTranslationField('summary', 'fa'));

        $post->forgetTranslation('title', 'fa'); // soft delete active
        $this->assertFalse($post->hasTranslationField('title', 'fa'));
        $this->assertTrue($post->hasTranslationField('summary', 'fa'));

        $post->forgetTranslations('fa', force: true); // hard delete remaining fa rows
        $this->assertFalse($post->hasTranslationField('summary', 'fa'));

        // check db state
        $this->assertDatabaseMissing(config('translation.tables.translation'), [
            'translatable_type' => VersionedPost::class,
            'translatable_id' => $post->id,
            'locale' => 'fa',
            'field' => 'title',
            'value' => 't1',
            'deleted_at' => null,
        ]);

        $this->assertDatabaseMissing(config('translation.tables.translation'), [
            'translatable_type' => VersionedPost::class,
            'translatable_id' => $post->id,
            'locale' => 'fa',
            'field' => 'summary',
            'value' => 's1',
            'deleted_at' => null,
        ]);
    }

    /**
     * Scope: equals in ANY locale and in specific locale.
     *
     * @return void
     * @throws Throwable
     */
    public function test_scope_where_translation_equals(): void
    {
        Post::factory()->setSlug('a')->setTranslation(['fa' => ['title' => 'سلام']])->create();
        Post::factory()->setSlug('b')->setTranslation(['en' => ['title' => 'Hello']])->create();
        Post::factory()->setSlug('c')->setTranslation(['fa' => ['title' => 'Hello']])->create();

        $any = Post::whereTranslationEquals('title', 'Hello')->pluck('slug')->all();
        sort($any);
        $this->assertSame(['b', 'c'], $any);

        $fa = Post::whereTranslationEquals('title', 'Hello', 'fa')->pluck('slug')->all();
        $this->assertSame(['c'], $fa);

        $en = Post::whereTranslationEquals('title', 'Hello', 'en')->pluck('slug')->all();
        $this->assertSame(['b'], $en);
    }

    /**
     * Scope: LIKE (SQLite fallback).
     *
     * @return void
     * @throws Throwable
     */
    public function test_scope_where_translation_like(): void
    {
        App::setLocale('fa');

        Post::factory()->setSlug('like')
            ->setTranslation([
                'fa' => ['title' => 'سلام دنیا', 'summary' => 'این یک تست است'],
                'en' => ['title' => 'Hello World'],
            ])->create();

        $faLike = Post::whereTranslationLike('title', 'دن', 'fa')->pluck('slug')->all();
        $this->assertSame(['like'], $faLike);

        $anyLike = Post::whereTranslationLike('summary', 'تست')->pluck('slug')->all();
        $this->assertSame(['like'], $anyLike);
    }

    /**
     * Scope: searchTranslation should fallback to LIKE on SQLite.
     *
     * @return void
     * @throws Throwable
     */
    public function test_scope_search_translation_like_fallback_on_sqlite(): void
    {
        App::setLocale('fa');

        Post::factory()->setSlug('search')
            ->setTranslation([
                'fa' => ['title' => 'لورم ایپسوم تست', 'summary' => 'عبارت جستجو'],
            ])->create();

        $hits1 = Post::searchTranslation('title', 'ایپسوم', 'fa')->pluck('slug')->all();
        $this->assertSame(['search'], $hits1);

        $hits2 = Post::searchTranslation('summary', 'جستجو', 'fa')->pluck('slug')->all();
        $this->assertSame(['search'], $hits2);
    }

    /**
     * Soft-deleting a parent model with SoftDeletes should soft-delete
     * only the active translations; restoring should restore only the latest
     * version per (locale, field), keeping historical versions soft-deleted.
     */
    public function test_soft_delete_then_restore_restores_latest_only(): void
    {
        // Given a versioned parent with two versions for the same (locale, field)
        $post = VersionedPost::create([
            'slug' => 'ver-rest',
            'translation' => ['fa' => ['title' => 'v1']],
        ]);
        // Create v2 (v1 becomes soft-deleted by versioning)
        $post->setTranslation('fa', 'title', 'v2');

        // Sanity check: v2 active, v1 soft-deleted
        $this->assertSame('v2', $post->getTranslation('title', 'fa'));
        $this->assertSame(2, $post->latestTranslationVersion('title', 'fa'));

        // When: soft-deleting the parent
        $post->delete();

        // Then: active rows (latest = v2) should be soft-deleted
        $this->assertDatabaseMissing(config('translation.tables.translation'), [
            'translatable_type' => VersionedPost::class,
            'translatable_id' => $post->id,
            'locale' => 'fa',
            'field' => 'title',
            'version' => 2,
            'deleted_at' => null,
        ]);

        // And: row exists with deleted_at NOT NULL
        $trashedV2 = TranslationModel::withTrashed()
            ->where('translatable_type', VersionedPost::class)
            ->where('translatable_id', $post->id)
            ->where('locale', 'fa')
            ->where('field', 'title')
            ->where('version', 2)
            ->first();
        $this->assertNotNull($trashedV2);
        $this->assertNotNull($trashedV2->deleted_at);

        // When: restoring the parent
        $post->restore();

        // Then: only latest version (v2) is restored (deleted_at = null)
        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => VersionedPost::class,
            'translatable_id' => $post->id,
            'locale' => 'fa',
            'field' => 'title',
            'version' => 2,
            'deleted_at' => null,
        ]);

        // And: older version (v1) stays soft-deleted
        $v1 = TranslationModel::withTrashed()
            ->where('translatable_type', VersionedPost::class)
            ->where('translatable_id', $post->id)
            ->where('locale', 'fa')
            ->where('field', 'title')
            ->where('version', 1)
            ->first();
        $this->assertNotNull($v1);
        $this->assertNotNull($v1->deleted_at);
    }

    /**
     * Force-deleting a parent model with SoftDeletes should remove
     * all translation history (including already soft-deleted older versions).
     */
    public function test_force_delete_parent_removes_all_history(): void
    {
        $post = VersionedPost::create([
            'slug' => 'ver-force',
            'translation' => ['fa' => ['title' => 'v1']],
        ]);
        $post->setTranslation('fa', 'title', 'v2');

        // Ensure we have both versions in history
        $this->assertSame(2, $post->latestTranslationVersion('title', 'fa'));

        // When: force delete the parent
        $post->forceDelete();

        // Then: no translation rows remain (with or without trashed)
        $count = TranslationModel::withTrashed()
            ->where('translatable_type', VersionedPost::class)
            ->where('translatable_id', $post->id)
            ->count();

        $this->assertSame(0, $count);
    }

    /**
     * Deleting a parent model WITHOUT SoftDeletes should still soft-delete
     * child translation rows. (No restore path here.)
     */
    public function test_delete_parent_without_softdeletes_soft_deletes_translations(): void
    {
        $post = Post::factory()->setSlug('plain')->setTranslation([
            'fa' => ['title' => 'سلام', 'summary' => 'متن'],
        ])->create();

        // Active row must exist first
        $this->assertDatabaseHas(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id' => $post->id,
            'locale' => 'fa',
            'field' => 'title',
            'value' => 'سلام',
            'deleted_at' => null,
        ]);

        // When: deleting the parent (hard delete)
        $post->delete();

        // Then: active child row should no longer exist (deleted_at is NOT NULL)
        $this->assertDatabaseMissing(config('translation.tables.translation'), [
            'translatable_type' => Post::class,
            'translatable_id' => $post->id,
            'locale' => 'fa',
            'field' => 'title',
            'value' => 'سلام',
            'deleted_at' => null,
        ]);

        // And: soft-deleted row should be present
        $trashed = TranslationModel::withTrashed()
            ->where('translatable_type', Post::class)
            ->where('translatable_id', $post->id)
            ->where('locale', 'fa')
            ->where('field', 'title')
            ->first();

        $this->assertNotNull($trashed);
        $this->assertNotNull($trashed->deleted_at);
    }
}
