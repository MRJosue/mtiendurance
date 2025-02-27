<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'string';

    protected $fillable = [ 'nombre', 'flag_tallas'];


    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
    

}
