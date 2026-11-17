<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config('purchase-orders.tables.receipts', 'purchase_order_receipts'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->string('reference')->nullable();
            $table->timestamp('received_at');
            $table->timestamps();
            $table->index(['purchase_order_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('purchase-orders.tables.receipts', 'purchase_order_receipts'));
    }
};
