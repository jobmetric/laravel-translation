<?php

namespace JobMetric\Translation\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Translation\Services\Translation
 *
 * @method static \JobMetric\PackageCore\Output\Response setTranslation(array $data, array $requestContext, string $modelClass, string $message, int $status = 200, ?\Closure $notFoundExceptionFactory = null)
 */
class Translation extends Facade
{
    /**
     * Get the registered name of the component in the service container.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'translation';
    }
}
