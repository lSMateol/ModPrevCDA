<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rechazo extends Model
{
    protected $table = 'rechazo';
    protected $primaryKey = 'idrec';

    protected $fillable = [
        'iddia', 'idper_ant', 'idper_nvo', 'motivo', 'prioridad', 
        'camposmod', 'notas', 'fecreasig', 'estadorec',
    ];

    protected $casts = [
        'fecreasig' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function diagnostico()
    {
        return $this->belongsTo(Diag::class, 'iddia', 'iddia');
    }

    public function inspectorAnterior()
    {
        return $this->belongsTo(Persona::class, 'idper_ant', 'idper');
    }

    public function inspectorNuevo()
    {
        return $this->belongsTo(Persona::class, 'idper_nvo', 'idper');
    }
}
