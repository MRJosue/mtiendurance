<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{

    public $showModal = false;
    public $userId;


    public function index(){

        return view('user.index');
    }

    public function actions(){

        return view('tables.actions');
    }

}
