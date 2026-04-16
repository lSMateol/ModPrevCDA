<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Valor extends Model
{
    protected $table = 'valor';
    protected $primaryKey = 'idval';
    public $timestamps = false;

    // Se agrega 'idval' para permitir la asignación manual de IDs del Seeder
    protected $fillable = ['idval', 'iddom', 'nomval', 'parval', 'actval'];

    /**
     * Relación inversa con el Dominio
     */
    public function dominio(): BelongsTo
    {
        return $this->belongsTo(Dominio::class, 'iddom', 'iddom');
    }

    /**
     * Relación con los Vehículos (Clase de vehículo)
     */
    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class, 'clveh', 'idval');
    }
}
