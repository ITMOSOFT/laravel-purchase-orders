# Release: v1.0.2

## Summary

Patch release fixing Eloquent's in-memory defaults for new purchase orders.

## Fixes

- Set model defaults for `status`, `currency`, `subtotal`, `tax_total`, and `total`.
- Ensure a newly created order is immediately recognized as `draft` without requiring a database refresh.
- Add a regression assertion covering both the in-memory and reloaded status.

## Validation

Run the full test suite and CI matrix before tagging:

```bash
composer install
composer test
```

Do not publish this release until local tests and GitHub Actions pass.
