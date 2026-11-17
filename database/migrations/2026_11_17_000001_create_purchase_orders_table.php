<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config('purchase-orders.tables.purchase_orders', 'purchase_orders'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained(config('itmosoft-suppliers.suppliers_table', 'suppliers'))->restrictOnDelete();
            $table->string('number')->unique();
            $table->string('supplier_reference')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->char('currency', 3)->default('USD');
            $table->date('expected_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['supplier_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('purchase-orders.tables.purchase_orders', 'purchase_orders'));
    }
};
