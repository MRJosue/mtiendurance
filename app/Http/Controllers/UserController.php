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

}
