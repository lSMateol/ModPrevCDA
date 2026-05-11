<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoVehiculoConfig extends Model
{
    protected $table = 'tipo_vehiculo_config';
    public $timestamps = false;

    protected $fillable = ['idval_combu', 'idtip', 'idpar', 'orden'];

    public function combustible()
    {
        return $this->belongsTo(Valor::class, 'idval_combu', 'idval');
    }

    public function domain()
    {
        return $this->belongsTo(Tippar::class, 'idtip', 'idtip');
    }

    public function parameter()
    {
        return $this->belongsTo(Param::class, 'idpar', 'idpar');
    }
}
