<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            // 0 = no usa tallas, 1 = usa tallas para calcular total
            $table->boolean('flag_tallas')
                ->default(false)
                ->after('total'); // ponlo donde te acomode
        });
    }

    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn('flag_tallas');
        });
    }
};
