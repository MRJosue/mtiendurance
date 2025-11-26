<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class MigrarUsuariosLegacy extends Command
{
    protected $signature = 'migrar:usuarios-legacy
                            {--adminId=1 : ID de usuario fallback}
                            {--dry-run : Solo simula, no inserta}';

    protected $description = 'Migra client, clientsup y staff a users, sincroniza roles y jerarquías.';

    protected string $logFilePath = '';

    public function handle()
    {
        $this->initMigrationLog(true);

        $dry     = (bool) $this->option('dry-run');
        $adminId = (int)  $this->option('adminId');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $rolPrincipal   = Role::where('name', 'cliente_principal')->first();
        $rolSubordinado = Role::where('name', 'cliente_subordinado')->first();
        $rolStaff       = Role::where('name', 'staff')->first();
        $rolProveedor   = Role::where('name', 'proveedor')->first();

        if (!$rolPrincipal || !$rolSubordinado || !$rolProveedor) {
            $faltan = [];
            if (!$rolPrincipal)   $faltan[] = 'cliente_principal';
            if (!$rolSubordinado) $faltan[] = 'cliente_subordinado';
            if (!$rolProveedor)   $faltan[] = 'proveedor';
            $this->error("Faltan roles: ".implode(', ', $faltan));
            return self::FAILURE;
        }

        if (!$this->ensureLegacyColumns()) return self::FAILURE;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->info('===> Paso 1: CLIENT -> USERS');
        $this->migrarClientesAUsers($dry, $rolPrincipal, $rolSubordinado);

        $this->info('===> Paso 1.1: CLIENTSUP -> USERS (espacio 700000 + id)');
        $this->migrarClientsUpAUsers($dry, $rolProveedor);

        $this->info('===> Paso 1.2: STAFF -> USERS (espacio 800000 + id)');
        $this->migrarStaffAUsers($dry, $rolStaff);

        // AQUÍ metemos al chingon, después de tener todo el staff
        $this->info('===> Paso 1.3: Admin "el chingon" y subordinados STAFF');
        $this->crearAdminElChingon($dry);

        $this->info('===> Paso 2: AutoIncrement users');
        $this->ajustarAutoIncrement('users', 'id', $dry);

        $this->info('===> Paso 2.2: Jerarquías y roles por subordinados');
        $this->backfillSubordinadosYRoles($rolPrincipal, $rolSubordinado, $dry);

        $this->info('===> Paso 2.3: Empresas / Sucursales');
        $this->backfillEmpresasYSucursales($rolPrincipal, $rolSubordinado, $dry);


        // $this->info('===> Paso 1.1: CLIENTSUP -> USERS (espacio 700000 + id)');
        // $this->migrarClientsUpAUsers($dry, $rolProveedor);

        // $this->info('===> Paso 1.2: STAFF -> USERS (espacio 800000 + id)');
        // $this->migrarStaffAUsers($dry, $rolStaff);

        // $this->info('===> Paso 2: AutoIncrement users');
        // $this->ajustarAutoIncrement('users', 'id', $dry);

        // $this->info('===> Paso 2.2: Jerarquías y roles por subordinados');
        // $this->backfillSubordinadosYRoles($rolPrincipal, $rolSubordinado, $dry);

        // $this->info('===> Paso 2.3: Empresas / Sucursales');
        // $this->backfillEmpresasYSucursales($rolPrincipal, $rolSubordinado, $dry);

        // $this->info('===> Paso 2.4: Admin "el chingon" y subordinados STAFF');
        // $this->crearAdminElChingon($dry);


        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info('✔ Migración de USUARIOS finalizada ' . ($dry ? '(DRY-RUN)' : ''));
        return self::SUCCESS;
    }

    /* ================== helpers y rutinas existentes ================== */

    protected function migrarClientesAUsers(bool $dry, Role $rolPrincipal, Role $rolSubordinado) { 
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
            $rolDestino = $this->esPrincipalDesdeLegacy($superIdLegacy)
            ? $rolPrincipal
            : $rolSubordinado;


            // NUEVO: config por rol
            $configArr = ($rolDestino->name === 'cliente_principal')
                ? ['flag-user-sel-preproyectos' => false, 'flag-can-user-sel-preproyectos' => true]
                : ['flag-user-sel-preproyectos' => true,  'flag-can-user-sel-preproyectos' => false];      

             $tipo = 1; // 1 = CLIENTE

            // Fila para INSERT (sin role_id)
            $row = [
                'id'                => (int) $c->client_id,
                'name'              => $name,
                'email'             => mb_substr($email, 0, 255),
                'email_verified_at' => null,
                'password'          => $password,
                'remember_token'    => null,

                'tipo'                        => $tipo,       

                // 👇 ya no dupliques estas claves
                'config'                       => json_encode($configArr),
                'user_can_sel_preproyectos'    => null, // se poblará en backfill
                'subordinados'                 => null,

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
                    ->select('user_legacy','company_legacy','super_legacy','super_id_legacy','config') // <- agrega 'config'
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
                if (is_null($existente->config)) {
                    $toUpdate['config'] = json_encode($configArr);
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

    protected function migrarClientsUpAUsers(bool $dry, Role $rolProveedor)
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

            // Nombre para el usuario
            $name = trim((string)($r->name ?: $r->user ?: 'ClienteSup '.$r->clientsup_id));
            $name = mb_substr($name, 0, 255);

            // Legacy
            $userLegacy    = $r->user ?? null;
            $companyLegacy = $r->company ?? null;
            $superLegacy   = isset($r->super) ? (int)$r->super : null;
            $superIdLegacy = isset($r->super_id) ? (int)$r->super_id : null;

            // Fuerza ROL proveedor
            $rolDestino = $rolProveedor;

            // Espacio de IDs para evitar colisión con client_id
            $idDestino = 700000 + (int) $r->clientsup_id;

            $tipo = 2;



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
                'tipo'              => $tipo,  
                // Legacy
                'user_legacy'       => $userLegacy,
                'company_legacy'    => $companyLegacy,
                'super_legacy'      => $superLegacy,
                'super_id_legacy'   => $superIdLegacy,
            ];

            // ¿Ya existe por email?
            $userByEmail = DB::table('users')->where('email', $row['email'])->first();
            if ($userByEmail) {
                $this->warn(" - Ya existe users.email={$row['email']} (id={$userByEmail->id}). Backfill legacy + rol proveedor + Empresa/Sucursal.");
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
                // Rol proveedor
                $this->asignarRolSpatiePorId((int)$userByEmail->id, $rolDestino, $dry);

                // Empresa/Sucursal con nombre de usuario
                $baseName = $userLegacy && trim($userLegacy) !== '' ? trim($userLegacy) : $name;
                $this->crearEmpresaSucursalParaUsuario((int)$userByEmail->id, $baseName, $dry);
                continue;
            }

            // ¿Ya existe el idDestino?
            $existeId = DB::table('users')->where('id', $idDestino)->exists();
            if ($existeId) {
                $this->warn(" - Ya existe users.id={$idDestino} — rol proveedor + Empresa/Sucursal (si falta).");
                $this->asignarRolSpatiePorId($idDestino, $rolDestino, $dry);
                $baseName = $userLegacy && trim($userLegacy) !== '' ? trim($userLegacy) : $name;
                $this->crearEmpresaSucursalParaUsuario($idDestino, $baseName, $dry);
                continue;
            }

            $emailsYaInsertados[$email] = true;

            if ($dry) {
                $this->line(" + [DRY] Insertaría users.id={$row['id']} email={$row['email']} (clientsup) y rol=proveedor");
               $this->line('   [DRY] Crearía Empresa/Sucursal con nombre=\'' . $baseName . '\' y asignaría al usuario');
            } else {
                DB::table('users')->insert($row);
                $this->asignarRolSpatiePorId((int)$row['id'], $rolDestino, $dry);

                // Empresa/Sucursal con nombre de usuario
                $baseName = $userLegacy && trim($userLegacy) !== '' ? trim($userLegacy) : $name;
                $this->crearEmpresaSucursalParaUsuario((int)$row['id'], $baseName, $dry);

                $this->line(" + Insertado users.id={$row['id']} email={$row['email']} (clientsup) con rol=proveedor");
            }
        }
    }

    protected function migrarStaffAUsers(bool $dry, ?Role $rolStaff) { 
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

               $tipo = 3; // 3 = STAFF

            $row = [
                'id'                => $idDestino,
                'name'              => $name,
                'email'             => mb_substr($email, 0, 255),
                'email_verified_at' => null,
                'password'          => $password,
                'remember_token'    => null,
                  'tipo'              => $tipo,  
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

    protected function asignarRolSpatiePorId(int $userId, Role $role, bool $dry): void { 
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

    protected function syncRolSpatieUnico(int $userId, Role $rolDestino, Role $rolContrario, bool $dry): void {
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
    protected function backfillSubordinadosYRoles(Role $rolPrincipal, Role $rolSubordinado, bool $dry): void { 
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

            $principalConfig = ['flag-user-sel-preproyectos' => false, 'flag-can-user-sel-preproyectos' => true];

            if ($dry) {
                $this->line("   [DRY] users.id={$padreId} subordinados=" . json_encode($subIds));
                $this->line("   [DRY] users.id={$padreId} user_can_sel_preproyectos=" . json_encode($subIds));
                $this->line("   [DRY] users.id={$padreId} config=" . json_encode($principalConfig));
            } else {
                DB::table('users')->where('id', $padreId)->update([
                    'subordinados'               => json_encode($subIds),
                    'user_can_sel_preproyectos'  => json_encode($subIds), // <-- IGUAL que subordinados
                    'config'                     => json_encode($principalConfig),
                    'updated_at'                 => now(),
                ]);
            }

            // Sincronizar rol del padre: principal
            $this->syncRolSpatieUnico((int)$padreId, $rolPrincipal, $rolSubordinado, $dry);
        }

        // 4) Hijos (subordinados)
        $todosHijosIds = $hijosPorPadre->flatten(1)->pluck('id')->unique()->map(fn($v) => (int)$v)->values();
        $subConfig = ['flag-user-sel-preproyectos' => true, 'flag-can-user-sel-preproyectos' => false]; // <-- AQUI

        foreach ($todosHijosIds as $hijoId) {
            $this->syncRolSpatieUnico((int)$hijoId, $rolSubordinado, $rolPrincipal, $dry);

            if ($dry) {
                $this->line("   [DRY] users.id={$hijoId} config=" . json_encode($subConfig));
                $this->line("   [DRY] users.id={$hijoId} user_can_sel_preproyectos=null");
            } else {
                DB::table('users')->where('id', $hijoId)->update([
                    'config'                    => json_encode($subConfig),
                    'user_can_sel_preproyectos' => null, // hijos no tienen subordinados
                    'updated_at'                => now(),
                ]);
            }
        }

        $this->line('   + Jerarquía aplicada: padres con subordinados y roles sincronizados.');
     }

    protected function backfillEmpresasYSucursales(Role $rolPrincipal, Role $rolSubordinado, bool $dry): void
    {
        if (!Schema::hasTable('empresas') || !Schema::hasTable('sucursales')) {
            $this->warn('   ! No existen tablas empresas/sucursales. Se omite Paso 2.3.');
            return;
        }
        $tienePivot = Schema::hasTable('sucursal_user');

        // Excluir al admin "el chingon" de este proceso (se maneja aparte)
        $adminId = DB::table('users')
            ->where('email', 'el.chingon@mtiendurance.com')
            ->value('id');

        // === PRINCIPALES SOLO CLIENTES (tipo = 1) ===
        // 1) Por rol cliente_principal
        $principalesPorRol = DB::table('model_has_roles')
            ->join('users', function ($join) {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', User::class);
            })
            ->where('model_has_roles.role_id', $rolPrincipal->id)
            ->where('users.tipo', 1) // SOLO clientes
            ->pluck('users.id');

        // 2) Por legacy: super_id_legacy NULL o 0, pero SOLO tipo=1
        $principalesPorLegacy = DB::table('users')
            ->where('tipo', 1) // SOLO clientes
            ->where(function ($q) {
                $q->whereNull('super_id_legacy')
                ->orWhere('super_id_legacy', 0);
            })
            ->pluck('id');

        $principalesIds = collect()
            ->merge($principalesPorRol)
            ->merge($principalesPorLegacy)
            ->unique()
            ->map(fn($v) => (int) $v)
            ->filter(function ($id) use ($adminId) {
                // Si tenemos admin y coincide, lo excluimos
                if ($adminId) {
                    return $id !== (int) $adminId;
                }
                return true;
            })
            ->values();

        if ($principalesIds->isEmpty()) {
            $this->line('   = No hay usuarios principales (clientes) para Empresas/Sucursales.');
            return;
        }
        // === FIN NUEVO ===

        // 2) Traer principales (SOLO clientes tipo=1)
        $principales = DB::table('users')
            ->select('id', 'name', 'company_legacy', 'subordinados', 'empresa_id', 'sucursal_id')
            ->whereIn('id', $principalesIds)
            ->where('tipo', 1) // refuerzo
            ->get()
            ->keyBy('id');

        foreach ($principales as $principal) {
            $principalId = (int) $principal->id;

            // Nombre de empresa (company_legacy del principal si existe; si no, name; si no, fallback)
            $empresaNombre = trim((string)($principal->company_legacy ?? ''));
            if ($empresaNombre === '') {
                $empresaNombre = trim((string)$principal->name);
                if ($empresaNombre === '') {
                    $empresaNombre = "Empresa de Usuario {$principalId}";
                }
            }

            // 3) Obtener/crear empresa
            $empresaId = DB::table('empresas')->where('nombre', $empresaNombre)->value('id');
            if (!$empresaId) {
                if ($dry) {
                    $this->line("   [DRY] Crear empresa(nombre='{$empresaNombre}')");
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

            // 4) Sucursal "principal" = nombre de la empresa
            $sucursalDefaultNombre = $empresaNombre;

            // Intentar encontrar sucursal con el nombre de la empresa
            $sucursalDefaultId = null;
            if (!$dry) {
                $sucursalDefaultId = DB::table('sucursales')
                    ->where('empresa_id', $empresaId)
                    ->where('nombre', $sucursalDefaultNombre)
                    ->value('id');
            }

            // Si no existe, ver si hay una 'Matriz' para renombrarla
            if (!$sucursalDefaultId) {
                $sucursalMatrizId = null;
                if (!$dry) {
                    $sucursalMatrizId = DB::table('sucursales')
                        ->where('empresa_id', $empresaId)
                        ->where('nombre', 'Matriz')
                        ->value('id');
                }

                if ($sucursalMatrizId) {
                    if ($dry) {
                        $this->line("   [DRY] Renombrar sucursal id={$sucursalMatrizId} de 'Matriz' a '{$sucursalDefaultNombre}'");
                    } else {
                        DB::table('sucursales')->where('id', $sucursalMatrizId)->update([
                            'nombre'     => $sucursalDefaultNombre,
                            'tipo'       => 1,
                            'updated_at' => now(),
                        ]);
                        $sucursalDefaultId = $sucursalMatrizId;
                        $this->line("   + Sucursal renombrada id={$sucursalDefaultId} ('{$sucursalDefaultNombre}')");
                    }
                }
            }

            // Si aún no existe, crearla
            if (!$sucursalDefaultId) {
                if ($dry) {
                    $this->line("   [DRY] Crear sucursal(nombre='{$sucursalDefaultNombre}', empresa_id={$empresaId})");
                } else {
                    $sucursalDefaultId = DB::table('sucursales')->insertGetId([
                        'empresa_id' => $empresaId,
                        'nombre'     => $sucursalDefaultNombre,
                        'tipo'       => 1, 
                        'telefono'   => null,
                        'direccion'  => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->line("   + Sucursal creada id={$sucursalDefaultId} ('{$sucursalDefaultNombre}')");
                }
            } else {
                $this->line("   = Sucursal existente id={$sucursalDefaultId} ('{$sucursalDefaultNombre}')");
            }

            // 5) Asignar empresa/sucursal al principal
            if ($dry) {
                $this->line("   [DRY] users.id={$principalId} -> empresa_id={$empresaId}, sucursal_id={$sucursalDefaultId}");
            } else {
                DB::table('users')->where('id', $principalId)->update([
                    'empresa_id'  => $empresaId,
                    'sucursal_id' => $sucursalDefaultId,
                    'updated_at'  => now(),
                ]);
            }

            // 6) Subordinados… (igual que ya lo tenías)
            $subsIds = [];
            if ($principal->subordinados) {
                try {
                    $decoded = is_string($principal->subordinados)
                        ? json_decode($principal->subordinados, true)
                        : $principal->subordinados;
                    if (is_array($decoded)) {
                        $subsIds = array_values(array_unique(array_map('intval', $decoded)));
                    }
                } catch (\Throwable $e) { /* ignore */ }
            }

            if (!empty($subsIds)) {
                $subs = DB::table('users')
                    ->select('id', 'company_legacy')
                    ->whereIn('id', $subsIds)
                    ->get();

                foreach ($subs as $sub) {
                    $subId = (int) $sub->id;
                    $sucursalDestinoId = $sucursalDefaultId;

                    $nombreSucursalSub = trim((string)($sub->company_legacy ?? ''));
                    if ($nombreSucursalSub !== '') {
                        if (strcasecmp($nombreSucursalSub, $sucursalDefaultNombre) === 0) {
                            $this->line("     = Sub {$subId} company_legacy = nombre de empresa -> usa sucursal default");
                        } else {
                            $existeSucursal = null;
                            if (!$dry) {
                                $existeSucursal = DB::table('sucursales')
                                    ->where('empresa_id', $empresaId)
                                    ->where('nombre', $nombreSucursalSub)
                                    ->value('id');
                            }
                            if (!$existeSucursal) {
                                if ($dry) {
                                    $this->line("     [DRY] Crear sucursal(nombre='{$nombreSucursalSub}', empresa_id={$empresaId}) para sub {$subId}");
                                } else {
                                    $existeSucursal = DB::table('sucursales')->insertGetId([
                                        'empresa_id' => $empresaId,
                                        'nombre'     => $nombreSucursalSub,
                                        'telefono'   => null,
                                        'direccion'  => null,
                                        'tipo'       => 2,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                    $this->line("     + Sucursal creada id={$existeSucursal} ('{$nombreSucursalSub}')");
                                }
                            } else {
                                $this->line("     = Sucursal existente id={$existeSucursal} ('{$nombreSucursalSub}')");
                            }
                            if ($existeSucursal) {
                                $sucursalDestinoId = $existeSucursal;
                            }
                        }
                    } else {
                        $this->line("     = Sub {$subId} sin company_legacy -> usa sucursal default ('{$sucursalDefaultNombre}')");
                    }

                    if ($dry) {
                        $this->line("     [DRY] users.id={$subId} -> empresa_id={$empresaId}, sucursal_id={$sucursalDestinoId}");
                    } else {
                        DB::table('users')->where('id', $subId)->update([
                            'empresa_id'  => $empresaId,
                            'sucursal_id' => $sucursalDestinoId,
                            'updated_at'  => now(),
                        ]);
                    }

                    if ($tienePivot) {
                        if ($dry) {
                            $this->line("       [DRY] Pivot sucursal_user: sucursal={$sucursalDestinoId}, user={$subId}");
                        } else {
                            $ya = DB::table('sucursal_user')
                                ->where('sucursal_id', $sucursalDestinoId)
                                ->where('user_id', $subId)
                                ->exists();
                            if (!$ya) {
                                DB::table('sucursal_user')->insert([
                                    'sucursal_id' => $sucursalDestinoId,
                                    'user_id'     => $subId,
                                    'created_at'  => now(),
                                    'updated_at'  => now(),
                                ]);
                                $this->line("       + Pivot sucursal_user: sucursal={$sucursalDestinoId}, user={$subId}");
                            }
                        }
                    }
                }

                // Pivot para el principal
                if ($tienePivot) {
                    if ($dry) {
                        $this->line("     [DRY] Pivot sucursal_user (principal): sucursal={$sucursalDefaultId}, user={$principalId}");
                    } else {
                        $ya = DB::table('sucursal_user')
                            ->where('sucursal_id', $sucursalDefaultId)
                            ->where('user_id', $principalId)
                            ->exists();
                        if (!$ya) {
                            DB::table('sucursal_user')->insert([
                                'sucursal_id' => $sucursalDefaultId,
                                'user_id'     => $principalId,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                            $this->line("       + Pivot sucursal_user (principal): sucursal={$sucursalDefaultId}, user={$principalId}");
                        }
                    }
                }
            } else {
                // Principal sin subordinados: mantener pivot
                if ($tienePivot) {
                    if ($dry) {
                        $this->line("   [DRY] Pivot sucursal_user (principal sin subs): sucursal={$sucursalDefaultId}, user={$principalId}");
                    } else {
                        $ya = DB::table('sucursal_user')
                            ->where('sucursal_id', $sucursalDefaultId)
                            ->where('user_id', $principalId)
                            ->exists();
                        if (!$ya) {
                            DB::table('sucursal_user')->insert([
                                'sucursal_id' => $sucursalDefaultId,
                                'user_id'     => $principalId,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                            $this->line("   + Pivot sucursal_user (principal sin subs): sucursal={$sucursalDefaultId}, user={$principalId}");
                        }
                    }
                }
            }
        }

        $this->line('   + Empresas/Sucursales: sucursal principal renombrada al nombre de la empresa y subordinados asignados correctamente.');
    }


    /**
     * Crea (si no existen) Empresa y Sucursal con el mismo nombre ($baseName)
     * y asigna empresa_id / sucursal_id al usuario. También inserta en el
     * pivot sucursal_user si dicha tabla existe.
     */
    protected function crearEmpresaSucursalParaUsuario(int $userId, string $baseName, bool $dry): void
    {
        if (!Schema::hasTable('empresas') || !Schema::hasTable('sucursales')) {
            $this->warn("   ! No existen tablas empresas/sucursales. Se omite creación para user {$userId}.");
            return;
        }

        $baseName = mb_substr(trim($baseName) !== '' ? trim($baseName) : "Empresa de Usuario {$userId}", 0, 255);
        $tienePivot = Schema::hasTable('sucursal_user');

        // Si ya tiene empresa/sucursal, evitar trabajo innecesario
        $userRow = DB::table('users')->where('id', $userId)->select('empresa_id','sucursal_id')->first();
        if ($userRow && $userRow->empresa_id && $userRow->sucursal_id) {
            $this->line("   = Usuario {$userId} ya tiene empresa_id={$userRow->empresa_id} y sucursal_id={$userRow->sucursal_id}");
            return;
        }

        // Empresa: buscar por nombre
        $empresaId = DB::table('empresas')->where('nombre', $baseName)->value('id');

        if (!$empresaId) {
            if ($dry) {
                $this->line("   [DRY] Crear empresa(nombre='{$baseName}')");
            } else {
                $empresaId = DB::table('empresas')->insertGetId([
                    'nombre'     => $baseName,
                    'rfc'        => null,
                    'telefono'   => null,
                    'direccion'  => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->line("   + Empresa creada id={$empresaId} nombre='{$baseName}'");
            }
        } else {
            $this->line("   = Empresa existente id={$empresaId} nombre='{$baseName}'");
        }

        // Sucursal: mismo nombre que la empresa/baseName, tipo=1
        $sucursalId = null;
        if (!$dry) {
            $sucursalId = DB::table('sucursales')
                ->where('empresa_id', $empresaId)
                ->where('nombre', $baseName)
                ->value('id');
        }

        if (!$sucursalId) {
            if ($dry) {
                $this->line("   [DRY] Crear sucursal(nombre='{$baseName}', empresa_id={$empresaId}, tipo=1)");
            } else {
                $sucursalId = DB::table('sucursales')->insertGetId([
                    'empresa_id' => $empresaId,
                    'nombre'     => $baseName,
                    'tipo'       => 1, // principal
                    'telefono'   => null,
                    'direccion'  => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->line("   + Sucursal creada id={$sucursalId} ('{$baseName}')");
            }
        } else {
            $this->line("   = Sucursal existente id={$sucursalId} ('{$baseName}')");
        }

        // Asignar al usuario
        if ($dry) {
            $this->line("   [DRY] users.id={$userId} -> empresa_id={$empresaId}, sucursal_id={$sucursalId}");
        } else {
            DB::table('users')->where('id', $userId)->update([
                'empresa_id'  => $empresaId,
                'sucursal_id' => $sucursalId,
                'updated_at'  => now(),
            ]);
        }

        // Pivot sucursal_user (si existe)
        if ($tienePivot && !$dry) {
            $ya = DB::table('sucursal_user')
                ->where('sucursal_id', $sucursalId)
                ->where('user_id', $userId)
                ->exists();
            if (!$ya) {
                DB::table('sucursal_user')->insert([
                    'sucursal_id' => $sucursalId,
                    'user_id'     => $userId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $this->line("   + Pivot sucursal_user: sucursal={$sucursalId}, user={$userId}");
            } else {
                $this->line("   = Pivot sucursal_user ya existente: sucursal={$sucursalId}, user={$userId}");
            }
        }
    }



    /**
     * Crea (o actualiza) el usuario administrador "el chingon"
     * con empresa "MTIENDURANCE" y sucursal "Comercial de Viniles",
     * y asigna como subordinados a todos los usuarios STAFF,
     * detectados por tipo=3 o por rol Spatie "staff".
     * Todos los STAFF comparten esa empresa/sucursal.
     */
    protected function crearAdminElChingon(bool $dry): void
    {
        $nombreAdmin    = 'el chingon';
        $emailAdmin     = 'el.chingon@mtiendurance.com';
        $nombreEmpresa  = 'MTIENDURANCE';
        $nombreSucursal = 'Comercial de Viniles';

        // Config tipo "principal"
        $configPrincipal = [
            'flag-user-sel-preproyectos'     => false,
            'flag-can-user-sel-preproyectos' => true,
        ];

        // ==========================
        // 1) Detectar STAFF (por tipo y por rol Spatie)
        // ==========================

        // Por tipo=3
        $staffIdsPorTipo = DB::table('users')
            ->where('tipo', 3)
            ->pluck('id')
            ->map(fn ($v) => (int) $v);

        // Por rol Spatie "staff"
        $rolStaff = Role::where('name', 'staff')->first();
        $staffIdsPorRol = collect();
        if ($rolStaff) {
            $staffIdsPorRol = DB::table('model_has_roles')
                ->where('role_id', $rolStaff->id)
                ->where('model_type', User::class)
                ->pluck('model_id')
                ->map(fn ($v) => (int) $v);
        }

        // Unir ambos (tipo y rol) y dejar únicos
        $staffIds = $staffIdsPorTipo
            ->merge($staffIdsPorRol)
            ->unique()
            ->values()
            ->all();

        $this->line('   = STAFF detectados (tipo=3 o rol staff) como subordinados de "el chingon": ' . json_encode($staffIds));

        // Intentar obtener rol admin (si existe)
        $rolAdmin = Role::where('name', 'admin')->first();
        if (!$rolAdmin) {
            $this->warn('   ! Rol "admin" no encontrado en Spatie. Se creará/actualizará el usuario pero sin asignar rol admin.');
        }

        // ==========================
        // 2) Crear / actualizar usuario "el chingon"
        // ==========================

        $existing = DB::table('users')->where('email', $emailAdmin)->first();
        $adminId  = null;

        if ($existing) {
            $adminId = (int) $existing->id;
            $this->line("   = Ya existe usuario admin con email {$emailAdmin} (id={$adminId}), se actualizará.");

            $update = [
                'name'                      => $nombreAdmin,
                'tipo'                      => 4, // 4 = ADMIN
                'config'                    => json_encode($configPrincipal),
                'subordinados'              => json_encode($staffIds),
                'user_can_sel_preproyectos' => json_encode($staffIds),
                'updated_at'                => now(),
            ];

            if ($dry) {
                $this->line("   [DRY] UPDATE users.id={$adminId} (el chingon) con nuevos subordinados y config principal");
            } else {
                DB::table('users')->where('id', $adminId)->update($update);
            }
        } else {
            $plainPassword = 'ElChingon2025!'; // cámbiala después
            $passwordHash  = bcrypt($plainPassword);

            $row = [
                'name'                      => $nombreAdmin,
                'email'                     => $emailAdmin,
                'email_verified_at'         => now(),
                'password'                  => $passwordHash,
                'remember_token'            => Str::random(10),
                'tipo'                      => 4, // ADMIN
                'config'                    => json_encode($configPrincipal),
                'user_can_sel_preproyectos' => json_encode($staffIds),
                'subordinados'              => json_encode($staffIds),
                'empresa_id'                => null,
                'sucursal_id'               => null,
                'user_legacy'               => null,
                'company_legacy'            => null,
                'super_legacy'              => null,
                'super_id_legacy'           => null,
                'created_at'                => now(),
                'updated_at'                => now(),
            ];

            if ($dry) {
                $this->line("   [DRY] Insertaría usuario admin '{$nombreAdmin}' con email={$emailAdmin}");
                $adminId = 0;
            } else {
                $adminId = DB::table('users')->insertGetId($row);
                $this->line("   + Usuario admin creado id={$adminId} email={$emailAdmin}");
            }
        }

        // ==========================
        // 3) Crear/asegurar Empresa "MTIENDURANCE" y Sucursal "Comercial de Viniles"
        // ==========================

        if ($dry) {
            $this->line("   [DRY] Crear/asegurar empresa '{$nombreEmpresa}' y sucursal '{$nombreSucursal}'");
            return;
        }

        if (!$adminId || $adminId <= 0) {
            $this->warn("   ! No se tiene un adminId válido para 'el chingon'.");
            return;
        }

        if (!Schema::hasTable('empresas') || !Schema::hasTable('sucursales')) {
            $this->warn("   ! No existen tablas empresas/sucursales. No se pueden asignar a 'el chingon' ni a STAFF.");
            return;
        }

        // Empresa MTIENDURANCE
        $empresaId = DB::table('empresas')
            ->where('nombre', $nombreEmpresa)
            ->value('id');

        if (!$empresaId) {
            $empresaId = DB::table('empresas')->insertGetId([
                'nombre'     => $nombreEmpresa,
                'rfc'        => null,
                'telefono'   => null,
                'direccion'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->line("   + Empresa creada id={$empresaId} nombre='{$nombreEmpresa}'");
        } else {
            $this->line("   = Empresa existente id={$empresaId} nombre='{$nombreEmpresa}'");
        }


        // Asegurar que el propietario de la empresa sea "el chingon"
        if ($empresaId && $adminId && !$dry) {
            // Detectar columna de propietario
            $propColumn = null;
            if (Schema::hasColumn('empresas', 'propietario_id')) {
                $propColumn = 'propietario_id';
            } elseif (Schema::hasColumn('empresas', 'owner_id')) {
                $propColumn = 'owner_id';
            } elseif (Schema::hasColumn('empresas', 'user_id')) {
                $propColumn = 'user_id';
            }

            if ($propColumn) {
                DB::table('empresas')
                    ->where('id', $empresaId)
                    ->update([
                        $propColumn => $adminId,
                    ]);

                $this->line("   + Propietario de empresa MTIENDURANCE actualizado ({$propColumn}={$adminId})");
            } else {
                $this->warn('   ! No se encontró columna de propietario en empresas (propietario_id / owner_id / user_id).');
            }
        }

        // Sucursal Comercial de Viniles (tipo=1)
        $sucursalId = DB::table('sucursales')
            ->where('empresa_id', $empresaId)
            ->where('nombre', $nombreSucursal)
            ->value('id');

        if (!$sucursalId) {
            $sucursalId = DB::table('sucursales')->insertGetId([
                'empresa_id' => $empresaId,
                'nombre'     => $nombreSucursal,
                'tipo'       => 1, // principal
                'telefono'   => null,
                'direccion'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->line("   + Sucursal creada id={$sucursalId} nombre='{$nombreSucursal}'");
        } else {
            $this->line("   = Sucursal existente id={$sucursalId} nombre='{$nombreSucursal}'");
        }

        // Asignar empresa/sucursal al admin
        DB::table('users')->where('id', $adminId)->update([
            'empresa_id'  => $empresaId,
            'sucursal_id' => $sucursalId,
            'updated_at'  => now(),
        ]);
        $this->line("   + 'el chingon' asignado a empresa_id={$empresaId}, sucursal_id={$sucursalId}");

        // Rol admin
        if ($rolAdmin) {
            $this->asignarRolSpatiePorId($adminId, $rolAdmin, false);
        }

        // ==========================
        // 4) Asignar TODOS los STAFF a esa misma empresa/sucursal
        // ==========================

        if (!empty($staffIds)) {
            DB::table('users')
                ->whereIn('id', $staffIds)
                ->update([
                    'empresa_id'  => $empresaId,
                    'sucursal_id' => $sucursalId,
                    'updated_at'  => now(),
                ]);

            $this->line("   + STAFF actualizados para usar la misma empresa/sucursal que 'el chingon'.");

            if (Schema::hasTable('sucursal_user')) {
                foreach ($staffIds as $sid) {
                    $ya = DB::table('sucursal_user')
                        ->where('sucursal_id', $sucursalId)
                        ->where('user_id', $sid)
                        ->exists();

                    if (!$ya) {
                        DB::table('sucursal_user')->insert([
                            'sucursal_id' => $sucursalId,
                            'user_id'     => $sid,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                        $this->line("       + Pivot sucursal_user: sucursal={$sucursalId}, user={$sid}");
                    } else {
                        $this->line("       = Pivot sucursal_user ya existe: sucursal={$sucursalId}, user={$sid}");
                    }
                }
            }
        } else {
            $this->line("   = No se encontraron STAFF (tipo=3 o rol staff) para asignar a la empresa/sucursal de 'el chingon'.");
        }
    }



    protected function ajustarAutoIncrement(string $table, string $pk, bool $dry)
    {
        $max = DB::table($table)->max($pk);
        if (!$max) return;
        $sql = "ALTER TABLE `{$table}` AUTO_INCREMENT = " . ((int)$max + 1);
        if ($dry) $this->line("[DRY] {$sql}");
        else      DB::statement($sql);
    }

    protected function ensureLegacyColumns(): bool
    {
        $cols = ['user_legacy','company_legacy','super_legacy','super_id_legacy'];
        $missing = collect($cols)->filter(fn($c) => !Schema::hasColumn('users', $c))->values();
        if ($missing->isNotEmpty()) {
            $this->error('Faltan columnas en users: '. $missing->join(', '));
            return false;
        }
        return true;
    }

    /* ========= logging duplicado a archivo ========= */

    protected function initMigrationLog(bool $truncate = true): void
    {
        $this->logFilePath = storage_path('logs/migracion_usuarios_legacy.log');
        $dir = dirname($this->logFilePath);
        if (!File::exists($dir)) File::makeDirectory($dir, 0755, true);

        $header  = "==== MIGRACIÓN USUARIOS LEGACY ====\n";
        $header .= 'Fecha: ' . now()->toDateTimeString() . "\n";
        $header .= "===================================\n";
        $truncate ? File::put($this->logFilePath, $header) : File::append($this->logFilePath, "\n".$header);
    }
    protected function appendToMigrationLog(string $level, string $message): void
    {
        if ($this->logFilePath === '') $this->initMigrationLog(false);
        foreach (preg_split("/\\r\\n|\\r|\\n/", $message) as $line) {
            File::append($this->logFilePath, '['.now()->toDateTimeString()."] {$level}: {$line}\n");
        }
    }

    public function info($string, $verbosity = null){ $this->appendToMigrationLog('INFO', (string)$string); return parent::info($string, $verbosity); }

    public function warn($string, $verbosity = null){ $this->appendToMigrationLog('WARN', (string)$string); return parent::warn($string, $verbosity); }

    public function error($string, $verbosity = null){ $this->appendToMigrationLog('ERROR',(string)$string); return parent::error($string, $verbosity); }
    public function line($string, $style = null, $verbosity = null){
        $this->appendToMigrationLog($style ? strtoupper((string)$style) : 'LINE', (string)$string);
        return parent::line($string, $style, $verbosity);
    }

    protected function esPrincipalDesdeLegacy(?int $superIdLegacy): bool
    {
        // principal si no tiene padre, o el valor es 0
        return is_null($superIdLegacy) || (int)$superIdLegacy === 0;
    }

}
