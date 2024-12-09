<?php

namespace JobMetric\Translation;

use Closure;
use Illuminate\Support\Collection;
use JobMetric\CustomField\CustomFieldBuilder;
use JobMetric\Translation\ServiceType\TranslationBuilder;
use Throwable;

/**
 * Trait TranslationServiceType
 *
 * @package JobMetric\Translation
 */
trait TranslationServiceType
{
    /**
     * The translation custom fields
     *
     * @var array $translation
     */
    protected array $translation = [];

    /**
     * boot translation service type
     *
     * @return void
     * @throws Throwable
     */
    public function bootTranslationServiceType(): void
    {
        $this->translation(function (TranslationBuilder $translationBuilder) {
            $translationBuilder->customField(function (CustomFieldBuilder $customFieldBuilder) {
                $customFieldBuilder::text()
                    ->name('translation[{locale}][name]', 'name')
                    ->label('translation::base.components.translation_card.fields.name.label')
                    ->info('translation::base.components.translation_card.fields.name.info')
                    ->placeholder('translation::base.components.translation_card.fields.name.placeholder')
                    ->required();
            });
        });
    }

    /**
     * Set seo field.
     *
     * @return static
     * @throws Throwable
     */
    public function seoTranslation(): static
    {
        $this
            ->translation(function (TranslationBuilder $builder) {
                $builder->customField(function (CustomFieldBuilder $customFieldBuilder) {
                    $customFieldBuilder::text()
                        ->name('translation[{locale}][meta_title]', 'meta_title')
                        ->label('translation::base.components.translation_card.fields.meta_title.label')
                        ->info('translation::base.components.translation_card.fields.meta_title.info')
                        ->placeholder('translation::base.components.translation_card.fields.meta_title.placeholder')
                        ->validation('string|nullable|sometimes');
                });
            })->translation(function (TranslationBuilder $builder) {
                $builder->customField(function (CustomFieldBuilder $customFieldBuilder) {
                    $customFieldBuilder::text()
                        ->name('translation[{locale}][meta_description]', 'meta_description')
                        ->label('translation::base.components.translation_card.fields.meta_description.label')
                        ->info('translation::base.components.translation_card.fields.meta_description.info')
                        ->placeholder('translation::base.components.translation_card.fields.meta_description.placeholder')
                        ->validation('string|nullable|sometimes');
                });
            })->translation(function (TranslationBuilder $builder) {
                $builder->customField(function (CustomFieldBuilder $customFieldBuilder) {
                    $customFieldBuilder::text()
                        ->name('translation[{locale}][meta_keywords]', 'meta_keywords')
                        ->label('translation::base.components.translation_card.fields.meta_keywords.label')
                        ->info('translation::base.components.translation_card.fields.meta_keywords.info')
                        ->placeholder('translation::base.components.translation_card.fields.meta_keywords.placeholder')
                        ->validation('string|nullable|sometimes');
                });
            });

        return $this;
    }

    /**
     * Set translation.
     *
     * @param Closure|array $callable
     *
     * @return static
     * @throws Throwable
     */
    public function translation(Closure|array $callable): static
    {
        if ($callable instanceof Closure) {
            $callable($builder = new TranslationBuilder);

            $this->translation[] = $builder->build();
        } else {
            foreach ($callable as $translation) {
                $builder = new TranslationBuilder;

                $builder->customField($translation['customField'] ?? null);

                $this->translation[] = $builder->build();
            }
        }

        $this->setTypeParam('translation', $this->translation);

        return $this;
    }

    /**
     * Get translation.
     *
     * @return Collection
     */
    public function getTranslation(): Collection
    {
        return collect($this->getTypeParam('translation', []));
    }
}
