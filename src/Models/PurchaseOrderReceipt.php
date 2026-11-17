<?php

namespace ItmoSoft\PurchaseOrders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderReceipt extends Model
{
    protected $guarded = [];

    protected $casts = ['received_at' => 'datetime'];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(config('purchase-orders.models.purchase_order'));
    }

    public function items(): HasMany
    {
        return $this->hasMany(config('purchase-orders.models.receipt_item'));
    }
}
