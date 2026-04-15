<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diag extends Model
{
    protected $table = 'diag';
    protected $primaryKey = 'iddia';
    public $timestamps = false;

    protected $fillable = [
        'fecdia', 'idveh', 'aprobado', 'idper',
        'fecvig', 'kilomt', 'idinsp', 'iding', 'dpiddia'
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'idveh', 'idveh');
    }
     
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idper', 'idper');
    }

    public function inspector()
    {
        return $this->belongsTo(Persona::class, 'idinsp', 'idper');
    }

    public function ingeniero()
    {
        return $this->belongsTo(Persona::class, 'iding', 'idper');
    }

    public function diagnosticoPadre()
    {
        return $this->belongsTo(Diag::class, 'dpiddia', 'iddia');
    }

    public function parametros()
    {
        return $this->hasMany(Diapar::class, 'iddia', 'iddia');
    }

    public function fotos()
    {
        return $this->hasMany(Foto::class, 'iddia', 'iddia');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoRespaldo::class, 'iddia', 'iddia');
    }

    public function rechazo()
    {
        return $this->hasOne(RechazoD::class, 'iddia', 'iddia');
    }

    public function historial()
    {
        return $this->hasMany(HistorialMovimiento::class, 'idregistro', 'iddia')
                    ->where('modulo', 'diagnostico');
    }
}
