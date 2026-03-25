<?php

namespace JobMetric\Translation\Http\Requests;

use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Throwable;

class SetTranslationRequest extends FormRequest
{
    use TranslationArrayRequest;

    public string $modelClass;
    public string $table;
    public string $attributePath = 'translation.fields.{field}';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     * @throws Exception
     */
    public function rules(): array
    {
        $formData = $this->all();

        $locale = $formData['locale'] ?? null;
        $id = $formData['translatable_id'] ?? null;

        if (is_null($locale)) {
            throw ValidationException::withMessages([
                'locale' => [trans('validation.required', ['attribute' => 'locale'])],
            ]);
        }

        if (is_null($id)) {
            throw ValidationException::withMessages([
                'translatable_id' => [trans('validation.required', ['attribute' => 'translatable_id'])],
            ]);
        }

        $modelClass = $this->modelClass;
        $modelClass::query()->findOrFail($id);

        $rules = [
            'locale' => 'required|string',
            'translatable_id' => 'required|integer|exists:' . $this->table . ',id',
        ];

        $this->renderTranslationFiled($rules, $formData, $modelClass, object_id: (int) $id);

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        $formData = $this->all();

        $params = [];
        $this->renderTranslationAttribute($params, $formData, $this->modelClass, $this->attributePath);

        return $params;
    }

    /**
     * Configure model context used by the request.
     *
     * @param class-string $modelClass
     * @param string $table
     * @param string $attributePath
     *
     * @return static
     */
    public function setContext(array $context): static
    {
        $this->modelClass = (string) ($context['model_class'] ?? '');
        $this->table = (string) ($context['table'] ?? '');
        $this->attributePath = (string) ($context['attribute_path'] ?? 'translation.fields.{field}');

        return $this;
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws Throwable
     */
    public static function rulesFor(array $input, array $context = []): array
    {
        $request = new self;
        $request->merge($input);
        $request->setContext($context);

        return $request->rules();
    }
}
