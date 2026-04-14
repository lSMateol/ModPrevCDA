<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Valor extends Model
{
    protected $table = 'valor';
    protected $primaryKey = 'idval';
    public $timestamps = false;

    protected $fillable = ['iddom', 'nomval', 'parval', 'actval'];

    public function dominio()
    {
        return $this->belongsTo(Dominio::class, 'iddom', 'iddom');
    }

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'clveh', 'idval');
    }
}
