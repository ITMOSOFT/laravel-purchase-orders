<?php

return [
    'models' => [
        'purchase_order' => ItmoSoft\PurchaseOrders\Models\PurchaseOrder::class,
        'item' => ItmoSoft\PurchaseOrders\Models\PurchaseOrderItem::class,
        'receipt' => ItmoSoft\PurchaseOrders\Models\PurchaseOrderReceipt::class,
        'receipt_item' => ItmoSoft\PurchaseOrders\Models\PurchaseOrderReceiptItem::class,
    ],
    'tables' => [
        'purchase_orders' => 'purchase_orders',
        'items' => 'purchase_order_items',
        'receipts' => 'purchase_order_receipts',
        'receipt_items' => 'purchase_order_receipt_items',
    ],
];