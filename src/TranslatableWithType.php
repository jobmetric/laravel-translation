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
                'label' => trans('translation::base.filed.name.label'),
                'info' => trans('translation::base.filed.name.info'),
                'placeholder' => trans('translation::base.filed.name.placeholder'),
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
