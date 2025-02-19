<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Events\MessageSent;


class DashboardController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasAnyRole(['Cliente', 'admin'])) {
            abort(403, 'No tienes acceso a esta sección.');
        }
    
    
        return view('dashboard');
    }

    public function sendMessage(Request $request)
    {
        $message = $request->input('message');
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Message sent!']);
    }
}