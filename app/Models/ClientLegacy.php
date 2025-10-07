<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientLegacy extends Model
{
    protected $table = 'client';
    protected $primaryKey = 'client_id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'super'    => 'int',
        'super_id' => 'int',
        'aprobar'  => 'int',
    ];

    // Relación con proyectos legacy
    public function projects()
    {
        return $this->hasMany(ProjectLegacy::class, 'client_id', 'client_id');
    }


    use HasFactory;
}
