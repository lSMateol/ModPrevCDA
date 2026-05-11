<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoRespaldo extends Model
{
    protected $table = 'documento';
    protected $primaryKey = 'iddoc';

    protected $fillable = [
        'iddia', 'idveh', 'nomdoc', 'rutadoc',
        'tipodoc', 'tamdoc', 'estadoc', 'idper'
    ];

    public function diagnostico()
    {
        return $this->belongsTo(Diag::class, 'iddia', 'iddia');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'idveh', 'idveh');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idper', 'idper');
    }
}
