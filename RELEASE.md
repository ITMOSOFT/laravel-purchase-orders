# Release: v1.0.0

## Summary

Initial stable release of ITMOSOFT Laravel Purchase Orders.

## Highlights

- Supplier-linked purchase orders with unique order numbers.
- Draft, submitted, approved, partially received, completed, and cancelled lifecycle.
- Item-level quantity, unit cost, tax rate, and calculated totals.
- Partial and complete receiving with over-receipt protection.
- Supplier reference, currency, expected delivery date, and notes.
- Laravel package auto-discovery and configurable models/table names.

## Compatibility

- PHP 8.3+
- Laravel 12 and 13
- `itmosoft/laravel-suppliers` ^1.0

## Validation

Run the full test suite and CI matrix before tagging this release:

```bash
composer install
composer test
```

Do not publish this release until local tests and GitHub Actions pass.
