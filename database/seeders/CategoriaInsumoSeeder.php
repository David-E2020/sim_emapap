<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaInsumoSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		DB::table('insumos.categorias')->insert(
			[
				[
					'nombre' => 'Sistema de Archivo',
					'descripcion' => 'Archivadores de palanca, Indice Alfabetico, Separadores plásticos, folder colgante, archivadores rápidos, Folders de cartulina, Folders de plástico, Fundas Plásticas, Fasteners, Pita de Amarre',
					'permitido' => 't',
					'usr_registrado' => 6,
				],

				[
					'nombre' => 'Papelería, Cartulina, Sobres',
					'descripcion' => 'Papel bond, papel bond de color, papel carbonico, papel continuo, papel para fax, rollo para máquina sumadora, sobres kraft, sobres bond, cartulinas, carton',
					'permitido' => 't',
					'usr_registrado' => 3,
				],

				[
					'nombre' => 'Insumos de computación',
					'descripcion' => 'Toner, cartuchos para impresoras, cinta de impresión, pad mouse, cd, dvd, memorias usb, ',
					'permitido' => 't',
					'usr_registrado' => 2,
				],

				[
					'nombre' => 'Cintas de Embalaje y pegamentos',
					'descripcion' => 'Cinta de embalaje, cinta masquin, cinta estandar, despachadores de cinta adhesiva, cinta maskin, pegamento en barra, ligas',
					'permitido' => 't',
					'usr_registrado' => 3,
				],

				[
					'nombre' => 'Cuadernos, Blocks de Notas, Libro de Registro',
					'descripcion' => 'Blocks, Cuadernos empastados, Cuadernos espirales, Libro de Registro, Libro de Actas',
					'permitido' => 't',
					'usr_registrado' => 2,
				],

				[
					'nombre' => 'Material de Limpieza',
					'descripcion' => 'Artículos de Limpieza',
					'permitido' => 't',
					'usr_registrado' => 32,
				],

				[
					'nombre' => 'Articulos de Escritorio',
					'descripcion' => 'Engrapadoras, Sacagrapas, Perforadoras, Clips, POrta clips, Pestañas precortadas, Arandelas, Banderitas señalizadoras, Notas adhesivas, Papel cubo, Porta papel cubo, Portalapiceros, Revisteros, Bandejas para documentos, Porta Tarjetero Reglas, Estiletes, Esponjeros, Tampos Tintas, Almohadilla, Alfileres, Chinches, Tijeras, Calculadoras',
					'permitido' => 't',
					'usr_registrado' => 3,
				],

				[
					'nombre' => 'Escritura y Corrección',
					'descripcion' => 'Lápices, portamminas, bolígrafos, micropuntas, marcadores de pizarra, marcadores permanentes, maracdor para CD, resaltadores de texto, borrador de lápiz, borrador de tinta y lápiz, borrador de tinta, minas, tajadores, correctores en lapiz, corrector en cinta, corrector liquido',
					'permitido' => 't',
					'usr_registrado' => 2,
				],

				[
					'nombre' => 'Suministro',
					'descripcion' => 'Suministro de Articulos',
					'permitido' => 'f',
					'usr_registrado' => 2,
				],

				[
					'nombre' => 'Solo Soporte Técnico Sistemas',
					'descripcion' => 'Componentes necesarios  para el mantenimiento de la plataforma computacional de la Empresa',
					'permitido' => 't',
					'usr_registrado' => 3,
				],

				[
					'nombre' => 'Ropa de Trabajo y Accesorios',
					'descripcion' => 'Implemento de seguridad y ropa de trabajo',
					'permitido' => 't',
					'usr_registrado' => 2,
				],

				[
					'nombre' => 'Suministro de Transporte',
					'descripcion' => 'Suministros como por ejemplo llantas, baterías y otros para los vehículos livianos y pesados',
					'permitido' => 't',
					'usr_registrado' => 32,
				],

				[
					'nombre' => 'Soporte Técnico Sistemas ',
					'descripcion' => 'Articulos Primera Compra',
					'permitido' => 't',
					'usr_registrado' => 3,
				],

				[
					'nombre' => 'Ropa de Trabajo y Accesorios GAF',
					'descripcion' => 'Ropa de trabajo para los funcionarios del área de Activos Fijos y Servicios Generales',
					'permitido' => 't',
					'usr_registrado' => 2,
				],

				[
					'nombre' => 'Soporte Bienes y Servicios',
					'descripcion' => 'Se ingresara en este grupo Materiales Electricos y otros',
					'permitido' => 't',
					'usr_registrado' => 6,
				],

				[
					'nombre' => 'Soporte Comercializacion',
					'descripcion' => 'MATERIALES PARA LOGISTICA DE LA GERENCIA DE COMERCIALIZACION',
					'permitido' => 't',
					'usr_registrado' => 2,
				],

				[
					'nombre' => 'Soporte Bienes y Servicios Super EMAPA',
					'descripcion' => 'Todo material Electrico, rrollos de cables, termicos, tubos fluorecente, arrancadores y reactancias',
					'permitido' => 't',
					'usr_registrado' => 6,
				],

				[
					'nombre' => 'Ropa de Trabajo y Accesorios GP',
					'descripcion' => 'Ropa de trabajo botines de seguridad para trabajos de campo para la Gerencia de Produccion',
					'permitido' => 't',
					'usr_registrado' => 6,
				],

				[
					'nombre' => 'Ropa de Trabajo y Accesorios GAT',
					'descripcion' => 'ADQUISICION DE ROPA DE SEGURIDAD INDUSTRIAL Y EQUIPO DE PROTECCION PERSONAL',
					'permitido' => 't',
					'usr_registrado' => 2,
				],

				[
					'nombre' => 'Soporte GC',
					'descripcion' => 'PRODUCTOS PLÁSTICOS, CODIFICADORES DE PRECIOS, ROLLO PARA CODIFICA PRECIOS Y SEÑALITICAS',
					'permitido' => 't',
					'usr_registrado' => 8,
				],

				[
					'nombre' => 'Soporte de Bienes y Servicios UAP',
					'descripcion' => 'PRODUCTOS METÁLICOS  BANDEJA DE MELANINA',
					'permitido' => 't',
					'usr_registrado' => 8,
				],

				[
					'nombre' => 'Soporte Comunicacion',
					'descripcion' => 'Todos los materiales del Area de Comunicacion',
					'permitido' => 't',
					'usr_registrado' => 7,
				],

				[
					'nombre' => 'Soporte B.S. Reynaldo',
					'descripcion' => 'MOTOBOMBAS DE AGUA, MANGERA DE SUCCION Y MANGERA DE SALIDA',
					'permitido' => 't',
					'usr_registrado' => 7,
				],

				[
					'nombre' => 'Soporte Gerencia de Produccion',
					'descripcion' => 'ROPA DE TRABAJO  CAMISAS ETC.',
					'permitido' => 't',
					'usr_registrado' => 7,
				],

				[
					'nombre' => 'Soporte Planificacion ',
					'descripcion' => 'ADQUISICION DE MATERIAL DE SEGURIDAD INDUSTRIAL Y EQUIPOS DE MEDICION',
					'permitido' => 't',
					'usr_registrado' => 7,
				],

				[
					'nombre' => 'Suministros Comunicacion',
					'descripcion' => 'SUMINISTROS COMUNICACION',
					'permitido' => 't',
					'usr_registrado' => 7,
				],

				[
					'nombre' => 'Soporte Sistemas 2018',
					'descripcion' => 'MATERIALES Y HERRAMIENTAS SOPORTE TECNICO Y REDES',
					'permitido' => 't',
					'usr_registrado' => 7,
				],

				[
					'nombre' => 'Servicios Generales',
					'descripcion' => 'TODO MATERIAL ELECTRICO',
					'permitido' => 't',
					'usr_registrado' => 8,
				],

				[
					'nombre' => 'Accesorios Medicos',
					'descripcion' => 'INTRUMENTOS MEDICOS',
					'permitido' => 't',
					'usr_registrado' => 8,
				],

				[
					'nombre' => 'Ropa de Trabajo Acopio 2020',
					'descripcion' => 'GUARDAPOLVO, CAMISAS JEAN PANTALON JEAN POLERAS CHALECOS CHAMARRAS COFIA DE TELA  PONCHO IMPERMEABLE ',
					'permitido' => 't',
					'usr_registrado' => 7,
				],

				[
					'nombre' => 'Equipo de Seguridad Plantas Acopio',
					'descripcion' => 'FAJA LUMBAR, RESPIRADORES, CARTUCHOS FILTROS ETC.',
					'permitido' => 't',
					'usr_registrado' => 9,
				],

				[
					'nombre' => ' MATERIALES - INSUMOS PLANTA SAN JULIAN',
					'descripcion' => 'INSUMOS DEL LA PLANTA SAN JULIAN ',
					'permitido' => 't',
					'usr_registrado' => 9,
				],

				[
					'nombre' => 'MATERIALES - INSUMOS CUATRO CAÑADAS',
					'descripcion' => 'MATERIALES - INSUMOS CUATRO CAÑADAS',
					'permitido' => 't',
					'usr_registrado' => 9,
				],

				[
					'nombre' => 'MATERIALES - INSUMOS PLANTA CARACOLLO',
					'descripcion' => 'MATERIALES - INSUMOS PLANTA CARACOLLO',
					'permitido' => 't',
					'usr_registrado' => 9,
				],

				[
					'nombre' => 'MATERIALES-INSUMOS PLANTA SAN ANDRES',
					'descripcion' => 'MATERIALES-INSUMOS PLANTA SAN ANDRES',
					'permitido' => 't',
					'usr_registrado' => 9,
				],

				[
					'nombre' => 'PLANTA SAN PEDRO',
					'descripcion' => 'MATERIALES- INSUMOS PLANTA SAN PEDRO',
					'permitido' => 't',
					'usr_registrado' => 9,
				],

				[
					'nombre' => 'A.B. VACUNO MANTENIMIENTO PELETIZADO 46 kg-EMAPA',
					'descripcion' => 'A.B. VACUNO MANTENIMIENTO PELETIZADO ,A.B. VACUNO MANTENIMIENTO PELETIZADO 46 kg-EMAPA',
					'permitido' => 't',
					'usr_registrado' => 6,
				],

				[
					'nombre' => 'A. BALANCEADO VACUNO EN MANTENIMIENTO 46 kg-EMAPA',
					'descripcion' => 'A. BALANCEADO VACUNO EN MANTENIMIENTO',
					'permitido' => 't',
					'usr_registrado' => 7,
				],

				[
					'nombre' => 'A.B. VACUNO LECHERO PELETIZADO 46 kg-EMAPA',
					'descripcion' => 'A.B. VACUNO LECHERO PELETIZADO',
					'permitido' => 't',
					'usr_registrado' => 7,
				],

				[
					'nombre' => 'A.B. VACUNO MANTENIMIENTO 45 KG-EMAPA',
					'descripcion' => 'A.B. VACUNO MANTENIMIENTO 45 KG',
					'permitido' => 't',
					'usr_registrado' => 9,
				],

				[
					'nombre' => 'Ropa de Trabajo Acopio',
					'descripcion' => 'A.B. AVICOLA PARRILLERO INICIO',
					'permitido' => 't',
					'usr_registrado' => 9,
				],

				[
					'nombre' => 'Ropa de Trabajo y Accesorios',
					'descripcion' => 'A.B. AVICOLA PARRILLERO CRECIMIENTO ',
					'permitido' => 't',
					'usr_registrado' => 2,
				],

				[
					'nombre' => 'Insumos de consumo rápido',
					'descripcion' => 'A.B. AVICOLA PARRILLERO ACABADO ',
					'permitido' => 't',
					'usr_registrado' => 2,
				],

				[
					'nombre' => 'Productos químicos',
					'descripcion' => 'A.B. CERDO CRECIMIENTO ',
					'permitido' => 't',
					'usr_registrado' => 23,
				],

				[
					'nombre' => 'Insumos de limpieza',
					'descripcion' => 'A.B. CERDO ACABADO',
					'permitido' => 't',
					'usr_registrado' => 3,
				],

				[
					'nombre' => 'Insumos REUSABLES',
					'descripcion' => 'A.B. CERDO LACTANTE',
					'permitido' => 't',
					'usr_registrado' => 4,
				],

				[
					'nombre' => 'Material de embalaje',
					'descripcion' => 'A.B. CERDO GESTANTE',
					'permitido' => 't',
					'usr_registrado' => 9,
				],

				[
					'nombre' => 'Insumos perecederos',
					'descripcion' => 'A.B. VACUNO LECHERO',
					'permitido' => 't',
					'usr_registrado' => 2,
				],
			]
		);
	}
}
