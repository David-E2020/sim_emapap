<?php

namespace Database\Seeders;

use App\Models\MovimientoExistenciaInsumo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
	/**
	 * Seed the application's database.
	 *
	 * @return void
	 */
	public function run() {
		$this->call([
			RolSeeder::class,
			MenuSeeder::class,
			ParametricaSeeder::class,
			AcoParametroSeeder::class,
			PlantaSeeder::class,
			TipoDocumentoInsumoSeeder::class,
			LineaInsumoSeeder::class,
			SubLineaInsumoSeeder::class,
			ProveedorInsumoSeeder::class,
			SolicitudInsumoSeeder::class,
			TransporteAcopioSeeder::class,
			ConductorAcopioSeeder::class,
			UnidadMedidaInsumoSeeder::class,
			CategoriaArticuloInsumoSeeder::class,
			CategoriaInsumoSeeder::class,
			ArticuloInsumoSeeder::class,
			IngresoArticuloInsumoSeeder::class,
			// IngresosDetalleInsumoSeeder::class,
			// PartidaPresupuestariaInsumoSeeder::class,
			UbicacionAlmacenInsumoSeeder::class,
			RecursosHumanosSeeder::class,
			//SucursalsInventarioSeeder::class,
			// InventarioLoteSeeder::class,
			// MovimientoExistenciaInsumoSeeder::class,
			// MovimientoExistenciaDetalleInsumoSeeder::class,
			// StockInventarioSeeder::class,
		]);

	}
}
