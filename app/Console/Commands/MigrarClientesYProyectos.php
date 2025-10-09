<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class MigrarClientesYProyectos extends Command
{
    protected $signature = 'migrar:clientes-proyectos 
                            {--adminId=1 : ID de usuario fallback si no existe el client_id en users}
                            {--dry-run : Solo simula, no inserta}';

    protected $description = 'Migra tabla client -> users (conservando IDs y deduplicando emails) y project -> proyectos.';

    public function handle()
    {
        $dry = $this->option('dry-run');
        $adminId = (int) $this->option('adminId');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->info('===> Paso 1: Migrar CLIENT -> USERS');
        $this->migrarClientesAUsers($dry);

        $this->info('===> Paso 2: Ajustar AUTO_INCREMENT en users');
        $this->ajustarAutoIncrement('users', 'id', $dry);

        $this->info('===> Paso 3: Migrar PROJECT -> PROYECTOS');
        $this->migrarProjectsAProyectos($adminId, $dry);

        $this->info('===> Paso 4: Ajustar AUTO_INCREMENT en proyectos');
        $this->ajustarAutoIncrement('proyectos', 'id', $dry);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('✔ Migración finalizada ' . ($dry ? '(DRY-RUN)' : ''));
        return Command::SUCCESS;
    }

    /**
     * MIGRA client -> users
     * - Conserva id original (users.id = client.client_id)
     * - Deduplica por email: conserva el de menor client_id y omite el resto
     * - Hashea password si no parece bcrypt
     */
    protected function migrarClientesAUsers(bool $dry)
    {
        // Tomamos por cada email el registro con MENOR client_id
        $dedup = DB::table('client')
            ->select(DB::raw('MIN(client_id) as keep_id'), 'email')
            ->groupBy('email')
            ->get()
            ->keyBy('keep_id');

        // Índice rápido por email para detectar duplicados
        $emailsYaInsertados = [];

        $clientes = DB::table('client')->orderBy('client_id')->get();

        foreach ($clientes as $c) {
            // ¿Este registro es el "keeper" para su email?
            $isKeeper = $dedup->has($c->client_id);
            if (!$isKeeper) {
                $this->line(" - Omitiendo duplicado por email {$c->email} (client_id={$c->client_id})");
                continue;
            }

            // Email limpio
            $email = trim(strtolower($c->email));
            if (isset($emailsYaInsertados[$email])) {
                $this->line(" - Omitiendo duplicado (post-chequeo) {$email} (client_id={$c->client_id})");
                continue;
            }

            // Password: si ya es bcrypt ($2y$...) lo conservamos; si no, lo hasheamos
            $password = $c->password ?? '';
            if (!is_string($password)) {
                $password = '';
            }
            if (!preg_match('/^\$2y\$/', $password)) {
                // si password viene vacío, generamos uno aleatorio
                $plain = strlen($password) ? $password : Str::random(16);
                $password = bcrypt($plain);
            }

            // name (obligatorio en users)
            $name = trim($c->name ?: ($c->user ?: 'Usuario '.$c->client_id));

            // Construimos fila de destino
            $row = [
                'id'                => (int) $c->client_id, // conservar ID original
                'name'              => mb_substr($name, 0, 255),
                'email'             => mb_substr($email, 0, 255),
                'email_verified_at' => null,
                'password'          => $password,
                'remember_token'    => null,
                'config'            => null,
                'user_can_sel_preproyectos' => null,
                'subordinados'      => null,
                'empresa_id'        => null,
                'sucursal_id'       => null,
                'created_at'        => now(),
                'updated_at'        => now(),
                'role_id'           => null,
            ];

            // Evitar choque si ya existe un users.id igual
            $existe = DB::table('users')->where('id', $row['id'])->exists();
            if ($existe) {
                $this->warn(" - Ya existe users.id={$row['id']} — se omite (email={$row['email']})");
                continue;
            }

            $emailsYaInsertados[$email] = true;

            if ($dry) {
                $this->line(" + [DRY] Insertaría users.id={$row['id']} email={$row['email']}");
            } else {
                DB::table('users')->insert($row);
                $this->line(" + Insertado users.id={$row['id']} email={$row['email']}");
            }
        }
    }

    /**
     * MIGRA project -> proyectos
     * - usuario_id = project.client_id (si no existe, usa --adminId)
     * - nombre = title (255)
     * - descripcion = description
     * - estado mapeado desde (aprobado, status)
     * - fechas: se intenta parsear entrega/timestamp_start/timestamp_end
     * - tipo = 'PROYECTO' por defecto
     * - JSONs y banderas: por defecto NULL/1
     */
protected function migrarProjectsAProyectos(int $fallbackUserId, bool $dry)
{
    $projects = DB::table('project')->orderBy('project_id')->get();

    foreach ($projects as $p) {
        // usuario_id: si no existe ese user, usa fallback
        $usuarioId = (int) $p->client_id;
        $userExiste = DB::table('users')->where('id', $usuarioId)->exists();
        if (!$userExiste) {
            $this->warn(" - project_id={$p->project_id} sin users.id={$usuarioId}, usando fallback={$fallbackUserId}");
            $usuarioId = $fallbackUserId;
        }

        // nombre
        $nombre = trim((string) $p->title);
        if ($nombre === '') {
            $nombre = 'Proyecto '.$p->project_id;
        }
        $nombre = mb_substr($nombre, 0, 255);

        // descripcion
        $descripcion = $p->description ?: null;

        // estado
        $estado = $this->mapEstado($p);

        // fechas
        [$fechaCreacionDT, $fechaProduccion, $fechaEmbarque, $fechaEntregaDT] = $this->mapFechas($p);
        $minTS = Carbon::create(1970, 1, 1, 0, 0, 1, 'UTC');

        $fechaCreacion = null;
        if ($fechaCreacionDT && $fechaCreacionDT->greaterThanOrEqualTo($minTS)) {
            $fechaCreacion = $fechaCreacionDT->toDateTimeString(); // YYYY-MM-DD HH:MM:SS
        }
        $fechaEntrega = $fechaEntregaDT ? $fechaEntregaDT->toDateString() : null;

        $row = [
            'id'                  => (int) $p->project_id,
            'usuario_id'          => $usuarioId,
            'direccion_fiscal_id' => null,
            'direccion_fiscal'    => null,
            'direccion_entrega_id'=> null,
            'direccion_entrega'   => null,
            'nombre'              => $nombre,
            'descripcion'         => $descripcion,
            'id_tipo_envio'       => null,
            'tipo'                => 'PROYECTO',
            'numero_muestras'     => 0,
            'estado'              => $estado,
            // 'fecha_creacion'    => (opcional si existe en tu esquema de proyectos),
            'fecha_produccion'    => null,
            'fecha_embarque'      => null,
            'fecha_entrega'       => $fechaEntrega,
            'categoria_sel'       => null,
            'flag_armado'         => 1,
            'producto_sel'        => null,
            'caracteristicas_sel' => null,
            'opciones_sel'        => null,
            'total_piezas_sel'    => null,
            'created_at'          => now(),
            'updated_at'          => now(),
        ];

        // Evitar choque si ya existe proyectos.id
        $existe = DB::table('proyectos')->where('id', $row['id'])->exists();
        if ($existe) {
            $this->warn(" - Ya existe proyectos.id={$row['id']} — se omite");
            continue;
        }

        // INSERT PROYECTO
        if ($dry) {
            $this->line(" + [DRY] Insertaría proyectos.id={$row['id']} usuario_id={$row['usuario_id']} estado={$row['estado']}");
        } else {
            DB::table('proyectos')->insert($row);
            $this->line(" + Insertado proyectos.id={$row['id']} usuario_id={$row['usuario_id']} estado={$row['estado']}");
        }

        // ====== NUEVO: Crear CHAT por proyecto (dentro del loop) ======
        $chatFecha = $fechaCreacion ?? now()->toDateTimeString();
        if ($dry) {
            $this->line("   [DRY] Crearía chat para proyecto_id={$row['id']} fecha_creacion={$chatFecha}");
        } else {
            DB::table('chats')->insert([
                'proyecto_id'    => $row['id'],
                'fecha_creacion' => $chatFecha,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            $this->line("   + Chat creado para proyecto_id={$row['id']}");
        }

        // ====== NUEVO: Crear PEDIDO por proyecto (producto 15, estado_id 9) ======
        // Producto: (15, 9, 'Reconfigurar', 1, 1, '2025-10-09 18:14:15', '2025-10-09 18:14:15', 1)
        // Estado:   (9, 'RECONFIGURAR', 'reconfigurar', 90, 'bg-purple-600 text-white', 1, NULL, NULL)
        $pedidoRow = [
            'proyecto_id'           => $row['id'],
            'producto_id'           => 15,
            'user_id'               => $row['usuario_id'],
            'cliente_id'            => $row['usuario_id'], // si tu cliente es el mismo usuario creador
            'fecha_creacion'        => now(),
            'total'                 => 0,
            'total_minutos'         => null,
            'total_pasos'           => null,
            'resumen_tiempos'       => null,
            'estatus'               => 'PENDIENTE',
            'direccion_fiscal_id'   => null,
            'direccion_fiscal'      => null,
            'direccion_entrega_id'  => null,
            'direccion_entrega'     => null,
            'tipo'                  => 'PEDIDO',
            'estatus_entrega_muestra'=> null,
            'estatus_muestra'       => null,
            'estado'                => 'RECONFIGURAR',
            'estado_id'             => 9,
            'estado_produccion'     => 'POR APROBAR',
            'fecha_produccion'      => null,
            'fecha_embarque'        => null,
            'fecha_entrega'         => null,
            'id_tipo_envio'         => null,
            'descripcion_pedido'    => 'Pedido generado automáticamente al migrar proyecto.',
            'instrucciones_muestra' => null,
            'flag_facturacion'      => 0,
            'url'                   => null,
            'last_uploaded_file_id' => null,
            'flag_aprobar_sin_fechas'          => 0,
            'flag_solicitud_aprobar_sin_fechas'=> 0,
            'created_at'            => now(),
            'updated_at'            => now(),
        ];

        if ($dry) {
            $this->line("   [DRY] Crearía pedido para proyecto_id={$row['id']} producto_id=15 estado_id=9 (RECONFIGURAR)");
        } else {
            // ---- Validaciones defensivas (no truenan si falta la tabla) ----
            // Productos
            if (Schema::hasTable('productos')) {
                $productoOk = DB::table('productos')->where('id', 15)->exists();
                if (!$productoOk) {
                    $this->warn("   ! Producto id=15 no existe en 'productos'; el insert de pedido seguirá si tus FKs lo permiten.");
                }
            } else {
                $this->warn("   ! Tabla 'productos' no encontrada; continúo sin validar existencia de producto.");
            }

            // Estados de pedido (acepta 'estados_pedido' ó 'estado_pedidos')
            $estadoTable = null;
            if (Schema::hasTable('estados_pedido')) {
                $estadoTable = 'estados_pedido';
            } elseif (Schema::hasTable('estado_pedidos')) {
                $estadoTable = 'estado_pedidos';
            }

            if ($estadoTable) {
                $estadoOk = DB::table($estadoTable)->where('id', 9)->exists();
                if (!$estadoOk) {
                    $this->warn("   ! Estado id=9 no existe en '{$estadoTable}'; verifica tu seed de estados. Se continúa.");
                }
            } else {
                $this->warn("   ! Catálogo de estados no encontrado (ni 'estados_pedido' ni 'estado_pedidos'); se continúa sin validar.");
            }

            // ---- Insert del pedido ----
            DB::table('pedido')->insert($pedidoRow);
            $this->line("   + Pedido creado (proyecto_id={$row['id']}, producto_id=15, estado=RECONFIGURAR)");
        }
    }
}




    protected function ajustarAutoIncrement(string $table, string $pk, bool $dry)
    {
        $max = DB::table($table)->max($pk);
        if (!$max) return;

        $sql = "ALTER TABLE `{$table}` AUTO_INCREMENT = ".((int)$max + 1);
        if ($dry) {
            $this->line(" [DRY] {$sql}");
        } else {
            DB::statement($sql);
        }
    }

    /**
     * Mapea estado destino (ENUM) desde columnas legacy
     */
    protected function mapEstado($p): string
    {
        // 1) Si está aprobado explícitamente
        if ((int) ($p->aprobado ?? 0) === 1) {
            return 'DISEÑO APROBADO';
        }

        // 2) Map desde "status" numérico (ajusta estos valores según tu legado)
        $status = is_null($p->status) ? null : (int)$p->status;
        switch ($status) {
            case 1:  return 'ASIGNADO';
            case 2:  return 'EN PROCESO';
            case 3:  return 'REVISION';
            case 4:  return 'DISEÑO RECHAZADO';
            case 5:  return 'CANCELADO';
            case 0:
            default: return 'PENDIENTE';
        }
    }

    /**
     * Intenta parsear fechas desde legacy (LONGTEXT variados)
     */
protected function mapFechas($p): array
{
    $parseDT = function ($val) {
        if (!$val || !is_string($val)) return null;
        $s = trim(str_replace(['.', '/'], '-', $val));

        // Si es un timestamp "0" u otro dígito
        if (ctype_digit($s)) {
            try {
                // FORZAR UTC para no caer en 1969-12-31 por TZ
                return Carbon::createFromTimestamp((int)$s, 'UTC');
            } catch (\Throwable $e) {}
        }

        try {
            // Parse flexible en UTC
            return Carbon::parse($s, 'UTC');
        } catch (\Throwable $e) {}

        return null;
    };

    $cre = $parseDT($p->timestamp_start ?? null); // datetime
    $ent = $parseDT($p->entrega ?? null);         // datetime o date

    // Sólo retorno Carbon o null; el formateo se hace afuera
    return [$cre, null, null, $ent];
}

}