<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$id = 23;
$d = App\Models\Diag::with('parametros.parametro.tippar')->find($id);

foreach ($d->parametros as $p) {
    $pm = $p->parametro;
    $v = $p->valor;
    $failed = false;
    $nt = strtoupper($pm->tippar->nomtip ?? '');

    if ($pm->nompar == 'desc_inspeccion') {
        // Ignorar visual para fallas técnicas
    } else {
        if ($pm->control == 'number' && ($pm->rini !== null && $pm->rfin !== null)) {
            if ($v < $pm->rini || $v > $pm->rfin) $failed = true;
        } elseif ($pm->control == 'radio') {
            if (str_contains($nt, 'DEFECTOS') && !str_contains($nt, 'VISUAL')) {
                if (str_contains(strtolower($pm->nompar), 'criterios')) {
                    if (!in_array(strtolower($v), ['si', 'na'])) $failed = true;
                } else {
                    if (strtolower($v) == 'si') $failed = true;
                }
            } else {
                if (in_array($v, ['no', 'no_funciona'])) $failed = true;
            }
        }
    }
    
    echo ($failed ? '[FAILED]' : '[ OK ]') . " {$pm->nompar} = {$v} (Tip: {$nt})\n";
}
