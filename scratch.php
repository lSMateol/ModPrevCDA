<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$counts = ['1' => ['aprobado' => 0, 'rechazado' => 0], '2' => ['aprobado' => 0, 'rechazado' => 0], '3' => ['aprobado' => 0, 'rechazado' => 0]];

$tipos = DB::connection('legacy')->table('diapar')->where('idpar', 36)->get();
foreach($tipos as $t) {
    $aprobado = DB::connection('legacy')->table('diag')->where('iddia', $t->iddia)->value('aprobado');
    if ($aprobado) {
        $counts[$t->valor]['aprobado']++;
    } else {
        $counts[$t->valor]['rechazado']++;
    }
}
echo json_encode($counts);
