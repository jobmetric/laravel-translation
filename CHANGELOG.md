# Changelog

## 4.1.0

- Add registered immutable business outcome events with English and Persian discovery metadata.
- Publish minimal allowlisted identifiers after the owning transaction commits; preserve all legacy event signatures and behavior.
- Suppress outcomes for validation failures, rollback and unchanged business state. Listener failures cannot invalidate committed writes. Delivery is synchronous without an outbox.


## 3.9.1

- Explicitly mark the optional locale as nullable in `translationResourceData` and `translationDataSelect`, avoiding implicit-nullability deprecations on PHP 8.4+.
- Preserve existing locale filtering and default-locale behavior for omitted or explicit `null` arguments.
