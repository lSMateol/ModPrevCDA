<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Historial extends Model
{
    protected $table = 'historial';
    protected $primaryKey = 'idhis';

    protected $fillable = ['tabla_ref', 'id_ref', 'accion', 
    'descripcion', 'idper', 'es_sistema'];

    protected $casts = [
        'es_sistema' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idper', 'idper');
    }
}
