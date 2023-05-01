<?php

namespace JobMetric\Translation\Rules;

use JobMetric\Translation\Models\Translation;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class TranslationTitleExistRule implements Rule
{
    private string $class_name;

    public function __construct(string $class_name)
    {
        $this->class_name = $class_name;
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
            $query->where('title', $value);
        })->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('translation::base.rule.exist_title');
    }
}
