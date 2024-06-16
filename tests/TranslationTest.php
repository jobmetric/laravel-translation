<?php

namespace tests;

use App\Models\Product;
use Tests\BaseDatabaseTestCase as BaseTestCase;
use Throwable;

class TranslationTest extends BaseTestCase
{
    private function storeProduct(): Product
    {
        /* @var $product Product */
        return Product::create([
            'status' => true,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testStore(): void
    {
        $product = $this->storeProduct();

        $product->translate('en', [
            'title' => 'Product title',
            'description' => 'Product description',
            'meta_title' => 'Product meta title',
            'meta_description' => 'Product meta description',
            'meta_keywords' => 'Product meta keywords',
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_id' => $product->id,
            'translatable_type' => Product::class,
            'locale' => 'en',
            'key' => 'title',
            'value' => 'Product title',
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_id' => $product->id,
            'translatable_type' => Product::class,
            'locale' => 'en',
            'key' => 'description',
            'value' => 'Product description',
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_id' => $product->id,
            'translatable_type' => Product::class,
            'locale' => 'en',
            'key' => 'meta_title',
            'value' => 'Product meta title',
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_id' => $product->id,
            'translatable_type' => Product::class,
            'locale' => 'en',
            'key' => 'meta_description',
            'value' => 'Product meta description',
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_id' => $product->id,
            'translatable_type' => Product::class,
            'locale' => 'en',
            'key' => 'meta_keywords',
            'value' => 'Product meta keywords',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testForget(): void
    {
        $product = $this->storeProduct();

        $product->translate('en', [
            'title' => 'Product title',
            'description' => 'Product description',
            'meta_title' => 'Product meta title',
            'meta_description' => 'Product meta description',
            'meta_keywords' => 'Product meta keywords',
        ]);

        $product->forgetTranslation('title', 'en');

        $this->assertDatabaseMissing('translations', [
            'translatable_id' => $product->id,
            'translatable_type' => Product::class,
            'locale' => 'en',
            'key' => 'title',
            'value' => 'Product title',
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_id' => $product->id,
            'translatable_type' => Product::class,
            'locale' => 'en',
            'key' => 'description',
            'value' => 'Product description',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testForgetAllInLocale(): void
    {
        $product = $this->storeProduct();

        $product->translate('en', [
            'title' => 'Product title',
            'description' => 'Product description'
        ]);

        $product->forgetTranslations('en');

        $this->assertDatabaseMissing('translations', [
            'translatable_id' => $product->id,
            'translatable_type' => Product::class,
            'locale' => 'en',
            'key' => 'title',
            'value' => 'Product title',
        ]);

        $this->assertDatabaseMissing('translations', [
            'translatable_id' => $product->id,
            'translatable_type' => Product::class,
            'locale' => 'en',
            'key' => 'description',
            'value' => 'Product description',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testForgetAll(): void
    {
        $product = $this->storeProduct();

        $product->translate('en', [
            'title' => 'Product title',
            'description' => 'Product description'
        ]);

        $product->forgetTranslations();

        $this->assertDatabaseMissing('translations', [
            'translatable_id' => $product->id,
            'translatable_type' => Product::class,
            'locale' => 'en',
            'key' => 'title',
            'value' => 'Product title',
        ]);

        $this->assertDatabaseMissing('translations', [
            'translatable_id' => $product->id,
            'translatable_type' => Product::class,
            'locale' => 'en',
            'key' => 'description',
            'value' => 'Product description',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testWithTranslation(): void
    {
        $product = $this->storeProduct();

        $product->translate('en', [
            'title' => 'Product title',
            'description' => 'Product description'
        ]);

        $product_result = Product::query()->limit(1)->first()->withTranslation('en', 'title');

        $this->assertEquals('Product title', $product_result->translation->first()->value);
    }

    /**
     * @throws Throwable
     */
    public function testWithTranslations(): void
    {
        $product = $this->storeProduct();

        $product->translate('en', [
            'title' => 'Product title',
            'description' => 'Product description'
        ]);

        $product_result = Product::query()->limit(1)->first()->withTranslations();

        $this->assertEquals('Product title', $product_result->translation->first()->value);
    }

    /**
     * @throws Throwable
     */
    public function testHasTranslationField(): void
    {
        $product = $this->storeProduct();

        $check = Product::find(1)->hasTranslationField();

        $this->assertFalse($check);

        $product->translate('en', [
            'title' => 'Product title',
            'description' => 'Product description'
        ]);

        $check = Product::find(1)->hasTranslationField();

        $this->assertTrue($check);
    }

    /**
     * @throws Throwable
     */
    public function testGetTranslation(): void
    {
        $product = $this->storeProduct();

        $product->translate('en', [
            'title' => 'Product title',
            'description' => 'Product description'
        ]);

        $product_title = Product::find(1)->getTranslation('title');

        $this->assertEquals('Product title', $product_title);
    }

    /**
     * @throws Throwable
     */
    public function testGetTranslations(): void
    {
        $product = $this->storeProduct();

        $product->translate('en', [
            'title' => 'Product title',
            'description' => 'Product description'
        ]);

        $get_translation = Product::find(1)->getTranslations('en');

        $this->assertEquals([
            'title' => 'Product title',
            'description' => 'Product description'
        ], $get_translation);
    }
}
