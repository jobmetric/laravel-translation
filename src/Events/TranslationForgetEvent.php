<?php

namespace JobMetric\Translation\Events;

use JobMetric\Translation\Models\Translation;

class TranslationForgetEvent
{
    public Translation $translation;

    /**
     * Create a new event instance.
     */
    public function __construct(Translation $translation)
    {
        $this->translation = $translation;
    }
}
