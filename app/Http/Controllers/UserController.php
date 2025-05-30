<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{

    public $showModal = false;

    public function index(){

                if (!auth()->user()->hasRole('admin')) {
                    return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
                }

        return view('user.index');
    }

    public function show(User $user){

               if (!auth()->user()->hasRole('admin')) {
                    return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
                }
        return view('user.show',['user' => $user]);
    }

    public function create(){
        
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }
        return view('user.create');
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


}
