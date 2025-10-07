<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectLegacy extends Model
{
    use HasFactory;


    protected $table = 'project';
    protected $primaryKey = 'project_id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'status'               => 'int',
        'project_category_id'  => 'int',
        'client_id'            => 'int',
        'subclient_id'         => 'int',
        'timer_status'         => 'int',
        'total_time_spent'     => 'int',
        'produccion'           => 'int',
        'aprobado'             => 'int',
        'a_horas'              => 'int',
        'client_address_id'    => 'int',
        'hidde'                => 'int',
        'factura'              => 'int',
        'ajuste'               => 'int',
        'cscontrol'            => 'int',
    ];

    public function client()
    {
        return $this->belongsTo(ClientLegacy::class, 'client_id', 'client_id');
    }
}
