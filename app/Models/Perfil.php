<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $table = 'perfil';
    protected $primaryKey = 'idpef';
    public $timestamps = false;

    protected $fillable = ['nompef', 'pagpri'];

    public function personas()
    {
        return $this->hasMany(Persona::class, 'idpef', 'idpef');
    }

    public function paginas()
    {
        return $this->belongsToMany(Pagina::class, 'pagper', 'idpef', 'idpag');
    }

    public function tippar()
    {
        return $this->hasMany(Tippar::class, 'idpef', 'idpef');
    }
}
