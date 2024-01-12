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

    public function __construct(string $class_name, string $field_name = 'title', string $locale = null)
    {
        $this->class_name = $class_name;
        $this->field_name = $field_name;

        if ($locale === null) {
            $this->locale = app()->getLocale();
        } else {
            $this->locale = $locale;
        }
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
        return !Translation::query()->whereHasMorph('translatable', $this->class_name, function (Builder $query) use ($value) {
            $query->where('locale', $this->locale);
            $query->where('key', $this->field_name);
            $query->where('value', $value);
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
