[Back To Readme.md](https://github.com/jobmetric/laravel-translation/blob/master/README.md)

# TranslationFieldExistRule

`TranslationFieldExistRule` is a validation rule that ensures a translated **field value is unique per locale** across records of a given model that uses the `HasTranslation` trait. It is aware of the package’s versioning semantics and supports optional scoping (by parent constraints) and update-safe exclusions (by current object id).

> This guide focuses on how to **use** the rule and what **outputs** you can expect from the validator. No database setup or tests are covered here.

---

## Key Behavior

- **Uniqueness Target**: `(model_type, locale, field_name, value)`
- **Versioning-Aware**:
    - **Versioning ON** (model’s `usesTranslationVersioning()` returns true): checks **active** rows only (those not soft‑deleted).
    - **Versioning OFF**: checks `version = 1` and `deleted_at IS NULL`.
- **Update‑Safe Exclusion**: Pass the current record id to ignore its own row.
- **Optional Parent Scoping**:
    - `parent_id` (exact match on the parent table’s `parent_id` column when provided and not `-1`).
    - `parent_where` (associative array of additional column/value filters on the parent table).

---

## Constructor

```php
use JobMetric\Translation\Rules\TranslationFieldExistRule;

/**
 * @param class-string $class_name      // Parent model FQCN (must use HasTranslation)
 * @param string       $field_name      // Translated field key (e.g., "title")
 * @param string|null  $locale          // Locale (defaults to app()->getLocale())
 * @param int|null     $object_id       // Current parent id to exclude (for updates)
 * @param int|null     $parent_id       // Optional: constrain "<parent_table>.parent_id" (use -1 to ignore)
 * @param array        $parent_where    // Optional: additional constraints on parent table, e.g. ['status' => 'published']
 * @param string       $field_name_trans// i18n key to render a human-friendly field name in the error message
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
```

---

## Typical Usage in a FormRequest

```php
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Translation\Rules\TranslationFieldExistRule;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        $locale = 'en'; // or dynamic from payload
        return [
            'translation.en.title' => [
                'required',
                'string',
                new TranslationFieldExistRule(
                    \App\Models\Post::class,
                    'title',
                    $locale,
                    null,        // object_id (null on create)
                    -1,          // parent_id (ignore)
                    []           // parent_where
                ),
            ],
        ];
    }
}
```

**Expected Output (on conflict)** – Validator errors example:
```json
{
  "errors": {
    "translation.en.title": [
      "The translation field already exists." // (message may vary by your i18n key)
    ]
  }
}
```

---

## Update Flow (Exclude Current Record)

When updating a record, pass the current **object id** to exclude it from the uniqueness check.

```php
new TranslationFieldExistRule(
    \App\Models\Post::class,
    'title',
    'fa',
    $post->id, // exclude current record
    -1,
    []
);
```

**Expected Behavior:** The existing value for the current record does **not** trigger a conflict; conflicting values from **other** records do.

---

## With Parent Scoping

If your model needs scoping by a parent relationship (e.g., a child belongs to a category), you can pass:

- A **specific** `parent_id` (any value other than `-1` will enable a constraint on `<parent_table>.parent_id`).
- Additional `parent_where` constraints as an associative array.

```php
new TranslationFieldExistRule(
    \App\Models\Post::class,
    'title',
    'en',
    null,
    5, // parent_id: only check within parent_id = 5
    ['status' => 'published']
);
```

**Expected Output (conflict case):**
```json
{
  "errors": {
    "translation.en.title": [
      "The translation field already exists."
    ]
  }
}
```

> If no conflict exists **within the scoped subset**, the rule passes.

---

## Versioning Awareness (What It Means)

- If the parent model’s `usesTranslationVersioning()` returns **true**, then only **active** (non‑deleted) rows are considered conflicts. This aligns with “latest active per (locale, field)” semantics.
- Otherwise (versioning **off**), the rule checks for conflicts on `version = 1` with `deleted_at` null.

This keeps your validations consistent with how your application treats active vs historical translations.

---

## Customizing the Error Message

The rule uses this translation key by default for the field label in the message:

- `translation::base.rule.default_field`

And uses `translation::base.rule.exist` for the main message. You can pass a custom label key for the field via the constructor’s last parameter:

```php
new TranslationFieldExistRule(
    \App\Models\Post::class,
    'title',
    'en',
    null,
    -1,
    [],
    'validation.attributes.title' // field name label
);
```

**Expected Error Message (illustrative):**
```
The “Title” translation already exists.
```

*(The exact string depends on your language files.)*

---

## End‑to‑End Validator Example

```php
use Illuminate\Support\Facades\Validator;
use JobMetric\Translation\Rules\TranslationFieldExistRule;

$payload = [
    'translation' => [
        'en' => ['title' => 'Hello World']
    ]
];

$rules = [
    'translation.en.title' => [
        'required',
        'string',
        new TranslationFieldExistRule(\App\Models\Post::class, 'title', 'en')
    ],
];

$validator = Validator::make($payload, $rules);

if ($validator->fails()) {
    // Expected output example
    print_r($validator->errors()->toArray());
}
```

**Expected Output (on conflict):**
```
Array
(
    [translation.en.title] => Array
        (
            [0] => The translation field already exists.
        )
)
```

**Expected Output (when passes):**
```
Array
(
)
```

---

## Summary

`TranslationFieldExistRule` gives you a precise, versioning‑aware uniqueness check for translated fields:

- Works per locale and field
- Safe for update flows (exclude current id)
- Optional parent scoping and additional constraints
- Customizable error labels via i18n keys

Use it inside your FormRequests or manual validators to keep your translated attributes consistent and conflict‑free.

[Next To Events](https://github.com/jobmetric/laravel-translation/blob/master/docs/Events.md)
