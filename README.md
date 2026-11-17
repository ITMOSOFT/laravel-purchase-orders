# ITMOSOFT Laravel Purchase Orders

Supplier-linked purchase order management for Laravel 12 and 13.

## Requirements

- PHP 8.3+
- Laravel 12 or 13
- `itmosoft/laravel-suppliers` ^1.0

## Installation

```bash
composer require itmosoft/laravel-purchase-orders
php artisan vendor:publish --tag=purchase-orders-config
php artisan migrate
```

Laravel package discovery registers the service provider automatically.

## Basic usage

```php
use ItmoSoft\PurchaseOrders\Models\PurchaseOrder;

$order = PurchaseOrder::create([
    'supplier_id' => $supplier->id,
    'currency' => 'USD',
    'expected_at' => now()->addDays(10),
    'notes' => 'Initial stock replenishment',
]);

$order->items()->create([
    'product_type' => App\Models\Product::class,
    'product_id' => $product->getKey(),
    'description' => $product->name,
    'quantity' => 25,
    'unit_cost' => 12.50,
    'tax_rate' => 5,
]);

$order->recalculateTotals();
$order->submit();
$order->approve();
```

## Receiving goods

A receipt records what physically arrived. Multiple receipts can be attached to
one approved purchase order, allowing partial deliveries.

```php
$receipt = $order->receive([
    [
        'purchase_order_item_id' => $order->items()->first()->id,
        'quantity' => 10,
        'notes' => 'First delivery',
    ],
], receivedAt: now(), reference: 'GRN-1001');
```

After receiving, the order moves to `partially_received` or `completed`
depending on whether all ordered quantities have been received. Receipt
quantities cannot exceed the remaining ordered quantity.

## Status lifecycle

- `draft`: editable; can be submitted or cancelled.
- `submitted`: awaiting approval; can be approved or cancelled.
- `approved`: ready for receiving; can be received or cancelled if nothing has been received.
- `partially_received`: some quantities received; can receive more or complete.
- `completed`: all quantities received.
- `cancelled`: terminal state.

Approval and receipt operations are intended to be called from application
authorization-controlled workflows. This package does not implement user
permissions, approval chains, inventory posting, or supplier portal access.

## Money and quantity handling

Amounts are stored as decimal strings with two decimal places. Quantities are
stored to three decimal places. The package calculates line subtotal, tax, and
grand total on the server when `recalculateTotals()` is called. Applications
should not trust client-submitted totals.

## Configuration

Publish `config/purchase-orders.php` to customize model classes and table names.

## Testing

```bash
composer install
composer test
```

## Scope boundaries

This package manages purchasing documents and receiving records. It does not
automatically increase inventory, create supplier invoices, pay suppliers, or
synchronize external accounting systems. Connect those operations through
application-level listeners or services.

## License

MIT.
