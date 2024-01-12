<?php

namespace JobMetric\Translation\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Translation\Translation
 */
class Translation extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Translation';
    }
}
