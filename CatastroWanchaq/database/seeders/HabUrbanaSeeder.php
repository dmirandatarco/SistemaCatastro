<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HabUrbana; 

class HabUrbanaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $haburbanas = [
            ['id_hab_urba'=>'0801081001','grup_urba'=>NULL,'tipo_hab_urba'=>'AA.HH.','nomb_hab_urba'=>'JOSE OLAYA','codi_hab_urba'=>'1001','id_ubi_geo'=>'080108','estado'=>'0'],
            ['id_hab_urba'=>'0801081002','grup_urba'=>NULL,'tipo_hab_urba'=>'AA.HH.','nomb_hab_urba'=>'PROGRAMA DE VIVIENDA TTIO SUR','codi_hab_urba'=>'1002','id_ubi_geo'=>'080108','estado'=>'1'],
            ['id_hab_urba'=>'0801081003','grup_urba'=>NULL,'tipo_hab_urba'=>'URB.','nomb_hab_urba'=>'REYNA DE BELEN','codi_hab_urba'=>'1003','id_ubi_geo'=>'080108','estado'=>'1'],
            ['id_hab_urba'=>'0801081004','grup_urba'=>NULL,'tipo_hab_urba'=>'HU.PR.','nomb_hab_urba'=>'SIMON HERRERA','codi_hab_urba'=>'1004','id_ubi_geo'=>'080108','estado'=>'1'],
            ['id_hab_urba'=>'0801081005','grup_urba'=>NULL,'tipo_hab_urba'=>'AA.HH.','nomb_hab_urba'=>'VALLECITO','codi_hab_urba'=>'1005','id_ubi_geo'=>'080108','estado'=>'1'],
            ['id_hab_urba'=>'0801081006','grup_urba'=>NULL,'tipo_hab_urba'=>'HU.PR.','nomb_hab_urba'=>'CAPAC YUPANQUI','codi_hab_urba'=>'1006','id_ubi_geo'=>'080108','estado'=>'1'],
            ['id_hab_urba'=>'0801081007','grup_urba'=>NULL,'tipo_hab_urba'=>'CA.','nomb_hab_urba'=>'HILARIO MENDIVIL 1 ETAPA','codi_hab_urba'=>'1007','id_ubi_geo'=>'080108','estado'=>'1'],
            ['id_hab_urba'=>'0801081008','grup_urba'=>NULL,'tipo_hab_urba'=>'CA.','nomb_hab_urba'=>'HILARIO MENDIVIL 2 ETAPA','codi_hab_urba'=>'1008','id_ubi_geo'=>'080108','estado'=>'1'],
            ['id_hab_urba'=>'0801081009','grup_urba'=>NULL,'tipo_hab_urba'=>'ASOC.','nomb_hab_urba'=>'INGENIERIA','codi_hab_urba'=>'1009','id_ubi_geo'=>'080108','estado'=>'1'],
            ['id_hab_urba'=>'0801081010','grup_urba'=>NULL,'tipo_hab_urba'=>'ASOC.','nomb_hab_urba'=>'KENNEDY "B"','codi_hab_urba'=>'1010','id_ubi_geo'=>'080108','estado'=>'1'],
        ];
        
        foreach ($haburbanas as $haburb) {
            HabUrbana::create($haburb);
        }
        
    }
}
