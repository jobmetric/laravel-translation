<?php

namespace JobMetric\Translation\Exceptions;

use Exception;
use Throwable;

class ModelHasTranslationNotFoundException extends Exception
{
    public function __construct(string $model, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans("translation::base.exceptions.model_not_use_trait", $model), $code, $previous);
    }
}
