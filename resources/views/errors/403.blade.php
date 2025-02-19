@extends('layouts.app') {{-- Si usas un layout principal, ajústalo a tu estructura --}}

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
    @auth
        @include('partials.navbar') {{-- Ajusta esto si tu navbar está en otro archivo --}}
    @endauth

    <div class="bg-white p-8 rounded-lg shadow-md text-center">
        <h1 class="text-4xl font-bold text-red-600">403</h1>
        <p class="text-gray-700 text-lg mt-2">No tienes permiso para acceder a esta página.</p>

        @auth
            <a href="{{ url()->previous() }}" class="mt-4 inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Regresar</a>
        @else
            <a href="{{ route('login') }}" class="mt-4 inline-block px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Iniciar sesión</a>
        @endauth
    </div>
</div>
@endsection
