<?php

namespace JobMetric\Translation\Services;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use JobMetric\PackageCore\Output\Response;
use JobMetric\Translation\Exceptions\ModelHasTranslationNotFoundException;
use JobMetric\Translation\Http\Requests\SetTranslationRequest;
use Throwable;

class Translation
{
    /**
     * Generic translation setter for any translatable model/service.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $requestContext
     * @param class-string<Model> $modelClass
     * @param string $message
     * @param int $status
     * @param Closure|null $notFoundExceptionFactory fn(int $id, ModelNotFoundException $e): Throwable
     *
     * @return Response
     * @throws Throwable
     */
    public function setTranslation(
        array $data,
        array $requestContext,
        string $modelClass,
        string $message,
        int $status = 200,
        ?Closure $notFoundExceptionFactory = null
    ): Response {
        $validated = dto($data, SetTranslationRequest::class, $requestContext);

        return DB::transaction(function () use ($validated, $modelClass, $message, $status, $notFoundExceptionFactory) {
            try {
                /** @var Model $model */
                $model = $modelClass::query()->findOrFail($validated['translatable_id']);
            } catch (ModelNotFoundException $e) {
                if ($notFoundExceptionFactory instanceof Closure) {
                    throw $notFoundExceptionFactory((int) $validated['translatable_id'], $e);
                }

                throw $e;
            }

            if (! translation_model_uses_has_translation($model)) {
                throw new ModelHasTranslationNotFoundException($modelClass);
            }

            foreach ($validated['translation'] as $locale => $translationData) {
                foreach ($translationData as $translationKey => $translationValue) {
                    $model->translate($locale, [
                        $translationKey => $translationValue,
                    ]);
                }
            }

            return Response::make(true, $message, null, $status);
        });
    }
}
