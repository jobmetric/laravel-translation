<?php

namespace JobMetric\Translation\Tests\Feature\Http\Requests;

use Illuminate\Support\Collection;
use JobMetric\Language\Facades\Language;
use JobMetric\Translation\Http\Requests\MultiTranslationTypeObjectRequest;
use JobMetric\Translation\Tests\TestCase;
use Mockery;

/**
 * MultiTranslationTypeObjectRequestTest
 *
 * - Swaps Language facade to stub Language::all()
 * - Overload-mocks TranslationFieldExistRule to avoid constructor side-effects (trans()).
 */
class MultiTranslationTypeObjectRequestTest extends TestCase
{
    /** @var object */
    protected $stub;

    protected function setUp(): void
    {
        parent::setUp();

        // 1) Swap Language facade to return fixed locales.
        $this->swapLanguageAll(['en', 'fa']);

        // 2) Overload mock to neutralize constructor of the rule class.
        //    This ensures new TranslationFieldExistRule(...) doesn't call trans() or hit DB.
        Mockery::mock('overload:JobMetric\\Translation\\Rules\\TranslationFieldExistRule')
            ->shouldIgnoreMissing();

        // Expose trait
        $this->stub = new class {
            use MultiTranslationTypeObjectRequest;
        };
    }

    protected function tearDown(): void
    {
        Mockery::close();
        // clear facade root to avoid cross-test pollution
        Language::clearResolvedInstance('language');

        parent::tearDown();
    }

    /**
     * Swap Language facade root with a tiny stub that implements ->all()
     *
     * @param array<int, string> $locales
     * @return void
     */
    protected function swapLanguageAll(array $locales): void
    {
        $mock = new class($locales) {
            public function __construct(private array $locales) {}
            public function all(): Collection
            {
                return collect(array_map(fn ($l) => (object) ['locale' => $l], $this->locales));
            }
        };

        Language::swap($mock);
    }

    /**
     * Build a minimal Typeify-like Translation item.
     *
     * @param string      $uniqName
     * @param string|null $validation
     * @param bool        $unique
     * @param string|null $label
     * @return object
     */
    protected function makeTranslationItem(string $uniqName, ?string $validation = null, bool $unique = false, ?string $label = null): object
    {
        $item = new \stdClass();
        $item->customField = new \stdClass();
        $item->customField->params = [
            'uniqName' => $uniqName,
            'unique'   => $unique,
        ];
        if ($validation !== null) {
            $item->customField->validation = $validation;
        }
        if ($label !== null) {
            $item->customField->label = $label;
        }

        return $item;
    }

    public function test_builds_rules_for_all_locales_with_primary_and_custom_fields(): void
    {
        $translations = new Collection([
            $this->makeTranslationItem('slug', 'string|min:3', true),
            $this->makeTranslationItem('description', 'string|nullable|sometimes', false),
            // equals primary -> ignored
            $this->makeTranslationItem('name', 'string|min:2', true),
        ]);

        $rules = [];
        $this->stub->renderMultiTranslationFiled(
            $rules,
            $translations,
            \stdClass::class,
            'name',
            null,
            null,
            []
        );

        $this->assertSame('array', $rules['translation']);

        foreach (['en', 'fa'] as $locale) {
            $this->assertSame('array', $rules["translation.$locale"]);

            // primary field has string + "some object" (mocked rule)
            $primary = $rules["translation.$locale.name"];
            $this->assertIsArray($primary);
            $this->assertContains('string', $primary);
            $this->assertTrue(
                collect($primary)->contains(fn ($r) => is_object($r)),
                "Primary field should contain a rule object (mocked)."
            );

            // unique custom has validation + mocked rule
            $slug = $rules["translation.$locale.slug"];
            $this->assertIsArray($slug);
            $this->assertContains('string|min:3', $slug);
            $this->assertTrue(
                collect($slug)->contains(fn ($r) => is_object($r)),
                "Unique custom field should contain a rule object (mocked)."
            );

            // non-unique custom is just validation string
            $this->assertSame('string|nullable|sometimes', $rules["translation.$locale.description"]);
        }
    }

    public function test_attribute_labels_are_mapped_per_locale(): void
    {
        $translations = new Collection([
            $this->makeTranslationItem('slug', 'string|min:3', true, 'translation.fields.slug'),
            $this->makeTranslationItem('description', 'string|nullable|sometimes', false, 'translation.fields.description'),
            // no uniqName -> ignored
            tap(new \stdClass(), function ($o) {
                $o->customField = new \stdClass();
                $o->customField->params = [];
                $o->customField->label = 'translation.fields.ignored';
            }),
        ]);

        $params = [];
        $this->stub->renderMultiTranslationAttribute($params, $translations);

        $this->assertSame('translation.fields.slug', $params['translation.en.slug']);
        $this->assertSame('translation.fields.slug', $params['translation.fa.slug']);
        $this->assertSame('translation.fields.description', $params['translation.en.description']);
        $this->assertSame('translation.fields.description', $params['translation.fa.description']);

        // no empty keys
        $this->assertArrayNotHasKey('translation.en.', $params);
        $this->assertArrayNotHasKey('translation.fa.', $params);
    }

    public function test_skips_custom_field_when_name_equals_primary(): void
    {
        // limit to a single locale (already swapped to ['en','fa'], but this test doesn't depend on locales)
        $translations = new Collection([
            $this->makeTranslationItem('name', 'string|min:2', true),
        ]);

        $rules = [];
        $this->stub->renderMultiTranslationFiled(
            $rules,
            $translations,
            \stdClass::class,
            'name',
            null,
            null,
            []
        );

        $this->assertArrayHasKey('translation.en.name', $rules);
        $this->assertIsArray($rules['translation.en.name']);
        // 'string' + mocked rule object
        $this->assertCount(2, $rules['translation.en.name']);
    }
}
