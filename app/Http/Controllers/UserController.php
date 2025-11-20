<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{

    public $showModal = false;

    public function index(){

                // if (!auth()->user()->hasRole('admin')) {
                //     return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
                // }

        return view('user.index');
    }

    public function show(User $user){

                //    if (!auth()->user()->hasRole('admin')) {
                //         return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
                //     }

         return view('user.show',['user' => $user]);
    }


    public function create(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 1 = CLIENTE, 2 = PROVEEDOR, 3 = STAFF, 4 = ADMIN
        $tipo = (int) $request->get('tipo', 1);
        if (!in_array($tipo, [1, 2, 3, 4], true)) {
            $tipo = 1;
        }

        // Mapea tipo → permiso requerido
        $permisoPorTipo = [
            1 => 'usuarios.crear.cliente',
            2 => 'usuarios.crear.proveedor',
            3 => 'usuarios.crear.staff',
            4 => 'usuarios.crear.admin',
        ];

        $permisoNecesario = $permisoPorTipo[$tipo] ?? null;

        // Si no hay permiso mapeado o no lo tiene, lo regresas a la lista
        if (!$permisoNecesario || !$user->can($permisoNecesario)) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'No tienes permiso para crear este tipo de usuario.');
        }

        return view('user.create', compact('tipo'));
    }

    public function actions(){

                if (!auth()->user()->hasRole('admin')) {
                    return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
                }

        return view('tables.actions');
    }


    public function getusersselect(Request $request)
    {
            $search = $request->input('search');

        return User::query()
            ->select('id', 'name')
            ->where(function ($q) {
                $q->whereJsonContains('config->flag-user-sel-preproyectos', true);
            })
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->limit(15)
            ->get();
    }

    public function getusersselectpreproyecto(Request $request)
    {
            $search = $request->input('search');

            // Usuario autenticado
            $user = auth()->user();

            // Asegura que el usuario está autenticado
            if (!$user) {
                return response()->json([], 401);
            }

            // Accede al campo config y verifica el flag
            $config = $user->config ?? [];
            $puedeSeleccionar = $config['flag-can-user-sel-preproyectos'] ?? false;

            if (!$puedeSeleccionar) {
                return response()->json([]); // No tiene permisos
            }

            // IDs autorizados
            $idsPermitidos = $user->user_can_sel_preproyectos ?? [];


        return User::query()
            ->select('id', 'name')
            ->whereIn('id', $idsPermitidos)
            ->where(function ($q) {
                $q->whereJsonContains('config->flag-user-sel-preproyectos', true);
            })
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->limit(15)
            ->get();
    }


    public function showclientes(){

                // if (!auth()->user()->hasRole('admin')) {
                //     return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
                // }

        return view('user.show.clientes');
    }

    public function showcproveedor(){

                // if (!auth()->user()->hasRole('admin')) {
                //     return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
                // }

        return view('user.show.proveedor');
    }

    public function showstaff(){

                // if (!auth()->user()->hasRole('admin')) {
                //     return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
                // }

        return view('user.show.staff');
    }


    public function showadmin(){

                // if (!auth()->user()->hasRole('admin')) {
                //     return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
                // }

        return view('user.show.admin');
    }




}
