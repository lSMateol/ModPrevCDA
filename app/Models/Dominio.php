<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dominio extends Model
{
    protected $table = 'dominio';
    protected $primaryKey = 'iddom';
    public $timestamps = false;

    protected $fillable = ['nomdom'];

    public function valores()
    {
        return $this->hasMany(Valor::class, 'iddom', 'iddom');
    }
}
