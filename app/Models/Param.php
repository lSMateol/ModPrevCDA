<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Param extends Model
{
    protected $table = 'param';
    protected $primaryKey = 'idpar';
    public $timestamps = false;

    protected $fillable = [
        'nompar', 'idtip', 'rini', 'rfin',
        'control', 'nomcampo', 'unipar', 'colum', 'actpar', 'can', 'se_mantiene'
    ];

    public function tippar()
    {
        return $this->belongsTo(Tippar::class, 'idtip', 'idtip');
    }

    public function diapar()
    {
        return $this->hasMany(Diapar::class, 'idpar', 'idpar');
    }
}
