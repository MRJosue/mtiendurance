<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Este archivo actua como punto de entrada para las rutas web y delega
| cada modulo a archivos mas pequenos dentro de routes/web.
|
*/

require __DIR__ . '/web/public.php';
require __DIR__ . '/web/profile.php';
require __DIR__ . '/web/proyectos.php';
require __DIR__ . '/web/pedidos.php';
require __DIR__ . '/web/preproyectos.php';
require __DIR__ . '/web/usuarios.php';
require __DIR__ . '/web/disenio.php';
require __DIR__ . '/web/produccion.php';
require __DIR__ . '/web/catalogos.php';
require __DIR__ . '/web/admin.php';
require __DIR__ . '/auth.php';
