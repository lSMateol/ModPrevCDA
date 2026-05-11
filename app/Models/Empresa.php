<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresa';
    protected $primaryKey = 'idemp';
    public $timestamps = false;

    protected $fillable = [
        'nonitem', 'razsoem', 'abremp', 'direm', 'ciudeem', 'telem', 'idpef',
        'emaem', 'nomger', 'usuaemp', 'passemp', 'codcons', 'codubi'
    ];

    protected $hidden = ['passemp'];

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'idpef', 'idpef');
    }
    public function ubicacion()
    {
        return $this->belongsTo(Ubica::class, 'codubi', 'codubi');
    }

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'idemp', 'idemp');
    }

    public function personas()
    {
        return $this->hasMany(Persona::class, 'idemp', 'idemp');
    }
}
