<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function mostrarNotificacion()
    {
        notify()->success('Operación exitosa', '¡Bien hecho!');
        return redirect()->back();
    }
    
}
