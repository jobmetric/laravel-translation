# Changelog

## 3.9.1

- Explicitly mark the optional locale as nullable in `translationResourceData` and `translationDataSelect`, avoiding implicit-nullability deprecations on PHP 8.4+.
- Preserve existing locale filtering and default-locale behavior for omitted or explicit `null` arguments.
