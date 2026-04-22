<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $table = 'vehiculo';
    protected $primaryKey = 'idveh';
    public $timestamps = false;

    protected $fillable = [
        'nordveh', 'tipoveh', 'tipo_servicio', 'placaveh', 'linveh', 'modveh', 'paiveh',
        'fmatv', 'idemp', 'capveh', 'clveh', 'crgveh', 'combuveh',
        'cilveh', 'lictraveh', 'colveh', 'nmotveh', 'tmotveh', 'nchaveh',
        'taroperveh', 'radaccveh', 'fecexpr', 'fecvenr', 'soat', 'fecvens',
        'extcontveh', 'fecvene', 'cactveh', 'fecvenc', 'tecmecveh',
        'fecvent', 'polaveh', 'blinveh', 'prop', 'cond'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'idemp', 'idemp');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'linveh', 'idmar');
    }

    public function clase()
    {
        return $this->belongsTo(Valor::class, 'clveh', 'idval');
    }

    public function combustible()
    {
        return $this->belongsTo(Valor::class, 'combuveh', 'idval');
    }

    public function tipoMotor()
    {
        return $this->belongsTo(Valor::class, 'tmotveh', 'idval');
    }

    public function categoriaCarga()
    {
        return $this->belongsTo(Valor::class, 'crgveh', 'idval');
    }

    public function propietario()
    {
        return $this->belongsTo(Persona::class, 'prop', 'idper');
    }

    public function conductor()
    {
        return $this->belongsTo(Persona::class, 'cond', 'idper');
    }

    public function personas()
    {
        return $this->belongsToMany(Persona::class, 'proveh', 'idveh', 'idper');
    }

    public function diagnosticos()
    {
        return $this->hasMany(Diag::class, 'idveh', 'idveh');
    }

}
