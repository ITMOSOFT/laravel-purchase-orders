<?php

namespace ItmoSoft\PurchaseOrders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use ItmoSoft\Suppliers\Models\Supplier;
use ItmoSoft\PurchaseOrders\Enums\PurchaseOrderStatus;

class PurchaseOrder extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expected_at' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(config('purchase-orders.models.item'));
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(config('purchase-orders.models.receipt'));
    }

    public function recalculateTotals(): static
    {
        $subtotal = 0.0;
        $taxTotal = 0.0;

        foreach ($this->items as $item) {
            $lineSubtotal = (float) $item->quantity * (float) $item->unit_cost;
            $lineTax = $lineSubtotal * ((float) $item->tax_rate / 100);
            $subtotal += $lineSubtotal;
            $taxTotal += $lineTax;
            $item->forceFill([
                'line_subtotal' => number_format($lineSubtotal, 2, '.', ''),
                'line_tax' => number_format($lineTax, 2, '.', ''),
                'line_total' => number_format($lineSubtotal + $lineTax, 2, '.', ''),
            ])->save();
        }

        return tap($this->forceFill([
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'tax_total' => number_format($taxTotal, 2, '.', ''),
            'total' => number_format($subtotal + $taxTotal, 2, '.', ''),
        ]))->save();
    }

    public function submit(): static
    {
        $this->assertStatus(PurchaseOrderStatus::DRAFT);
        if ($this->items()->count() === 0) {
            throw ValidationException::withMessages(['items' => 'A purchase order must contain at least one item.']);
        }

        $this->recalculateTotals();
        return tap($this->forceFill([
            'status' => PurchaseOrderStatus::SUBMITTED->value,
            'submitted_at' => now(),
        ]))->save();
    }

    public function approve(): static
    {
        $this->assertStatus(PurchaseOrderStatus::SUBMITTED);
        return tap($this->forceFill([
            'status' => PurchaseOrderStatus::APPROVED->value,
            'approved_at' => now(),
        ]))->save();
    }

    public function cancel(?string $reason = null): static
    {
        if (! in_array($this->status, [
            PurchaseOrderStatus::DRAFT->value,
            PurchaseOrderStatus::SUBMITTED->value,
            PurchaseOrderStatus::APPROVED->value,
        ], true)) {
            throw ValidationException::withMessages(['status' => 'This purchase order cannot be cancelled in its current state.']);
        }

        if ($this->receipts()->exists()) {
            throw ValidationException::withMessages(['status' => 'A purchase order with received goods cannot be cancelled.']);
        }

        return tap($this->forceFill([
            'status' => PurchaseOrderStatus::CANCELLED->value,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]))->save();
    }

    public function receive(array $lines, mixed $receivedAt = null, ?string $reference = null): PurchaseOrderReceipt
    {
        if (! in_array($this->status, [
            PurchaseOrderStatus::APPROVED->value,
            PurchaseOrderStatus::PARTIALLY_RECEIVED->value,
        ], true)) {
            throw ValidationException::withMessages(['status' => 'Only approved or partially received orders can receive goods.']);
        }

        return DB::transaction(function () use ($lines, $receivedAt, $reference) {
            $receipt = $this->receipts()->create([
                'reference' => $reference,
                'received_at' => $receivedAt ?? now(),
            ]);

            foreach ($lines as $line) {
                $item = $this->items()->lockForUpdate()->findOrFail($line['purchase_order_item_id']);
                $quantity = (float) $line['quantity'];
                if ($quantity <= 0) {
                    throw ValidationException::withMessages(['quantity' => 'Received quantity must be greater than zero.']);
                }

                $alreadyReceived = (float) $item->receiptItems()->sum('quantity');
                $remaining = (float) $item->quantity - $alreadyReceived;
                if ($quantity > $remaining + 0.000001) {
                    throw ValidationException::withMessages(['quantity' => 'Received quantity exceeds the remaining ordered quantity.']);
                }

                $receipt->items()->create([
                    'purchase_order_item_id' => $item->id,
                    'quantity' => number_format($quantity, 3, '.', ''),
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            $allReceived = $this->items()->get()->every(
                fn ($item) => (float) $item->receiptItems()->sum('quantity') >= (float) $item->quantity - 0.000001
            );

            $this->forceFill([
                'status' => $allReceived
                    ? PurchaseOrderStatus::COMPLETED->value
                    : PurchaseOrderStatus::PARTIALLY_RECEIVED->value,
            ])->save();

            return $receipt;
        });
    }

    protected function assertStatus(PurchaseOrderStatus $expected): void
    {
        if ($this->status !== $expected->value) {
            throw ValidationException::withMessages(['status' => "Purchase order must be {$expected->value} to perform this action."]);
        }
    }
}
