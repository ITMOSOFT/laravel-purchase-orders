<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config('purchase-orders.tables.receipt_items', 'purchase_order_receipt_items'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_receipt_id')->constrained(config('purchase-orders.tables.receipts', 'purchase_order_receipts'))->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->constrained(config('purchase-orders.tables.items', 'purchase_order_items'))->restrictOnDelete();
            $table->decimal('quantity', 14, 3);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('purchase_order_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('purchase-orders.tables.receipt_items', 'purchase_order_receipt_items'));
    }
};
