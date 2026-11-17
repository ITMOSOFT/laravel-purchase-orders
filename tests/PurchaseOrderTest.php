<?php

namespace ItmoSoft\PurchaseOrders\Tests;

use Illuminate\Validation\ValidationException;
use ItmoSoft\PurchaseOrders\Enums\PurchaseOrderStatus;
use ItmoSoft\PurchaseOrders\Models\PurchaseOrder;
use ItmoSoft\Suppliers\Models\Supplier;

class PurchaseOrderTest extends TestCase
{
    protected function supplier(): Supplier
    {
        return Supplier::create([
            'name' => 'Acme Supplies',
            'slug' => 'acme-supplies-'.uniqid(),
            'code' => 'ACME-'.strtoupper(substr(uniqid(), -6)),
            'status' => 'active',
        ]);
    }

    protected function orderWithItem(): PurchaseOrder
    {
        $order = PurchaseOrder::create([
            'supplier_id' => $this->supplier()->id,
            'number' => 'PO-'.uniqid(),
            'currency' => 'USD',
        ]);

        $order->items()->create([
            'description' => 'Widget',
            'quantity' => 10,
            'unit_cost' => 5,
            'tax_rate' => 10,
        ]);

        return $order;
    }

    public function test_order_belongs_to_supplier_and_starts_as_draft(): void
    {
        $order = $this->orderWithItem();

        $this->assertSame(PurchaseOrderStatus::DRAFT->value, $order->status);
        $this->assertSame(PurchaseOrderStatus::DRAFT->value, $order->fresh()->status);
        $this->assertSame('Acme Supplies', $order->supplier->name);
    }

    public function test_submit_recalculates_totals_and_changes_status(): void
    {
        $order = $this->orderWithItem()->submit();

        $this->assertSame('50.00', $order->subtotal);
        $this->assertSame('5.00', $order->tax_total);
        $this->assertSame('55.00', $order->total);
        $this->assertSame('submitted', $order->status);
    }

    public function test_cannot_submit_empty_order(): void
    {
        $order = PurchaseOrder::create([
            'supplier_id' => $this->supplier()->id,
            'number' => 'PO-EMPTY',
            'currency' => 'USD',
        ]);

        $this->expectException(ValidationException::class);
        $order->submit();
    }

    public function test_approval_requires_submitted_state(): void
    {
        $this->expectException(ValidationException::class);
        $this->orderWithItem()->approve();
    }

    public function test_can_approve_submitted_order(): void
    {
        $order = $this->orderWithItem()->submit()->approve();

        $this->assertSame('approved', $order->status);
        $this->assertNotNull($order->approved_at);
    }

    public function test_partial_receiving_updates_status_and_records_receipt(): void
    {
        $order = $this->orderWithItem()->submit()->approve();
        $item = $order->items()->first();

        $receipt = $order->receive([
            ['purchase_order_item_id' => $item->id, 'quantity' => 4],
        ], reference: 'GRN-1');

        $this->assertCount(1, $receipt->items);
        $this->assertSame('partially_received', $order->fresh()->status);
    }

    public function test_fully_receiving_order_marks_it_completed(): void
    {
        $order = $this->orderWithItem()->submit()->approve();
        $item = $order->items()->first();

        $order->receive([
            ['purchase_order_item_id' => $item->id, 'quantity' => 10],
        ]);

        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_cannot_receive_more_than_ordered_quantity(): void
    {
        $order = $this->orderWithItem()->submit()->approve();
        $item = $order->items()->first();

        $this->expectException(ValidationException::class);
        $order->receive([
            ['purchase_order_item_id' => $item->id, 'quantity' => 11],
        ]);
    }

    public function test_can_cancel_draft_order(): void
    {
        $order = $this->orderWithItem()->cancel('No longer required');

        $this->assertSame('cancelled', $order->status);
        $this->assertSame('No longer required', $order->cancellation_reason);
    }

    public function test_cannot_cancel_order_after_receiving_goods(): void
    {
        $order = $this->orderWithItem()->submit()->approve();
        $item = $order->items()->first();
        $order->receive([
            ['purchase_order_item_id' => $item->id, 'quantity' => 2],
        ]);

        $this->expectException(ValidationException::class);
        $order->cancel();
    }
}
