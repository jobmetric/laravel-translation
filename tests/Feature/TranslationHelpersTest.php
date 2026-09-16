<?php

namespace JobMetric\Translation\Tests\Feature;

use Illuminate\Database\Eloquent\Collection;
use JobMetric\Translation\Tests\TestCase;

class TranslationHelpersTest extends TestCase
{
    public function test_resource_data_accepts_null_or_an_explicit_locale(): void
    {
        $rows = [
            (object) ['locale' => 'en', 'field' => 'title', 'value' => 'Hello'],
            (object) ['locale' => 'fa', 'field' => 'title', 'value' => 'سلام'],
        ];

        $this->assertSame([
            'en' => ['title' => 'Hello'],
            'fa' => ['title' => 'سلام'],
        ], translationResourceData($rows));
        $this->assertSame(translationResourceData($rows), translationResourceData($rows, null));
        $this->assertSame(['en' => ['title' => 'Hello']], translationResourceData($rows, 'en'));
    }

    public function test_data_select_accepts_null_or_an_explicit_locale_for_empty_models(): void
    {
        $objects = new Collection;

        $this->assertTrue(translationDataSelect($objects, 'title')->isEmpty());
        $this->assertTrue(translationDataSelect($objects, 'title', null)->isEmpty());
        $this->assertTrue(translationDataSelect($objects, 'title', 'en')->isEmpty());
    }
}
