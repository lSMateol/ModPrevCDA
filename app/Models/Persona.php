<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'persona';
    protected $primaryKey = 'idper';
    public $timestamps = false;

    protected $fillable = [
        'ndocper', 'tdocper', 'nomper', 'apeper', 'dirper', 'ciuper',
        'telper', 'codubi', 'idpef', 'pass', 'emaper',
        'idemp', 'nliccon', 'fvencon', 'catcon', 'actper'
    ];

    protected $hidden = ['pass'];

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'idpef', 'idpef');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubica::class, 'codubi', 'codubi');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'idemp', 'idemp');
    }

    public function vehiculos()
    {
        return $this->belongsToMany(Vehiculo::class, 'proveh', 'idper', 'idveh');
    }

    public function diagnosticos()
    {
        return $this->hasMany(Diag::class, 'idper', 'idper');
    }

    public function vehiculosConducidos()
    {
        return $this->hasMany(Vehiculo::class, 'cond', 'idper');
    }

    public function vehiculosPropios()
    {
        return $this->hasMany(Vehiculo::class, 'prop', 'idper');
    }

    public function diapar()
    {
        return $this->hasMany(Diapar::class, 'idper', 'idper');
    }

    public function getNombreCompletoAttribute()
    {
        return "{$this->nomper} {$this->apeper}";
    }
}
