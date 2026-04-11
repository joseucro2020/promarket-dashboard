<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasBulkUnit = Schema::hasColumn('products', 'bulk_unit');
        $hasBulkMinSale = Schema::hasColumn('products', 'bulk_min_sale');
        $hasBulkStep = Schema::hasColumn('products', 'bulk_step');
        $hasBulkStock = Schema::hasColumn('products', 'bulk_stock');

        if ($hasBulkUnit && $hasBulkMinSale && $hasBulkStep && $hasBulkStock) {
            return;
        }

        Schema::table('products', function (Blueprint $table) use ($hasBulkUnit, $hasBulkMinSale, $hasBulkStep, $hasBulkStock) {
            if (!$hasBulkUnit) {
                $table->string('bulk_unit')->nullable()->after('variable');
            }

            if (!$hasBulkMinSale) {
                $table->decimal('bulk_min_sale', 10, 3)->default(1.000)->after('bulk_unit');
            }

            if (!$hasBulkStep) {
                $table->decimal('bulk_step', 10, 3)->default(0.100)->after('bulk_min_sale');
            }

            if (!$hasBulkStock) {
                $table->decimal('bulk_stock', 12, 3)->default(0)->after('bulk_step');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'bulk_stock')) {
                $table->dropColumn('bulk_stock');
            }
            if (Schema::hasColumn('products', 'bulk_step')) {
                $table->dropColumn('bulk_step');
            }
            if (Schema::hasColumn('products', 'bulk_min_sale')) {
                $table->dropColumn('bulk_min_sale');
            }
            if (Schema::hasColumn('products', 'bulk_unit')) {
                $table->dropColumn('bulk_unit');
            }
        });
    }
};
