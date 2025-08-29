[Back To Readme.md](https://github.com/jobmetric/laravel-translation/blob/master/README.md)

# MultiTranslationArrayRequest Trait

The `MultiTranslationArrayRequest` trait helps you build **validation rules** and **friendly attribute labels** for multi-locale translation payloads under a single input root named `translation`. It is designed to work with models using the `HasTranslation` trait and a locale provider (via `Language::all()`).

> **No database or tests required here.** This document focuses on request-side validation and attribute labeling, with practical, copy‑pasteable examples **including expected output**.

---

## What It Generates

For each locale returned by `Language::all()`, this trait will generate:

- A container rule for `translation.{locale}` (array)
- A **primary field** rule with uniqueness enforcement via `TranslationFieldExistRule`
- Rules for **other allowed fields**:
    - If the model allows all fields (`['*']`): `translation.{locale}.* => string|nullable|sometimes`
    - Otherwise: adds `string|nullable|sometimes` for each explicit allowed field

It can also generate human-friendly attribute labels (e.g., for error messages) for each `translation.{locale}.{field}` key.

---

## Requirements (Conceptual)

- A model that **uses `HasTranslation`**, so the trait can query allowed fields through `getTranslatableFields()`.
- A language provider (e.g., `Language::all()`) that returns locales like `en`, `fa`, etc.
- The rule class `TranslationFieldExistRule` to apply uniqueness checks for the **primary** translated field per locale.

> The trait **does not** access your database here; it only builds arrays for validation and attributes.

---

## Quick Start (Typical Usage)

### 1) Put the trait in your **FormRequest**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Translation\Http\Requests\MultiTranslationArrayRequest;

class StorePostRequest extends FormRequest
{
    use MultiTranslationArrayRequest;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'slug' => 'required|string',
        ];

        // Build translation rules for App\Models\Post
        $this->renderMultiTranslationFiled(
            $rules,
            \App\Models\Post::class, // model using HasTranslation
            'title',                 // primary field to enforce uniqueness on
            null,                    // object_id: exclude current record on update (null for create)
            -1,                      // parent_id: leave -1 to ignore
            []                       // parent_where: extra constraints (optional)
        );

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'slug' => 'Slug',
        ];

        // Create readable labels. You can pass a translation key that contains "{field}"
        // Example value: "Translation ({field})"
        $this->renderMultiTranslationAttribute(
            $attributes,
            \App\Models\Post::class,
            null // or 'validation.attributes.translation_field' containing "{field}"
        );

        return $attributes;
    }
}
```

> **Note:** The method name is `renderMultiTranslationFiled` (with a trailing “d”), keep it exactly as implemented in your trait.

---

## Example: Resulting `rules()` Output

Assume:
- `Language::all()` returns locales: `en`, `fa`
- `App\Models\Post` uses `HasTranslation` with `protected array $translatables = ['title','summary'];`
- You set `field_name = 'title'` as the **primary** field

**Code (from `rules()`):**
```php
$rules = [
    'slug' => 'required|string',
];

$this->renderMultiTranslationFiled(
    $rules,
    \App\Models\Post::class,
    'title',
    null,
    -1,
    []
);

return $rules;
```

**Expected Output (`print_r($rules)`):**
```
Array
(
    [slug] => required|string
    [translation] => array
    [translation.en] => array
    [translation.en.title] => Array
        (
            [0] => string
            [1] => JobMetric\Translation\Rules\TranslationFieldExistRule Object (...)
        )

    [translation.en.summary] => string|nullable|sometimes
    [translation.fa] => array
    [translation.fa.title] => Array
        (
            [0] => string
            [1] => JobMetric\Translation\Rules\TranslationFieldExistRule Object (...)
        )

    [translation.fa.summary] => string|nullable|sometimes
)
```

### Wildcard Mode Example

If the model allows all fields (`getTranslatableFields()` returns `['*']`), then instead of enumerating each field you will get a wildcard:

**Expected Output (excerpt):**
```
[translation.en.*] => string|nullable|sometimes
[translation.fa.*] => string|nullable|sometimes
```

---

## Example: Resulting `attributes()` Output

Assume the same setup as above and **no** translation key passed (`$trans_scope = null`).

**Code (from `attributes()`):**
```php
$attributes = [
    'slug' => 'Slug',
];

$this->renderMultiTranslationAttribute(
    $attributes,
    \App\Models\Post::class,
    null
);

return $attributes;
```

**Expected Output (`print_r($attributes)`):**
```
Array
(
    [slug] => Slug
    [translation.en.title] => title
    [translation.en.summary] => summary
    [translation.fa.title] => title
    [translation.fa.summary] => summary
)
```

### Using a Translation Key with `{field}`

If you pass a key that contains `{field}`, the token will be replaced per field. For example, if your translation key renders to `Translation ({field})`, labels would look like:

**Expected Output (excerpt):**
```
[translation.en.title] => Translation (title)
[translation.en.summary] => Translation (summary)
...
```

### Wildcard Mode for Attributes

If the model allows all fields (`['*']`), you will get a wildcard label per locale:

**Expected Output (excerpt):**
```
[translation.en.*] => translation en
[translation.fa.*] => translation fa
```

---

## API Reference

### `renderMultiTranslationFiled(array &$rules, string $model_name, string $field_name = 'name', ?int $object_id = null, ?int $parent_id = -1, array $parent_where = []): void`

Builds validation rules for each available locale under `translation.{locale}`.

**Parameters**
- `&$rules`: The rules array (passed by reference) to be populated.
- `model_name`: FQCN of the model that uses `HasTranslation` (e.g., `App\Models\Post::class`).
- `field_name`: The **primary** translatable field to enforce uniqueness on (default `name`).
- `object_id`: Current record id to exclude during updates (use `null` for create).
- `parent_id`: Optional parent constraint (use `-1` to ignore).
- `parent_where`: Additional constraints (array) used by your uniqueness rule logic.

**Result**
- Adds entries like `translation.en`, `translation.en.title`, etc., to your `$rules` array.
- Primary field gets the `TranslationFieldExistRule` instance.
- Non-primary allowed fields get `string|nullable|sometimes`.

---

### `renderMultiTranslationAttribute(array &$params, string $model_name, ?string $trans_scope = null): void`

Builds user-friendly attribute labels for each `translation.{locale}.{field}` (or wildcard).

**Parameters**
- `&$params`: The attributes array (passed by reference) to be populated.
- `model_name`: FQCN of the model that uses `HasTranslation`.
- `trans_scope` (optional): A translation key whose resulting string may include `{field}` which will be replaced. If omitted, raw field keys or a simple "translation {locale}" label is used.

**Result**
- Adds readable attribute labels suitable for validation messages.

---

## Complete Copy‑Paste Example

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Translation\Http\Requests\MultiTranslationArrayRequest;

class StorePostRequest extends FormRequest
{
    use MultiTranslationArrayRequest;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'slug' => 'required|string',
        ];

        $this->renderMultiTranslationFiled(
            $rules,
            \App\Models\Post::class,
            'title',
            null,
            -1,
            []
        );

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'slug' => 'Slug',
        ];

        $this->renderMultiTranslationAttribute(
            $attributes,
            \App\Models\Post::class,
            null
        );

        return $attributes;
    }
}
```

**Expected Output (rules):**
```
Array
(
    [slug] => required|string
    [translation] => array
    [translation.en] => array
    [translation.en.title] => Array
        (
            [0] => string
            [1] => JobMetric\Translation\Rules\TranslationFieldExistRule Object (...)
        )

    [translation.en.summary] => string|nullable|sometimes
    [translation.fa] => array
    [translation.fa.title] => Array
        (
            [0] => string
            [1] => JobMetric\Translation\Rules\TranslationFieldExistRule Object (...)
        )

    [translation.fa.summary] => string|nullable|sometimes
)
```

**Expected Output (attributes):**
```
Array
(
    [slug] => Slug
    [translation.en.title] => title
    [translation.en.summary] => summary
    [translation.fa.title] => title
    [translation.fa.summary] => summary
)
```

---

## Tips

- Use the **primary field** (`$field_name`) for uniqueness (e.g., `title`, `name`, or `slug`).
- Pass `object_id` when updating a record to exclude it from uniqueness checks.
- Keep `parent_id` as `-1` if you don't need any parent scoping.
- Use `renderMultiTranslationAttribute` to produce clean labels for validation errors per-locale and per-field.
- If your model allows `['*']`, you receive wildcard rules and wildcard attribute labels automatically.

---

## Summary

`MultiTranslationArrayRequest` makes multi-locale validation of translated fields straightforward:
- Builds locale-aware rules under `translation.{locale}`
- Enforces per-locale uniqueness on a primary field
- Adds user-friendly attribute labels
- Respects the model’s allowed fields (`['*']` or explicit list) via `HasTranslation`

Copy the snippets above into your `FormRequest` classes, and you’re ready to validate multi-locale translation payloads with clear error messages and clean structure.

[Next To Request - MultiTranslationTypeObject](https://github.com/jobmetric/laravel-translation/blob/master/docs/MultiTranslationTypeObjectRequest.md)
