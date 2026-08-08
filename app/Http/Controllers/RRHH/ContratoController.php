<?php

namespace App\Http\Controllers\RRHH;

use App;
use App\Http\Controllers\Controller;
use App\Models\RRHH\ContractType;
use App\Models\RRHH\Employee;
use ZipArchive;

class ContratoController extends Controller {
	public function reporteDPF($employee_id, $id_tipo_contrato, $bandera, $to_date) {
		if ($bandera == 1) {
			$employee = Employee::find($employee_id);
			$persona = $employee->getFullName();

			$contract_type_id = $employee->getIdContract();

			// $type_contrato=ContractType::find($contract_type_id);
			$type_contrato = ContractType::find($id_tipo_contrato);

			$url_contrato = $type_contrato->getUrlTipoContrato();
			$name_contrato = $type_contrato->getNombreContrato();

			$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('app/' . $url_contrato));

			$templateProcessor->setValue('nombre_contratado', $persona);
			$templateProcessor->setValue('name_contrato', $name_contrato);
			$file_name = $persona;

			$templateProcessor->saveAs($file_name . '.docx');

			return response()->download($file_name . '.docx')->deleteFileAfterSend(false);
		} else {
			// echo $employee_id;
			$ids = explode(",", $employee_id);
			$cantidad_id = count($ids);
			// echo $cantidad_id;
			// echo $ids[$i];
			$zip = new ZipArchive();
			$nameZip = "Contratos.zip";
			$zip->open($nameZip, ZipArchive::CREATE);
			$dir = 'Contratos';
			$zip->addEmptyDir($dir);
			for ($i = 0; $i < $cantidad_id; $i++) {
				$employee = Employee::find($ids[$i]);
				$persona = $employee->getFullName();
				$type_contrato = ContractType::find($id_tipo_contrato);
				$tipo_contr = $type_contrato->getUrlTipoContrato();
				$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('app/' . $tipo_contr));
				$templateProcessor->setValue('nombre_contratado', $persona);
				$file_name = $persona;
				$templateProcessor->saveAs($file_name . '.docx');
				$zip->addFile($file_name . '.docx', $dir . '/' . $file_name . '.docx');
			}
			$zip->close();
			header("Content-type: application/octet-stream");
			header("Content-disposition: attachment; filename=" . $nameZip);
			readfile($nameZip);
			unlink($nameZip);
			for ($i = 0; $i < $cantidad_id; $i++) {
				$employee = Employee::find($ids[$i]);
				$persona = $employee->getFullName();
				$file_name = $persona;
				unlink($file_name . '.docx');
			}

			return;
		}
	}
}
