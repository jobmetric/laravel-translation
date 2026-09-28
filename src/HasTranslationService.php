<?php

namespace JobMetric\Translation;

use BadMethodCallException;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use JobMetric\PackageCore\Output\Response;
use Throwable;

trait HasTranslationService
{
    /**
     * Generic setTranslation entry point for services.
     *
     * @param array<string, mixed> $data
     *
     * @return Response
     * @throws Throwable
     */
    public function setTranslation(array $data): Response
    {
        return translation_set(
            data: $data,
            requestContext: [
                'model_class' => $this->resolvedTranslationModelClass(),
                'table' => $this->resolvedTranslationTable(),
                'attribute_path' => $this->translationAttributePath(),
            ],
            modelClass: $this->resolvedTranslationModelClass(),
            message: $this->translationMessage(),
            status: $this->translationStatus(),
            notFoundExceptionFactory: $this->translationNotFoundExceptionFactory()
        );
    }

    /**
     * Resolve model class from service static modelClass.
     * Works for services extending AbstractCrudService.
     *
     * @return string
     */
    protected function resolvedTranslationModelClass(): string
    {
        if (property_exists(static::class, 'modelClass') && isset(static::$modelClass) && is_string(static::$modelClass)) {
            /** @var class-string $modelClass */
            $modelClass = static::$modelClass;

            return $modelClass;
        }

        throw new BadMethodCallException('Translation model class is not configured on this service.');
    }

    /**
     * Resolve database table from the target model.
     *
     * @return string
     */
    protected function resolvedTranslationTable(): string
    {
        $modelClass = $this->resolvedTranslationModelClass();
        $model = new $modelClass;

        return $model->getTable();
    }

    /**
     * Translation key pattern used for validation attribute labels.
     *
     * @return string
     */
    abstract protected function translationAttributePath(): string;

    /**
     * Success message returned by translation response.
     * Can be overridden per consumer service.
     *
     * @return string
     */
    protected function translationMessage(): string
    {
        return trans('translation::base.messages.set_translation');
    }

    /**
     * Optional HTTP status code for successful setTranslation response.
     *
     * @return int
     */
    protected function translationStatus(): int
    {
        return 200;
    }

    /**
     * Optional factory to map ModelNotFoundException into domain exception.
     *
     * @return Closure(int, ModelNotFoundException): Throwable|null
     */
    protected function translationNotFoundExceptionFactory(): ?Closure
    {
        return null;
    }
}
