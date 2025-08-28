<?php

namespace JobMetric\Translation\Tests\Feature\Http\Resources;

use JobMetric\Translation\Http\Resources\TranslationResource;
use JobMetric\Translation\Tests\Stubs\Models\Post;
use JobMetric\Translation\Tests\Stubs\Models\VersionedPost;
use JobMetric\Translation\Tests\TestCase;

class TranslationResourceTest extends TestCase
{
    public function test_basic_output_includes_expected_fields(): void
    {
        $post = Post::factory()->setSlug('p10')->setTranslation([
            'fa' => ['title' => 'سلام'],
        ])->create();

        $row = $post->translations()
            ->where('locale', 'fa')
            ->where('field', 'title')
            ->firstOrFail();

        $resource = new TranslationResource($row);
        $data = $resource->toArray(request());

        $this->assertIsArray($data);
        $this->assertSame($post::class, $data['translatable_type']);
        $this->assertSame($post->id, $data['translatable_id']);
        $this->assertSame('fa', $data['locale']);
        $this->assertSame('title', $data['field']);
        $this->assertSame('سلام', $data['value']);
        $this->assertArrayHasKey('version', $data); // version included by default
    }

    public function test_can_include_timestamps_and_deleted_at(): void
    {
        $post = VersionedPost::query()->create([
            'slug' => 'vp-2',
            'translation' => [
                'fa' => ['title' => 'v1'],
            ],
        ]);

        // Create a new version; v1 becomes soft-deleted
        $post->setTranslation('fa', 'title', 'v2');

        $rowDeleted = $post->translations()
            ->withTrashed()
            ->where('locale', 'fa')
            ->where('field', 'title')
            ->orderBy('version', 'asc')
            ->firstOrFail(); // should be version 1 and soft-deleted

        $resource = (new TranslationResource($rowDeleted))
            ->withVersion()
            ->withTimestamps()
            ->withDeletedAt();

        $data = $resource->toArray(request());

        $this->assertIsArray($data);
        $this->assertSame('fa', $data['locale']);
        $this->assertSame('title', $data['field']);
        $this->assertArrayHasKey('version', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('updated_at', $data);
        $this->assertArrayHasKey('deleted_at', $data);
        $this->assertNotNull($data['deleted_at']);
    }
}
