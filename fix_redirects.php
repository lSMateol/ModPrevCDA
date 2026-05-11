<?php
$file = 'c:/laragon/www/ModPrevCDA/app/Http/Controllers/Admin/MupController.php';
$content = file_get_contents($file);

// Replace in storeConductor & updateConductor
$content = preg_replace(
    "/return redirect\(\)->back\(\)->with\('success'/s",
    "\$rolePrefix = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';\n            return redirect()->route(\"{\$rolePrefix}.mup.conductores.index\")->with('success'",
    $content,
    2
);
$content = preg_replace(
    "/return redirect\(\)->back\(\)->with\('error'/s",
    "\$rolePrefix = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';\n            return redirect()->route(\"{\$rolePrefix}.mup.conductores.index\")->with('error'",
    $content,
    2
);

// Replace in storePropietario & updatePropietario (starts at next occurrences)
$content = preg_replace(
    "/return redirect\(\)->back\(\)->with\('success'/s",
    "\$rolePrefix = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';\n            return redirect()->route(\"{\$rolePrefix}.mup.propietarios.index\")->with('success'",
    $content,
    2
);
$content = preg_replace(
    "/return redirect\(\)->back\(\)->with\('error'/s",
    "\$rolePrefix = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';\n            return redirect()->route(\"{\$rolePrefix}.mup.propietarios.index\")->with('error'",
    $content,
    2
);

// Replace in storeEmpresa & updateEmpresa
$content = preg_replace(
    "/return redirect\(\)->back\(\)->with\('success'/s",
    "\$rolePrefix = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';\n            return redirect()->route(\"{\$rolePrefix}.mup.empresas.index\")->with('success'",
    $content,
    2
);
$content = preg_replace(
    "/return redirect\(\)->back\(\)->with\('error'/s",
    "\$rolePrefix = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';\n            return redirect()->route(\"{\$rolePrefix}.mup.empresas.index\")->with('error'",
    $content,
    2
);

// Any remaining back() for error/success (like perfil) can default to a general page or back
$content = preg_replace(
    "/return redirect\(\)->back\(\)->with\('error'/s",
    "\$rolePrefix = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';\n            return redirect()->route(\"{\$rolePrefix}.mup.conductores.index\")->with('error'",
    $content
);
$content = preg_replace(
    "/return redirect\(\)->back\(\)->with\('success'/s",
    "\$rolePrefix = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';\n            return redirect()->route(\"{\$rolePrefix}.mup.conductores.index\")->with('success'",
    $content
);

file_put_contents($file, $content);
echo "Replaced properly";
