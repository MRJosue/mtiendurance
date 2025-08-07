    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('ordenes_produccion', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('create_user');
                $table->foreign('create_user')->references('id')->on('users')->onDelete('cascade');

                // Usuario asignado
                $table->unsignedBigInteger('assigned_user_id')->nullable();
                $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('set null');

                $table->enum('tipo', ['CORTE','SUBLIMADO','COSTURA','MAQUILA','FACTURACION','ENVIO','OTRO','RECHAZADO'])->default('CORTE');
                $table->enum('estado', ['SIN INICIAR','EN PROCESO','TERMINADO','CANCELADO'])->default('SIN INICIAR');

                $table->timestamp('fecha_sin_iniciar')->nullable();
                $table->timestamp('fecha_en_proceso')->nullable();
                $table->timestamp('fecha_terminado')->nullable();
                $table->timestamp('fecha_cancelado')->nullable();

                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('ordenes_produccion');
        }
    };
