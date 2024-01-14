<?php

namespace JobMetric\Translation\Events;

use Illuminate\Database\Eloquent\Model;

class TranslationForgetEvent
{
    public Model $model;

    /**
     * Create a new event instance.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
