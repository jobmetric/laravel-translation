<?php

namespace JobMetric\Translation\Exceptions;

use Exception;
use Throwable;

class TranslationDisallowFieldException extends Exception
{
    public function __construct(string $model, string|array $field, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('translation::base.exceptions.disallow_field', [
            'model' => $model,
            'field' => is_array($field) ? implode(", ", $field) : $field,
        ]), $code, $previous);
    }
}
