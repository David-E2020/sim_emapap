<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

//use App\Http\Controllers\Administracion\Parametricas\UsuarioController;

class UsuarioSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$user = User::find(1);
		$sistemas = json_decode($user->usr_access_sistem);
		$user->usr_access_sistem = $this->estadoCOMEX($sistemas, true);
		$user->save();
	}

	private function estadoCOMEX($sistemas, $estado) {

		$resp = [];
		foreach ($sistemas as $key => $value) {

			$object = (object) array();

			$object->sistema = $value->sistema;
			if ($value->sistema == "SAP") {
				$object->activado = $estado;
				$object->value = $estado;
			} else {
				$object->activado = $value->activado;
				$object->value = $value->activado;
			}
			$resp[] = $object;
		}

		return $resp;
	}
}
