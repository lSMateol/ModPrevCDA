<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diapar extends Model
{
    protected $table = 'diapar';
    public $timestamps = true;
    
    protected $primaryKey = 'iddiapar';
    public $incrementing = true;
    protected $fillable = ['iddia','idpar', 'idper', 'valor'];

    public function diagnostico()
    {
        return $this->belongsTo(Diag::class, 'iddia', 'iddia');
    }

    public function parametro()
    {
        return $this->belongsTo(Param::class, 'idpar', 'idpar');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idper', 'idper');
    }
}
