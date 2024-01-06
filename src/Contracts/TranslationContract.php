<?php

namespace JobMetric\Translation\Contracts;

interface TranslationContract
{
    /**
     * translation allow fields.
     *
     * @return array
     */
    public function translationAllowFields(): array;
}
