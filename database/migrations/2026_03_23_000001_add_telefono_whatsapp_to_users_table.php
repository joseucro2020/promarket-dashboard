<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTelefonoWhatsappToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'telefono_whatsapp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('telefono_whatsapp', 20)->nullable()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'telefono_whatsapp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['telefono_whatsapp']);
                $table->dropColumn('telefono_whatsapp');
            });
        }
    }
}
