<?php

namespace JobMetric\Translation\Exceptions;

use Exception;
use Throwable;

class ModelHasTranslationTraitNotFoundException extends Exception
{
    public function __construct(string $model, int $code = 400, ?Throwable $previous = null)
    {
        $message = 'Model "'.$model.'" not use JobMetric\Translation\Traits\HasTranslation Trait!';

        parent::__construct($message, $code, $previous);
    }
}
