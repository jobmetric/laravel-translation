<?php

namespace JobMetric\Translation;

/**
 * @property array transType
 */
trait TranslatableWithType
{
    protected array $transType = [];

    /**
     * translation allow fields.
     *
     * @return array
     */
    public function translationAllowFields(): array
    {
        return $this->getTrans($this->{$this->getTransFieldTypeName()});
    }

    /**
     * get translation default filed.
     *
     * @return array
     */
    public function getTransDefaultField(): array
    {
        return [
            'name' => [
                'type' => 'text',
                'label' => trans('translation::base.fields.name.label'),
                'info' => trans('translation::base.fields.name.info'),
                'placeholder' => trans('translation::base.fields.name.placeholder'),
            ]
        ];
    }

    /**
     * get trans filed type name.
     *
     * @return string
     */
    public function getTransFieldTypeName(): string
    {
        return 'type';
    }

    /**
     * Set translation.
     *
     * @param string $type
     * @param array $trans
     *
     * @return static
     */
    public function setTrans(string $type, array $trans): static
    {
        $this->transType[$type] = array_merge($this->getTransDefaultField(), $trans);

        return $this;
    }

    /**
     * Set seo translation fields.
     *
     * @param string $type
     *
     * @return static
     */
    public function setSeoTransFields(string $type): static
    {
        $this->transType[$type] = array_merge($this->transType[$type], [
            'meta_title' => [
                'type' => 'text',
                'label' => trans('translation::base.fields.meta_title.label'),
                'info' => trans('translation::base.fields.meta_title.info'),
                'placeholder' => trans('translation::base.fields.meta_title.placeholder'),
            ],
            'meta_description' => [
                'type' => 'text',
                'label' => trans('translation::base.fields.meta_description.label'),
                'info' => trans('translation::base.fields.meta_description.info'),
                'placeholder' => trans('translation::base.fields.meta_description.placeholder'),
            ],
            'meta_keywords' => [
                'type' => 'text',
                'label' => trans('translation::base.fields.meta_keywords.label'),
                'info' => trans('translation::base.fields.meta_keywords.info'),
                'placeholder' => trans('translation::base.fields.meta_keywords.placeholder'),
            ],
        ]);

        return $this;
    }

    /**
     * Get translation by type.
     *
     * @param string $type
     *
     * @return array
     */
    public function getTransType(string $type): array
    {
        return $this->transType[$type];
    }

    /**
     * Get translation keys by type.
     *
     * @param string $type
     *
     * @return array
     */
    public function getTrans(string $type): array
    {
        return array_keys($this->transType[$type] ?? []);
    }
}
