<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exchange_rates', function (Blueprint $table) {
            if (!Schema::hasColumn('exchange_rates', 'currency_from')) {
                $table->string('currency_from', 10)->default('USD')->after('id');
            }
            if (!Schema::hasColumn('exchange_rates', 'currency_to')) {
                $table->string('currency_to', 10)->default('BS')->after('currency_from');
            }
            if (!Schema::hasColumn('exchange_rates', 'notes')) {
                $table->text('notes')->nullable()->after('change');
            }
        });
    }

    public function down()
    {
        Schema::table('exchange_rates', function (Blueprint $table) {
            $table->dropColumn(['currency_from', 'currency_to', 'notes']);
        });
    }
};
