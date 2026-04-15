<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = 'marca';
    protected $primaryKey = 'idmar';
    public $timestamps = false;

    protected $fillable = ['nommarlin', 'depmar'];

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'linveh', 'idmar');
    }
}
