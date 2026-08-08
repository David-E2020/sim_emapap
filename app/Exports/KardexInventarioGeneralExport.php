<?php

namespace App\Exports;

use App\Models\Insumos\Articulos;
use App\Models\Inventario\StockExistenciaInventario;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class KardexInventarioGeneralExport implements FromView {
	public function __construct(string $producto, int $almacen) {
		$this->producto = $producto;
		$this->almacen = $almacen;
	}
	public function view(): View {
		try {
			$datosSucursal = Sucursal::find($this->almacen);
			$storage = $datosSucursal->nombre;
			$history = \DB::select('select * from inventario.sp_calcular_kardex_valorado(?,?)', array($this->almacen, $this->producto));
			$stocks = StockExistenciaInventario::where('sk_articulos_id', $this->producto)
				->select(
					DB::raw('split_part((sum(sk_cantidad))::text,$$.$$,1) ||$$.$$||substring(split_part((sum(sk_cantidad))::text,$$.$$,2),1,2) as cantidad'),
					DB::raw('split_part((sk_precio_unitario)::text,$$.$$,1) ||$$.$$||substring(split_part((sk_precio_unitario)::text,$$.$$,2),1,2) as sk_precio_unitario'),
					DB::raw('split_part((sk_precio_unitario*sum(sk_cantidad))::text,$$.$$,1) ||$$.$$||substring(split_part((sk_precio_unitario*sum(sk_cantidad))::text,$$.$$,2),1,2) as saldo')
				)
				->where('sk_destino_id', $this->almacen)
				->groupBy('sk_precio_unitario')
				->get();
			$product = Articulos::select('id', 'nombre_producto', 'identificador_mapeo')->where('id', $this->producto)->first();
			$username = Auth::user()->name;
			$date = Carbon::now();
			$quantity = 0;
			$count = 1;
			$code = $this->producto . '/' . $date->year;
			return view('reportExcel.kardex_valorado_inventario_export', [
				'date' => $date, 'username' => $username, 'product' => $product, 'stocks' => $stocks, 'history' => $history, "storage" => $storage,
			]);
		} catch (\Illuminate\Database\QueryException $ex) {
			var_dump($ex);
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}
}
