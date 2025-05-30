<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PreproyectoUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:zip|max:10240',
        ]);

        $path = $request->file('file')->store('tmp_preproyectos', 'public');

        return response()->json(['path' => $path]);
    }
}
