<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $table = 'foto';
    protected $primaryKey = 'idfot';
    public $timestamps = false;

    protected $fillable = ['iddia', 'rutafoto'];

    public function diagnostico()
    {
        return $this->belongsTo(Diag::class, 'iddia', 'iddia');
    }
}
