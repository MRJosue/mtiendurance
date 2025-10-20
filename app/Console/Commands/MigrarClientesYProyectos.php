<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

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

    // Limpia cache de Spatie (por si acaso)
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    // Debe existir ya el rol cliente_principal
    $clienteRole = Role::where('name', 'cliente_principal')->first();
    if (!$clienteRole) {
        $this->error("El rol 'cliente_principal' no existe. Créalo antes de correr este comando.");
        return Command::FAILURE;
    }

    // (Opcional) asegura guard_name esperado
    if ($clienteRole->guard_name !== 'web') {
        $this->warn("El rol 'cliente_principal' tiene guard_name='{$clienteRole->guard_name}'. Se recomienda 'web'.");
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    $this->info('===> Paso 1: Migrar CLIENT -> USERS');
    $this->migrarClientesAUsers($dry, $clienteRole);   // <— pásalo

    $this->info('===> Paso 2: Ajustar AUTO_INCREMENT en users');
    $this->ajustarAutoIncrement('users', 'id', $dry);

    // Backfill: asegura el rol a TODOS los usuarios (por si ya había users)
    // $this->info('===> Paso 2.1: Asignar rol cliente_principal a TODOS los users');
    // $this->asegurarRolClienteATodos($dry, $clienteRole);

    $this->info('===> Paso 3: Migrar PROJECT -> PROYECTOS');
    $this->migrarProjectsAProyectos($adminId, $dry);

    $this->info('===> Paso 4: Ajustar AUTO_INCREMENT en proyectos');
    $this->ajustarAutoIncrement('proyectos', 'id', $dry);

    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    // Limpia cache nuevamente
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->info('✔ Migración finalizada ' . ($dry ? '(DRY-RUN)' : ''));
    return Command::SUCCESS;
}

    /**
     * MIGRA client -> users
     * - Conserva id original (users.id = client.client_id)
     * - Deduplica por email: conserva el de menor client_id y omite el resto
     * - Hashea password si no parece bcrypt
     */
    protected function migrarClientesAUsers(bool $dry, Role  $rolCliente)
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
                $this->asignarRolSpatiePorId((int)$row['id'], $rolCliente, $dry);
                continue;
            }

            $emailsYaInsertados[$email] = true;

            if ($dry) {
                $this->line(" + [DRY] Insertaría users.id={$row['id']} email={$row['email']}");
            } else {
                DB::table('users')->insert($row);
                $this->asignarRolSpatiePorId((int)$row['id'], $rolCliente, $dry);

                $this->line(" + Insertado users.id={$row['id']} email={$row['email']}");
            }
        }


    }

    protected function asignarRolSpatiePorId(int $userId, Role $role, bool $dry): void
    {
        $pivot = [
            'role_id'    => $role->id,
            'model_type' => User::class,
            'model_id'   => $userId,
        ];

        $yaTiene = DB::table('model_has_roles')->where($pivot)->exists();

        if ($yaTiene) {
            $this->line("   = User {$userId} ya tiene rol '{$role->name}'");
            return;
        }

        if ($dry) {
            $this->line("   + [DRY] Asignaría rol '{$role->name}' a user {$userId}");
            return;
        }

        DB::table('model_has_roles')->insert($pivot);
        $this->line("   + Rol '{$role->name}' asignado a user {$userId}");
    }



    protected function migrarProjectsAProyectos(int $fallbackUserId, bool $dry)
    {
        // SOLO projects 53000..53200, aprobados=3, sin "/ COMPLEMENTO" (variantes con espacios)
        $projects = DB::table('project')
            ->whereBetween('project_id', [53000, 53200])
            ->where('aprobado', '=', 3)
            ->whereRaw("LOWER(title) NOT REGEXP '\\\\/[[:space:]]*complemento'")
            ->orderBy('project_id')
            ->get();

        foreach ($projects as $p) {
            // Usuario dueño del proyecto (o fallback si no existe)
            $usuarioId = (int) $p->client_id;
            $userExiste = DB::table('users')->where('id', $usuarioId)->exists();
            if (!$userExiste) {
                $this->warn(" - project_id={$p->project_id} sin users.id={$usuarioId}, usando fallback={$fallbackUserId}");
                $usuarioId = $fallbackUserId;
            }

            // Nombre/Descripción
            $nombre = trim((string) $p->title);
            if ($nombre === '') $nombre = 'Proyecto ' . $p->project_id;
            $nombre = mb_substr($nombre, 0, 255);

            $descripcion = ($p->description ? trim((string)$p->description).' ' : '') . 'Proyecto Legacy';

            // Fechas
            [$fechaCreacionDT, $fechaProduccion, $fechaEmbarque, $fechaEntregaDT] = $this->mapFechas($p);
            $minTS = Carbon::create(1970, 1, 1, 0, 0, 1, 'UTC');

            $fechaCreacion = null;
            if ($fechaCreacionDT && $fechaCreacionDT->greaterThanOrEqualTo($minTS)) {
                $fechaCreacion = $fechaCreacionDT->toDateTimeString();
            }
            $fechaEntrega = $fechaEntregaDT ? $fechaEntregaDT->toDateString() : null;

            // Estado (usa tu mapper)
            $estado = $this->mapEstado($p);

            // Armar fila proyecto
            $row = [
                'id'                    => (int) $p->project_id,
                'usuario_id'            => $usuarioId,
                'direccion_fiscal_id'   => null,
                'direccion_fiscal'      => null,
                'direccion_entrega_id'  => null,
                'direccion_entrega'     => null,
                'nombre'                => $nombre,
                'descripcion'           => $descripcion,
                'id_tipo_envio'         => null,
                'tipo'                  => 'PROYECTO',
                'numero_muestras'       => 0,
                'estado'                => 'DiSEÑO APROBADO',
                'fecha_produccion'      => null,
                'fecha_embarque'        => null,
                'fecha_entrega'         => $fechaEntrega,
                'categoria_sel'         => null,
                'flag_armado'           => 1,
                'producto_sel'          => null,
                'caracteristicas_sel'   => null,
                'opciones_sel'          => null,
                'total_piezas_sel'      => null,
                'flag_reconfigurar'     => 1,
                'activo'                => 1,
                'created_at'            => now(),
                'updated_at'            => now(),
            ];

            // Evitar choque si ya existe proyectos.id
            $existe = DB::table('proyectos')->where('id', $row['id'])->exists();
            if ($existe) {
                $this->warn(" - Ya existe proyectos.id={$row['id']} — se omite");
                continue;
            }

            if ($dry) {
                $this->line(" + [DRY] Insertaría proyecto {$row['id']} y su chat + mensaje inicial");
                continue;
            }

            // Transacción por proyecto: proyecto + chat + primer mensaje
            DB::transaction(function () use ($row, $usuarioId, $fechaCreacion) {
                // 1) Proyecto
                DB::table('proyectos')->insert($row);
                $this->line(" + Insertado proyectos.id={$row['id']} usuario_id={$row['usuario_id']} estado={$row['estado']}");

                // 2) Chat
                $chatFecha = $fechaCreacion ?? now()->toDateTimeString();
                $chatId = DB::table('chats')->insertGetId([
                    'proyecto_id'    => $row['id'],
                    'fecha_creacion' => $chatFecha,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
                $this->line("   + Chat creado (id={$chatId}) para proyecto_id={$row['id']}");

                // 3) Mensaje inicial (tabla y columnas definidas por tu modelo MensajeChat)
                DB::table('mensajes_chat')->insert([
                    'chat_id'    => $chatId,
                    'usuario_id' =>  9002,                
                    'tipo'       => 2,                
                    'mensaje'    => "Chat creado automáticamente durante la migración. \n El Proyecto #{$row['id']} — {$row['nombre']} Requiere ser reconfigurado para crear pedidos.",
                    'fecha_envio'=> now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->line("   + Mensaje inicial insertado en mensajes_chat (chat_id={$chatId})");
            });
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