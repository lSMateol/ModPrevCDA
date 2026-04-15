<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagina extends Model
{
    protected $table = 'pagina';
    protected $primaryKey = 'idpag';
    public $timestamps = false;

    protected $fillable = ['nompag', 'rutpag', 'mospag', 'ordpag', 'icopag', 'despag'];

    public function perfiles()
    {
        return $this->belongsToMany(Perfil::class, 'pagper', 'idpag', 'idpef');
    }
}
