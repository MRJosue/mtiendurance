<?php

namespace App\Livewire\Usuarios;

use App\Models\Ciudad;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\Estado;
use App\Models\Pais;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

class ConfigInicial extends Component
{
    use WithPagination;

    public int $userId;

    /** =======================
     *  Datos del usuario
     *  ======================= */
    public string $name = '';
    public string $email = '';
    public string $rfc = '';
    public string $password = '';
    public string $password_confirmation = '';

    /** =======================
     *  Direcciones Fiscales (listado + modal)
     *  ======================= */
    public string $searchFiscal = '';
    public string $queryFiscal = '';
    public bool $modalFiscal = false;
    public ?int $direccionFiscalId = null;

    public string $f_razon_social = '';
    public string $f_rfc = '';
    public string $f_calle = '';
    public ?int $f_pais_id = null;
    public ?int $f_estado_id = null;
    public ?int $f_ciudad_id = null;
    public string $f_codigo_postal = '';

    public $f_paisesList;
    public $f_estadosList;
    public $f_ciudadesList;

    /** =======================
     *  Direcciones Entrega (listado + modal)
     *  ======================= */
    public string $searchEntrega = '';
    public string $queryEntrega = '';
    public bool $modalEntrega = false;
    public ?int $direccionEntregaId = null;

    public string $e_nombre_contacto = '';
    public string $e_nombre_empresa = '';
    public string $e_calle = '';
    public ?int $e_pais_id = null;
    public ?int $e_estado_id = null;
    public ?int $e_ciudad_id = null;
    public string $e_codigo_postal = '';
    public string $e_telefono = '';

    public $e_paisesList;
    public $e_estadosList;
    public $e_ciudadesList;


    public bool $faltaFiscal = false;
    public bool $faltaEntrega = false;
        
    public function mount(int $userId): void
    {
            $this->userId = $userId;

            $u = Auth::user();
                abort_if(!$u || $u->id !== $this->userId, 403);

                $tipoRol = $this->obtenerTipoRolUsuario($u); // 1=CLIENTE,2=PROVEEDOR,3=STAFF,4=ADMIN

                // ✅ STAFF/ADMIN: no necesitan direcciones -> marcar perfil configurado y salir
                if (in_array($tipoRol, [3, 4], true)) {

                    // evita writes innecesarios
                    if (!$u->flag_perfil_configurado) {
                        $u->flag_perfil_configurado = 1;
                        $u->save();
                    }

                    $this->redirectRoute('dashboard', navigate: true);
                    return;
                }


                $this->name  = $u->name ?? '';
                $this->email = $u->email ?? '';
                $this->rfc   = $u->config['rfc'] ?? '';

                // Catálogos en memoria
                $paises = Pais::orderBy('nombre')->get(['id','nombre']);

                $this->f_paisesList = $paises;
                $this->f_estadosList = collect();
                $this->f_ciudadesList = collect();

                $this->e_paisesList = $paises;
                $this->e_estadosList = collect();
                $this->e_ciudadesList = collect();


                // if ($u->esStaffOAdmin()) {
                //      $this->redirect(route('dashboard'), navigate: true);
                //     return;
                // }

    }

    /** ========= Reglas ========= */
    protected function rulesDatosUsuario(): array
    {
        return [
            'rfc' => ['required','string','max:13'],
            'password' => ['nullable','string','min:8','same:password_confirmation'],
            'password_confirmation' => ['nullable','string','min:8'],
        ];
    }

    protected function rulesFiscal(): array
    {
        return [
            'f_razon_social' => ['required','string','max:255'],
            'f_rfc' => ['required','string','max:13'],
            'f_calle' => ['required','string','max:255'],
            'f_pais_id' => ['required','exists:paises,id'],
            'f_estado_id' => ['required','exists:estados,id'],
            'f_ciudad_id' => ['required','exists:ciudades,id'],
            'f_codigo_postal' => ['required','string','max:10'],
        ];
    }

    protected function rulesEntrega(): array
    {
        return [
            'e_nombre_contacto' => ['required','string','max:255'],
            'e_nombre_empresa' => ['nullable','string','max:255'],
            'e_calle' => ['required','string','max:255'],
            'e_pais_id' => ['required','exists:paises,id'],
            'e_estado_id' => ['required','exists:estados,id'],
            'e_ciudad_id' => ['required','exists:ciudades,id'],
            'e_codigo_postal' => ['required','string','max:10'],
            'e_telefono' => ['nullable','string','max:15'],
        ];
    }

    /** ========= Cascadas: Fiscal ========= */
    public function updatedFPaisId($paisId): void
    {
        $this->f_estado_id = null;
        $this->f_ciudad_id = null;
        $this->f_ciudadesList = collect();

        $this->f_estadosList = $paisId
            ? Estado::where('pais_id', $paisId)->orderBy('nombre')->get(['id','nombre'])
            : collect();
    }

    public function updatedFEstadoId($estadoId): void
    {
        $this->f_ciudad_id = null;

        $this->f_ciudadesList = $estadoId
            ? Ciudad::where('estado_id', $estadoId)->orderBy('nombre')->get(['id','nombre'])
            : collect();
    }

    /** ========= Cascadas: Entrega ========= */
    public function updatedEPaisId($paisId): void
    {
        $this->e_estado_id = null;
        $this->e_ciudad_id = null;
        $this->e_ciudadesList = collect();

        $this->e_estadosList = $paisId
            ? Estado::where('pais_id', $paisId)->orderBy('nombre')->get(['id','nombre'])
            : collect();
    }

    public function updatedEEstadoId($estadoId): void
    {
        $this->e_ciudad_id = null;

        $this->e_ciudadesList = $estadoId
            ? Ciudad::where('estado_id', $estadoId)->orderBy('nombre')->get(['id','nombre'])
            : collect();
    }

    /** ========= Buscar ========= */
    public function buscarFiscal(): void
    {
        $this->searchFiscal = $this->queryFiscal;
        $this->resetPage('fiscalPage');
    }

    public function buscarEntrega(): void
    {
        $this->searchEntrega = $this->queryEntrega;
        $this->resetPage('entregaPage');
    }

    /** ========= Modal Fiscal ========= */
    public function crearFiscal(): void
    {
        $this->limpiarFiscal();
        $this->modalFiscal = true;
    }

    public function editarFiscal(int $id): void
    {
        $d = DireccionFiscal::with(['ciudad.estado.pais'])
            ->where('usuario_id', $this->userId)
            ->findOrFail($id);

        $this->direccionFiscalId = $d->id;
        $this->f_razon_social = $d->razon_social;
        $this->f_rfc = $d->rfc;
        $this->f_calle = $d->calle;
        $this->f_pais_id = $d->pais_id;

        $this->f_estadosList = Estado::where('pais_id', $this->f_pais_id)->orderBy('nombre')->get(['id','nombre']);
        $this->f_estado_id = $d->estado_id;

        $this->f_ciudadesList = Ciudad::where('estado_id', $this->f_estado_id)->orderBy('nombre')->get(['id','nombre']);
        $this->f_ciudad_id = $d->ciudad_id;

        $this->f_codigo_postal = $d->codigo_postal;

        $this->modalFiscal = true;
    }

    public function guardarFiscal(): void
    {
        $this->validate($this->rulesFiscal());

        $data = [
            'usuario_id' => $this->userId,
            'razon_social' => $this->f_razon_social,
            'rfc' => strtoupper(trim($this->f_rfc)),
            'calle' => $this->f_calle,
            'pais_id' => $this->f_pais_id,
            'estado_id' => $this->f_estado_id,
            'ciudad_id' => $this->f_ciudad_id,
            'codigo_postal' => $this->f_codigo_postal,
        ];

        if ($this->direccionFiscalId) {
            DireccionFiscal::where('usuario_id', $this->userId)
                ->findOrFail($this->direccionFiscalId)
                ->update($data);

            $msg = 'Dirección fiscal actualizada.';
        } else {
            DireccionFiscal::create($data);
            $msg = 'Dirección fiscal creada.';
        }

        $this->modalFiscal = false;
        $this->limpiarFiscal();



        $this->dispatch('notify', [
            'title' => 'Listo',
            'description' => $msg,
            'icon' => 'success',
        ]);

        $this->recalcularFaltantesDirecciones();


    }

    public function borrarFiscal(int $id): void
    {
        DireccionFiscal::where('usuario_id', $this->userId)->findOrFail($id)->delete();

        $this->dispatch('notify', [
            'title' => 'Eliminado',
            'description' => 'Dirección fiscal eliminada.',
            'icon' => 'success',
        ]);

        $this->recalcularFaltantesDirecciones();
    }

    public function establecerDefaultFiscal(int $id): void
    {
        DireccionFiscal::where('usuario_id', $this->userId)->update(['flag_default' => false]);
        DireccionFiscal::where('usuario_id', $this->userId)->findOrFail($id)->update(['flag_default' => true]);

        $this->dispatch('notify', [
            'title' => 'Actualizado',
            'description' => 'Dirección fiscal predeterminada actualizada.',
            'icon' => 'success',
        ]);

        $this->recalcularFaltantesDirecciones();
    }

    public function limpiarFiscal(): void
    {
        $this->direccionFiscalId = null;
        $this->f_razon_social = '';
        $this->f_rfc = '';
        $this->f_calle = '';
        $this->f_pais_id = null;
        $this->f_estado_id = null;
        $this->f_ciudad_id = null;
        $this->f_codigo_postal = '';

        $this->f_estadosList = collect();
        $this->f_ciudadesList = collect();
        $this->resetValidation();
    }

    /** ========= Modal Entrega ========= */
    public function crearEntrega(): void
    {
        $this->limpiarEntrega();
        $this->modalEntrega = true;
    }

    public function editarEntrega(int $id): void
    {
        $d = DireccionEntrega::with(['ciudad.estado.pais'])
            ->where('usuario_id', $this->userId)
            ->findOrFail($id);

        $this->direccionEntregaId = $d->id;
        $this->e_nombre_contacto = $d->nombre_contacto;
        $this->e_nombre_empresa = $d->nombre_empresa;
        $this->e_calle = $d->calle;

        $this->e_pais_id = $d->pais_id;
        $this->e_estadosList = Estado::where('pais_id', $this->e_pais_id)->orderBy('nombre')->get(['id','nombre']);
        $this->e_estado_id = $d->estado_id;

        $this->e_ciudadesList = Ciudad::where('estado_id', $this->e_estado_id)->orderBy('nombre')->get(['id','nombre']);
        $this->e_ciudad_id = $d->ciudad_id;

        $this->e_codigo_postal = $d->codigo_postal;
        $this->e_telefono = $d->telefono;

        $this->modalEntrega = true;
    }

    public function guardarEntrega(): void
    {
        $this->validate($this->rulesEntrega());

        $data = [
            'usuario_id' => $this->userId,
            'nombre_contacto' => $this->e_nombre_contacto,
            'nombre_empresa' => $this->e_nombre_empresa,
            'calle' => $this->e_calle,
            'pais_id' => $this->e_pais_id,
            'estado_id' => $this->e_estado_id,
            'ciudad_id' => $this->e_ciudad_id,
            'codigo_postal' => $this->e_codigo_postal,
            'telefono' => $this->e_telefono,
        ];

        if ($this->direccionEntregaId) {
            DireccionEntrega::where('usuario_id', $this->userId)
                ->findOrFail($this->direccionEntregaId)
                ->update($data);

            $msg = 'Dirección de entrega actualizada.';
        } else {
            DireccionEntrega::create($data);
            $msg = 'Dirección de entrega creada.';
        }

        $this->modalEntrega = false;
        $this->limpiarEntrega();

        $this->dispatch('notify', [
            'title' => 'Listo',
            'description' => $msg,
            'icon' => 'success',
        ]);

        $this->recalcularFaltantesDirecciones();
    }

    public function borrarEntrega(int $id): void
    {
        DireccionEntrega::where('usuario_id', $this->userId)->findOrFail($id)->delete();

        $this->dispatch('notify', [
            'title' => 'Eliminado',
            'description' => 'Dirección de entrega eliminada.',
            'icon' => 'success',
        ]);

        $this->recalcularFaltantesDirecciones();
    }

    public function establecerDefaultEntrega(int $id): void
    {
        DireccionEntrega::where('usuario_id', $this->userId)->update(['flag_default' => false]);
        DireccionEntrega::where('usuario_id', $this->userId)->findOrFail($id)->update(['flag_default' => true]);

        $this->dispatch('notify', [
            'title' => 'Actualizado',
            'description' => 'Dirección de entrega predeterminada actualizada.',
            'icon' => 'success',
        ]);
        $this->recalcularFaltantesDirecciones();
    }

    public function limpiarEntrega(): void
    {
        $this->direccionEntregaId = null;
        $this->e_nombre_contacto = '';
        $this->e_nombre_empresa = '';
        $this->e_calle = '';
        $this->e_pais_id = null;
        $this->e_estado_id = null;
        $this->e_ciudad_id = null;
        $this->e_codigo_postal = '';
        $this->e_telefono = '';

        $this->e_estadosList = collect();
        $this->e_ciudadesList = collect();
        $this->resetValidation();
    }

    /** ========= Guardar y continuar (único botón) ========= */
    public function guardarYContinuar(): void
    {
        $u = Auth::user();
        abort_if(!$u || $u->id !== $this->userId, 403);

        $tipoRol = $this->obtenerTipoRolUsuario($u);

        if (in_array($tipoRol, [3, 4], true)) {
            if (!$u->flag_perfil_configurado) {
                $u->flag_perfil_configurado = 1;
                $u->save();
            }
            $this->redirectRoute('dashboard', navigate: true);
            return;
        }

        $this->validate($this->rulesDatosUsuario());

        $cfg = $u->config ?? [];
        $cfg['rfc'] = strtoupper(trim($this->rfc));
        $u->config = $cfg;

        // ✅ Este es el campo real en la tabla users
        $u->flag_perfil_configurado = 1;

        if (!empty($this->password)) {
            $u->password = Hash::make($this->password);
        }

        $u->save();




        $this->recalcularFaltantesDirecciones();

        if ($this->faltaFiscal) {
            $this->addError('direcciones_fiscal', 'Agrega al menos una dirección fiscal.');
        }

        if ($this->faltaEntrega) {
            $this->addError('direcciones_entrega', 'Agrega al menos una dirección de entrega.');
        }

        if ($this->getErrorBag()->hasAny(['direcciones_fiscal', 'direcciones_entrega'])) {
            $this->dispatch('notify', [
                'title' => 'Falta información',
                'description' => 'Completa las direcciones para continuar.',
                'icon' => 'warning',
            ]);

            return;
        }
        
        $this->dispatch('notify', [
            'title' => 'Guardado',
            'description' => 'Configuración inicial completada.',
            'icon' => 'success',
        ]);


        // Redirige a donde tú quieras
        $this->redirect(route('dashboard'), navigate: true);
    }

    private function recalcularFaltantesDirecciones(): void
    {
        $this->faltaFiscal = !DireccionFiscal::where('usuario_id', $this->userId)->exists();
        $this->faltaEntrega = !DireccionEntrega::where('usuario_id', $this->userId)->exists();
    }

    private function obtenerTipoRolUsuario($u): ?int
    {
        // Prioridad: users.rol_id
        if (!empty($u->rol_id)) {
            $tipo = DB::table('roles')->where('id', $u->rol_id)->value('tipo');
            return $tipo !== null ? (int)$tipo : null;
        }

        // Fallback: roles de Spatie (model_has_roles)
        $tipo = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', $u::class)
            ->where('model_has_roles.model_id', $u->id)
            ->orderByDesc('roles.tipo')
            ->value('roles.tipo');

        return $tipo !== null ? (int)$tipo : null;
    }



    public function render()
    {
        $qFiscal = DireccionFiscal::where('usuario_id', $this->userId);
        if ($this->searchFiscal !== '') {
            $qFiscal->where('rfc', 'like', '%' . $this->searchFiscal . '%');
        }

        $qEntrega = DireccionEntrega::where('usuario_id', $this->userId);
        if ($this->searchEntrega !== '') {
            $qEntrega->where('nombre_contacto', 'like', '%' . $this->searchEntrega . '%');
        }

        return view('livewire.usuarios.config-inicial', [
            'direccionesFiscales' => $qFiscal->with(['ciudad.estado.pais'])
                ->orderByDesc('created_at')
                ->paginate(5, ['*'], 'fiscalPage'),

            'direccionesEntrega' => $qEntrega->with(['ciudad.estado.pais'])
                ->orderByDesc('created_at')
                ->paginate(10, ['*'], 'entregaPage'),
        ]);
    }
}
