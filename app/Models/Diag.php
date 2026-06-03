<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diag extends Model
{
    protected $table = 'diag';
    protected $primaryKey = 'iddia';
    public $timestamps = false;

    protected $fillable = [
        'fecdia', 'idveh', 'idval_combu', 'aprobado', 'idper',
        'fecvig', 'kilomt', 'idinsp', 'iding', 'iddiapar', 'dpiddia', 'tipo_formulario'
    ];

    /**
     * Fuerza que 'aprobado' sea integer en JSON/serialización.
     * Sin esto, algunos drivers MySQL/PDO devuelven TINYINT(1) como boolean PHP
     * (true/false), lo que rompe comparaciones estrictas (===) en JavaScript.
     * null permanece null con este cast.
     */
    protected $casts = [
        'aprobado' => 'integer',
    ];


    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'idveh', 'idveh');
    }
     
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idper', 'idper');
    }

    public function inspector()
    {
        return $this->belongsTo(Persona::class, 'idinsp', 'idper');
    }

    public function ingeniero()
    {
        return $this->belongsTo(Persona::class, 'iding', 'idper');
    }

    public function diagnosticoPadre()
    {
        return $this->belongsTo(Diag::class, 'dpiddia', 'iddia');
    }

    public function tipoVehiculo()
    {
        return $this->belongsTo(Valor::class, 'idval_combu', 'idval');
    }

    public function parametros()
    {
        return $this->hasMany(Diapar::class, 'iddia', 'iddia');
    }

    public function fotos()
    {
        return $this->hasMany(Foto::class, 'iddia', 'iddia');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoRespaldo::class, 'iddia', 'iddia');
    }

    public function rechazo()
    {
        return $this->hasOne(Rechazo::class, 'iddia', 'iddia');
    }

    public function historial()
    {
        return $this->hasMany(Historial::class, 'idregistro', 'iddia')
                    ->where('modulo', 'diagnostico');
    }

    /**
     * Obtiene la colección de parámetros activos y válidos para este diagnóstico
     * según el combustible y el tipo de formulario.
     */
    public function getActiveParameters()
    {
        $idval_combu = $this->idval_combu ?? ($this->vehiculo->combuveh ?? 43);
        $configIds = TipoVehiculoConfig::where('idval_combu', $idval_combu)->pluck('idpar');
        $parametrosRaw = Param::with('tippar')
            ->whereIn('idpar', $configIds)
            ->where('actpar', 1)
            ->get();

        $formType = $this->tipo_formulario ?? '';
        $tipoVehiculoStr = strtolower(optional($this->vehiculo)->tipoveh ?? '');
        if (empty($tipoVehiculoStr) && is_string($this->vehiculo->tipoveh)) {
            $tipoVehiculoStr = strtolower($this->vehiculo->tipoveh);
        }
        $isMoto = str_contains($tipoVehiculoStr, 'motocicleta') || str_contains($tipoVehiculoStr, 'motocileta');

        $activeParams = collect();
        foreach ($parametrosRaw as $param) {
            $nomTip = strtoupper($param->tippar->nomtip ?? '');
            $skip = false;

            if ($formType == 'diesel_basico' && str_contains($nomTip, 'GASES')) {
                $skip = true;
            } elseif ($formType == 'otto_sin_gases' && (str_contains($nomTip, 'GASES') || str_contains($nomTip, 'OTTO') || str_contains($nomTip, 'CICLO OTTO'))) {
                $skip = true;
            } elseif ($formType == 'solo_gases') {
                if (!str_contains($nomTip, 'GASES') && !str_contains($nomTip, 'OTTO') && !str_contains($nomTip, 'CICLO OTTO')) {
                    $skip = true;
                }
            }

            if ($isMoto && $param->nompar === 'reversa') {
                $skip = true;
            }

            if (!$skip) {
                $activeParams->push($param);
            }
        }

        return $activeParams;
    }

    /**
     * Obtiene el listado asociativo de campos obligatorios (nombre_parametro => etiqueta)
     */
    public function getRequiredFields()
    {
        $labelsDict = [
            'reversa' => 'Reversa',
            'frenos' => 'Frenos',
            'direccionales' => 'Direccionales',
            'exploradoras' => 'Exploradoras',
            'dilusion_gasolina' => 'Dilución Gasolina',
            'Criterios_de_validacion' => 'Criterios de Validación',
            'temp_c' => 'Temp C (V. Diesel)',
            'rpm' => 'RPM (V. Diesel)',
            'ciclo1' => 'Ciclo 1 (V. Diesel)',
            'ciclo2' => 'Ciclo 2 (V. Diesel)',
            'ciclo3' => 'Ciclo 3 (V. Diesel)',
            'ciclo4' => 'Ciclo 4 (V. Diesel)',
            'resultado_diesel' => 'Resultado Diesel',
            'co_ralenti' => 'CO Ralenti',
            'co_crucero' => 'CO Crucero',
            'co2_ralenti' => 'CO2 Ralenti',
            'co2_crucero' => 'CO2 Crucero',
            'hc_ralenti' => 'HC Ralenti',
            'hc_crucero' => 'HC Crucero',
            'o2_ralenti' => 'O2 Ralenti',
            'o2_crucero' => 'O2 Crucero',
            'temperatura_gases' => 'Temperatura Gases',
            'rpm_gases' => 'RPM Gases',
            'no_ralenti' => 'NO Ralenti',
            'no_crucero' => 'NO Crucero',
        ];

        $requiredFields = [];
        foreach ($this->getActiveParameters() as $param) {
            if (in_array($param->control, ['number', 'radio'])) {
                $label = $labelsDict[$param->nompar] ?? ucwords(str_replace('_', ' ', $param->nompar));
                $requiredFields[$param->nompar] = $label;
            }
        }

        return $requiredFields;
    }

    /**
     * Obtiene los campos faltantes/incompletos del diagnóstico actual
     */
    public function getMissingFields()
    {
        $answeredParams = $this->parametros->filter(function($p) {
            return !is_null($p->valor) && $p->valor !== '';
        })->pluck('parametro.nompar')->toArray();

        $requiredFields = $this->getRequiredFields();
        $missingFields = [];
        
        foreach ($requiredFields as $key => $label) {
            if (!in_array($key, $answeredParams)) {
                $missingFields[] = $label;
            }
        }

        // Evidencia fotográfica mínima (2 fotos)
        $fotosCount = $this->fotos->count();
        if ($fotosCount < 2) {
            $missingFields[] = 'Evidencia Fotográfica (Se requieren al menos 2 fotos. Actual: ' . $fotosCount . ')';
        }

        return $missingFields;
    }
}
