<?php

namespace JobMetric\Translation\Exceptions;

use Exception;
use Throwable;

class TranslationDisallowFieldException extends Exception
{
    public function __construct(string $model, string|array $field, int $code = 400, ?Throwable $previous = null)
    {
        $fieldWord = "field";
        if(is_array($field)){
            $field = implode(", " , $field);
            $fieldWord = "fields";
        }
        parent::__construct("The model $model does not allow the $fieldWord $field to be translated.", $code, $previous);
    }
}
