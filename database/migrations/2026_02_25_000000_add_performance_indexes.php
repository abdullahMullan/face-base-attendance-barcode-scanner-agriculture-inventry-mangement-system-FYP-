<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sales table indexes
        Schema::table('sales', function (Blueprint $table) {
            $table->index('customer_id');
            $table->index('user_id');
            $table->index('payment_type');
            $table->index('sale_date');
            $table->index(['created_at', 'id']); // For recent sales queries
        });

        // Sale items table indexes
        Schema::table('sale_items', function (Blueprint $table) {
            $table->index('sale_id');
            $table->index('product_id');
        });

        // Purchases table indexes
        Schema::table('purchases', function (Blueprint $table) {
            $table->index('supplier_id');
            $table->index('user_id');
            $table->index(['created_at', 'id']);
        });

        // Purchase items table indexes
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->index('purchase_id');
            $table->index('product_id');
        });

        // Products table indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('is_active');
            $table->index(['stock_quantity', 'low_stock_threshold']); // For low stock queries
        });

        // Customers table indexes
        Schema::table('customers', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('name');
        });

        // Suppliers table indexes
        Schema::table('suppliers', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('name');
        });

        // Categories table indexes
        Schema::table('categories', function (Blueprint $table) {
            $table->index('name');
        });

        // Credit payments table indexes
        Schema::table('credit_payments', function (Blueprint $table) {
            $table->index('customer_id');
            $table->index('sale_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Sales table
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['payment_type']);
            $table->dropIndex(['sale_date']);
            $table->dropIndex(['created_at', 'id']);
        });

        // Sale items
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['sale_id']);
            $table->dropIndex(['product_id']);
        });

        // Purchases
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['supplier_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at', 'id']);
        });

        // Purchase items
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropIndex(['purchase_id']);
            $table->dropIndex(['product_id']);
        });

        // Products
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['stock_quantity', 'low_stock_threshold']);
        });

        // Customers
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['name']);
        });

        // Suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['name']);
        });

        // Categories
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        // Credit payments
        Schema::table('credit_payments', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['sale_id']);
            $table->dropIndex(['payment_date']);
        });
    }
};
