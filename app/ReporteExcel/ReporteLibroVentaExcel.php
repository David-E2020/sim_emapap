<?php

namespace App\ReporteExcel;

//use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

use App;
use DB;
use Auth;
use PDF;
use TCPDF;
use Carbon\Carbon;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class ReporteLibroVentaExcel implements FromView {  

    public function __construct($facturas,$sumTotal,$username,$puntoVenta,$date,$title, $fechas){
        $this->facturas= $facturas;
        $this->sumTotal= $sumTotal;
        $this->username= $username;
        $this->puntoVenta= $puntoVenta;
        $this->date= $date;
        $this->title= $title;
        $this->fechas= $fechas;
    }

    public function view(): View{
        $facturas = $this->facturas;
        $sumTotal = $this->sumTotal ;
        $username = $this->username;
        $puntoVenta = $this->puntoVenta ;
        $date = $this->date ;
        $title = $this->title ;
        $fechas = $this->fechas ;
		return view('reportExcel.libro-ventas-excel', [
			'facturas' => $facturas,
            'sumTotal' => $sumTotal,
            'username' => $username,
            'puntoVenta' => $puntoVenta,
            'date' => $date,
            'title' => $title,
            'fechas' => $fechas
		]);
	}
}
