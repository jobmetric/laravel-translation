[Back To Readme.md](https://github.com/jobmetric/laravel-translation/blob/master/README.md)

# TranslationCollectionResource

`TranslationCollectionResource` is a JSON resource that serializes a model's translations into a clean, predictable shape. It supports:

- **Locale filtering** (`withLocale()`)
- **Field filtering** (`onlyFields()`)
- **Including historical rows** (soft‑deleted) via `withHistory()`
- **Including version/status metadata** via `includeVersion()`
- Smart **per‑field reduction** to a single definitive row (latest active; otherwise latest by version).

> This document focuses on how to use the resource and what it outputs. No tests or database discussion here.

---

## Constructor

```php
use JobMetric\Translation\Http\Resources\TranslationCollectionResource;

// Wrap an Eloquent model instance that uses HasTranslation
return new TranslationCollectionResource($post);
```

The resource will read translations from the model's `translations` relationship. If the relation is already eager‑loaded, it uses that data; otherwise it resolves it on demand.

---

## Output Shapes

### 1) Single Locale (no version info)

When `withLocale('fa')` is used, the resource returns a **flat map** of `field => value` for that locale.  
For each field, the **latest active** row is chosen; if none is active, the **latest by version** is returned.

**Example Usage:**
```php
return (new TranslationCollectionResource($post))
    ->withLocale('fa');
```

**Expected Output:**
```json
{
  "title": "تیتر فارسی",
  "summary": "خلاصه فارسی"
}
```

---

### 2) Single Locale + Version/Status Metadata

Add `includeVersion()` to include per‑field metadata:

- `value`: translated value
- `version`: integer version number
- `is_active`: `true` if not soft‑deleted
- `deleted_at`: timestamp string or `null`

**Example Usage:**
```php
return (new TranslationCollectionResource($post))
    ->withLocale('fa')
    ->includeVersion();
```

**Expected Output:**
```json
{
  "title": {
    "value": "تیتر فارسی",
    "version": 3,
    "is_active": true,
    "deleted_at": null
  },
  "summary": {
    "value": "خلاصه قدیمی",
    "version": 2,
    "is_active": false,
    "deleted_at": "2025-08-10 12:34:56"
  }
}
```

---

### 3) All Locales (no version info)

Without `withLocale(...)`, the resource returns an **object keyed by locale**, where each value is a flat map of `field => value` reduced to a single definitive row per field.

**Example Usage:**
```php
return new TranslationCollectionResource($post);
```

**Expected Output:**
```json
{
  "en": {
    "title": "Hello Title",
    "summary": "English Summary"
  },
  "fa": {
    "title": "تیتر فارسی",
    "summary": "خلاصه فارسی"
  }
}
```

---

### 4) All Locales + Version/Status Metadata

**Example Usage:**
```php
return (new TranslationCollectionResource($post))
    ->includeVersion();
```

**Expected Output:**
```json
{
  "en": {
    "title": { "value": "Hello Title", "version": 1, "is_active": true, "deleted_at": null },
    "summary": { "value": "English Summary", "version": 2, "is_active": true, "deleted_at": null }
  },
  "fa": {
    "title": { "value": "تیتر فارسی", "version": 3, "is_active": true, "deleted_at": null },
    "summary": { "value": "خلاصه قدیمی", "version": 2, "is_active": false, "deleted_at": "2025-08-10 12:34:56" }
  }
}
```

---

## Filtering

### Filter by Locale

```php
(new TranslationCollectionResource($post))->withLocale('en');
```

**Output (example):**
```json
{
  "title": "Hello Title",
  "summary": "English Summary"
}
```

---

### Filter by Fields

Use `onlyFields()` to restrict output to certain fields. It accepts an array of field names and de‑duplicates them.

```php
return (new TranslationCollectionResource($post))
    ->withLocale('fa')
    ->onlyFields(['title', 'seo_title', 'title']); // duplicates are removed
```

**Expected Output (example):**
```json
{
  "title": "تیتر فارسی",
  "seo_title": "تیتر سئو"
}
```

> When `includeVersion()` is also enabled, each field becomes an object with `value`, `version`, `is_active`, and `deleted_at` as shown earlier.

---

### Include Historical Rows (Soft‑Deleted)

By default, only **active** rows are considered for reduction. If you want the reduction to consider historical rows for selection (and return them when no active row exists), you can call `withHistory(true)`. This also affects the **pool** of rows visible to `onlyFields()` and reduction logic.

```php
return (new TranslationCollectionResource($post))
    ->withLocale('fa')
    ->withHistory(); // enable history consideration
```

**Output (example without version info):**
```json
{
  "title": "تیتر فارسی",
  "summary": "خلاصه قدیمی"
}
```

> Here `summary` may be picked from a soft‑deleted row if it is the latest available and there is no active row.

---

## Chaining Examples

### Example A: All locales, version metadata, restrict to subset of fields
```php
return (new TranslationCollectionResource($post))
    ->onlyFields(['title', 'summary'])
    ->includeVersion();
```

**Expected Output (example):**
```json
{
  "en": {
    "title": { "value": "Hello Title", "version": 1, "is_active": true, "deleted_at": null },
    "summary": { "value": "English Summary", "version": 2, "is_active": true, "deleted_at": null }
  },
  "fa": {
    "title": { "value": "تیتر فارسی", "version": 3, "is_active": true, "deleted_at": null },
    "summary": { "value": "خلاصه قدیمی", "version": 2, "is_active": false, "deleted_at": "2025-08-10 12:34:56" }
  }
}
```

### Example B: Single locale, history + version metadata
```php
return (new TranslationCollectionResource($post))
    ->withLocale('fa')
    ->withHistory()
    ->includeVersion();
```

**Expected Output (example):**
```json
{
  "title": { "value": "تیتر فارسی", "version": 3, "is_active": true, "deleted_at": null },
  "summary": { "value": "خلاصه قدیمی", "version": 2, "is_active": false, "deleted_at": "2025-08-10 12:34:56" }
}
```

---

## Reduction Strategy (How a Single Row is Chosen Per Field)

For every `(locale, field)` group, the resource picks **one** definitive row:

1. If there are **active** rows (not soft‑deleted), choose the one with the **highest version**.
2. Otherwise, choose the row with the **highest version** among all rows (including soft‑deleted).

This ensures stable and predictable outputs whether or not you include historical rows.

---

## Summary

`TranslationCollectionResource` turns raw translation rows into compact, consumer‑friendly JSON:

- Locale‑aware output (single or all locales)
- Optional per‑field metadata (version, active flag, deleted timestamp)
- Optional inclusion of history (soft‑deleted rows)
- Field whitelisting
- Deterministic selection of the best row per field

Use the chaining methods to quickly tailor outputs to your API consumers.

[Next To Resource - Translation](https://github.com/jobmetric/laravel-translation/blob/master/dosc/TranslationResource.md)
