
import json, re

# ── Leer datos del Excel ya procesados ───────────────────────────────────────
d = json.load(open('database/seeders/cleaned_data.json', encoding='utf-8'))

def php_array(lst):
    items = ', '.join(f"'{x}'" for x in lst)
    return f"[{items}]"

docs_inspector   = php_array(d['Perfiles']['Inspector'])
docs_ingeniero   = php_array(d['Perfiles']['Ingeniero'])
docs_digitador   = php_array(d['Perfiles']['Digitador'])
docs_admin       = php_array(d['Perfiles']['Administrador'])
docs_superadmin  = php_array(d['Perfiles']['SuperAdministrador'])
docs_todos       = php_array(d['Todos'])

# ── Leer la parte intacta del archivo actual ─────────────────────────────────
old = open('database/seeders/LegacyImportSeeder.php', encoding='utf-8').read()
idx = old.find('    private function limpiarDatosPrevios')
parte_intacta = old[idx:]

# Eliminar el docblock duplicado si existe justo antes de limpiarDatosPrevios
parte_intacta = re.sub(
    r'/\*\*\s*\n\s*\* Limpia los datos transaccionales.*?\*/\s*\n',
    '',
    parte_intacta,
    count=1,
    flags=re.DOTALL
)

# ── Construir la PARTE 1 (encabezado + run() + aplicarLimpiezaExcel()) ───────
parte1 = f'''<?php

namespace Database\\Seeders;

use App\\Models\\User;
use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Hash;
use Illuminate\\Support\\Str;

class LegacyImportSeeder extends Seeder
{{
    /**
     * IMPORTANTE: Para correr este seeder, debes configurar una segunda conexión
     * de base de datos en config/database.php llamada 'legacy' que apunte a la
     * base de datos cdarastr_cdarev.
     *
     * FECHA DE CORTE: Solo se importan diagnósticos a partir del 01/06/2025.
     * Empresas, personas y vehículos se importan completos porque son datos
     * maestros requeridos para vínculos.
     *
     * TABLAS EXCLUIDAS DE LA MIGRACIÓN (9 tablas):
     * ┌──────────────────┬──────────────────────────────────────────────────┐
     * │ config           │ No existe en el nuevo sistema                   │
     * │ mantenimiento    │ No se migra (decisión de negocio)               │
     * │ maquina          │ No existe en el nuevo sistema                   │
     * │ pagina           │ Se usa PaginaSeeder propio del nuevo sistema    │
     * │ pagper           │ Se usa PaginaSeeder propio del nuevo sistema    │
     * │ punaten          │ No existe en el nuevo sistema                   │
     * │ tippar           │ Se usa TipparSeeder + DynamicFieldsSeeder       │
     * │ param            │ Se usa TipparSeeder + DynamicFieldsSeeder       │
     * │ perfil           │ Se usa PerfilSeeder propio (incluye perfil 8)   │
     * └──────────────────┴──────────────────────────────────────────────────┘
     */

    /** Fecha de corte para datos transaccionales */
    protected const FECHA_CORTE = '2025-06-01';

    /** Cache de mapeo de IDs legacy a IDs reales para evitar N+1 queries */
    protected $mapaPersonas = [];
    protected $idsVehiculos = [];

    /** IDs a excluir según limpieza Excel */
    protected $excluirPersonasDocs = [];
    protected $excluirPersonasIdsLegacy = [];
    protected $excluirEmpresasIds = [];

    /** IDs de diagnósticos importados (post-filtro de fecha) */
    protected $idsDiagImportados = [];

    /** Mapa de vehículo → combustible para asignar idval_combu */
    protected $vehiculoCombustible = [];

    public function run(): void
    {{
        $this->command->info('══════════════════════════════════════════════');
        $this->command->info('  Importación Legacy — Corte: ' . self::FECHA_CORTE);
        $this->command->info('══════════════════════════════════════════════');

        // ═══════════════════════════════════════════════════════════════
        // 0. LIMPIEZA PREVIA (Opcional, según condiciones)
        // ═══════════════════════════════════════════════════════════════
        $this->aplicarLimpiezaExcel();
        $this->limpiarDatosPrevios();
        $this->purgarLegacyAntiguo();

        // ═══════════════════════════════════════════════════════════════
        // 1. MAESTROS DEL NUEVO SISTEMA (Roles, Perfiles, Parámetros)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('');
        $this->command->info('▸ [1/9] Configurando Roles, Perfiles y Parámetros del sistema...');
        $this->call(RoleSeeder::class);
        $this->call(PerfilSeeder::class);

        // ═══════════════════════════════════════════════════════════════
        // 2. DICCIONARIOS BÁSICOS (desde legacy)
        //    NOTA: tippar y param NO se importan desde legacy.
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [2/9] Importando diccionarios (dominio, valor, ubica, marca)...');
        $this->importarTablaSimple('dominio', 'iddom');
        $this->importarTablaSimple('valor', 'idval');
        $this->importarTablaSimple('ubica', 'codubi');
        $this->importarTablaSimple('marca', 'idmar');

        // ═══════════════════════════════════════════════════════════════
        // 3. PARÁMETROS TÉCNICOS (desde seeders del sistema actual)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [3/9] Configurando parámetros técnicos y configuración por combustible...');
        $this->call(TipparSeeder::class);
        $this->call(DynamicFieldsSeeder::class);

        // ═══════════════════════════════════════════════════════════════
        // 4. EMPRESAS (desde legacy — con filtro Excel)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [4/9] Importando Empresas...');
        $this->importarEmpresas();

        // ═══════════════════════════════════════════════════════════════
        // 5. PERSONAS (desde legacy — con filtro Excel)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [5/9] Importando Personas (ETL con perfil dual ID 8)...');
        $this->importarPersonas();

        // 5b. Cargar mapa de personas en memoria
        $this->cargarMapaPersonas();

        // ═══════════════════════════════════════════════════════════════
        // 6. VEHÍCULOS (desde legacy — con filtro por empresa)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [6/9] Importando Vehículos...');
        $this->importarVehiculos();

        // 6b. Cargar mapas auxiliares
        $this->idsVehiculos = DB::table('vehiculo')->pluck('idveh', 'idveh')->toArray();
        $this->vehiculoCombustible = DB::table('vehiculo')->pluck('combuveh', 'idveh')->toArray();

        // ═══════════════════════════════════════════════════════════════
        // 7. PROVEH (relación persona-vehículo, completa)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [7/9] Importando relaciones Persona-Vehículo (proveh)...');
        $this->importarTablaSimple('proveh', ['idveh', 'idper']);

        // ═══════════════════════════════════════════════════════════════
        // 8. DIAGNÓSTICOS (filtrados por fecha de corte)
        //    + Asignación automática de idval_combu desde vehiculo.combuveh
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [8/9] Importando Diagnósticos (≥ ' . self::FECHA_CORTE . ')...');
        $this->importarDiagnosticos();

        // ═══════════════════════════════════════════════════════════════
        // 9. DIAPAR + FOTO (solo los vinculados a diagnósticos importados)
        //    + ROLES SPATIE
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [9/9] Importando Diapar, Fotos y asignando Roles...');
        $this->importarDiapar();
        $this->importarFotos();
        $this->asignarRoles();

        $this->command->info('');
        $this->command->info('══════════════════════════════════════════════');
        $this->command->info('  ✓ Importación finalizada con éxito');
        $this->command->info('    Diagnósticos importados: ' . count($this->idsDiagImportados));
        $this->command->info('══════════════════════════════════════════════');
    }}

    // ─────────────────────────────────────────────────────────────────
    //  MÉTODOS AUXILIARES
    // ─────────────────────────────────────────────────────────────────

    /**
     * Aplica la limpieza previa para excluir registros del Excel Perfiles_Eliminar.
     * Usa arrays PHP nativos para máxima compatibilidad — sin JSON embebido.
     */
    private function aplicarLimpiezaExcel()
    {{
        $this->command->warn("  Aplicando capa de limpieza previa desde Excel...");

        // ── Perfiles a excluir (documentos normalizados: solo dígitos) ──────
        $docsPorPerfil = [
            'Inspector'          => {docs_inspector},
            'Ingeniero'          => {docs_ingeniero},
            'Digitador'          => {docs_digitador},
            'Administrador'      => {docs_admin},
            'SuperAdministrador' => {docs_superadmin},
        ];
        foreach ($docsPorPerfil as $perfil => $docs) {{
            foreach ($docs as $d) {{ $this->excluirPersonasDocs[$d] = true; }}
            $this->command->info("    - {{$perfil}}: " . count($docs) . " docs excluidos.");
        }}

        // ── Pestaña Todos (lista completa — idempotente) ─────────────────────
        $docsTodos = {docs_todos};
        $adicionales = 0;
        foreach ($docsTodos as $d) {{
            if (!isset($this->excluirPersonasDocs[$d])) {{
                $this->excluirPersonasDocs[$d] = true;
                $adicionales++;
            }}
        }}
        $this->command->info("    - Todos (adicionales): {{$adicionales}} registros.");

        // ── Empresas a excluir ───────────────────────────────────────────────
        $empresasFiltro = [
            1  => '',           // Particular
            4  => '80000000002',
            10 => '8320035042',
            11 => '0',
            14 => '8906000559',
            19 => '900291473',
        ];
        $empresasLegacy = DB::connection('legacy')->table('empresa')->get();
        $countEmpresas  = 0;
        foreach ($empresasLegacy as $e) {{
            $norma  = preg_replace('/[^0-9]/', '', (string)($e->nonitem ?? ''));
            if (array_key_exists((int)$e->idemp, $empresasFiltro)) {{
                $patron = $empresasFiltro[(int)$e->idemp];
                if ($patron === '' || $patron === $norma) {{
                    $this->excluirEmpresasIds[$e->idemp] = true;
                    $countEmpresas++;
                }}
            }}
        }}
        $this->command->info("    - Empresas: {{$countEmpresas}} excluidas.");

        // ── Relaciones dependientes de empresas excluidas ─────────────────────
        if (!empty($this->excluirEmpresasIds)) {{
            $vehs = DB::connection('legacy')->table('vehiculo')
                ->whereIn('idemp', array_keys($this->excluirEmpresasIds))
                ->get();
            $pIds = [];
            foreach ($vehs as $v) {{
                if (!empty($v->prop)) $pIds[$v->prop] = true;
                if (!empty($v->cond)) $pIds[$v->cond] = true;
            }}
            $this->excluirPersonasIdsLegacy = $pIds;
            $this->command->info("    - Dependencias: " . count($vehs) . " vehículos, " . count($pIds) . " personas vinculadas.");
        }}

        $totalDocs = count($this->excluirPersonasDocs);
        $totalIds  = count($this->excluirPersonasIdsLegacy);
        $this->command->info("    ✓ Total a excluir → docs: {{$totalDocs}} | IDs legacy: {{$totalIds}}");
    }}

'''

# ── Combinar y guardar ───────────────────────────────────────────────────────
resultado = parte1 + parte_intacta

open('database/seeders/LegacyImportSeeder.php', 'w', encoding='utf-8').write(resultado)
print("OK - archivo reconstruido:", len(resultado), "bytes")
