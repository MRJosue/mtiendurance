<?php

use App\Http\Controllers\importacioncontroller;
use App\Http\Controllers\permisoscontroller;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/usuarios/clientes', [UserController::class, 'showclientes'])
    ->middleware(['auth', 'verified'])
    ->name('usuarios.clientes');

Route::get('/usuarios/proveedor', [UserController::class, 'showcproveedor'])
    ->middleware(['auth', 'verified'])
    ->name('usuarios.proveedor');

Route::get('/usuarios/staff', [UserController::class, 'showstaff'])
    ->middleware(['auth', 'verified'])
    ->name('usuarios.staff');

Route::get('/usuarios/admin', [UserController::class, 'showadmin'])
    ->middleware(['auth', 'verified'])
    ->name('usuarios.admin');

Route::get('/usuarios', [UserController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('usuarios.index');

Route::get('/usuarios/crear', [UserController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('usuarios.create');

Route::get('/usuarios/detalles/{user}', [UserController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('usuarios.show');

Route::get('/usuarios/editar/{user}', [UserController::class, 'edit'])
    ->middleware(['auth', 'verified'])
    ->name('usuarios.edit');

Route::get('/usuarios/modal', [UserController::class, 'actions'])
    ->middleware(['auth', 'verified'])
    ->name('usuarios.actions');

Route::get('/importacion/proyectos', [importacioncontroller::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('importacion.proyectos.index');

Route::get('/usuarios/permisos', [permisoscontroller::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('permisos.index');

Route::get('/usuarios/empresas', [permisoscontroller::class, 'showempresas'])
    ->middleware(['auth', 'verified'])
    ->name('permisos.empresas');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/users/appi', [UserController::class, 'getusersselect'])->name('api.users.index');
    Route::get('/users/appi/preproyecto', [UserController::class, 'getusersselectpreproyecto'])->name('api.users.preproyecto.index');

    Route::get('/api/users/index', function (Request $request) {
        $search = (string) $request->input('search', '');

        $q = User::query()->select('id', 'name', 'email');

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $q->limit(15)->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => "{$u->name} ({$u->email})",
            ]);
    })->name('api.users.index')->middleware('auth');
});
