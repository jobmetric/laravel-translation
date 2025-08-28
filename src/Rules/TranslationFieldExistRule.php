<?php

namespace JobMetric\Translation\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use JobMetric\Translation\Exceptions\ModelHasTranslationNotFoundException;
use JobMetric\Translation\HasTranslation;
use JobMetric\Translation\Models\Translation;

/**
 * Class TranslationFieldExistRule
 *
 * Validation rule to ensure a translated field value is unique across a model's records
 * for a specific locale. The rule is aware of the package's versioning behavior:
 *
 * - When versioning is enabled on the parent model (via HasTranslation::usesTranslationVersioning()):
 *   the rule only checks "active" rows (i.e., rows where deleted_at IS NULL),
 *   which implicitly represents the latest version for each (locale, field).
 *
 * - When versioning is disabled on the parent model:
 *   the rule matches strictly on version = 1 and deleted_at IS NULL.
 *
 * The rule also respects:
 * - The configured translations table name via config('translation.tables.translation').
 * - Excluding the current record by id (useful on update flows).
 * - Optional constraints on the parent table (parent_id and arbitrary where conditions).
 */
class TranslationFieldExistRule implements Rule
{
    /**
     * Fully-qualified class name of the parent (translatable) model.
     *
     * @var string
     */
    private string $class_name;

    /**
     * Translation field (e.g., "title").
     *
     * @var string
     */
    private string $field_name;

    /**
     * Target locale to check. Defaults to app()->getLocale().
     *
     * @var string|null
     */
    private ?string $locale;

    /**
     * Parent model id to exclude from the check (useful when updating).
     *
     * @var int|null
     */
    private ?int $object_id;

    /**
     * Optional filter on the parent model's table: "<table>.parent_id = :parent_id".
     * Use -1 to ignore.
     *
     * @var int|null
     */
    private ?int $parent_id;

    /**
     * Additional where filters on the parent model's table:
     * e.g., ['status' => 'published'].
     *
     * @var array
     */
    private array $parent_where;

    /**
     * Translation key used to render a friendly field name in the error message.
     *
     * @var string
     */
    private string $field_name_trans;

    /**
     * @param string $class_name Fully-qualified parent model class (must use HasTranslation)
     * @param string $field_name Translation field to check (e.g., "title")
     * @param string|null $locale Locale; defaults to app()->getLocale()
     * @param int|null $object_id Current parent id to exclude (on update)
     * @param int|null $parent_id Optional: filter on "<parent_table>.parent_id"; pass -1 to ignore
     * @param array $parent_where Optional: extra where constraints on parent table (['col' => 'value'])
     * @param string $field_name_trans i18n key for readable field name in the error message
     *
     * @throws ModelHasTranslationNotFoundException If the given model class does not use HasTranslation trait
     */
    public function __construct(
        string  $class_name,
        string  $field_name = 'title',
        ?string $locale = null,
        ?int    $object_id = null,
        ?int    $parent_id = -1,
        array   $parent_where = [],
        string  $field_name_trans = 'translation::base.rule.default_field'
    )
    {
        if (!class_exists($class_name) || !in_array(HasTranslation::class, class_uses_recursive($class_name), true)) {
            throw new ModelHasTranslationNotFoundException($class_name);
        }

        $this->class_name = $class_name;
        $this->field_name = $field_name;
        $this->locale = $locale ?? app()->getLocale();
        $this->object_id = $object_id;
        $this->parent_id = $parent_id;
        $this->parent_where = $parent_where;
        $this->field_name_trans = $field_name_trans;
    }

    /**
     * Determine if the validation rule passes.
     *
     * Semantics:
     * - Returns TRUE when no conflicting translation exists for the given
     *   (model type, locale, field, value) under the current versioning policy.
     * - Returns FALSE if any row matches the constraints.
     *
     * Constraints applied:
     * - translations.translatable_type = :class_name
     * - translations.locale            = :locale
     * - translations.field             = :field_name
     * - translations.value             = :value
     * - Versioning ON  -> translations.deleted_at IS NULL
     * - Versioning OFF -> translations.version = 1 AND translations.deleted_at IS NULL
     * - Optional parent filters (parent_id / extra where)
     * - Optional exclusion of the current record by id
     *
     * @param string $attribute Current attribute name under validation
     * @param mixed $value Submitted translation value
     * @return bool              TRUE when valid (no conflict), FALSE otherwise
     */
    public function passes($attribute, $value): bool
    {
        $translationTable = (string)config('translation.tables.translation', (new Translation)->getTable());

        $parentModel = new $this->class_name();
        $parentTable = $parentModel->getTable();
        $parentKeyName = $parentModel->getKeyName();

        $versioningEnabled = method_exists($parentModel, 'usesTranslationVersioning') && (bool)$parentModel->usesTranslationVersioning();

        $query = DB::table($translationTable)
            ->join($parentTable, "{$translationTable}.translatable_id", '=', "{$parentTable}.{$parentKeyName}")
            ->where("{$translationTable}.translatable_type", $this->class_name)
            ->where("{$translationTable}.locale", $this->locale)
            ->where("{$translationTable}.field", $this->field_name)
            ->where("{$translationTable}.value", $value);

        if ($versioningEnabled) {
            $query->whereNull("{$translationTable}.deleted_at");
        } else {
            $query->where("{$translationTable}.version", 1)
                ->whereNull("{$translationTable}.deleted_at");
        }

        if ($this->parent_id !== null && $this->parent_id !== -1) {
            $query->where("{$parentTable}.parent_id", $this->parent_id);
        }

        foreach ($this->parent_where as $col => $val) {
            $query->where("{$parentTable}.{$col}", $val);
        }

        if (!empty($this->object_id)) {
            $query->where("{$translationTable}.translatable_id", '!=', $this->object_id);
        }

        // Passes if there is no conflicting row
        return !$query->exists();
    }

    /**
     * The validation error message.
     *
     * Uses the provided i18n key to render a readable field name.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('translation::base.rule.exist', ['field' => trans($this->field_name_trans)]);
    }
}
