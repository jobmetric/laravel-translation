<?php

namespace JobMetric\Translation\Exceptions;

use Exception;
use Throwable;

class ModelTranslationInterfaceNotFoundException extends Exception
{
    public function __construct(string $model, int $code = 400, ?Throwable $previous = null)
    {
        $message = 'Model "'.$model.'" not implements JobMetric\Translation\TranslationInterface interface!';

        parent::__construct($message, $code, $previous);
    }
}
