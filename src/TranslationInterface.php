<?php

namespace JobMetric\Translation;

interface TranslationInterface
{
    /**
     * allow translation fields.
     *
     * @return array
     */
    public function allowTranslationFields(): array;
}
