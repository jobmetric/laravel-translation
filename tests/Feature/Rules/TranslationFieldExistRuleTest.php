<?php

namespace JobMetric\Translation\Tests\Feature\Rules;

use JobMetric\Translation\Exceptions\ModelHasTranslationNotFoundException;
use JobMetric\Translation\Rules\TranslationFieldExistRule;
use JobMetric\Translation\Tests\Stubs\Models\Post;
use JobMetric\Translation\Tests\Stubs\Models\VersionedPost;
use JobMetric\Translation\Tests\TestCase;

class TranslationFieldExistRuleTest extends TestCase
{
    public function test_passes_when_value_is_unique_for_locale_and_field(): void
    {
        // seed baseline
        Post::factory()->setSlug('p1')->setTranslation([
            'fa' => ['title' => 'سلام'],
        ])->create();

        $rule = new TranslationFieldExistRule(Post::class, 'title', 'fa');

        // unique value should pass
        $this->assertTrue($rule->passes('title', 'متفاوت'));
    }

    public function test_fails_when_duplicate_exists_for_same_locale_and_field(): void
    {
        Post::factory()->setSlug('p1')->setTranslation([
            'fa' => ['title' => 'تکراری'],
        ])->create();

        $rule = new TranslationFieldExistRule(Post::class, 'title', 'fa');

        // duplicate should fail
        $this->assertFalse($rule->passes('title', 'تکراری'));
    }

    public function test_passes_when_same_value_exists_but_in_different_locale(): void
    {
        Post::factory()->setSlug('p1')->setTranslation([
            'fa' => ['title' => 'یکسان'],
        ])->create();

        $rule = new TranslationFieldExistRule(Post::class, 'title', 'en');

        // same value in another locale is allowed
        $this->assertTrue($rule->passes('title', 'یکسان'));
    }

    /**
     * @throws ModelHasTranslationNotFoundException
     */
    public function test_ignores_current_record_when_object_id_is_provided(): void
    {
        $p = Post::factory()->setSlug('p1')->setTranslation([
            'fa' => ['title' => 'ثابت'],
        ])->create();

        // exclude same record by its id (update flow)
        $rule = new TranslationFieldExistRule(Post::class, 'title', 'fa', $p->id);

        $this->assertTrue($rule->passes('title', 'ثابت'));
    }

    /**
     * @throws ModelHasTranslationNotFoundException
     */
    public function test_parent_where_scopes_uniqueness_check(): void
    {
        // two posts with the same translated value but different slugs
        Post::factory()->setSlug('group-a')->setTranslation([
            'fa' => ['title' => 'مشترک'],
        ])->create();

        Post::factory()->setSlug('group-b')->setTranslation([
            'fa' => ['title' => 'مشترک'],
        ])->create();

        // constrain to slug=group-a: duplicate exists -> fail
        $ruleA = new TranslationFieldExistRule(Post::class, 'title', 'fa', null, -1, ['slug' => 'group-a']);
        $this->assertFalse($ruleA->passes('title', 'مشترک'));

        // constrain to slug=non-exist: no duplicate -> pass
        $ruleB = new TranslationFieldExistRule(Post::class, 'title', 'fa', null, -1, ['slug' => 'nope']);
        $this->assertTrue($ruleB->passes('title', 'مشترک'));
    }

    public function test_versioning_off_checks_version_one_on_post(): void
    {
        // default Post: versioning OFF
        $p = Post::factory()->setSlug('v0')->setTranslation([
            'fa' => ['title' => 'اول'],
        ])->create();

        // non-versioned path updates version=1 in place
        $p->setTranslation('fa', 'title', 'دوم');

        $rule = new TranslationFieldExistRule(Post::class, 'title', 'fa');

        // 'دوم' exists at version=1 -> conflict
        $this->assertFalse($rule->passes('title', 'دوم'));

        // 'اول' no longer at version=1 -> no conflict
        $this->assertTrue($rule->passes('title', 'اول'));
    }

    public function test_versioning_on_considers_only_active_latest_version_on_versioned_post(): void
    {
        // VersionedPost: versioning ON (uses the same 'posts' table)
        $p = VersionedPost::query()->create([
            'slug' => 'vp-1',
            'translation' => [
                'fa' => ['title' => 'v1'],
            ],
        ]);

        // create a new version; old becomes soft-deleted
        $p->setTranslation('fa', 'title', 'v2');

        $rule = new TranslationFieldExistRule(VersionedPost::class, 'title', 'fa');

        // latest active value 'v2' conflicts
        $this->assertFalse($rule->passes('title', 'v2'));

        // old 'v1' is soft-deleted; should not conflict
        $this->assertTrue($rule->passes('title', 'v1'));
    }
}
