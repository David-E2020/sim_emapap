<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Menu;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PrepararProduccionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emapap:preparar-produccion {--force : Forzar la ejecución sin confirmación interactiva}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepara y reconstruye la base de datos limpia para PRODUCCIÓN (Menús, roles, permisos y paramétricas completas; tablas operativas en 0).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('===============================================================');
        $this->info('  EMAPAP - Preparación de Base de Datos para PRODUCCIÓN');
        $this->info('===============================================================');

        if (!$this->option('force')) {
            if (!$this->confirm('¿Está completamente seguro de reconstruir la base de datos para PRODUCCIÓN? Esto limpiará tablas de prueba y transaccionales.', false)) {
                $this->warn('Operación cancelada por el usuario.');
                return self::SUCCESS;
            }
        }

        // 1. Limpieza de esquemas secundarios en PostgreSQL
        $this->warn('==> Paso 1: Limpiando esquemas secundarios de PostgreSQL...');
        $schemas = ['rrhh', 'correspondencia', 'facturacion', 'comercial', 'contabilidad', 'migracion', 'almacen', 'activos_fijos'];
        foreach ($schemas as $schema) {
            DB::statement("DROP SCHEMA IF EXISTS {$schema} CASCADE;");
            $this->line("    - Esquema [{$schema}] eliminado en cascada.");
        }

        // 2. Ejecutar migrate:fresh en esquema public
        $this->warn('==> Paso 2: Ejecutando migraciones limpias (migrate:fresh)...');
        $exitCode = Artisan::call('migrate:fresh', ['--force' => true], $this->output);
        if ($exitCode !== 0) {
            $this->error('Error al ejecutar migrate:fresh.');
            return self::FAILURE;
        }

        // 3. Ejecutar seeders de producción
        $this->warn('==> Paso 3: Sembrando datos estructurales de producción (db:seed)...');
        $exitCode = Artisan::call('db:seed', ['--force' => true], $this->output);
        if ($exitCode !== 0) {
            $this->error('Error al ejecutar seeders de producción.');
            return self::FAILURE;
        }

        // 4. Verificación de Integridad y Resumen
        $this->info('===============================================================');
        $this->info('  RESULTADOS DE LA AUDITORÍA DE INTEGRIDAD DE PRODUCCIÓN');
        $this->info('===============================================================');

        $usersCount = User::count();
        $adminUser = User::where('usr_usuario', 'admin')->first();
        $rolesCount = Rol::count();
        $permissionsCount = Permission::count();
        $menusPadre = Menu::where('level', 0)->count();
        $menusHijos = Menu::where('level', 1)->count();
        $parametricasCount = DB::table('parametricas')->count();

        $this->table(
            ['Elemento Estructural', 'Cantidad', 'Estado'],
            [
                ['Usuario Administrador', $usersCount, $adminUser ? 'OK (' . $adminUser->usr_usuario . ')' : 'ERROR'],
                ['Roles Oficiales', $rolesCount, $rolesCount >= 3 ? 'OK' : 'VERIFICAR'],
                ['Permisos Granulares Spatie', $permissionsCount, $permissionsCount >= 75 ? 'OK' : 'VERIFICAR'],
                ['Módulos Principales (Menús Nivel 0)', $menusPadre, $menusPadre >= 7 ? 'OK' : 'VERIFICAR'],
                ['Submódulos (Menús Nivel 1)', $menusHijos, $menusHijos >= 30 ? 'OK' : 'VERIFICAR'],
                ['Paramétricas de Sistema', $parametricasCount, $parametricasCount > 0 ? 'OK' : 'VERIFICAR'],
            ]
        );

        // 5. Verificación de Tablas Transaccionales Vacías
        $this->info('==> Verificando tablas transaccionales (deben estar en 0)...');
        $tablasVacias = [
            'migracion.logs' => DB::table('migracion.logs')->count(),
            'parametricas (comercial)' => DB::table('parametricas')->where('param_tabla', 'like', 'TABLA_COMERCIAL_%')->count(),
            'comercial.zonas' => DB::table('comercial.zonas')->count(),
            'comercial.calles' => DB::table('comercial.calles')->count(),
            'comercial.categorias_tarifarias' => DB::table('comercial.categorias_tarifarias')->count(),
            'comercial.tarifas_escalonadas' => DB::table('comercial.tarifas_escalonadas')->count(),
            'comercial.abonados' => DB::table('comercial.abonados')->count(),
            'comercial.lecturas_mensuales' => DB::table('comercial.lecturas_mensuales')->count(),
            'comercial.recibos_caja' => DB::table('comercial.recibos_caja')->count(),
            'comercial.caja_sesiones' => DB::table('comercial.caja_sesiones')->count(),
            'facturacion.facturas' => DB::table('facturacion.facturas')->count(),
            'facturacion.factura_detalles' => DB::table('facturacion.factura_detalles')->count(),
            'correspondencia.hojas_ruta' => DB::table('correspondencia.hojas_ruta')->count(),
            'correspondencia.documentos' => DB::table('correspondencia.documentos')->count(),
            'rrhh.marcaciones' => DB::table('rrhh.marcaciones')->count(),
            'rrhh.asistencias' => DB::table('rrhh.asistencias')->count(),
            'almacen.materiales' => DB::table('almacen.materiales')->count(),
            'activos_fijos.bienes' => DB::table('activos_fijos.bienes')->count(),
            'audit_logs' => DB::table('audit_logs')->count(),
        ];

        $tablaReporte = [];
        $todasVacias = true;
        foreach ($tablasVacias as $tabla => $count) {
            $esCero = ($count === 0);
            if (!$esCero) $todasVacias = false;
            $tablaReporte[] = [$tabla, $count, $esCero ? 'LIMPIA (0)' : 'NO VACIA'];
        }

        $this->table(['Tabla Operativa', 'Registros', 'Estado'], $tablaReporte);

        if ($todasVacias && $adminUser && $permissionsCount >= 75) {
            $this->info('✔ ¡La base de datos está 100% lista para despliegue en PRODUCCIÓN en Dokploy!');
            return self::SUCCESS;
        }

        $this->warn('Revisa las advertencias en la tabla anterior.');
        return self::SUCCESS;
    }
}
