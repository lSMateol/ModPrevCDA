<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubica extends Model
{
    protected $table = 'ubica';
    protected $primaryKey = 'codubi';
    public $timestamps = false;

    protected $fillable = ['nomubi', 'depubi'];

    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'codubi', 'codubi');
    }

    public function personas()
    {
        return $this->hasMany(Persona::class, 'codubi', 'codubi');
    }
}
