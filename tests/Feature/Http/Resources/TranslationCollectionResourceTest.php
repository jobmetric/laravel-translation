<?php

namespace JobMetric\Translation\Tests\Feature\Http\Resources;

use JobMetric\Translation\Http\Resources\TranslationCollectionResource;
use JobMetric\Translation\Tests\Stubs\Models\Post;
use JobMetric\Translation\Tests\Stubs\Models\VersionedPost;
use JobMetric\Translation\Tests\TestCase;

class TranslationCollectionResourceTest extends TestCase
{
    public function test_returns_single_locale_plain_values(): void
    {
        $post = Post::factory()->setSlug('p1')->setTranslation([
            'fa' => ['title' => 'سلام', 'summary' => 'خلاصه'],
            'en' => ['title' => 'hello'],
        ])->create();

        $resource = (new TranslationCollectionResource($post))
            ->withLocale('fa');

        $data = $resource->toArray(request());

        $this->assertIsArray($data);
        $this->assertSame('سلام', $data['title']);
        $this->assertSame('خلاصه', $data['summary']);
        $this->assertArrayNotHasKey('en', $data);
    }

    public function test_returns_all_locales_grouped(): void
    {
        $post = Post::factory()->setSlug('p2')->setTranslation([
            'fa' => ['title' => 'سلام', 'summary' => 'خلاصه'],
            'en' => ['title' => 'hello', 'summary' => 'abstract'],
        ])->create();

        $resource = new TranslationCollectionResource($post);
        $data = $resource->toArray(request());

        $this->assertIsArray($data);
        $this->assertArrayHasKey('fa', $data);
        $this->assertArrayHasKey('en', $data);

        $this->assertSame('سلام', $data['fa']['title']);
        $this->assertSame('خلاصه', $data['fa']['summary']);
        $this->assertSame('hello', $data['en']['title']);
        $this->assertSame('abstract', $data['en']['summary']);
    }

    public function test_only_fields_filter_is_applied(): void
    {
        $post = Post::factory()->setSlug('p3')->setTranslation([
            'fa' => ['title' => 'سلام', 'summary' => 'خلاصه'],
        ])->create();

        $resource = (new TranslationCollectionResource($post))
            ->withLocale('fa')
            ->onlyFields(['title']);

        $data = $resource->toArray(request());

        $this->assertIsArray($data);
        $this->assertSame(['title' => 'سلام'], $data);
        $this->assertArrayNotHasKey('summary', $data);
    }

    public function test_with_history_and_version_metadata_for_locale(): void
    {
        // Versioned model: previous versions are soft-deleted; latest stays active
        $post = VersionedPost::query()->create([
            'slug' => 'vp-1',
            'translation' => [
                'fa' => ['title' => 'v1', 'summary' => 's1'],
            ],
        ]);

        // Create a new version for "title"
        $post->setTranslation('fa', 'title', 'v2');

        $resource = (new TranslationCollectionResource($post))
            ->withLocale('fa')
            ->withHistory()
            ->includeVersion();

        $data = $resource->toArray(request());

        $this->assertIsArray($data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('summary', $data);

        // Ensure version metadata exists
        $this->assertIsArray($data['title']);
        $this->assertSame('v2', $data['title']['value']);
        $this->assertEquals(2, $data['title']['version']);
        $this->assertTrue($data['title']['is_active']);
        $this->assertNull($data['title']['deleted_at']);

        $this->assertIsArray($data['summary']);
        $this->assertSame('s1', $data['summary']['value']);
        $this->assertEquals(1, $data['summary']['version']);
        $this->assertTrue($data['summary']['is_active']);
    }
}
