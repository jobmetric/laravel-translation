<?php

namespace JobMetric\Translation\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Translation\TranslationService
 */
class TranslationService extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'TranslationService';
    }
}
