<?php

namespace JobMetric\Translation\ServiceType;

use Closure;
use Illuminate\Support\Traits\Macroable;
use JobMetric\CustomField\CustomField;
use JobMetric\CustomField\CustomFieldBuilder;
use JobMetric\CustomField\Exceptions\OptionEmptyLabelException;
use Throwable;

class TranslationBuilder
{
    use Macroable;

    /**
     * The translation instances
     *
     * @var array $translation
     */
    protected array $translation;

    /**
     * The custom field instance.
     *
     * @var CustomField|null $customField
     */
    public ?CustomField $customField = null;

    /**
     * Set custom field.
     *
     * @param Closure $callable
     *
     * @return static
     */
    public function customField(Closure $callable): static
    {
        $callable($builder = new CustomFieldBuilder);

        $this->customField = $builder->build();

        $this->translation[] = $this->customField;

        return $this;
    }

    /**
     * Build the translation.
     *
     * @return Translation
     * @throws Throwable
     */
    public function build(): Translation
    {
        if (is_null($this->customField)) {
            throw new OptionEmptyLabelException;
        }

        $translation = new Translation($this->customField);

        $this->translation[] = $translation;

        return $translation;
    }

    /**
     * Execute the callback to build the translation.
     *
     * @return array
     */
    public function get(): array
    {
        return $this->translation;
    }
}
