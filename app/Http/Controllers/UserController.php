<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{

    public $showModal = false;

    public function index(){

        return view('user.index');
    }

    public function show(User $user){

       
        return view('user.show',['user' => $user]);
    }

    public function actions(){

        return view('tables.actions');
    }

}
