<?php

namespace JobMetric\Translation\Exceptions;

use Exception;
use Throwable;

class TranslationDisallowFieldException extends Exception
{
    public function __construct(string $model, string $field, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct("The model $model does not allow the field $field to be translated.", $code, $previous);
    }
}
