<?php

namespace JobMetric\Translation\Events;

use Illuminate\Database\Eloquent\Model;

class TranslationStoredEvent
{
    public Model $model;
    public string $locale;
    public array $data;

    /**
     * Create a new event instance.
     */
    public function __construct(Model $model, string $locale, array $data)
    {
        $this->model = $model;
        $this->locale = $locale;
        $this->data = $data;
    }
}
