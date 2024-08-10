<?php

namespace JobMetric\Translation\Rules;

use JobMetric\Translation\Models\Translation;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class TranslationFieldExistRule implements Rule
{
    private string $class_name;
    private string $field_name;
    private ?string $locale;
    private ?int $unit_id;

    public function __construct(string $class_name, string $field_name = 'title', string $locale = null, int $unit_id = null)
    {
        $this->class_name = $class_name;
        $this->field_name = $field_name;

        if ($locale === null) {
            $this->locale = app()->getLocale();
        } else {
            $this->locale = $locale;
        }

        $this->unit_id = $unit_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return !Translation::query()->where([
            'translatable_type' => $this->class_name,
            'locale' => $this->locale,
            'key' => $this->field_name,
            'value' => $value
        ])->when($this->unit_id, function (Builder $q) {
            $q->where('translatable_id', '!=', $this->unit_id);
        })->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('translation::base.rule.exist', ['field' => $this->field_name]);
    }
}
