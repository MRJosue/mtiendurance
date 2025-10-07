<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project', function (Blueprint $table) {
            // Motor y collation como en el esquema antiguo
            $table->engine = 'MyISAM';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->charset = 'utf8mb4';

            $table->increments('project_id');

            $table->integer('status')->nullable()->default(0);

            // NOTA: MySQL no permite DEFAULT en LONGTEXT (omitido o comentado)
            $table->longText('title')->nullable();
            $table->longText('description')->nullable();
            $table->longText('demo_url')->nullable();    // en original default '0'
            $table->longText('currency')->nullable();

            $table->integer('project_category_id')->nullable()->default(0);
            $table->integer('client_id')->nullable()->default(0);
            $table->integer('subclient_id')->nullable()->default(0);

            $table->longText('staffs')->nullable();
            $table->longText('budget')->nullable();
            $table->integer('timer_status')->nullable()->default(0);
            $table->longText('timer_starting_timestamp')->nullable(); // default '0'
            $table->integer('total_time_spent')->nullable()->default(0);
            $table->longText('progress_status')->nullable(); // default '0'
            $table->longText('timestamp_start')->nullable(); // default ''
            $table->longText('timestamp_end')->nullable();   // default ''

            $table->longText('category')->nullable();
            $table->longText('cantidad')->nullable();
            $table->longText('entrega')->nullable(); // default '0'

            $table->integer('produccion')->nullable()->default(0);
            $table->longText('precio')->nullable(); // default '0'

            $table->longText('adicionales')->nullable();
            $table->longText('listones')->nullable();
            $table->longText('playeras')->nullable();

            $table->integer('aprobado')->nullable()->default(0);
            $table->longText('aprobado_time')->nullable(); // default '0'

            $table->longText('a_solicitud')->nullable();
            $table->integer('a_horas')->nullable()->default(0);
            $table->integer('client_address_id')->nullable()->default(0);

            $table->longText('archivo')->nullable(); // default '0'

            $table->integer('hidde')->nullable()->default(0);
            $table->integer('factura')->nullable()->default(0);
            $table->integer('ajuste')->nullable()->default(0);
            $table->integer('cscontrol')->nullable()->default(0);

            // No timestamps en el esquema original
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project');
    }
};
