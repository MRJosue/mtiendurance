<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MensajeChat extends Model
{
    use HasFactory;

    protected $table = 'mensajes_chat';

    protected $casts = [
        'fecha_envio' => 'datetime',
    ];


    protected $fillable = [
        'chat_id',
        'usuario_id',
        'mensaje',
        'fecha_envio',
    ];



    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
