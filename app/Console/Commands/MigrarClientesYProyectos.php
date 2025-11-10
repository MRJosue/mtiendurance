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
use Illuminate\Support\Facades\File;


class MigrarClientesYProyectos extends Command
{
    protected $signature = 'migrar:clientes-proyectos 
                            {--adminId=1 : ID de usuario fallback si no existe el client_id en users}
                            {--dry-run : Solo simula, no inserta}';

    protected $description = 'Migra tabla client -> users (conservando IDs y deduplicando emails) y project -> proyectos.';

    protected string $logFilePath = '';

    public function handle()
    {

        // Reiniciar el log
        $this->initMigrationLog(true);

        $dry = $this->option('dry-run');
        $adminId = (int) $this->option('adminId');

        // Limpia cache de Spatie (por si acaso)
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // === Roles requeridos ===
        $rolPrincipal   = Role::where('name', 'cliente_principal')->first();
        $rolSubordinado = Role::where('name', 'cliente_subordinado')->first();

          $rolStaff = Role::where('name', 'staff')->first();


        if (!$rolPrincipal || !$rolSubordinado) {
            $faltan = [];
            if (!$rolPrincipal)   $faltan[] = 'cliente_principal';
            if (!$rolSubordinado) $faltan[] = 'cliente_subordinado';
            $this->error("Faltan roles: ".implode(', ', $faltan).". Créelos antes de correr este comando.");
            return Command::FAILURE;
        }

        foreach ([$rolPrincipal, $rolSubordinado] as $r) {
            if ($r->guard_name !== 'web') {
                $this->warn("El rol '{$r->name}' tiene guard_name='{$r->guard_name}'. Se recomienda 'web'.");
            }
        }

        if (!$rolStaff) {
            $this->warn("El rol 'staff' no existe. Migraré staff sin asignar rol (puedes crearlo y correr de nuevo si quieres asignarlo).");
        }



        // Debe existir ya el rol cliente_principal
        $clienteRole = Role::where('name', 'cliente_principal')->first();
        if (!$clienteRole) {
            $this->error("El rol 'cliente_principal' no existe. Créalo antes de correr este comando.");
            return Command::FAILURE;
        }

        // Asegura que existan los campos legacy en users
        if (!$this->ensureLegacyColumns()) {
            return Command::FAILURE;
        }
        
        // Limpia cache de Spatie (por si acaso)
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // (Opcional) asegura guard_name esperado
        if ($clienteRole->guard_name !== 'web') {
            $this->warn("El rol 'cliente_principal' tiene guard_name='{$clienteRole->guard_name}'. Se recomienda 'web'.");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->info('===> Paso 1: Migrar CLIENT -> USERS');
        $this->migrarClientesAUsers($dry, $rolPrincipal, $rolSubordinado);

                // 👇 NUEVOS PASOS
        $this->info('===> Paso 1.1: Migrar CLIENTSUP -> USERS (espacio de IDs 700000 + clientsup_id)');
        $this->migrarClientsUpAUsers($dry, $rolPrincipal, $rolSubordinado);

        $this->info('===> Paso 1.2: Migrar STAFF -> USERS (espacio de IDs 800000 + staff_id)');
        $this->migrarStaffAUsers($dry, $rolStaff);

        $this->info('===> Paso 2: Ajustar AUTO_INCREMENT en users');
        $this->ajustarAutoIncrement('users', 'id', $dry);


        /** NUEVO PASO 2.2 **/
        $this->info('===> Paso 2.2: Construir subordinados y sincronizar roles por jerarquía');
        $this->backfillSubordinadosYRoles($rolPrincipal, $rolSubordinado, $dry);

        $this->info('===> Paso 2.3: Crear Empresas/Sucursales y relacionar usuarios');
        $this->backfillEmpresasYSucursales($rolPrincipal, $rolSubordinado, $dry);

        

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


    protected function migrarClientesAUsers(bool $dry, Role $rolPrincipal, Role $rolSubordinado)
    {
        // Por email, conservar el de menor client_id
        $dedup = DB::table('client')
            ->select(DB::raw('MIN(client_id) as keep_id'), 'email')
            ->groupBy('email')
            ->get()
            ->keyBy('keep_id');

        $emailsYaInsertados = [];
        $clientes = DB::table('client')->orderBy('client_id')->get();

        foreach ($clientes as $c) {
            $isKeeper = $dedup->has($c->client_id);
            if (!$isKeeper) {
                $this->line(" - Omitiendo duplicado por email {$c->email} (client_id={$c->client_id})");
                continue;
            }

            $email = trim(strtolower((string)$c->email));
            if ($email === '') {
                $this->warn(" - Omitido client_id={$c->client_id} por email vacío");
                continue;
            }
            if (isset($emailsYaInsertados[$email])) {
                $this->line(" - Omitiendo duplicado (post-chequeo) {$email} (client_id={$c->client_id})");
                continue;
            }

            // Password
            $password = $c->password ?? '';
            if (!is_string($password)) $password = '';
            if (!preg_match('/^\$2y\$/', $password)) {
                $plain = strlen($password) ? $password : Str::random(16);
                $password = bcrypt($plain);
            }

            // Nombre
            $name = trim((string)($c->name ?: $c->user ?: 'Usuario '.$c->client_id));
            $name = mb_substr($name, 0, 255);

            // Legacy
            $userLegacy     = $c->user ?? null;
            $companyLegacy  = $c->company ?? null;
            $superLegacy    = isset($c->super) ? (int)$c->super : null;
            $superIdLegacy  = isset($c->super_id) ? (int)$c->super_id : null;

            // Regla: super_id > 0 => cliente_principal; else => cliente_subordinado
            $rolDestino = ($superIdLegacy !== null && $superIdLegacy > 0) ? $rolPrincipal : $rolSubordinado;

            // Fila para INSERT (sin role_id)
            $row = [
                'id'                => (int) $c->client_id,
                'name'              => $name,
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

                // Legacy
                'user_legacy'       => $userLegacy,
                'company_legacy'    => $companyLegacy,
                'super_legacy'      => $superLegacy,
                'super_id_legacy'   => $superIdLegacy,
            ];

            $existe = DB::table('users')->where('id', $row['id'])->exists();

            if ($existe) {
                $this->warn(" - Ya existe users.id={$row['id']} — backfill legacy si aplica (email={$row['email']})");

                // Backfill NO destructivo solo de legacy
                $existente = DB::table('users')->where('id', $row['id'])
                    ->select('user_legacy','company_legacy','super_legacy','super_id_legacy')
                    ->first();

                $toUpdate = [];
                if (is_null($existente->user_legacy) && !is_null($row['user_legacy'])) {
                    $toUpdate['user_legacy'] = $row['user_legacy'];
                }
                if (is_null($existente->company_legacy) && !is_null($row['company_legacy'])) {
                    $toUpdate['company_legacy'] = $row['company_legacy'];
                }
                if (is_null($existente->super_legacy) && !is_null($row['super_legacy'])) {
                    $toUpdate['super_legacy'] = $row['super_legacy'];
                }
                if (is_null($existente->super_id_legacy) && !is_null($row['super_id_legacy'])) {
                    $toUpdate['super_id_legacy'] = $row['super_id_legacy'];
                }

                if (!empty($toUpdate)) {
                    $toUpdate['updated_at'] = now();
                    if ($dry) {
                        $this->line("   + [DRY] UPDATE users.id={$row['id']} set ".implode(', ', array_keys($toUpdate)));
                    } else {
                        DB::table('users')->where('id', $row['id'])->update($toUpdate);
                        $this->line("   + Legacy actualizado en users.id={$row['id']}");
                    }
                } else {
                    $this->line("   = Sin cambios de legacy (ya estaban poblados)");
                }

                // Solo Spatie (asegura el rol destino en model_has_roles)
                $this->asignarRolSpatiePorId((int)$row['id'], $rolDestino, $dry);
                continue;
            }

            $emailsYaInsertados[$email] = true;

            if ($dry) {
                $this->line(" + [DRY] Insertaría users.id={$row['id']} email={$row['email']} (legacy) y asignaría rol Spatie={$rolDestino->name}");
            } else {
                DB::table('users')->insert($row);
                $this->asignarRolSpatiePorId((int)$row['id'], $rolDestino, $dry);
                $this->line(" + Insertado users.id={$row['id']} email={$row['email']} (legacy) y rol Spatie={$rolDestino->name}");
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
            ->whereBetween('project_id', [33000, 53200])
            //->where('aprobado', '=', 3)
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
            // Nombre/Descripción
            $nombre = trim((string) $p->title);
            if ($nombre === '') $nombre = 'Proyecto ' . $p->project_id;
            $nombre = mb_substr($nombre, 0, 255);

            // NUEVO: descripción según reglas de categoría (y mantiene "Proyecto Legacy")
            $descripcion = $this->buildDescripcionProyecto($p);

            
         

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
                'estado'                => $estado,
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

            $archivoLegacy = (string) ($p->archivo ?? '');

            // Transacción por proyecto: proyecto + chat + primer mensaje
            DB::transaction(function () use ($row, $usuarioId, $fechaCreacion,  $archivoLegacy) {

                
                // 1) Proyecto
                DB::table('proyectos')->insert($row);
                $this->line(" + Insertado proyectos.id={$row['id']} usuario_id={$row['usuario_id']} estado={$row['estado']}");


                // 1.1) Archivo de diseño (usa $archivoLegacy)
                $this->insertArchivoDisenoDesdeLegacy(
                    proyectoId: (int)$row['id'],
                    usuarioId:  (int)$usuarioId,
                    nombreLegacy: $archivoLegacy

                );

                
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

        $aprobado = isset($p->aprobado) ? (int)$p->aprobado : 0;
        if ($aprobado === 0) {
            return 'PENDIENTE';
        }
        return 'DISEÑO APROBADO';
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

    protected function ensureLegacyColumns(): bool
    {
        $cols = ['user_legacy','company_legacy','super_legacy','super_id_legacy'];
        $missing = collect($cols)->filter(fn($c) => !Schema::hasColumn('users', $c))->values();

        if ($missing->isNotEmpty()) {
            $this->error('Faltan columnas en users: '. $missing->join(', ') .
                '. Corre la migración que agrega los campos legacy antes de ejecutar este comando.');
            return false;
        }
        return true;
    }


    protected function syncRolSpatieUnico(int $userId, Role $rolDestino, Role $rolContrario, bool $dry): void
    {
        $pivotDestino = [
            'role_id'    => $rolDestino->id,
            'model_type' => User::class,
            'model_id'   => $userId,
        ];
        $pivotContrario = [
            'role_id'    => $rolContrario->id,
            'model_type' => User::class,
            'model_id'   => $userId,
        ];

        if ($dry) {
            $this->line("   [DRY] sync rol -> user {$userId}: +{$rolDestino->name}, -{$rolContrario->name}");
            return;
        }

        // Remueve el rol contrario si existe
        DB::table('model_has_roles')->where($pivotContrario)->delete();

        // Asegura el destino
        $yaTiene = DB::table('model_has_roles')->where($pivotDestino)->exists();
        if (!$yaTiene) {
            DB::table('model_has_roles')->insert($pivotDestino);
        }
    }


    protected function backfillSubordinadosYRoles(Role $rolPrincipal, Role $rolSubordinado, bool $dry): void
    {
        // 1) Traer (id, super_id_legacy) de todos los users
        $users = DB::table('users')
            ->select('id', 'super_id_legacy')
            ->get();

        // 2) Agrupar hijos por super_id_legacy (solo > 0)
        $hijosPorPadre = $users
            ->filter(fn($u) => !is_null($u->super_id_legacy) && is_numeric($u->super_id_legacy) && (int)$u->super_id_legacy > 0)
            ->groupBy(fn($u) => (int)$u->super_id_legacy);

        if ($hijosPorPadre->isEmpty()) {
            $this->line('   = No hay jerarquías (nadie con super_id_legacy > 0).');
            return;
        }

        // 3) Para cada padre => actualizar subordinados JSON y rol principal
        foreach ($hijosPorPadre as $padreId => $hijos) {
            // Validar que el padre exista como user.id
            $existePadre = DB::table('users')->where('id', $padreId)->exists();
            if (!$existePadre) {
                $this->warn("   ! Padre no encontrado en users.id={$padreId}, se omite su asignación de subordinados.");
                continue;
            }

            $subIds = $hijos->pluck('id')->map(fn($v) => (int)$v)->values()->all();

            if ($dry) {
                $this->line("   [DRY] users.id={$padreId} subordinados=" . json_encode($subIds));
            } else {
                DB::table('users')->where('id', $padreId)->update([
                    'subordinados' => json_encode($subIds),
                    'updated_at'   => now(),
                ]);
            }

            // Sincronizar rol del padre: principal
            $this->syncRolSpatieUnico((int)$padreId, $rolPrincipal, $rolSubordinado, $dry);
        }

        // 4) Asegurar rol de cada hijo: subordinado
        $todosHijosIds = $hijosPorPadre->flatten(1)->pluck('id')->unique()->map(fn($v) => (int)$v)->values();

        foreach ($todosHijosIds as $hijoId) {
            // Si quieres dejar limpio 'subordinados' en los hijos, podría ponerse null (no requerido)
            $this->syncRolSpatieUnico((int)$hijoId, $rolSubordinado, $rolPrincipal, $dry);
        }

        $this->line('   + Jerarquía aplicada: padres con subordinados y roles sincronizados.');
    }


    protected function backfillEmpresasYSucursales(Role $rolPrincipal, Role $rolSubordinado, bool $dry): void
    {
        // Verificaciones rápidas
        if (!Schema::hasTable('empresas') || !Schema::hasTable('sucursales')) {
            $this->warn('   ! No existen tablas empresas/sucursales. Se omite Paso 2.3.');
            return;
        }
        $tienePivot = Schema::hasTable('sucursal_user');

        // 1) Obtener IDs de usuarios con rol cliente_principal (Spatie)
        $principalesIds = DB::table('model_has_roles')
            ->where('role_id', $rolPrincipal->id)
            ->where('model_type', User::class)
            ->pluck('model_id')
            ->map(fn($v) => (int)$v)
            ->values();

        if ($principalesIds->isEmpty()) {
            $this->line('   = No hay usuarios con rol cliente_principal.');
            return;
        }

        // 2) Traer datos de principales (id, name, company_legacy, subordinados)
        $principales = DB::table('users')
            ->select('id', 'name', 'company_legacy', 'subordinados', 'empresa_id', 'sucursal_id')
            ->whereIn('id', $principalesIds)
            ->get()
            ->keyBy('id');

        foreach ($principales as $principal) {
            $principalId = (int)$principal->id;

            // Determinar nombre de Empresa
            $empresaNombre = trim((string)($principal->company_legacy ?? ''));
            if ($empresaNombre === '') {
                $empresaNombre = trim((string)$principal->name);
                if ($empresaNombre === '') {
                    $empresaNombre = "Empresa de Usuario {$principalId}";
                }
            }

            // 3) Obtener/crear Empresa
            $empresaId = DB::table('empresas')->where('nombre', $empresaNombre)->value('id');
            if (!$empresaId) {
                if ($dry) {
                    $this->line("   [DRY] Crear empresa(nombre='{$empresaNombre}')");
                    // Simular ID (no se usa realmente, sólo para logs)
                    $empresaId = null;
                } else {
                    $empresaId = DB::table('empresas')->insertGetId([
                        'nombre'     => $empresaNombre,
                        'rfc'        => null,
                        'telefono'   => null,
                        'direccion'  => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->line("   + Empresa creada id={$empresaId} nombre='{$empresaNombre}'");
                }
            } else {
                $this->line("   = Empresa existente id={$empresaId} nombre='{$empresaNombre}'");
            }

            // 4) Obtener/crear Sucursal principal (nombre fijo "Matriz")
            $sucursalId = null;
            if (!$dry) {
                $sucursalId = DB::table('sucursales')
                    ->where('empresa_id', $empresaId)
                    ->where('nombre', 'Matriz')
                    ->value('id');
            }

            if (!$sucursalId) {
                if ($dry) {
                    $this->line("   [DRY] Crear sucursal(nombre='Matriz', empresa='{$empresaNombre}')");
                } else {
                    $sucursalId = DB::table('sucursales')->insertGetId([
                        'empresa_id' => $empresaId,
                        'nombre'     => 'Matriz',
                        'telefono'   => null,
                        'direccion'  => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->line("   + Sucursal creada id={$sucursalId} (Matriz) para empresa_id={$empresaId}");
                }
            } else {
                $this->line("   = Sucursal existente id={$sucursalId} (Matriz) empresa_id={$empresaId}");
            }

            // 5) Asignar empresa/sucursal al principal
            if ($dry) {
                $this->line("   [DRY] users.id={$principalId} -> empresa_id={$empresaId}, sucursal_id={$sucursalId}");
            } else {
                DB::table('users')->where('id', $principalId)->update([
                    'empresa_id'  => $empresaId,
                    'sucursal_id' => $sucursalId,
                    'updated_at'  => now(),
                ]);
            }

            // 6) Si tiene subordinados, asignarlos a la misma Sucursal/Empresa y crear pivote
            $subs = [];
            if ($principal->subordinados) {
                // subordinados es JSON; seguridad por si viene string inválido
                try {
                    $decoded = is_string($principal->subordinados)
                        ? json_decode($principal->subordinados, true)
                        : $principal->subordinados;
                    if (is_array($decoded)) {
                        $subs = array_values(array_unique(array_map('intval', $decoded)));
                    }
                } catch (\Throwable $e) {
                    // ignorar
                }
            }

            if (!empty($subs)) {
                $this->line("   - Vinculando subordinados de user {$principalId}: ".implode(',', $subs));

                // Filtrar a los que existan en users
                $subsExistentes = DB::table('users')->whereIn('id', $subs)->pluck('id')->map(fn($v)=>(int)$v)->values();

                foreach ($subsExistentes as $subId) {
                    if ($dry) {
                        $this->line("     [DRY] users.id={$subId} -> empresa_id={$empresaId}, sucursal_id={$sucursalId}");
                    } else {
                        DB::table('users')->where('id', $subId)->update([
                            'empresa_id'  => $empresaId,
                            'sucursal_id' => $sucursalId,
                            'updated_at'  => now(),
                        ]);
                    }

                    // Pivote sucursal_user (si existe la tabla)
                    if ($tienePivot && !$dry) {
                        $ya = DB::table('sucursal_user')
                            ->where('sucursal_id', $sucursalId)
                            ->where('user_id', $subId)
                            ->exists();

                        if (!$ya) {
                            DB::table('sucursal_user')->insert([
                                'sucursal_id' => $sucursalId,
                                'user_id'     => $subId,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                            $this->line("       + Pivot sucursal_user: sucursal={$sucursalId}, user={$subId}");
                        }
                    } elseif ($tienePivot && $dry) {
                        $this->line("       [DRY] Pivot sucursal_user: sucursal={$sucursalId}, user={$subId}");
                    }
                }

                // (Opcional) también puedes crear pivote para el principal
                if ($tienePivot) {
                    if ($dry) {
                        $this->line("     [DRY] Pivot sucursal_user para principal: sucursal={$sucursalId}, user={$principalId}");
                    } else {
                        $ya = DB::table('sucursal_user')
                            ->where('sucursal_id', $sucursalId)
                            ->where('user_id', $principalId)
                            ->exists();
                        if (!$ya) {
                            DB::table('sucursal_user')->insert([
                                'sucursal_id' => $sucursalId,
                                'user_id'     => $principalId,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                            $this->line("       + Pivot sucursal_user (principal): sucursal={$sucursalId}, user={$principalId}");
                        }
                    }
                }
            } else {
                // Sin subordinados: aún así dejamos al principal asignado a su empresa/sucursal
                if ($tienePivot) {
                    if ($dry) {
                        $this->line("   [DRY] Pivot sucursal_user para principal sin subordinados: sucursal={$sucursalId}, user={$principalId}");
                    } else {
                        $ya = DB::table('sucursal_user')
                            ->where('sucursal_id', $sucursalId)
                            ->where('user_id', $principalId)
                            ->exists();
                        if (!$ya) {
                            DB::table('sucursal_user')->insert([
                                'sucursal_id' => $sucursalId,
                                'user_id'     => $principalId,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                            $this->line("   + Pivot sucursal_user (principal sin subs): sucursal={$sucursalId}, user={$principalId}");
                        }
                    }
                }
            }
        }

        $this->line('   + Empresas/Sucursales y relaciones de usuarios completadas.');
    }


    protected function buildDescripcionProyecto($p): string
    {
        // Base: description legacy + sufijo informativo
        $base = trim((string)($p->description ?? ''));
        $desc = $base === '' ? 'Proyecto Legacy' : ($base . ' ' . 'Proyecto Legacy');

        // Determinar categoría
        $catId = isset($p->project_category_id) ? (int)$p->project_category_id : null;

        // Si está entre 3 y 19, no modificar
        if (!is_null($catId) && $catId >= 3 && $catId <= 19) {
            return $desc;
        }

        // Category = 1 -> agregar listones
        if ($catId === 1) {
            $extra = trim((string)($p->listones ?? ''));
            if ($extra !== '') {
                // Anexamos con etiqueta
                $desc .= ' | Listones: ' . mb_substr($extra, 0, 1000);
            }
            return $desc;
        }

        // Category = 2 -> agregar playeras
        if ($catId === 2) {
            $extra = trim((string)($p->playeras ?? ''));
            if ($extra !== '') {
                $desc .= ' | Playeras: ' . mb_substr($extra, 0, 1000);
            }
            return $desc;
        }

        // Otros valores: sin cambios
        return $desc;
    }
    /**
     * Inserta un registro en archivos_proyectos tomando el nombre legado del campo "archivo" (tabla project).
     * No mueve archivos físicos; solo registra el metadato.
     */
    protected function insertArchivoDisenoDesdeLegacy(int $proyectoId, int $usuarioId, string $nombreLegacy): void
    {
        $nombreLegacy = trim($nombreLegacy);
        if ($nombreLegacy === '') {
            // Nada que registrar
            return;
        }

        // Normaliza nombre básico (evita desbordes extremos)
        $nombre = mb_substr($nombreLegacy, 0, 255);

        // Si ya existe un archivo con ese nombre en el proyecto, agrega timestamp para hacerlo único.
        $yaExiste = DB::table('archivos_proyecto')
            ->where('proyecto_id', $proyectoId)
            ->where('nombre_archivo', $nombre)
            ->exists();

        if ($yaExiste) {
            $pi      = pathinfo($nombre);
            $base    = $pi['filename'] ?? $nombre;
            $ext     = isset($pi['extension']) && $pi['extension'] !== '' ? ('.'.$pi['extension']) : '';
            $stamp   = now()->format('Ymd_Hi');
            $base    = \Illuminate\Support\Str::limit(\Illuminate\Support\Str::slug($base, '_'), 80, '');
            $nombre  = "{$base}_{$stamp}{$ext}";
        }

        // Ruta lógica (ajústala a tu estructura real si ya migraste archivos físicos)
        $ruta = "legacy/proyectos/{$proyectoId}/{$nombre}";


        // Determinar tipo mime de forma simple por extensión (con fallback seguro)
        $ext  = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        $map  = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'webp'=>'image/webp',  'svg'  => 'image/svg+xml',
            'ai'  => 'application/postscript', 'psd' => 'image/vnd.adobe.photoshop',
            'pdf' => 'application/pdf', 'zip' => 'application/zip',
        ];

        // Fallback por defecto (si no se detecta extensión o no coincide en el mapa)
        $mime = $map[$ext] ?? 'application/octet-stream';

        // Calcular versión (si tu modelo tiene método, úsalo)
        try {
            if (class_exists(\App\Models\ArchivoProyecto::class) &&
                method_exists(\App\Models\ArchivoProyecto::class, 'calcularVersion')) {
                $version = \App\Models\ArchivoProyecto::calcularVersion($proyectoId);
            } else {
                // Fallback: contar registros existentes + 1
                $version = (int) DB::table('archivos_proyecto')
                    ->where('proyecto_id', $proyectoId)->count() + 1;
            }
        } catch (\Throwable $e) {
            $version = 1;
        }

        // Insertar
        DB::table('archivos_proyecto')->insert([
            'proyecto_id'     => $proyectoId,
            'usuario_id'      => $usuarioId,
            'nombre_archivo'  => $nombre,
            'ruta_archivo'    => $ruta,
            'tipo_archivo'    => $mime,
            'tipo_carga'      => 1,      // 1 = diseños, 2 = iniciales
            'version'         => $version,
            'flag_can_delete' => 0,      // proteger archivo migrado
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->line("   + Archivo de diseño registrado para proyecto {$proyectoId}: {$nombre}");
    }

    protected function migrarClientsUpAUsers(bool $dry, Role $rolPrincipal, Role $rolSubordinado): void
    {
        // Dedupe por email (mantener el menor clientsup_id)
        $dedup = DB::table('clientsup')
            ->select(DB::raw('MIN(clientsup_id) as keep_id'), 'email')
            ->groupBy('email')
            ->get()
            ->keyBy('keep_id');

        $emailsYaInsertados = [];

        $registros = DB::table('clientsup')->orderBy('clientsup_id')->get();
        foreach ($registros as $r) {
            $isKeeper = $dedup->has($r->clientsup_id);
            if (!$isKeeper) {
                $this->line(" - Omitiendo duplicado por email {$r->email} (clientsup_id={$r->clientsup_id})");
                continue;
            }

            $email = trim(strtolower((string)$r->email));
            if ($email === '') {
                $this->warn(" - Omitido clientsup_id={$r->clientsup_id} por email vacío");
                continue;
            }
            if (isset($emailsYaInsertados[$email])) {
                $this->line(" - Omitiendo duplicado (post-chequeo) {$email} (clientsup_id={$r->clientsup_id})");
                continue;
            }

            // Password: si no es bcrypt, generar hash
            $password = $r->password ?? '';
            if (!is_string($password)) $password = '';
            if (!preg_match('/^\$2y\$/', $password)) {
                $plain    = strlen($password) ? $password : Str::random(16);
                $password = bcrypt($plain);
            }

            // Nombre
            $name = trim((string)($r->name ?: $r->user ?: 'ClienteSup '.$r->clientsup_id));
            $name = mb_substr($name, 0, 255);

            // Legacy
            $userLegacy    = $r->user ?? null;
            $companyLegacy = $r->company ?? null;
            $superLegacy   = isset($r->super) ? (int)$r->super : null;
            $superIdLegacy = isset($r->super_id) ? (int)$r->super_id : null;

            // Regla rol (misma que clients): super_id > 0 => principal; else subordinado
            $rolDestino = ($superIdLegacy !== null && $superIdLegacy > 0) ? $rolPrincipal : $rolSubordinado;

            // Espacio de IDs para evitar colisión con client_id
            $idDestino = 700000 + (int) $r->clientsup_id;

            $row = [
                'id'                => $idDestino,
                'name'              => $name,
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
                // Legacy
                'user_legacy'       => $userLegacy,
                'company_legacy'    => $companyLegacy,
                'super_legacy'      => $superLegacy,
                'super_id_legacy'   => $superIdLegacy,
            ];

            // Si ya existe usuario con MISMO email, no dupliques: sólo backfill legacy y roles
            $userByEmail = DB::table('users')->where('email', $row['email'])->first();
            if ($userByEmail) {
                $this->warn(" - Ya existe users.email={$row['email']} (id={$userByEmail->id}). Backfill legacy + rol.");
                $toUpdate = [];
                foreach (['user_legacy','company_legacy','super_legacy','super_id_legacy'] as $f) {
                    if (is_null($userByEmail->$f) && !is_null($row[$f])) {
                        $toUpdate[$f] = $row[$f];
                    }
                }
                if (!empty($toUpdate)) {
                    $toUpdate['updated_at'] = now();
                    if ($dry) {
                        $this->line("   + [DRY] UPDATE users.id={$userByEmail->id} legacy fields");
                    } else {
                        DB::table('users')->where('id', $userByEmail->id)->update($toUpdate);
                        $this->line("   + Legacy actualizado en users.id={$userByEmail->id}");
                    }
                }
                $this->asignarRolSpatiePorId((int)$userByEmail->id, $rolDestino, $dry);
                continue;
            }

            // Si existe el idDestino (raro), sólo rol y backfill legacy
            $existeId = DB::table('users')->where('id', $idDestino)->exists();
            if ($existeId) {
                $this->warn(" - Ya existe users.id={$idDestino} — se asigna rol destino y backfill legacy si aplica");
                $this->asignarRolSpatiePorId($idDestino, $rolDestino, $dry);
                continue;
            }

            $emailsYaInsertados[$email] = true;

            if ($dry) {
                $this->line(" + [DRY] Insertaría users.id={$row['id']} email={$row['email']} (clientsup) y rol={$rolDestino->name}");
            } else {
                DB::table('users')->insert($row);
                $this->asignarRolSpatiePorId((int)$row['id'], $rolDestino, $dry);
                $this->line(" + Insertado users.id={$row['id']} email={$row['email']} (clientsup) y rol={$rolDestino->name}");
            }
        }
    }

    protected function migrarStaffAUsers(bool $dry, ?Role $rolStaff): void
    {
        $dedup = DB::table('staff')
            ->select(DB::raw('MIN(staff_id) as keep_id'), 'email')
            ->groupBy('email')
            ->get()
            ->keyBy('keep_id');

        $emailsYaInsertados = [];

        $registros = DB::table('staff')->orderBy('staff_id')->get();
        foreach ($registros as $s) {
            $isKeeper = $dedup->has($s->staff_id);
            if (!$isKeeper) {
                $this->line(" - Omitiendo duplicado por email {$s->email} (staff_id={$s->staff_id})");
                continue;
            }

            $email = trim(strtolower((string)$s->email));
            if ($email === '') {
                $this->warn(" - Omitido staff_id={$s->staff_id} por email vacío");
                continue;
            }
            if (isset($emailsYaInsertados[$email])) {
                $this->line(" - Omitiendo duplicado (post-chequeo) {$email} (staff_id={$s->staff_id})");
                continue;
            }

            // Password
            $password = $s->password ?? '';
            if (!is_string($password)) $password = '';
            if (!preg_match('/^\$2y\$/', $password)) {
                $plain    = strlen($password) ? $password : Str::random(16);
                $password = bcrypt($plain);
            }

            // Nombre
            $name = trim((string)($s->name ?: $s->user ?: 'Staff '.$s->staff_id));
            $name = mb_substr($name, 0, 255);

            // Legacy
            $userLegacy    = $s->user ?? null;
            $companyLegacy = null; // staff no trae company
            $superLegacy   = null;
            $superIdLegacy = null;

            // Espacio de IDs
            $idDestino = 800000 + (int) $s->staff_id;

            $row = [
                'id'                => $idDestino,
                'name'              => $name,
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
                // Legacy
                'user_legacy'       => $userLegacy,
                'company_legacy'    => $companyLegacy,
                'super_legacy'      => $superLegacy,
                'super_id_legacy'   => $superIdLegacy,
            ];

            // ¿Ya existe por email?
            $userByEmail = DB::table('users')->where('email', $row['email'])->first();
            if ($userByEmail) {
                $this->warn(" - Ya existe users.email={$row['email']} (id={$userByEmail->id}). Backfill legacy + rol staff (si existe).");
                $toUpdate = [];
                foreach (['user_legacy','company_legacy'] as $f) {
                    if (is_null($userByEmail->$f) && !is_null($row[$f])) {
                        $toUpdate[$f] = $row[$f];
                    }
                }
                if (!empty($toUpdate)) {
                    $toUpdate['updated_at'] = now();
                    if ($dry) {
                        $this->line("   + [DRY] UPDATE users.id={$userByEmail->id} legacy (staff)");
                    } else {
                        DB::table('users')->where('id', $userByEmail->id)->update($toUpdate);
                        $this->line("   + Legacy actualizado en users.id={$userByEmail->id}");
                    }
                }
                if ($rolStaff) {
                    $this->asignarRolSpatiePorId((int)$userByEmail->id, $rolStaff, $dry);
                }
                continue;
            }

            // ¿Ya existe el idDestino?
            $existeId = DB::table('users')->where('id', $idDestino)->exists();
            if ($existeId) {
                $this->warn(" - Ya existe users.id={$idDestino} — sólo rol staff (si existe) y backfill legacy");
                if ($rolStaff) {
                    $this->asignarRolSpatiePorId($idDestino, $rolStaff, $dry);
                }
                continue;
            }

            $emailsYaInsertados[$email] = true;

            if ($dry) {
                $this->line(" + [DRY] Insertaría users.id={$row['id']} email={$row['email']} (staff)".($rolStaff ? " y rol=staff" : " (sin rol staff)"));
            } else {
                DB::table('users')->insert($row);
                if ($rolStaff) {
                    $this->asignarRolSpatiePorId((int)$row['id'], $rolStaff, $dry);
                }
                $this->line(" + Insertado users.id={$row['id']} email={$row['email']} (staff)".($rolStaff ? " y rol=staff" : ""));
            }
        }
    }


       /** Inicializa el archivo de log. Si $truncate = true, lo resetea. */
    protected function initMigrationLog(bool $truncate = true): void
    {
        $this->logFilePath = storage_path('logs/migracion_clientes_proyectos.log');

        // Asegura el directorio
        $dir = dirname($this->logFilePath);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Encabezado y truncado
        $header  = "==== MIGRACIÓN clientes-proyectos ====\n";
        $header .= 'Fecha de ejecución: ' . now()->toDateTimeString() . "\n";
        $header .= "=====================================\n";

        if ($truncate) {
            File::put($this->logFilePath, $header);
        } else {
            File::append($this->logFilePath, "\n".$header);
        }
    }

        /** Escribe líneas en el log (acepta mensajes multi-línea) */
    protected function appendToMigrationLog(string $level, string $message): void
    {
        if ($this->logFilePath === '') {
            // fallback por si no se inicializó
            $this->initMigrationLog(false);
        }

        // Divide por líneas para mantener formato prolijo
        $lines = preg_split("/\\r\\n|\\r|\\n/", $message);
        foreach ($lines as $line) {
            File::append(
                $this->logFilePath,
                '['.now()->toDateTimeString()."] {$level}: {$line}\n"
            );
        }
    }


        // ==== Overrides para duplicar a archivo cada salida de consola ====

    public function info($string, $verbosity = null)
    {
        $this->appendToMigrationLog('INFO', (string) $string);
        return parent::info($string, $verbosity);
    }

    public function warn($string, $verbosity = null)
    {
        $this->appendToMigrationLog('WARN', (string) $string);
        return parent::warn($string, $verbosity);
    }

    public function error($string, $verbosity = null)
    {
        $this->appendToMigrationLog('ERROR', (string) $string);
        return parent::error($string, $verbosity);
    }

    public function line($string, $style = null, $verbosity = null)
    {
        // Usa el estilo como nivel cuando exista; por defecto "LINE"
        $level = $style ? strtoupper((string) $style) : 'LINE';
        $this->appendToMigrationLog($level, (string) $string);
        return parent::line($string, $style, $verbosity);
    }
}