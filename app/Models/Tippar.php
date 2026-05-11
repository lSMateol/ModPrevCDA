<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tippar extends Model
{
    protected $table = 'tippar';
    protected $primaryKey = 'idtip';
    public $timestamps = false;

    protected $fillable = ['nomtip', 'tittip', 'idpef', 'acttip', 'icotip'];

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'idpef', 'idpef');
    }

    public function params()
    {
        return $this->hasMany(Param::class, 'idtip', 'idtip');
    }
}
