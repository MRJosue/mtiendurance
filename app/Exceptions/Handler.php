<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if (! app()->isProduction() && ! config('app.force_friendly_error_pages', false)) {
                return null;
            }

            if ($request->expectsJson() || $request->ajax() || $request->header('X-Livewire')) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            if ($status < 500) {
                return null;
            }

            return response()->view('errors.404', [
                'errorMessage' => 'Ocurrio un problema inesperado. Puedes continuar usando tu sesion y la navegacion.',
            ], 404);
        });
    }
}
