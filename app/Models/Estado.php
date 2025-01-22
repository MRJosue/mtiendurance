<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $fillable = ['nombre','pais_id'];

    public function ciudades()
    {
        return $this->hasMany(Ciudad::class);
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }

 
}
