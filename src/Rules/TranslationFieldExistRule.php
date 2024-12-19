<?php

namespace JobMetric\Translation\Rules;

use JobMetric\Translation\Models\Translation;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class TranslationFieldExistRule implements Rule
{
    private string $class_name;
    private string $field_name;
    private string|null $locale;
    private int|null $object_id;
    private int|null $parent_id;
    private array $parent_where;

    public function __construct(string $class_name, string $field_name = 'title', string|null $locale = null, int|null $object_id = null, int|null $parent_id = -1, array $parent_where = [])
    {
        $this->class_name = $class_name;
        $this->field_name = $field_name;

        if ($locale === null) {
            $this->locale = app()->getLocale();
        } else {
            $this->locale = $locale;
        }

        $this->object_id = $object_id;
        $this->parent_id = $parent_id;
        $this->parent_where = $parent_where;
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
        $_translation = (new Translation)->getTable();

        $query = Translation::query();

        $join_table = (new $this->class_name)->getTable();

        $query->join($join_table, $_translation . '.translatable_id', '=', $join_table . '.id');

        if ($this->parent_id != -1) {
            $query->where($join_table . '.parent_id', $this->parent_id);
        }

        foreach ($this->parent_where as $parent_where_key => $parent_where_value) {
            $query->where($join_table . '.' . $parent_where_key, $parent_where_value);
        }

        $query->where($_translation . '.translatable_type', $this->class_name)
            ->where($_translation . '.locale', $this->locale)
            ->where($_translation . '.key', $this->field_name)
            ->where($_translation . '.value', $value)
            ->when($this->object_id, function (Builder $q) use ($_translation) {
                $q->where($_translation . '.translatable_id', '!=', $this->object_id);
            });

        return !$query->exists();
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
