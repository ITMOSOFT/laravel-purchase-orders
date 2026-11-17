# Changelog

All notable changes to `itmosoft/laravel-purchase-orders` are documented here.

## [1.0.2] - 2026-11-17

- Define Eloquent model defaults for status, currency, and monetary totals so newly created in-memory models immediately reflect their database defaults.
- Add a regression assertion that verifies the draft status both before and after reloading the order.

## [1.0.1] - 2026-11-17

- Fix Testbench migration setup by using a dedicated supplier schema fixture instead of assuming dependency migrations are executable PHP migrations.
- Fix lifecycle methods to return the updated purchase order model, enabling fluent calls such as `submit()->approve()`.
- Make foreign key constraints respect configured supplier and purchase-order table names.

## [1.0.0] - 2026-11-17

- Add supplier-linked purchase order management.
- Add draft, submitted, approved, partially received, completed, and cancelled states.
- Add order line items with quantity, unit cost, tax rate, and line totals.
- Add approval and cancellation actions with lifecycle validation.
- Add receiving records that support partial deliveries.
- Add supplier reference numbers, currency, expected delivery date, and notes.
- Add configurable model and table settings.
