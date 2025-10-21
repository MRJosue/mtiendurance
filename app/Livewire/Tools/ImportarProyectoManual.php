<?php
namespace App\Livewire\Tools;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ImportarProyectoManual extends Component
{
    // Búsqueda
    #[Validate('nullable|string|max:255')]
    public string $search = '';

    // Resultados y selección
    public array $results = [];           // items básicos para la tabla
    public ?int $selectedLegacyId = null; // project.project_id seleccionado
    public array $preview = [];           // fila completa + flags (duplicado/usuario)

    // Opciones
    #[Validate('required|integer|min:1')]
    public int $fallbackUserId = 1;       // similar a --adminId en tu comando

    public bool $dryRun = false;          // Simulación
    public bool $confirmModal = false;    // Confirmación antes de importar

    public function mount(): void
    {
        $this->results = [];
        $this->preview = [];
    }

    /** Búsqueda por ID exacto o título LIKE */
    public function buscar(): void
    {
        $q = trim($this->search);

        $query = DB::table('project')->select([
            'project_id', 'client_id', 'title', 'description', 'aprobado', 'status',
            'timestamp_start', 'entrega',
        ]);

        if ($q !== '') {
            if (ctype_digit($q)) {
                $query->where('project_id', (int) $q);
            } else {
                $query->where('title', 'like', '%' . $q . '%');
            }
        } else {
            // Si no hay query, trae un pequeño rango reciente para no saturar
            $query->orderByDesc('project_id')->limit(25);
        }

        $rows = $query->orderByDesc('project_id')->limit(50)->get();

        $this->results = $rows->map(function ($r) {
            $dup = DB::table('proyectos')->where('id', (int) $r->project_id)->exists();
            $userExists = DB::table('users')->where('id', (int) $r->client_id)->exists();
            return [
                'project_id' => (int) $r->project_id,
                'client_id'  => (int) $r->client_id,
                'title'      => (string) ($r->title ?? ''),
                'aprobado'   => (int) ($r->aprobado ?? 0),
                'status'     => is_null($r->status) ? null : (int) $r->status,
                'duplicado'  => $dup,
                'usuario'    => $userExists,
            ];
        })->toArray();

        $this->dispatch('toast', [
            'type' => 'info',
            'message' => count($this->results) ? 'Resultados actualizados.' : 'Sin resultados.',
        ]);
    }

    /** Selecciona un project y carga preview con validaciones */
    public function seleccionar(int $projectId): void
    {
        $p = DB::table('project')->where('project_id', $projectId)->first();
        if (!$p) {
            $this->selectedLegacyId = null;
            $this->preview = [];
            $this->dispatch('toast', ['type' => 'error', 'message' => "Project {$projectId} no encontrado."]);
            return;
        }

        $dup = DB::table('proyectos')->where('id', (int) $p->project_id)->exists();
        $userExists = DB::table('users')->where('id', (int) $p->client_id)->exists();

        [$fechaCreacionDT, , , $fechaEntregaDT] = $this->mapFechas($p);

        $this->selectedLegacyId = (int) $p->project_id;
        $this->preview = [
            'project_id'   => (int) $p->project_id,
            'client_id'    => (int) $p->client_id,
            'title'        => trim((string) ($p->title ?? '')),
            'description'  => trim((string) ($p->description ?? '')),
            'aprobado'     => (int) ($p->aprobado ?? 0),
            'status'       => is_null($p->status) ? null : (int) $p->status,
            'fecha_creacion' => $fechaCreacionDT?->toDateTimeString(),
            'fecha_entrega'  => $fechaEntregaDT?->toDateString(),
            //'estado_mapeado' => $this->mapEstado($p),
            'estado_final'   => 'DISEÑO APROBADO', 
            'duplicado'    => $dup,
            'usuario'      => $userExists,
        ];
    }

    /** Abre modal de confirmación si pasa validaciones básicas */
    public function confirmar(): void
    {
        if (!$this->selectedLegacyId || empty($this->preview)) {
            throw ValidationException::withMessages(['general' => 'Seleccione un proyecto a importar.']);
        }

        if ($this->preview['duplicado'] === true) {
            throw ValidationException::withMessages(['general' => "Ya existe proyectos.id={$this->preview['project_id']}."]);
        }

        if ($this->preview['usuario'] !== true && !$this->fallbackUserId) {
            throw ValidationException::withMessages(['fallbackUserId' => 'No existe el usuario asignado. Define un fallback válido.']);
        }

        $this->confirmModal = true;
    }

    /** Importa el registro (o simula) */
    public function importar(): void
    {
        if (!$this->selectedLegacyId || empty($this->preview)) {
            $this->confirmModal = false;
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Nada que importar.']);
            return;
        }

        $p = DB::table('project')->where('project_id', $this->selectedLegacyId)->first();
        if (!$p) {
            $this->confirmModal = false;
            $this->dispatch('toast', ['type' => 'error', 'message' => 'El proyecto ya no existe en legacy.']);
            return;
        }

        $usuarioId = (int) $p->client_id;
        $userExists = DB::table('users')->where('id', $usuarioId)->exists();
        if (!$userExists) {
            $usuarioId = (int) $this->fallbackUserId;
            $userExists = DB::table('users')->where('id', $usuarioId)->exists();
            if (!$userExists) {
                $this->confirmModal = false;
                throw ValidationException::withMessages(['fallbackUserId' => "Fallback user id={$usuarioId} no existe."]);
            }
        }

        // Mapear campos como en tu comando
        [$fechaCreacionDT, , , $fechaEntregaDT] = $this->mapFechas($p);

        $nombre = trim((string) ($p->title ?? ''));
        if ($nombre === '') $nombre = 'Proyecto ' . (int) $p->project_id;
        $nombre = mb_substr($nombre, 0, 255);

        $descripcion = ($p->description ? trim((string)$p->description).' ' : '') . 'Proyecto Legacy';

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
            'estado'               => 'DISEÑO APROBADO', 
            'fecha_produccion'      => null,
            'fecha_embarque'        => null,
            'fecha_entrega'         => $fechaEntregaDT?->toDateString(),
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

        // Duplicado último momento
        if (DB::table('proyectos')->where('id', $row['id'])->exists()) {
            $this->confirmModal = false;
            $this->dispatch('toast', ['type' => 'warning', 'message' => "proyectos.id={$row['id']} ya existe (verificación final)."]);
            return;
        }

        if ($this->dryRun) {
            $this->confirmModal = false;
            $this->dispatch('toast', [
                'type' => 'info',
                'message' => "DRY-RUN: Insertaría proyecto {$row['id']} (usuario_id={$row['usuario_id']}).",
            ]);
            return;
        }

        DB::transaction(function () use ($row, $fechaCreacionDT) {
            // 1) Proyecto
            DB::table('proyectos')->insert($row);

            // 2) Chat
            $chatFecha = $fechaCreacionDT?->toDateTimeString() ?? now()->toDateTimeString();
            $chatId = DB::table('chats')->insertGetId([
                'proyecto_id'    => $row['id'],
                'fecha_creacion' => $chatFecha,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // 3) Mensaje inicial
            DB::table('mensajes_chat')->insert([
                'chat_id'    => $chatId,
                'usuario_id' => 9002,      // ajusta si lo necesitas dinámico
                'tipo'       => 2,
                'mensaje'    => "Chat creado automáticamente durante la importación manual.\nEl Proyecto #{$row['id']} — {$row['nombre']} requiere reconfiguración para crear pedidos.",
                'fecha_envio'=> now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $this->confirmModal = false;
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Importado proyectos.id={$row['id']} correctamente.",
        ]);

        // Refrescar flags de la fila seleccionada
        $this->seleccionar($row['id']);
        $this->buscar();
    }

    /** === Helpers (mapeos) ================ */

    protected function mapEstado($p): string
    {
        if ((int) ($p->aprobado ?? 0) === 1) {
            return 'DISEÑO APROBADO';
        }
        $status = is_null($p->status) ? null : (int)$p->status;
        return match($status) {
            1 => 'ASIGNADO',
            2 => 'EN PROCESO',
            3 => 'REVISION',
            4 => 'DISEÑO RECHAZADO',
            5 => 'CANCELADO',
            0, null => 'PENDIENTE',
            default => 'PENDIENTE',
        };
    }

    protected function mapFechas($p): array
    {
        $parseDT = function ($val) {
            if (!$val || !is_string($val)) return null;
            $s = trim(str_replace(['.', '/'], '-', $val));
            if (ctype_digit($s)) {
                try {
                    return Carbon::createFromTimestamp((int)$s, 'UTC');
                } catch (\Throwable $e) {}
            }
            try {
                return Carbon::parse($s, 'UTC');
            } catch (\Throwable $e) {}
            return null;
        };

        $cre = $parseDT($p->timestamp_start ?? null);
        $ent = $parseDT($p->entrega ?? null);

        return [$cre, null, null, $ent];
    }

    public function render()
    {
        return view('livewire.tools.importar-proyecto-manual');
    }
}



// namespace App\Livewire\Tools;

// use Livewire\Component;

// class ImportarProyectoManual extends Component
// {
//     public function render()
//     {
//         return view('livewire.tools.importar-proyecto-manual');
//     }
// }
