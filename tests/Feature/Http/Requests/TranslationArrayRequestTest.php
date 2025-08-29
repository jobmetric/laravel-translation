<?php

namespace JobMetric\Translation\Tests\Feature\Http\Requests;

use JobMetric\Translation\Http\Requests\TranslationArrayRequest;
use JobMetric\Translation\Tests\TestCase;
use Mockery;

/**
 * Class TranslationArrayRequestTest
 *
 * Verifies TranslationArrayRequest:
 * - Builds per-locale validation rules for primary field (required|string + unique rule)
 * - Adds optional string rules for other allowed fields
 * - Falls back to app locale if no 'translation' block provided
 * - Produces human-friendly attribute labels with scope template and meta special-cases
 */
class TranslationArrayRequestTest extends TestCase
{
    /**
     * A lightweight stub class to expose the trait methods for testing.
     *
     * @var object
     */
    protected $stub;

    /**
     * A fake model class name that provides translationAllowFields().
     *
     * @var class-string
     */
    protected string $fakeModelClass;

    protected function setUp(): void
    {
        parent::setUp();

        // Expose trait via anonymous class
        $this->stub = new class {
            use TranslationArrayRequest;
        };

        // Define a fake model class with translationAllowFields()
        // phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
        if (!class_exists(\FakeTranslatableModelForTranslationArrayTraitTest::class)) {
            eval('
                class FakeTranslatableModelForTranslationArrayTraitTest {
                    /**
                     * Return the list of allowed translatable fields.
                     *
                     * @return array<int, string>
                     */
                    public function translationAllowFields(): array
                    {
                        return [
                            "name",
                            "slug",
                            "description",
                            "meta_title",
                            "meta_description",
                            "meta_keywords",
                        ];
                    }
                }
            ');
        }
        $this->fakeModelClass = \FakeTranslatableModelForTranslationArrayTraitTest::class;

        // Neutralize TranslationFieldExistRule constructor and side-effects (like trans())
        Mockery::mock('overload:JobMetric\\Translation\\Rules\\TranslationFieldExistRule')
            ->shouldIgnoreMissing();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * It builds rules for provided locales with primary (required|string + unique rule)
     * and other allowed fields as optional strings.
     */
    public function test_builds_rules_for_given_locales(): void
    {
        $data = [
            'translation' => [
                'en' => ['name' => 'Hello'],
                'fa' => ['name' => 'سلام'],
            ],
        ];

        $rules = [];
        $this->stub->renderTranslationFiled(
            $rules,
            $data,
            $this->fakeModelClass,
            'name',
            null,
            null,
            []
        );

        // Root and per-locale array shape
        $this->assertArrayHasKey('translation', $rules);
        $this->assertSame('array', $rules['translation']);
        $this->assertSame('array', $rules['translation.en']);
        $this->assertSame('array', $rules['translation.fa']);

        foreach (['en', 'fa'] as $locale) {
            // Primary field rules
            $primary = $rules["translation.$locale.name"];
            $this->assertIsArray($primary);
            $this->assertContains('required', $primary);
            $this->assertContains('string', $primary);
            $this->assertTrue(
                collect($primary)->contains(fn($r) => is_object($r)),
                "Primary field should include a rule object (mocked)."
            );

            // Other allowed fields become optional strings
            $this->assertSame('string|nullable|sometimes', $rules["translation.$locale.slug"]);
            $this->assertSame('string|nullable|sometimes', $rules["translation.$locale.description"]);
            $this->assertSame('string|nullable|sometimes', $rules["translation.$locale.meta_title"]);
            $this->assertSame('string|nullable|sometimes', $rules["translation.$locale.meta_description"]);
            $this->assertSame('string|nullable|sometimes', $rules["translation.$locale.meta_keywords"]);
        }
    }

    /**
     * It falls back to app()->getLocale() when 'translation' block is absent.
     */
    public function test_rules_fall_back_to_app_locale_when_missing_translation_key(): void
    {
        app()->setLocale('fa');

        $data = []; // no 'translation' provided

        $rules = [];
        $this->stub->renderTranslationFiled(
            $rules,
            $data,
            $this->fakeModelClass,
            'name',
            null,
            null,
            []
        );

        $this->assertArrayHasKey('translation', $rules);
        $this->assertSame('array', $rules['translation']);
        $this->assertArrayHasKey('translation.fa', $rules);
        $this->assertSame('array', $rules['translation.fa']);

        $primary = $rules['translation.fa.name'];
        $this->assertIsArray($primary);
        $this->assertContains('required', $primary);
        $this->assertContains('string', $primary);
        $this->assertTrue(
            collect($primary)->contains(fn($r) => is_object($r)),
            "Primary field should include a rule object (mocked)."
        );

        // spot-check another field
        $this->assertSame('string|nullable|sometimes', $rules['translation.fa.slug']);
    }

    /**
     * It composes attribute labels with a template scope and handles meta_* special cases.
     */
    public function test_attribute_labels_with_scope_and_meta_special_cases(): void
    {
        $data = [
            'translation' => [
                'en' => [],
                'fa' => [],
            ],
        ];

        $params = [];
        $this->stub->renderTranslationAttribute(
            $params,
            $data,
            $this->fakeModelClass,
            'translation::entities.product.fields.{field}.label'
        );

        // Meta fields should use fixed path
        $this->assertSame(
            'translation::base.components.translation_card.fields.meta_title.label',
            $params['translation.en.meta_title']
        );
        $this->assertSame(
            'translation::base.components.translation_card.fields.meta_title.label',
            $params['translation.fa.meta_title']
        );
        $this->assertSame(
            'translation::base.components.translation_card.fields.meta_description.label',
            $params['translation.en.meta_description']
        );
        $this->assertSame(
            'translation::base.components.translation_card.fields.meta_description.label',
            $params['translation.fa.meta_description']
        );
        $this->assertSame(
            'translation::base.components.translation_card.fields.meta_keywords.label',
            $params['translation.en.meta_keywords']
        );
        $this->assertSame(
            'translation::base.components.translation_card.fields.meta_keywords.label',
            $params['translation.fa.meta_keywords']
        );

        // Non-meta fields should use provided template scope
        $this->assertSame(
            'translation::entities.product.fields.name.label',
            $params['translation.en.name']
        );
        $this->assertSame(
            'translation::entities.product.fields.slug.label',
            $params['translation.en.slug']
        );
        $this->assertSame(
            'translation::entities.product.fields.description.label',
            $params['translation.en.description']
        );
        $this->assertSame(
            'translation::entities.product.fields.name.label',
            $params['translation.fa.name']
        );
        $this->assertSame(
            'translation::entities.product.fields.slug.label',
            $params['translation.fa.slug']
        );
        $this->assertSame(
            'translation::entities.product.fields.description.label',
            $params['translation.fa.description']
        );
    }
}
