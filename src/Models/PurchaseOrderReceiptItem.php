<?php

namespace ItmoSoft\PurchaseOrders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderReceiptItem extends Model
{
    protected $guarded = [];

    protected $casts = ['quantity' => 'decimal:3'];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(config('purchase-orders.models.receipt'));
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(config('purchase-orders.models.item'));
    }
}
