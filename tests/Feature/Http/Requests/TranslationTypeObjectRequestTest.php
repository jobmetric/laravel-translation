<?php

namespace JobMetric\Translation\Tests\Feature\Http\Requests;

use Illuminate\Support\Collection;
use JobMetric\Translation\Http\Requests\TranslationTypeObjectRequest;
use JobMetric\Translation\Tests\TestCase;
use Mockery;

/**
 * Class TranslationTypeObjectRequestTest
 *
 * Verifies TranslationTypeObjectRequest:
 * - Builds per-locale rules under "translation.{locale}".
 * - Adds primary field rule: string + unique rule object.
 * - Applies custom fields with validation and optional uniqueness rule.
 * - Skips custom items with no uniqName or those equal to primary.
 * - Maps attribute labels per locale using provided label keys.
 */
class TranslationTypeObjectRequestTest extends TestCase
{
    /**
     * @var object A lightweight stub exposing the trait methods.
     */
    protected $stub;

    protected function setUp(): void
    {
        parent::setUp();

        // Expose trait via anonymous class
        $this->stub = new class {
            use TranslationTypeObjectRequest;
        };

        // Neutralize TranslationFieldExistRule constructor/side-effects (e.g., trans/db)
        Mockery::mock('overload:JobMetric\\Translation\\Rules\\TranslationFieldExistRule')
            ->shouldIgnoreMissing();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Helper to build a minimal Typeify\Translation-like item.
     *
     * @param string|null $uniqName  The custom field key (null to simulate missing).
     * @param string|null $validation Validation string (default used if null).
     * @param bool $unique           Whether custom field is marked unique.
     * @param string|null $label     Optional label key for attributes.
     * @return object
     */
    protected function makeTypeifyItem(?string $uniqName, ?string $validation = null, bool $unique = false, ?string $label = null): object
    {
        $item = new \stdClass();
        $item->customField = new \stdClass();
        $item->customField->params = [];
        if ($uniqName !== null) {
            $item->customField->params['uniqName'] = $uniqName;
        }
        if ($unique) {
            $item->customField->params['unique'] = true;
        }
        if ($validation !== null) {
            $item->customField->validation = $validation;
        }
        if ($label !== null) {
            $item->customField->label = $label;
        }
        return $item;
    }

    public function test_builds_rules_for_given_locales_with_primary_and_custom_fields(): void
    {
        $data = [
            'translation' => [
                'en' => ['name' => 'Hello'],
                'fa' => ['name' => 'سلام'],
            ],
        ];

        // items: unique slug, non-unique description, ignored because equals primary "name", and ignored missing uniqName
        $translations = new Collection([
            $this->makeTypeifyItem('slug', 'string|min:3', true),
            $this->makeTypeifyItem('description', 'string|nullable|sometimes', false),
            $this->makeTypeifyItem('name', 'string|min:2', true),   // should be skipped (equals primary)
            $this->makeTypeifyItem(null, 'string|min:2', true),     // should be skipped (no uniqName)
        ]);

        $rules = [];
        $this->stub->renderTranslationFiled(
            $rules,
            $data,
            $translations,
            \stdClass::class,
            'name',
            null,
            null,
            []
        );

        // Root + per locale buckets
        $this->assertSame('array', $rules['translation']);
        $this->assertSame('array', $rules['translation.en']);
        $this->assertSame('array', $rules['translation.fa']);

        foreach (['en', 'fa'] as $locale) {
            // Primary field: string + mocked unique rule object
            $primary = $rules["translation.$locale.name"];
            $this->assertIsArray($primary);
            $this->assertContains('string', $primary);
            $this->assertTrue(
                collect($primary)->contains(fn ($r) => is_object($r)),
                "Primary '$locale' should include a rule object."
            );

            // Unique custom: array with validation + rule object
            $slug = $rules["translation.$locale.slug"];
            $this->assertIsArray($slug);
            $this->assertContains('string|min:3', $slug);
            $this->assertTrue(
                collect($slug)->contains(fn ($r) => is_object($r)),
                "Custom unique 'slug' for $locale should include a rule object."
            );

            // Non-unique custom: string only
            $this->assertSame('string|nullable|sometimes', $rules["translation.$locale.description"]);

            // Skipped ones should not exist
            $this->assertArrayNotHasKey("translation.$locale.name.0", $rules);   // no duplicate custom 'name'
            // No index for missing uniqName
            $this->assertArrayNotHasKey("translation.$locale.", $rules);
        }
    }

    public function test_falls_back_to_app_locale_when_translation_not_provided(): void
    {
        app()->setLocale('fa');

        $data = []; // no translation key
        $translations = new Collection([
            $this->makeTypeifyItem('slug', 'string|min:3', true),
            $this->makeTypeifyItem('description', null, false),
        ]);

        $rules = [];
        $this->stub->renderTranslationFiled(
            $rules,
            $data,
            $translations,
            \stdClass::class,
            'name',
            null,
            null,
            []
        );

        $this->assertSame('array', $rules['translation']);
        $this->assertSame('array', $rules['translation.fa']);

        $primary = $rules['translation.fa.name'];
        $this->assertIsArray($primary);
        $this->assertContains('string', $primary);
        $this->assertTrue(
            collect($primary)->contains(fn ($r) => is_object($r)),
            "Primary should include a rule object."
        );

        $this->assertIsArray($rules['translation.fa.slug']);              // unique → array
        $this->assertSame('string|nullable|sometimes', $rules['translation.fa.description']); // non-unique → string
    }

    public function test_attribute_labels_are_mapped_per_locale(): void
    {
        $data = [
            'translation' => [
                'en' => [],
                'fa' => [],
            ],
        ];

        $translations = new Collection([
            $this->makeTypeifyItem('slug', null, false, 'translation.fields.slug'),
            $this->makeTypeifyItem('description', null, false, 'translation.fields.description'),
            $this->makeTypeifyItem(null, null, false, 'translation.fields.ignored'), // ignored (no uniqName)
        ]);

        $params = [];
        $this->stub->renderTranslationAttribute($params, $data, $translations);

        // both locales mapped
        $this->assertSame('translation.fields.slug', $params['translation.en.slug']);
        $this->assertSame('translation.fields.slug', $params['translation.fa.slug']);
        $this->assertSame('translation.fields.description', $params['translation.en.description']);
        $this->assertSame('translation.fields.description', $params['translation.fa.description']);

        // ignored item has no mapping
        $this->assertArrayNotHasKey('translation.en.', $params);
        $this->assertArrayNotHasKey('translation.fa.', $params);
    }
}
