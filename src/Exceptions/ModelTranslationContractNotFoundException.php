<?php

namespace JobMetric\Translation\Exceptions;

use Exception;
use Throwable;

class ModelTranslationContractNotFoundException extends Exception
{
    public function __construct(string $model, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct("Model $model not implements JobMetric\Translation\Contracts\TranslationContract interface!", $code, $previous);
    }
}
