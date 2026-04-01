<?php

use App\Events\MessageSent;
use App\Events\NewChatMessage;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoController;
use App\Models\MensajeChat;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::view('/reverb-test', 'reverb-test')->name('reverb.test');

Route::post('/reverb-test/send', function (Request $request) {
    $data = $request->validate([
        'message' => ['required', 'string', 'max:500'],
    ]);

    $messages = collect(Cache::get('reverb_test_messages', []));
    $nextId = (int) ($messages->max('id') ?? 0) + 1;

    $message = [
        'id' => $nextId,
        'message' => $data['message'],
        'sentAt' => now()->toDateTimeString(),
    ];

    $messages->push($message);

    Cache::forever('reverb_test_messages', $messages->take(-50)->values()->all());

    return response()->json([
        'ok' => true,
        'message' => $message,
    ]);
})->name('reverb.test.send');

Route::get('/reverb-test/messages', function (Request $request) {
    $after = (int) $request->integer('after', 0);
    $messages = collect(Cache::get('reverb_test_messages', []))
        ->filter(fn (array $message) => (int) ($message['id'] ?? 0) > $after)
        ->values();

    return response()->json([
        'messages' => $messages,
        'serverTime' => Carbon::now()->toDateTimeString(),
    ]);
})->name('reverb.test.messages');

Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['es', 'en']), 404);

    session(['locale' => $locale]);
    Cookie::queue('locale', $locale, 60 * 24 * 365);

    if (auth()->check() && \Schema::hasColumn('users', 'locale')) {
        auth()->user()->forceFill(['locale' => $locale])->save();
    }

    return back();
})->name('lang.switch');

Route::get('/notificacion', [DemoController::class, 'mostrarNotificacion']);

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('auth.login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'perfil.configurado'])
    ->name('dashboard');

Route::get('/debug/error-preview', function () {
    return response()->view('errors.404', [
        'errorMessage' => 'Esta es una vista de prueba para revisar la pantalla amigable de error sin cerrar la sesion.',
    ], 404);
})->middleware(['auth', 'verified', 'perfil.configurado'])
    ->name('debug.error-preview');

Route::get('/debug/error-simulado', function () {
    throw new \RuntimeException('Error simulado para probar la pantalla amigable.');
})->middleware(['auth', 'verified', 'perfil.configurado'])
    ->name('debug.error-simulado');

Route::get('/MessageSent', function () {
    event(new MessageSent('¡Hola desde el servidor!'));

    return 'Evento emitido.';
});

Route::get('/emitir-demo', function () {
    broadcast(new class('Hola desde canal-demo') implements \Illuminate\Contracts\Broadcasting\ShouldBroadcastNow {
        public $mensaje;

        public function __construct($mensaje)
        {
            $this->mensaje = $mensaje;
        }

        public function broadcastOn()
        {
            return new \Illuminate\Broadcasting\Channel('canal-demo');
        }

        public function broadcastAs()
        {
            return 'evento.demo';
        }
    });

    return 'Evento emitido.';
});

Route::view('/demo', 'demo');

Route::get('/ChatMessageTest', function () {
    $mensaje = MensajeChat::create([
        'chat_id' => 4,
        'usuario_id' => 1,
        'mensaje' => 'Este es un mensaje de prueba desde Tinker.',
    ]);

    event(new NewChatMessage($mensaje));

    return 'Evento Chat emitido.';
});
