<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartidaPresupuestariaInsumoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::table('insumos.partidas_presupuestarias_insumos')->insert(

      [
        [
          'codigo' => 25600,
          'nombre' => 'Servicio de Imprenta, Fotocopiado y Fotograficos.',
          'descripcion' => 'Gastos que se realizan por trabajos de: diagramación, impresión, compaginación, encuadernación, fotocopias y otros efectuados por terceros. Incluye los gastos por  revelado de fotografías, slides y otros similares',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2012-2-2 20:43:14'
        ],

        [
          'codigo' => 32100,
          'nombre' => 'Papel',
          'descripcion' => 'Gastos destinados a la adquisición de papel de escritorio y otros.',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:43:45'
        ],

        [
          'codigo' => 32200,
          'nombre' => 'Productos de Artes Gráficas',
          'descripcion' => 'Gastos para la adquisición de productos de artes gráficas y otros relacionados. Incluye gastos destinados a la adquisición de artículos hechos de papel y cartón.',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:44:16'
        ],

        [
          'codigo' => 33100,
          'nombre' => 'Hilados, Telas, Fibras y Algodon',
          'descripcion' => 'Gastos destinados en Hilados, Telas, Fibras y Algodon',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012'
        ],

        [
          'codigo' => 33200,
          'nombre' => 'Confecciones Textiles',
          'descripcion' => 'Gastos destinados a la adquisición de tapices, alfombras, sábanas, toallas, sacos de fibras, colchones, carpas, cortinas y otros textiles  similares.',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:44:51'
        ],

        [
          'codigo' => 33300,
          'nombre' => 'Prendas de Vestir',
          'descripcion' => 'Gastos destinados a la adquisición de  uniformes y vestimenta de diversos tipos, utilizados como ropa de trabajo en orfanatos, hospitales y otros similares, no incluye la compra de calzados',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:45:20'
        ],

        [
          'codigo' => 33400,
          'nombre' => 'Calzados',
          'descripcion' => 'Gastos destinados a la compra de calzados o zapatos complementarios de uniformes y los de uso exclusivo de servidores públicos que, por razones de seguridad industrial y de acuerdo con normas de trabajo establecidas por disposiciones en vigencia, deben contar con ellos.',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:46:11'
        ],

        [
          'codigo' => 34100,
          'nombre' => 'Combustibles, Lubricantes, Derivados y otras Fuentes de Ener',
          'descripcion' => 'Combustibles,Lubricantes y Derivados para consumo',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012'
        ],

        [
          'codigo' => 34110,
          'nombre' => 'Combustibles, Lubricantes y Derivados para consumo',
          'descripcion' => 'Combustibles, Lubricantes y Derivados para consumo',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012'
        ],

        [
          'codigo' => 34200,
          'nombre' => 'Productos Químicos y Farmacéuticos',
          'descripcion' => 'Gastos para la adquisición de compuestos químicos, tales como ácidos, sales, bases industriales, salitres, calcáreos y pulimentos; abonos y fertilizantes destinados a labores agricolas; insecticidas, fumigantes y otros utilizados en labores agropecuarias; medicamentos, para hospitales, clinicas, policlinicas y dispensarios; incluyendo los utilizados en veterinaria. Ademas de los insumos requeridos en la construcción, remodelación y mantenimiento de activos fijos y otros materiales quimicos anticongelantes.',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:47:24'
        ],

        [
          'codigo' => 34300,
          'nombre' => 'Llantas y Neumáticos',
          'descripcion' => 'Gastos destinados a la compra de llantas y neumáticos para utilización en los equipos de tracción, transporte y elevación',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:48:00'
        ],

        [
          'codigo' => 34500,
          'nombre' => 'Productos de Minerales no Metálicos y Plásticos',
          'descripcion' => 'Gastos por la adquisición de productos de arcilla como macetas, floreros, ceniceros, adornos y otros similares. Comprende además productos de vidrio como ceniceros, floreros, adornos y vidrio plano. Productos elaborados en loza y porcelana como ser: jarros, vajillas, inodoros, lavamanos y otros similares. Adquisición de cemento, cal y yeso para construcción, remodelación o mantenimiento de edificaciones públicas. Compra de tubos sanitarios, bloques, tejas y otros productos elaborados con cemento. Incluye ropa de trabajo y accesorios de seguridad industrial, como la adquisición de productos en cuya elaboración se utilizaron minerales no metálicos; además de tubos utilizados  en instalaciones eléctricas y sanitarias, envases y otros productos en cuya elaboración se utilizó material plástico. Se excluye aquellos considerados como material de escritorio.',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:50:12'
        ],

        [
          'codigo' => 34600,
          'nombre' => 'Productos Metálicos',
          'descripcion' => 'Gastos destinados a la adquisición de lingotes, planchas, planchones, hojalata, perfiles, alambres, varillas y otros similares., siempre que sea de hierro o acero. Incluye productos elaborados con base en aluminio, cobre, zinc, bronce y otras aleaciones; Además de envases y otros artículos de hojalata, cuchillería, ferretería, tornillos, tuercas, redes, cercas y demás productos metálicos; puertas, ventanas, cortinas, tinglados, carrocerías metálicas y demás estructuras metálicas acabadas. Se excluye la compra de repuestos y/o accesorios de un activo.',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:50:52'
        ],

        [
          'codigo' => 34800,
          'nombre' => 'Herramientas Menores',
          'descripcion' => 'Gastos para la adquisición de herramientas y equipos menores para uso agropecuario, industrial, de transporte, de construcción, tales como destornilladores, alicates, martillos, tenazas, serruchos, picos, palas,  tarrajas  y otras herramientas menores no activables.',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:51:18'
        ],

        [
          'codigo' => 39100,
          'nombre' => 'Material de Limpieza',
          'descripcion' => 'Gastos destinados a la adquisición de materiales como jabones, detergentes, paños, ceras, cepillos, escobas y otros utilizados en la limpieza e higiene de bienes y lugares públicos.',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:52:50'
        ],

        [
          'codigo' => 39300,
          'nombre' => 'Utensilios de Cocina y Comedor',
          'descripcion' => 'Gastos destinados a la adquisición de menaje de cocina y vajilla de comedor a ser utilizada en hospitales, hogares de niños, asilos y otras dependencias públicas.',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:53:40'
        ],

        [
          'codigo' => 39400,
          'nombre' => 'Instrumental Menor Médico-Quirúrgico',
          'descripcion' => 'e material y útiles menores médicos quirúrgicos',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012'
        ],

        [
          'codigo' => 39500,
          'nombre' => 'Útiles de Escritorio y Oficina',
          'descripcion' => 'Gastos destinados a la adquisición de útiles de escritorio como ser: tintas, lápices, bolígrafos, engrapadoras, perforadoras, medios magnéticos, tóner para impresoras y fotocopiadoras y otros destinados al funcionamiento de oficinas.',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:54:05'
        ],

        [
          'codigo' => 39700,
          'nombre' => 'Útiles y Materiales Eléctricos',
          'descripcion' => 'Gastos para la adquisición de focos, cables, sockets, tubos fluorescentes, accesorios de radios, lámparas de escritorio, electrodos, planchas, linternas, conductores, aisladores, fusibles, baterías, pilas, interruptores, conmutadores, enchufes y otros similares.',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:54:28'
        ],

        [
          'codigo' => 39800,
          'nombre' => 'Otros Repuestos y Accesorios',
          'descripcion' => 'Gastos destinados a la compra de repuestos y accesorios para los equipos comprendidos en el Subgrupo 43000. Se exceptúan las llantas y  neumáticos y los clasificados en las partidas anteriores.',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:54:56'
        ],

        [
          'codigo' => 39990,
          'nombre' => 'Otros Materiales y Suministros',
          'descripcion' => 'Otros Materiales y Suministros',
          'permitido' => 't',
          'user_id' => 1,
          'user_id_modificador' => 2,
          'fecha_registro' => '2/2/2012 20:55:16'

        ]
      ]
    );
  }
}
