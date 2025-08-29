[Back To Readme.md](https://github.com/jobmetric/laravel-translation/blob/master/README.md)

# TranslationResource

`TranslationResource` is a lightweight JSON resource for serializing a **single translation row**. It provides toggles to include or exclude version, timestamps, and soft‑deletion metadata, so your API can shape output per use case without custom transformers.

> This guide focuses on usage and outputs. No tests or database details are discussed.

---

## Constructor

```php
use JobMetric\Translation\Http\Resources\TranslationResource;

// Wrap a single Translation model (or any object exposing the needed properties)
return new TranslationResource($translationRow);
```

If the underlying resource is `null`, the resource returns an **empty array** (`[]`).

---

## Default Behavior

By default:
- `version` **is included** (if present on the resource)
- `created_at` and `updated_at` are **excluded**
- `deleted_at` is **excluded**

You can change these with chainable methods:
- `withVersion(bool $with = true)`
- `withTimestamps(bool $with = true)`
- `withDeletedAt(bool $with = true)`

---

## Output Shape

Base fields (always present if available on the resource):
- `translatable_type`
- `translatable_id`
- `locale`
- `field`
- `value`

Optional fields (based on toggles and availability):
- `version` (integer)
- `created_at` (string datetime)
- `updated_at` (string datetime)
- `deleted_at` (string datetime or `null`)

---

## Examples

### 1) Default Output (version included, timestamps & deleted_at excluded)

**Usage:**
```php
return new TranslationResource($translation);
```

**Expected Output:**
```json
{
  "translatable_type": "App\\Models\\Post",
  "translatable_id": 42,
  "locale": "fa",
  "field": "title",
  "value": "تیتر فارسی",
  "version": 3
}
```

---

### 2) Exclude Version

**Usage:**
```php
return (new TranslationResource($translation))
    ->withVersion(false);
```

**Expected Output:**
```json
{
  "translatable_type": "App\\Models\\Post",
  "translatable_id": 42,
  "locale": "fa",
  "field": "title",
  "value": "تیتر فارسی"
}
```

---

### 3) Include Timestamps

**Usage:**
```php
return (new TranslationResource($translation))
    ->withTimestamps();
```

**Expected Output:**
```json
{
  "translatable_type": "App\\Models\\Post",
  "translatable_id": 42,
  "locale": "fa",
  "field": "title",
  "value": "تیتر فارسی",
  "version": 3,
  "created_at": "2025-08-10 12:00:00",
  "updated_at": "2025-08-12 09:15:30"
}
```

---

### 4) Include Soft‑Deletion Metadata

**Usage:**
```php
return (new TranslationResource($translation))
    ->withDeletedAt();
```

**Expected Output (soft‑deleted row example):**
```json
{
  "translatable_type": "App\\Models\\Post",
  "translatable_id": 42,
  "locale": "fa",
  "field": "summary",
  "value": "خلاصه قدیمی",
  "version": 2,
  "deleted_at": "2025-08-10 12:34:56"
}
```

**Expected Output (active row example):**
```json
{
  "translatable_type": "App\\Models\\Post",
  "translatable_id": 42,
  "locale": "fa",
  "field": "summary",
  "value": "خلاصه جدید",
  "version": 3,
  "deleted_at": null
}
```

---

### 5) Full Metadata (Version + Timestamps + DeletedAt)

**Usage:**
```php
return (new TranslationResource($translation))
    ->withVersion()
    ->withTimestamps()
    ->withDeletedAt();
```

**Expected Output:**
```json
{
  "translatable_type": "App\\Models\\Post",
  "translatable_id": 42,
  "locale": "fa",
  "field": "title",
  "value": "تیتر فارسی",
  "version": 3,
  "created_at": "2025-08-10 12:00:00",
  "updated_at": "2025-08-12 09:15:30",
  "deleted_at": null
}
```

---

## Chaining Patterns

- Minimal:
  ```php
  new TranslationResource($row);
  ```

- Without version:
  ```php
  (new TranslationResource($row))->withVersion(false);
  ```

- All metadata:
  ```php
  (new TranslationResource($row))->withTimestamps()->withDeletedAt();
  ```

- Custom combination:
  ```php
  (new TranslationResource($row))->withVersion(false)->withDeletedAt();
  ```

---

## Edge Cases

- **Null resource** → `[]`
- **Missing `version` property** on the resource while `withVersion(true)` is used → `version` is simply omitted.
- **Missing `created_at`/`updated_at`/`deleted_at`** on the resource → the corresponding fields are omitted (or `null` if accessors return null).

---

## Summary

`TranslationResource` gives you a small, reliable surface to serialize a single translation row. Toggle version, timestamps, and soft‑deletion data as needed, keep your controllers lean, and your API responses consistent.

[Next To Request - MultiTranslationArray](https://github.com/jobmetric/laravel-translation/blob/master/dosc/MultiTranslationArrayRequest.md)
