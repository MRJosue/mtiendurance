<?php

namespace App\Livewire\Dashboard\Notificaciones;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Notifications\DatabaseNotification;

class NotificacionesLista extends Component
{
    use WithPagination;

    public $perPage = 10;

    public function marcarComoLeida($id)
    {
        $notificacion = DatabaseNotification::find($id);
        if ($notificacion && !$notificacion->read_at) {
            $notificacion->markAsRead();
        }
    }

    // public function render()
    // {
    //     return view('livewire.dashboard.notificaciones.notificaciones-lista', [
    //         'notificaciones' => DatabaseNotification::latest()->paginate($this->perPage),
    //     ]);
    // }

    
    public function render()
    {
        $query = DatabaseNotification::query()
            ->latest();

        // Verifica si el usuario tiene el permiso
        if (!auth()->user()->can('ver-todas-notificaciones-del-sistema')) {
            $query->where('notifiable_id', auth()->id())
                ->where('notifiable_type', get_class(auth()->user()));
        }

        $notificaciones = $query->paginate(20);

        return view('livewire.dashboard.notificaciones.notificaciones-lista', compact('notificaciones'));
    }
}
