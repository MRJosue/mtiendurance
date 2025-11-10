<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MigrarProyectosLegacy extends Command
{
    protected $signature = 'migrar:proyectos-legacy
                            {--adminId=1 : ID de usuario fallback si no existe client_id en users}
                            {--dry-run : Solo simula, no inserta}';

    protected $description = 'Migra project -> proyectos y registra archivo inicial desde campo legacy o project_file.';

    protected string $logFilePath = '';

    public function handle()
    {
        $this->initMigrationLog(true);

        $dry     = (bool) $this->option('dry-run');
        $adminId = (int)  $this->option('adminId');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->info('===> Paso 3: PROJECT -> PROYECTOS');
        $this->migrarProjectsAProyectos($adminId, $dry);

        $this->info('===> Paso 4: AutoIncrement proyectos');
        $this->ajustarAutoIncrement('proyectos', 'id', $dry);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('✔ Migración de PROYECTOS finalizada ' . ($dry ? '(DRY-RUN)' : ''));
        return self::SUCCESS;
    }

    /* ================== Migración de proyectos ================== */

    protected function migrarProjectsAProyectos(int $fallbackUserId, bool $dry)
    {
        $projects = DB::table('project')
            ->whereBetween('project_id', [33000, 53200])
            ->whereRaw("LOWER(title) NOT REGEXP '\\\\/[[:space:]]*complemento'")
            ->orderBy('project_id')
            ->get();

        foreach ($projects as $p) {
            $usuarioId = (int) ($p->client_id ?? 0);
            $userExiste = DB::table('users')->where('id', $usuarioId)->exists();
            if (!$userExiste) {
                $this->warn(" - project_id={$p->project_id} sin users.id={$usuarioId}, usando fallback={$fallbackUserId}");
                $usuarioId = $fallbackUserId;
            }

            $nombre = trim((string) $p->title);
            if ($nombre === '') $nombre = 'Proyecto ' . $p->project_id;
            $nombre = mb_substr($nombre, 0, 255);

            $descripcion = $this->buildDescripcionProyecto($p);

            [$fechaCreacionDT, , , $fechaEntregaDT] = $this->mapFechas($p);
            $minTS = Carbon::create(1970, 1, 1, 0, 0, 1, 'UTC');

            $fechaCreacion = null;
            if ($fechaCreacionDT && $fechaCreacionDT->greaterThanOrEqualTo($minTS)) {
                $fechaCreacion = $fechaCreacionDT->toDateTimeString();
            }
            $fechaEntrega = $fechaEntregaDT ? $fechaEntregaDT->toDateString() : null;

            $estado = $this->mapEstado($p);

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

            $existe = DB::table('proyectos')->where('id', $row['id'])->exists();
            if ($existe) {
                $this->warn(" - Ya existe proyectos.id={$row['id']} — se omite");
                continue;
            }

            if ($dry) {
                $this->line(" + [DRY] Insertaría proyecto {$row['id']} y su chat + mensaje inicial");
                continue;
            }

            // Resuelve nombre legacy del archivo con FALLOBACK project_file (por proyecto y global).
            $archivoLegacy = $this->resolverNombreLegacyArchivo(
                legacyProjectId: (int)$p->project_id,
                nombreArchivoCampoArchivo: (string)($p->archivo ?? '')
            );

            DB::transaction(function () use ($row, $usuarioId, $fechaCreacion, $archivoLegacy) {

                DB::table('proyectos')->insert($row);
                $this->line(" + Insertado proyectos.id={$row['id']} usuario_id={$row['usuario_id']} estado={$row['estado']}");

                // 1.1) Archivo de diseño (con fallback implementado)
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

                // 3) Mensaje inicial
                DB::table('mensajes_chat')->insert([
                    'chat_id'    => $chatId,
                    'usuario_id' => 9002,
                    'tipo'       => 2,
                    'mensaje'    => "Chat creado automáticamente durante la migración.\nEl Proyecto #{$row['id']} — {$row['nombre']} requiere reconfiguración para crear pedidos.",
                    'fecha_envio'=> now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->line("   + Mensaje inicial insertado en mensajes_chat (chat_id={$chatId})");
            });
        }
    }

    /* ============ REGLA pedida: resolver nombre del archivo ============ */

    /**
     * Si $nombreArchivoCampoArchivo está vacío o "0":
     *  1) Busca en project_file el registro con MAYOR project_file_id para ese project_id.
     *  2) Si no hay, toma el MAYOR project_file_id GLOBAL.
     * Retorna string (puede ser vacío si no hay nada).
     */
    protected function resolverNombreLegacyArchivo(int $legacyProjectId, string $nombreArchivoCampoArchivo): string
    {
        $nombreArchivoCampoArchivo = trim($nombreArchivoCampoArchivo);

        if ($nombreArchivoCampoArchivo !== '' && $nombreArchivoCampoArchivo !== '0') {
            return $nombreArchivoCampoArchivo;
        }

        // 1) Último archivo del MISMO proyecto en project_file
        $porProyecto = DB::table('project_file')
            ->where('project_id', $legacyProjectId)
            ->orderByDesc('project_file_id')
            ->value('name');

        if (is_string($porProyecto) && trim($porProyecto) !== '') {
            $this->line("   = Fallback project_file (por proyecto): {$porProyecto}");
            return trim($porProyecto);
        }

        // 2) Último archivo GLOBAL (mayor project_file_id)
        $global = DB::table('project_file')
            ->orderByDesc('project_file_id')
            ->value('name');

        if (is_string($global) && trim($global) !== '') {
            $this->line("   = Fallback project_file (GLOBAL): {$global}");
            return trim($global);
        }

        // Nada encontrado
        $this->warn("   ! Sin archivo legacy para project_id={$legacyProjectId}");
        return '';
    }

    /**
     * Inserta un registro en archivos_proyecto usando $nombreLegacy (ya resuelto con fallback).
     * No mueve archivos físicos; sólo registra metadatos.
     */
    protected function insertArchivoDisenoDesdeLegacy(int $proyectoId, int $usuarioId, string $nombreLegacy): void
    {
        $nombreLegacy = trim($nombreLegacy);
        if ($nombreLegacy === '' || $nombreLegacy === '0') {
            // Nada que registrar
            return;
        }

        $nombre = mb_substr($nombreLegacy, 0, 255);

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

        $ruta = "legacy/proyectos/{$proyectoId}/{$nombre}";

        $ext  = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        $map  = [
            'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','svg'=>'image/svg+xml',
            'ai'=>'application/postscript','psd'=>'image/vnd.adobe.photoshop',
            'pdf'=>'application/pdf','zip'=>'application/zip',
        ];
        $mime = $map[$ext] ?? 'application/octet-stream';

        try {
            if (class_exists(\App\Models\ArchivoProyecto::class) &&
                method_exists(\App\Models\ArchivoProyecto::class, 'calcularVersion')) {
                $version = \App\Models\ArchivoProyecto::calcularVersion($proyectoId);
            } else {
                $version = (int) DB::table('archivos_proyecto')->where('proyecto_id', $proyectoId)->count() + 1;
            }
        } catch (\Throwable $e) {
            $version = 1;
        }

        DB::table('archivos_proyecto')->insert([
            'proyecto_id'     => $proyectoId,
            'usuario_id'      => $usuarioId,
            'nombre_archivo'  => $nombre,
            'ruta_archivo'    => $ruta,
            'tipo_archivo'    => $mime,
            'tipo_carga'      => 1,
            'version'         => $version,
            'flag_can_delete' => 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->line("   + Archivo de diseño registrado para proyecto {$proyectoId}: {$nombre}");
    }

    /* ================== demás helpers de tu script ================== */

    protected function ajustarAutoIncrement(string $table, string $pk, bool $dry)
    {
        $max = DB::table($table)->max($pk);
        if (!$max) return;
        $sql = "ALTER TABLE `{$table}` AUTO_INCREMENT = " . ((int)$max + 1);
        if ($dry) $this->line("[DRY] {$sql}");
        else      DB::statement($sql);
    }

    protected function mapEstado($p): string
    {
        $aprobado = isset($p->aprobado) ? (int)$p->aprobado : 0;
        return $aprobado === 0 ? 'PENDIENTE' : 'DISEÑO APROBADO';
    }

    protected function mapFechas($p): array
    {
        $parseDT = function ($val) {
            if (!$val || !is_string($val)) return null;
            $s = trim(str_replace(['.', '/'], '-', $val));
            if (ctype_digit($s)) {
                try { return Carbon::createFromTimestamp((int)$s, 'UTC'); } catch (\Throwable $e) {}
            }
            try { return Carbon::parse($s, 'UTC'); } catch (\Throwable $e) {}
            return null;
        };

        $cre = $parseDT($p->timestamp_start ?? null);
        $ent = $parseDT($p->entrega ?? null);
        return [$cre, null, null, $ent];
    }

    protected function buildDescripcionProyecto($p): string
    {
        $base = trim((string)($p->description ?? ''));
        $desc = $base === '' ? 'Proyecto Legacy' : ($base . ' Proyecto Legacy');

        $catId = isset($p->project_category_id) ? (int)$p->project_category_id : null;
        if (!is_null($catId) && $catId >= 3 && $catId <= 19) return $desc;

        if ($catId === 1) {
            $extra = trim((string)($p->listones ?? ''));
            return $extra !== '' ? $desc.' | Listones: '.mb_substr($extra, 0, 1000) : $desc;
        }
        if ($catId === 2) {
            $extra = trim((string)($p->playeras ?? ''));
            return $extra !== '' ? $desc.' | Playeras: '.mb_substr($extra, 0, 1000) : $desc;
        }
        return $desc;
    }

    /* ========= logging duplicado a archivo ========= */

    protected function initMigrationLog(bool $truncate = true): void
    {
        $this->logFilePath = storage_path('logs/migracion_proyectos_legacy.log');
        $dir = dirname($this->logFilePath);
        if (!File::exists($dir)) File::makeDirectory($dir, 0755, true);

        $header  = "==== MIGRACIÓN PROYECTOS LEGACY ====\n";
        $header .= 'Fecha: ' . now()->toDateTimeString() . "\n";
        $header .= "=====================================\n";
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
}
