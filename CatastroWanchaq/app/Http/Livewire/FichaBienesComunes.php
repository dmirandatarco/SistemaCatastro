<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Sectore;
use App\Models\UsosBc;
use App\Models\Persona;
use App\Models\Manzana;
use App\Models\UniCat;
use App\Models\Lote;
use App\Models\HabUrbana;
use App\Models\Via;
use App\Models\Institucion;
use App\Models\Ficha;
use App\Models\Puerta;
use App\Models\Edificaciones;
use App\Models\FichaBienComun;
use App\Models\Notarias;
use App\Models\CodigoInstalacion;
use App\Models\Lindero;
use App\Models\ServicioBasico;
use App\Models\RegistroLegal;
use App\Models\Construccion;
use App\Models\Instalacion;
use App\Models\Sunarp;
use App\Models\RecapEdificio;
use App\Models\RecapBbcc;
use App\Models\FichaIndividual;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class FichaBienesComunes extends Component
{
    public $sectores;
    public $usos;
    public $tecnicos;
    public $supervisores;
    public $verificadores;
    public $codigosinstalacion;
    public $notarias;

    public $numeficha;
    public $nume_ficha_lote;
    public $nume_ficha_lote2;
    public $cuc;
    public $dpto='08';
    public $prov='01';
    public $dist='08';
    public $sector;
    public $mzna;
    public $lote;
    public $edifica;
    public $entrada=99;
    public $piso=99;
    public $unidad=999;
    public $dc;
    public $codi_hoja_catastral;
    public $codi_cont_rentas;
    public $codi_pred_rentas;

    public $tipoHabi;
    public $nomb_hab_urba;
    public $zona_dist;
    public $mzna_dist;
    public $lote_dist;
    public $sub_lote_dist;
    public $via_id;
    public $hab_urbanas;
    public $hab_urbanas2;
    public $hab_urbana2;
    public $cont=1;
    public $via2=array([]);
    public $tipoVia;
    public $tipoVianombre;
    public $tipoViatipo;
    public $tipopuerta;
    public $nume_muni;
    public $cond_nume;
    public $nomb_edificacion;
    public $nume_certificacion;
    public $tipo_edificacion;

    #DESCRIPCION DEL PREDIO
    public $clasificacion;
    public $cont_en;
    public $codi_uso;
    public $estructuracion;
    public $zonificacion;
    public $area_declarada;
    public $area_verificada1;
    public $fren_campo;
    public $dere_campo;
    public $izqu_campo;
    public $fond_campo;
    public $fren_colinda_campo;
    public $dere_colinda_campo;
    public $izqu_colinda_campo;
    public $fond_colinda_campo;

    public $fren_titulo;
    public $dere_titulo;
    public $izqu_titulo;
    public $fond_titulo;
    public $fren_colinda_titulo;
    public $dere_colinda_titulo;
    public $izqu_colinda_titulo;
    public $fond_colinda_titulo;

    #SERVICIOS
    public $luz;
    public $agua;
    public $telefono;
    public $desague;
    public $gas;
    public $internet;
    public $tvcable;
    #SERVICIOS

    #CONSTRUCCIONES
    public $cont2=0;
    public $bloque;
    public $num_piso;
    public $fecha;
    public $mep;
    public $ecs;
    public $ecc;
    public $estr_muro_col;
    public $estr_techo;
    public $acab_piso;
    public $acab_puerta_ven;
    public $acab_revest;
    public $acab_bano;
    public $inst_elect_sanita;
    public $area_verificada;
    public $uca;
    #CONSTRUCCIONES

    #OBRAS COMPLEMENTARIAS
    public $cont3=0;
    public $codiinstalacion;
    public $inst_fecha;
    public $inst_mep;
    public $inst_ecs;
    public $inst_ecc;
    public $dime_largo;
    public $dime_ancho;
    public $dime_alto;
    public $inst_prod_total;
    public $inst_uni_med;
    public $inst_uca;
    #OBRAS COMPLEMENTARIAS

    #RECAP EDIFICIOS
    public $cont4=0;
    public $edificiobbc;
    public $porcentaje;
    public $atc;
    public $acc;
    public $aoic;
    #RECAP EDIFICIOS

    #EVALUACION PREDIO
    public $en_colindante;
    public $en_area_publica;
    public $en_jardin_aislamiento;
    public $en_area_intangible;
    #EVALUACION PREDIO


    #INSCRIPCION
    public $tipo_partida;
    public $nume_partida;
    public $fojas;
    public $asiento;
    public $fecha_inscripcion;
    public $codi_decla_fabrica;
    public $asie_fabrica;
    public $fecha_fabrica;
    #INSCRIPCION

    public $cond_declarante;
    public $esta_llenado;
    public $mantenimiento;
    public $observacion;

    #INFORMACION FINAL
    public $supervisor;
    public $fecha_supervision;
    public $tecnico;
    public $fecha_levantamiento;
    public $verificador;
    public $nume_registro;
    public $fecha_verificacion;
    public $numdocumentodeclarante;
    public $nombres_declarante;
    public $apellido_paterno_declarante;
    public $apellido_materno_declarante;
    public $fecha_declarante;
    #INFORMACION FINAL

    public $cont5=0;
    public $rbcedifica;
    public $rbcentrada;
    public $rbcnume_piso;
    public $rbcunidad;
    public $rbctotalporcentaje;
    public $rbctotalatc;
    public $rbctotalacc;
    public $rbctotalaoic;


    public $rbcnotaria;
    public $rbckardex;
    public $rbcfechaesc;

    public function mount()
    {
        $this->usos = UsosBc::orderBy('codi_uso', 'asc')->get();
        $this->tecnicos = Persona::where('tipo_funcion', 3)->orderBy('nume_doc', 'asc')->get();
        $this->supervisores = Persona::where('tipo_funcion', 2)->orderBy('nume_doc', 'asc')->get();
        $this->verificadores = Persona::where('tipo_funcion', 4)->orderBy('nume_doc', 'asc')->get();
        $this->codigosinstalacion = CodigoInstalacion::orderBy('codi_instalacion', 'asc')->get();
        $this->sectores = Sectore::orderBy('codi_sector', 'asc')->get();
        $this->manzanas = Manzana::orderBy('codi_mzna', 'asc')->get();
        $this->hab_urbanas = HabUrbana::orderBy('codi_hab_urba', 'asc')->get();
        $this->notarias = Notarias::orderBy('nomb_notaria', 'asc')->get();
        $this->vias = Via::orderBy('codi_via', 'asc')->get();
        for($i=0;$i<$this->cont;$i++){
            $this->tipoVianombre[$i]="";
            $this->tipoViatipo[$i]="";
        }
    }

    public function updatedsector($id_sector)
    {
        $this->mostrardc();
        $this->dc=($this->dpto+$this->prov+$this->dist+$this->sector+$this->mzna+$this->lote+$this->edifica+$this->entrada+$this->piso+$this->unidad)%9;
    }


    public function updatedunidad()
    {
        $this->mostrardc();
        $this->dc=($this->dpto+$this->prov+$this->dist+$this->sector+$this->mzna+$this->lote+$this->edifica+$this->entrada+$this->piso+$this->unidad)%9;
    }

    public function mostrardc()
    {
        if($this->sector=="")
        {
            $this->sector="01";
        }
        if($this->mzna=="")
        {
            $this->mzna="001";
        }
        if($this->lote=="")
        {
            $this->lote="001";
        }
        if($this->edifica=="")
        {
            $this->edifica="01";
        }
    }


    // public function updatededifica()
    // {
    //     $this->mostrardc();
    //     $this->dc=($this->dpto+$this->prov+$this->dist+$this->sector+$this->mzna+$this->lote+$this->edifica+$this->entrada+$this->piso+$this->unidad)%9;
    // }

    // public function updatedlote()
    // {
    //     $this->mostrardc();
    //     $this->dc=($this->dpto+$this->prov+$this->dist+$this->sector+$this->mzna+$this->lote+$this->edifica+$this->entrada+$this->piso+$this->unidad)%9;
    // }

    // public function updatedmzna()
    // {
    //     $this->mostrardc();
    //     $this->dc=($this->dpto+$this->prov+$this->dist+$this->sector+$this->mzna+$this->lote+$this->edifica+$this->entrada+$this->piso+$this->unidad)%9;
    // }
    public function calcularDC()
    {
        $this->validate([
            'dpto' => 'required|numeric',
            'prov' => 'required|numeric',
            'dist' => 'required|numeric',
            'sector' => 'required|numeric',
            'mzna' => 'required|numeric',
            'lote' => 'required|numeric',
            'edifica' => 'required|numeric',
            'entrada' => 'required|numeric',
            'piso' => 'required|numeric',
            'unidad' => 'required|numeric',
        ]);

        $this->dc = ($this->dpto + $this->prov + $this->dist + $this->sector + $this->mzna + $this->lote + $this->edifica + $this->entrada + $this->piso + $this->unidad) % 9;
        $codicatastral = '080108' . $this->sector . $this->mzna . $this->lote . $this->edifica . $this->entrada . $this->piso . $this->unidad;

        $exists = Unicat::where('id_uni_cat', $codicatastral)->exists();

        if ($exists) {
            $this->mensajeunicat = "Código de Referencia Catastral ya existe";
        } else {
            $this->mensajeunicat = "";
        }
    }

    public function updatednumeficha()
    {
        $this->numeficha=str_pad($this->numeficha,7,'0',STR_PAD_LEFT);
        $this->validate([
            'numeficha'                    => 'required|max:7|unique:tf_fichas_bienes_comunes,nume_ficha',
        ]);
    }

    public function render()
    {
        return view('livewire.ficha-bienes-comunes');
    }

    /* UBICACION DEL PREDIO */
    public function updatedtipoHabi( $id)
    {
        $idbuscar=str_pad($id,10,'0',STR_PAD_LEFT);
        $this->hab_urbana2=HabUrbana::where('id_hab_urba',$idbuscar)->first();

        if($this->hab_urbana2==""){
            $this->nomb_hab_urba="";
        }else{
            $this->nomb_hab_urba=$this->hab_urbana2->tipo_hab_urba." ".$this->hab_urbana2->nomb_hab_urba;
        }
    }


    public function updatedtipoVia($value,$nested)
    {
        $idbuscar=str_pad($value,12,'0',STR_PAD_LEFT);
        $this->via2=Via::where('id_via',$idbuscar)->first();
        if($this->via2=="")
        {
            $this->tipoVianombre[$nested]="";
            $this->tipoViatipo[$nested]="";
        }else{
            $this->tipoVianombre[$nested]=$this->via2->nomb_via;
            $this->tipoViatipo[$nested]=$this->via2->tipo_via;
        }
    }

    public function updatedcodiinstalacion($value,$nested)
    {
        $obras=CodigoInstalacion::where('codi_instalacion',$value)->first();
        if($obras=="")
        {
        }else{
            $this->inst_uni_med[$nested]=$obras->unidad;
        }
    }

    public function updatedverificador( $id)
    {
        $this->verificador2=Persona::where('id_persona',$id)->first();
        if($this->verificador2==""){
            $this->nume_registro="";
        }else{
            $this->nume_registro=$this->verificador2->nregistro;
        }
    }

    public function aumentarUbicacion()
    {
        $this->tipoViatipo[$this->cont]="";
        $this->tipoVianombre[$this->cont]="";
        $this->cont++;

    }

    public function reducirUbicacion()
    {
        $this->cont--;
    }

    /* UBICACION DEL PREDIO */

    /* CONSTRUCCIONES */

    public function aumentarConstruccion()
    {
        $this->cont2++;
    }

    public function reducirConstruccion()
    {
        $this->cont2--;        
        if (is_array($this->bloque)) {
            array_splice($this->bloque, $value, 1);
        }
        if (is_array($this->num_piso)) {
            array_splice($this->num_piso, $value, 1);
        }
        if (is_array($this->fecha)) {
            array_splice($this->fecha, $value, 1);
        }
        if (is_array($this->mep)) {
            array_splice($this->mep, $value, 1);
        }
        if (is_array($this->ecs)) {
            array_splice($this->ecs, $value, 1);
        }
        if (is_array($this->ecc)) {
            array_splice($this->ecc, $value, 1);
        }
        if (is_array($this->estr_muro_col)) {
            array_splice($this->estr_muro_col, $value, 1);
        }
        if (is_array($this->estr_techo)) {
            array_splice($this->estr_techo, $value, 1);
        }
        if (is_array($this->acab_piso)) {
            array_splice($this->acab_piso, $value, 1);
        }
        if (is_array($this->acab_puerta_ven)) {
            array_splice($this->acab_puerta_ven, $value, 1);
        }
        if (is_array($this->acab_revest)) {
            array_splice($this->acab_revest, $value, 1);
        }
        if (is_array($this->acab_bano)) {
            array_splice($this->acab_bano, $value, 1);
        }
        if (is_array($this->inst_elect_sanita)) {
            array_splice($this->inst_elect_sanita, $value, 1);
        }
        if (is_array($this->area_verificada)) {
            array_splice($this->area_verificada, $value, 1);
        }
        if (is_array($this->uca)) {
            array_splice($this->uca, $value, 1);
        }
    }

    /* CONSTRUCCIONES */

    /* OBRAS COMPLEMENTARIAS */

    public function aumentarObras()
    {
        $this->cont3++;
    }

    public function reducirObras()
    {
        $this->cont3--;
    }

    /* OBRAS COMPLEMENTARIAS */
    /* RECAP EDIFICIOS */
    public function aumentarEdificios()
    {
        $this->cont4++;
    }

    public function reducirEdificios()
    {
        $this->cont4--;
    }

    /* RECAP EDIFICIOS */

    /* INFORMACION FINAL*/
    public function updatednumdocumentodeclarante()
    {
        $dni=$this->numdocumentodeclarante;
        if($dni!=""){
            $token= config('services.apisunat.token');
            $urldni=config('services.apisunat.urldni');
            $response=Http::withHeaders([
                'Referer' => 'http://apis.net.pe/api-ruc',
                'Authorization' => 'Bearer ' . $token
            ])->get($urldni.$dni);

            $persona=($response->json());

            if(isset($persona['error']) || $persona==""){
                $this->nombres_declarante="";
                $this->apellido_paterno_declarante="";
                $this->apellido_materno_declarante="";
                $this->numdocumentodeclarante=$dni;
                if(isset($persona['error']))
                {
                    session()->flash('dark', 'Se necesita 8 digitos');
                }
                if($persona=="")
                {
                    session()->flash('dark', 'No se encontro datos');
                }
            }else{
                $this->nombres_declarante=$persona['nombres'];
                $this->apellido_paterno_declarante=$persona['apellidoPaterno'];
                $this->apellido_materno_declarante=$persona['apellidoMaterno'];
                $this->numdocumentodeclarante=$dni;
            }
        }
    }
    /* INFORMACION FINAL*/


    public function aumentarRecap()
    {
        $this->rbcedifica[$this->cont5]="";
        $this->rbcentrada[$this->cont5]="";
        $this->rbcnume_piso[$this->cont5]="";
        $this->rbcunidad[$this->cont5]="";
        $this->rbctotalporcentaje[$this->cont5]="";
        $this->rbctotalatc[$this->cont5]="";
        $this->rbctotalacc[$this->cont5]="";
        $this->rbctotalaoic[$this->cont5]="";
        $this->cont5++;
    }

    public function reducirRecap($i)
    {

        if($this->cont5 > 0){
            $this->cont5--;
            array_splice($this->rbcedifica, $i,1);
            array_splice($this->rbcentrada, $i,1);
            array_splice($this->rbcnume_piso, $i,1);
            array_splice($this->rbcunidad,$i,1);
            array_splice($this->rbctotalporcentaje, $i,1);
            array_splice($this->rbctotalatc,$i,1);
            array_splice($this->rbctotalacc, $i,1);
            array_splice($this->rbctotalaoic,$i,1);
        }
    }

    public function register()
    {
        try
        {
            $this->validate([
                'numeficha'                    => 'required|max:7|unique:tf_fichas_bienes_comunes,nume_ficha',
                'nume_ficha_lote'               => 'required|max:4',
                'nume_ficha_lote2'              => 'required|max:5',
                'cuc'                           => 'nullable|max:12',
                'sector'                        => 'required',
                'mzna'                          => 'required',
                'lote'                          => 'required|max:3',
                'edifica'                       => 'required|max:2',
                'entrada'                       => 'required|max:2',
                'piso'                          => 'required|max:2',
                'unidad'                        => 'required|max:3',

                'tipoHabi'                      => 'required',
                'zona_dist'                     => 'nullable|max:30',
                'mzna_dist'                     => 'nullable|max:15',
                'lote_dist'                     => 'nullable|max:5',
                'sub_lote_dist'                 => 'nullable|max:6',

                'tipo_edificacion'              => 'nullable',
                'nomb_edificacion'              => 'nullable|max:100',


                'clasificacion'                 => 'required',
                'cont_en'                       => 'required',
                'codi_uso'                      => 'required',
                'zonificacion'                  => 'required|max:30',
                'area_declarada'                => 'nullable|numeric|regex:/^[\d]{0,7}(\.[\d]{1,2})?$/',
                'area_verificada1'              => 'required|numeric|regex:/^[\d]{0,7}(\.[\d]{1,2})?$/',
                'area_declarada'                => 'nullable|numeric|regex:/^[\d]{0,7}(\.[\d]{1,2})?$/',
                'area_verificada1'              => 'required|numeric|regex:/^[\d]{0,7}(\.[\d]{1,2})?$/',
                'fren_campo'                    => 'nullable|max:100',
                'dere_campo'                    => 'nullable|max:100',
                'izqu_campo'                    => 'nullable|max:100',
                'fond_campo'                    => 'nullable|max:100',
                'fren_colinda_campo'            => 'nullable|max:100',
                'dere_colinda_campo'            => 'nullable|max:100',
                'izqu_colinda_campo'            => 'nullable|max:100',
                'fond_colinda_campo'            => 'nullable|max:100',

                'fren_titulo'                  => 'nullable|max:100',
                'dere_titulo'                  => 'nullable|max:100',
                'izqu_titulo'                  => 'nullable|max:100',
                'fond_titulo'                  => 'nullable|max:100',
                'fren_colinda_titulo'          => 'nullable|max:100',
                'dere_colinda_titulo'          => 'nullable|max:100',
                'izqu_colinda_titulo'          => 'nullable|max:100',
                'fond_colinda_titulo'          => 'nullable|max:100',

                'tipo_partida'                  => 'nullable',
                'nume_partida'                  => 'nullable|max:18',
                'fojas'                         => 'nullable|max:18',
                'asiento'                       => 'nullable|max:18',
                'fecha_inscripcion'             => 'nullable|date',
                'codi_decla_fabrica'            => 'nullable',
                'asie_fabrica'                  => 'nullable|max:18',
                'fecha_fabrica'                 => 'nullable|date',
                'en_colindante'                 => 'nullable|numeric|regex:/^[\d]{0,7}(\.[\d]{1,2})?$/',
                'en_area_publica'               => 'nullable|numeric|regex:/^[\d]{0,7}(\.[\d]{1,2})?$/',
                'en_jardin_aislamiento'         => 'nullable|numeric|regex:/^[\d]{0,7}(\.[\d]{1,2})?$/',
                'en_area_intangible'            => 'nullable|numeric|regex:/^[\d]{0,7}(\.[\d]{1,2})?$/',
                'cond_declarante'               => 'nullable',
                'esta_llenado'                  => 'required',

                'mantenimiento'                 => 'nullable',
                'observacion'                   => 'nullable|max:500',

                'numdocumentodeclarante'        => 'nullable|max:8',
                'nombres_declarante'            => 'nullable|max:150',
                'apellido_paterno_declarante'   => 'nullable|max:50',
                'apellido_materno_declarante'   => 'nullable|max:50',
                'fecha_declarante'              => 'nullable|date',
                'supervisor'                    => 'nullable',
                'fecha_supervision'             => 'nullable|date',
                'tecnico'                       => 'required',
                'fecha_levantamiento'           => 'required|date',
                'verificador'                   => 'nullable',
                'nume_registro'                 => 'nullable|max:10',
                'fecha_verificacion'            => 'nullable|date',

                'rbcnotaria'                    => 'nullable',
                'rbckardex'                     => 'nullable|max:20',
                'rbcfechaesc'                   => 'nullable|date',
            ]);

            for($i=0;$i<$this->cont;$i++){
                $this->validate([
                    'tipoVia.'.$i                     => 'required',
                    'tipopuerta.'.$i                  => 'required',
                    'nume_muni.'.$i                   => 'nullable|max:20',
                    'cond_nume.'.$i                   => 'nullable',
                ]);
            }

            for($i=0;$i<$this->cont2;$i++){
                $this->validate([
                    'num_piso.'.$i                    => 'required|max:2',
                    'bloque.'.$i                      => 'required|max:2',
                    'fecha.'.$i                       => 'nullable',
                    'mep.'.$i                         => 'nullable',
                    'ecs.'.$i                         => 'nullable',
                    'ecc.'.$i                         => 'nullable',
                    'estr_muro_col.'.$i               => 'nullable|max:1',
                    'estr_techo.'.$i                  => 'nullable|max:1',
                    'acab_piso.'.$i                   => 'nullable|max:1',
                    'acab_puerta_ven.'.$i             => 'nullable|max:1',
                    'acab_revest.'.$i                 => 'nullable|max:1',
                    'acab_bano.'.$i                   => 'nullable|max:1',
                    'inst_elect_sanita.'.$i           => 'nullable|max:1',
                    'area_verificada.'.$i             => 'nullable|numeric|regex:/^[\d]{0,8}(\.[\d]{1,2})?$/',
                    'uca.'.$i                         => 'nullable',
                ]);
            }


            for($i=0;$i<$this->cont3;$i++){
                $this->validate([
                    'codiinstalacion.'.$i            => 'required',
                    'inst_fecha.'.$i                  => 'nullable|date',
                    'inst_mep.'.$i                    => 'nullable',
                    'inst_ecs.'.$i                    => 'nullable',
                    'inst_ecc.'.$i                    => 'nullable',
                    'inst_prod_total.'.$i             => 'nullable|numeric|regex:/^[\d]{0,7}(\.[\d]{1,2})?$/',
                    'inst_uni_med.'.$i                => 'nullable|max:2',
                    'inst_uca.'.$i                    => 'nullable',
                ]);
            }

            for($i=0;$i<$this->cont4;$i++){
                $this->validate([
                    'edificiobbc.'.$i            => 'required|max:2',
                    'porcentaje.'.$i            => 'nullable|numeric',
                    'atc.'.$i                  => 'nullable|numeric',
                    'acc.'.$i                    => 'nullable|numeric',
                    'aoic.'.$i                    => 'nullable|numeric',
                ]);
            }

            for($i=0;$i<$this->cont5;$i++){
                $this->validate([
                    'rbcedifica.'.$i   =>  'required|max:2',
                    'rbcentrada.'.$i   =>  'required|max:2',
                    'rbcnume_piso.'.$i   =>  'required|max:2',
                    'rbcunidad.'.$i   =>  'required|max:3',
                    'rbctotalporcentaje.'.$i            => 'nullable|numeric',
                    'rbctotalatc.'.$i                  => 'nullable|numeric',
                    'rbctotalacc.'.$i                    => 'nullable|numeric',
                    'rbctotalaoic.'.$i                    => 'nullable|numeric',
                ]);
            }

            DB::beginTransaction();
            $ubigeo=Institucion::first();
            /*VALIDACIONES*/

            $mytime= Carbon::now('America/Lima');

            $sectorbuscar=str_pad($ubigeo->id_institucion,6,'0',STR_PAD_LEFT).''.str_pad($this->sector,2,'0',STR_PAD_LEFT);

            $sectorencontrar=Sectore::where('id_sector',$sectorbuscar)->first();
            if($sectorencontrar!=""){
                $sector=$sectorencontrar;
            }else{
                $sector= new Sectore();
                $sector->id_sector=str_pad($ubigeo->id_institucion,6,'0',STR_PAD_LEFT).''.str_pad($this->sector,2,'0',STR_PAD_LEFT);
                $sector->id_ubi_geo=str_pad($ubigeo->id_institucion,6,'0',STR_PAD_LEFT);
                $sector->codi_sector=str_pad($this->sector,2,'0',STR_PAD_LEFT);
                $sector->nomb_sector="SECTOR ".str_pad($this->sector,2,'0',STR_PAD_LEFT);
                $sector->estado=1;
                $sector->save();
            }

            $mznabuscar=str_pad($ubigeo->id_institucion,6,'0',STR_PAD_LEFT).''.str_pad($this->sector,2,'0',STR_PAD_LEFT).''.str_pad($this->mzna,3,'0',STR_PAD_LEFT);

            $mznaencontrar=Manzana::where('id_mzna',$mznabuscar)->first();
            if($mznaencontrar!=""){
                $mzna=$mznaencontrar;
            }else{
                $mzna= new Manzana();
                $mzna->id_mzna=str_pad($ubigeo->id_institucion,6,'0',STR_PAD_LEFT).''.str_pad($this->sector,2,'0',STR_PAD_LEFT).''.str_pad($this->mzna,3,'0',STR_PAD_LEFT);
                $mzna->id_sector=str_pad($ubigeo->id_institucion,6,'0',STR_PAD_LEFT).''.str_pad($this->sector,2,'0',STR_PAD_LEFT);
                $mzna->codi_mzna=str_pad($this->mzna,3,'0',STR_PAD_LEFT);
                $mzna->nume_mzna=str_pad($this->mzna,3,'0',STR_PAD_LEFT);
                $mzna->estado=1;
                $mzna->save();
            }

            $lotebuscar=str_pad($ubigeo->id_institucion,6,'0',STR_PAD_LEFT).''.str_pad($this->sector,2,'0',STR_PAD_LEFT).''.str_pad($this->mzna,3,'0',STR_PAD_LEFT).''.str_pad($this->lote,3,'0',STR_PAD_LEFT);

            $loteencontrar=Lote::where('id_lote',$lotebuscar)->first();
            if($loteencontrar!=""){
                $lote=$loteencontrar;
                $lote->id_mzna=str_pad($ubigeo->id_institucion,6,'0',STR_PAD_LEFT).''.str_pad($this->sector,2,'0',STR_PAD_LEFT).''.str_pad($this->mzna,3,'0',STR_PAD_LEFT);
                $lote->codi_lote=str_pad($this->lote,3,'0',STR_PAD_LEFT);
                $lote->id_hab_urba=$this->tipoHabi;
                $lote->mzna_dist=strtoupper($this->mzna_dist);
                $lote->lote_dist=$this->lote_dist;
                $lote->sub_lote_dist=$this->sub_lote_dist;
                $lote->zonificacion=$this->zonificacion;
                $lote->estructuracion=$this->estructuracion;
                $lote->zona_dist=$this->zona_dist;
                $lote->save();
            }else{
                $lote= new Lote();
                $lote->id_lote=str_pad($ubigeo->id_institucion,6,'0',STR_PAD_LEFT).''.str_pad($this->sector,2,'0',STR_PAD_LEFT).''.str_pad($this->mzna,3,'0',STR_PAD_LEFT).''.str_pad($this->lote,3,'0',STR_PAD_LEFT);
                $lote->id_mzna=str_pad($ubigeo->id_institucion,6,'0',STR_PAD_LEFT).''.str_pad($this->sector,2,'0',STR_PAD_LEFT).''.str_pad($this->mzna,3,'0',STR_PAD_LEFT);
                $lote->codi_lote=str_pad($this->lote,3,'0',STR_PAD_LEFT);
                $lote->id_hab_urba=$this->tipoHabi;
                $lote->mzna_dist=strtoupper($this->mzna_dist);
                $lote->lote_dist=$this->lote_dist;
                $lote->sub_lote_dist=$this->sub_lote_dist;
                $lote->zonificacion=$this->zonificacion;
                $lote->estructuracion=$this->estructuracion;
                $lote->zona_dist=$this->zona_dist;
                $lote->save();
            }

            $edificacionbuscar=str_pad($lote->id_lote,14,'0',STR_PAD_LEFT).''.str_pad($this->edifica,2,'0',STR_PAD_LEFT);

            $edificacionencontrar=Edificaciones::where('id_edificacion',$edificacionbuscar)->first();
            if($edificacionencontrar!=""){
                $edificacion=$edificacionencontrar;
                $edificacion->codi_edificacion=str_pad($this->edifica,2,'0',STR_PAD_LEFT);
                $edificacion->tipo_edificacion=$this->tipo_edificacion;
                $edificacion->clasificacion=$this->clasificacion;
                $edificacion->nomb_edificacion=$this->nomb_edificacion;
                $edificacion->save();
            }else{
                $edificacion= new Edificaciones();
                $edificacion->id_edificacion=str_pad($lote->id_lote,14,'0',STR_PAD_LEFT).''.str_pad($this->edifica,2,'0',STR_PAD_LEFT);
                $edificacion->id_lote=str_pad($lote->id_lote,14,'0',STR_PAD_LEFT);
                $edificacion->codi_edificacion=str_pad($this->edifica,2,'0',STR_PAD_LEFT);
                $edificacion->tipo_edificacion=$this->tipo_edificacion;
                $edificacion->clasificacion=$this->clasificacion;
                $edificacion->nomb_edificacion=$this->nomb_edificacion;
                $edificacion->save();
            }

            $unicatbuscar=str_pad($edificacion->id_edificacion,16,'0',STR_PAD_LEFT).''.str_pad($this->entrada,2,'0',STR_PAD_LEFT).''.str_pad($this->piso,2,'0',STR_PAD_LEFT).''.str_pad($this->unidad,3,'0',STR_PAD_LEFT);
            $unicatencontrar=UniCat::where('id_uni_cat',$unicatbuscar)->first();
            if($unicatencontrar!=""){
                $unicat=$unicatencontrar;
                $unicat->id_lote=$lote->id_lote;
                $unicat->id_edificacion=$edificacion->id_edificacion;
                $unicat->codi_entrada=str_pad($this->entrada,2,'0',STR_PAD_LEFT);
                $unicat->codi_piso=str_pad($this->piso,2,'0',STR_PAD_LEFT);
                $unicat->codi_unidad=str_pad($this->unidad,3,'0',STR_PAD_LEFT);
                $unicat->codi_hoja_catastral=str_pad($this->codi_hoja_catastral,10,'0',STR_PAD_LEFT);
                $unicat->codi_cont_rentas=$this->codi_cont_rentas;
                $unicat->codi_pred_rentas=$this->codi_pred_rentas;
                $unicat->cuc=str_pad($this->cuc,12,'0',STR_PAD_LEFT);
                $unicat->save();
            }else{
                $unicat= new UniCat();
                $unicat->id_uni_cat=str_pad($edificacion->id_edificacion,16,'0',STR_PAD_LEFT).''.str_pad($this->entrada,2,'0',STR_PAD_LEFT).''.str_pad($this->piso,2,'0',STR_PAD_LEFT).''.str_pad($this->unidad,3,'0',STR_PAD_LEFT);
                $unicat->id_lote=$lote->id_lote;
                $unicat->id_edificacion=$edificacion->id_edificacion;
                $unicat->codi_entrada=str_pad($this->entrada,2,'0',STR_PAD_LEFT);
                $unicat->codi_piso=str_pad($this->piso,2,'0',STR_PAD_LEFT);
                $unicat->codi_unidad=str_pad($this->unidad,3,'0',STR_PAD_LEFT);
                $unicat->codi_hoja_catastral=str_pad($this->codi_hoja_catastral,10,'0',STR_PAD_LEFT);
                $unicat->codi_cont_rentas=$this->codi_cont_rentas;
                $unicat->codi_pred_rentas=$this->codi_pred_rentas;
                $unicat->cuc=str_pad($this->cuc,12,'0',STR_PAD_LEFT);
                $unicat->save();
            }


            $mytime= Carbon::now('America/Lima');

            $date = $mytime->format('Y');
            if($this->numdocumentodeclarante!=''){
                $iddd=$this->numdocumentodeclarante.'5102';
                $buscarpersona=Persona::where('id_persona','=',$iddd)->first();
                if($buscarpersona!=""){
                    $declarante=$buscarpersona;
                }else{
                    $declarante= new Persona();
                    $declarante->id_persona=$this->numdocumentodeclarante.'5102';
                    $declarante->nume_doc=$this->numdocumentodeclarante;
                    $declarante->tipo_doc="02";
                    $declarante->tipo_persona=1;
                    $declarante->nombres=strtoupper($this->nombres_declarante);
                    $declarante->ape_paterno=strtoupper($this->apellido_paterno_declarante);
                    $declarante->ape_materno=strtoupper($this->apellido_materno_declarante);
                    $declarante->tipo_funcion=5;
                    $declarante->save();
                }
            }else{
                $declarante="";
            }


            $ficha=new Ficha();
            $ficha->id_ficha=$date.''.str_pad($ubigeo->id_institucion,6,'0',STR_PAD_LEFT).'04'.str_pad($this->numeficha,7,'0',STR_PAD_LEFT);
            $ficha->tipo_ficha="04";
            $ficha->nume_ficha=str_pad($this->numeficha,7,'0',STR_PAD_LEFT);
            $ficha->id_lote=$lote->id_lote;
            $ficha->dc=$this->dc;
            $ficha->nume_ficha_lote=$this->nume_ficha_lote.'-'.$this->nume_ficha_lote2;
            if($declarante==""){
            }else{
                $ficha->id_declarante=$declarante->id_persona;
            }
            if($this->fecha_declarante==""){
            }else{
                $ficha->fecha_declarante=$this->fecha_declarante;
            }

            if($this->supervisor==""){
            }else{
                $ficha->id_supervisor=$this->supervisor;
            }
            if($this->fecha_supervision==""){
            }else{
                $ficha->fecha_supervision=$this->fecha_supervision;
            }

            if($this->tecnico==""){
            }else{
                $ficha->id_tecnico=$this->tecnico;
            }
            if($this->fecha_levantamiento==""){
            }else{
                $ficha->fecha_levantamiento=$this->fecha_levantamiento;
            }

            if($this->verificador==""){
            }else{
                $ficha->id_verificador=$this->verificador;
            }
            if($this->fecha_verificacion==""){
            }else{
                $ficha->fecha_verificacion=$this->fecha_verificacion;
            }
            if($this->nume_registro==""){
            }else{
                $ficha->nume_registro=$this->nume_registro;
            }

            $ficha->id_uni_cat=$unicat->id_uni_cat;
            $ficha->id_usuario=\Auth::user()->id_usuario;
            $ficha->fecha_grabado=$mytime->toDateTimeString();
            $ficha->activo=1;
            $ficha->save();

            $contpuertas=0;
            while($contpuertas<$this->cont)
            {
                $contadorpuertas=$ficha->puertas()->where('tipo_puerta',$this->tipopuerta[$contpuertas])->count()+1;
                $buscarpuerta=$lote->id_lote.''.$this->tipopuerta[$contpuertas].''.$contadorpuertas;
                $encontrarpuerta=Puerta::where('id_puerta',$buscarpuerta)->first();
                if($encontrarpuerta!=""){
                    $puerta=$encontrarpuerta;
                    $puerta->id_lote=$lote->id_lote;
                    $puerta->codi_puerta=$this->tipopuerta[$contpuertas];
                    $puerta->tipo_puerta=$this->tipopuerta[$contpuertas];
                    if(isset($this->nume_muni[$contpuertas])){
                        $puerta->nume_muni=$this->nume_muni[$contpuertas];
                    }
                    if(isset($this->cond_nume[$contpuertas])){
                        $puerta->cond_nume=$this->cond_nume[$contpuertas];
                    }
                    if(isset($this->nume_certificacion[$contpuertas])){
                        $puerta->nume_certificacion=$this->nume_certificacion[$contpuertas];
                    }
                    $puerta->id_via=$this->tipoVia[$contpuertas];
                    $puerta->save();
                }else{
                    $puerta= new Puerta();
                    $puerta->id_puerta=$lote->id_lote.''.$this->tipopuerta[$contpuertas].''.$contadorpuertas;
                    $puerta->id_lote=$lote->id_lote;
                    $puerta->codi_puerta=$this->tipopuerta[$contpuertas];
                    $puerta->tipo_puerta=$this->tipopuerta[$contpuertas];
                    if(isset($this->nume_muni[$contpuertas])){
                        $puerta->nume_muni=$this->nume_muni[$contpuertas];
                    }
                    if(isset($this->cond_nume[$contpuertas])){
                        $puerta->cond_nume=$this->cond_nume[$contpuertas];
                    }
                    if(isset($this->nume_certificacion[$contpuertas])){
                        $puerta->nume_certificacion=$this->nume_certificacion[$contpuertas];
                    }
                    $puerta->id_via=$this->tipoVia[$contpuertas];
                    $puerta->save();
                }

                $contpuertas++;
                $puerta->fichas()->attach(str_pad($ficha->id_ficha,19,'0',STR_PAD_LEFT));
                $puerta->via->hab_urbanas()->attach($this->tipoHabi);

            }


            $fichabiencomun=new FichaBienComun();
            $fichabiencomun->id_ficha=$ficha->id_ficha;
            $fichabiencomun->cont_en=$this->cont_en;
            $fichabiencomun->clasificacion=$this->clasificacion;

            if($this->area_declarada!=""){
                $fichabiencomun->area_titulo=$this->area_declarada;
            }


            if($this->area_declarada!=""){
                $fichabiencomun->area_declarada=$this->area_declarada;
            }


            if($this->area_verificada1!=""){
                $fichabiencomun->area_verificada=$this->area_verificada1;
            }

            if($this->en_colindante!=""){
                $fichabiencomun->en_colindante=$this->en_colindante;
            }

            if($this->en_jardin_aislamiento!=""){
                $fichabiencomun->en_jardin_aislamiento=$this->en_jardin_aislamiento;
            }

            if($this->en_area_publica!=""){
                $fichabiencomun->en_area_publica=$this->en_area_publica;
            }

            if($this->en_area_intangible!=""){
                $fichabiencomun->en_area_intangible=$this->en_area_intangible;
            }

            $fichabiencomun->cond_declarante=$this->cond_declarante;
            $fichabiencomun->esta_llenado=$this->esta_llenado;
            $fichabiencomun->mantenimiento=$this->mantenimiento;
            $fichabiencomun->observaciones=$this->observacion;
            $fichabiencomun->codi_uso=$this->codi_uso;
            $fichabiencomun->nume_ficha=str_pad($this->numeficha,7,'0',STR_PAD_LEFT);
            $fichabiencomun->save();

            $lindero=new Lindero();
            $lindero->id_ficha=$ficha->id_ficha;
            $lindero->fren_campo=$this->fren_campo;
            $lindero->fren_colinda_campo=$this->fren_colinda_campo;
            $lindero->dere_campo=$this->dere_campo;
            $lindero->dere_colinda_campo=$this->dere_colinda_campo;
            $lindero->izqu_campo=$this->izqu_campo;
            $lindero->izqu_colinda_campo=$this->izqu_colinda_campo;
            $lindero->fond_campo=$this->fond_campo;
            $lindero->fond_colinda_campo=$this->fond_colinda_campo;

            $lindero->fren_titulo=$this->fren_titulo;
            $lindero->dere_titulo=$this->dere_titulo;
            $lindero->izqu_titulo=$this->izqu_titulo;
            $lindero->fond_titulo=$this->fond_titulo;
            $lindero->fren_colinda_titulo=$this->fren_colinda_titulo;
            $lindero->dere_colinda_titulo=$this->dere_colinda_titulo;
            $lindero->izqu_colinda_titulo=$this->izqu_colinda_titulo;
            $lindero->fond_colinda_titulo=$this->fond_colinda_titulo;
            $lindero->save();

            $servicios=new ServicioBasico();
            $servicios->id_ficha=$ficha->id_ficha;
            if($this->luz=='on'){
                $servicios->luz=1;
            }else{
                $servicios->luz=2;
            }
            if($this->agua=='on'){
                $servicios->agua=1;
            }else{
                $servicios->agua=2;
            }
            if($this->telefono=='on'){
                $servicios->telefono=1;
            }else{
                $servicios->telefono=2;
            }
            if($this->desague=='on'){
                $servicios->desague=1;
            }else{
                $servicios->desague=2;
            }
            if($this->gas=='on'){
                $servicios->gas=1;
            }else{
                $servicios->gas=2;
            }
            if($this->internet=='on'){
                $servicios->internet=1;
            }else{
                $servicios->internet=2;
            }
            if($this->tvcable=='on'){
                $servicios->tvcable=1;
            }else{
                $servicios->tvcable=2;
            }
            $servicios->save();

            $contcon=0;
            $construcciones=$this->cont2;
            if($construcciones!=""){
                while($contcon < $this->cont2){
                    $construccion=new Construccion();
                    $construccion->id_construccion=$ficha->id_ficha.''.$this->num_piso[$contcon].''.$contcon+1;
                    $construccion->id_ficha=$ficha->id_ficha;
                    $construccion->codi_construccion=$contcon+1;

                    if(isset($this->bloque[$contcon])){
                        if($this->bloque[$contcon]!=""){
                            $construccion->bloque=$this->bloque[$contcon];
                        }
                    }
                    if(isset($this->num_piso[$contcon])){
                        if($this->num_piso[$contcon]!=""){
                            $construccion->nume_piso=$this->num_piso[$contcon];
                        }
                    }

                    if(isset($this->fecha[$contcon])){
                        if($this->fecha[$contcon]!=""){
                            $fechaformato = $this->fecha[$contcon].'-01';
                            $construccion->fecha=$fechaformato;
                        }
                    }


                    if(isset($this->mep[$contcon])){
                        if($this->mep[$contcon]!=""){
                            $construccion->mep=$this->mep[$contcon];
                        }
                    }



                    if(isset($this->ecs[$contcon])){
                        if($this->ecs[$contcon]!=""){
                            $construccion->ecs=$this->ecs[$contcon];
                        }
                    }



                    if(isset($this->ecc[$contcon])){
                        if($this->ecc[$contcon]!=""){
                            $construccion->ecc=$this->ecc[$contcon];
                        }
                    }



                    if(isset($this->estr_muro_col[$contcon])){
                        if($this->estr_muro_col[$contcon]!=""){
                            $construccion->estr_muro_col=$this->estr_muro_col[$contcon];
                        }
                    }


                    if(isset($this->estr_techo[$contcon])){
                        if($this->estr_techo[$contcon]!=""){
                            $construccion->estr_techo=$this->estr_techo[$contcon];
                        }
                    }



                    if(isset($this->acab_piso[$contcon])){
                        if($this->acab_piso[$contcon]!=""){
                            $construccion->acab_piso=$this->acab_piso[$contcon];
                        }
                    }




                    if(isset($this->acab_puerta_ven[$contcon])){
                        if($this->acab_puerta_ven[$contcon]!=""){
                            $construccion->acab_puerta_ven=$this->acab_puerta_ven[$contcon];
                        }
                    }



                    if(isset($this->acab_revest[$contcon])){
                        if($this->acab_revest[$contcon]!=""){
                            $construccion->acab_revest=$this->acab_revest[$contcon];
                        }
                    }



                    if(isset($this->acab_bano[$contcon])){
                        if($this->acab_bano[$contcon]!=""){
                            $construccion->acab_bano=$this->acab_bano[$contcon];
                        }
                    }


                    if(isset($this->inst_elect_sanita[$contcon])){
                        if($this->inst_elect_sanita[$contcon]!=""){
                            $construccion->inst_elect_sanita=$this->inst_elect_sanita[$contcon];
                        }
                    }

                    $construccion->area_declarada=0.00;


                    if(isset($this->area_verificada[$contcon])){
                        if($this->area_verificada[$contcon]!=""){
                            $construccion->area_verificada=$this->area_verificada[$contcon];
                        }
                    }

                    if(isset($this->uca[$contcon])){
                        if($this->uca[$contcon]!=""){
                            $construccion->uca=$this->uca[$contcon];
                        }
                    }

                    $construccion->save();
                    $contcon++;
                }
            }

            $contins=0;
            while($contins < $this->cont3)
                {
                    $instalacion=new Instalacion();
                    $instalacion->id_instalacion=$ficha->id_ficha.''.$this->codiinstalacion[$contins].''.$contins+1;
                    $instalacion->id_ficha=$ficha->id_ficha;
                    $instalacion->codi_obra=$contins+1;
                    if(isset($this->codiinstalacion[$contins])){
                        if($this->codiinstalacion[$contins]!=""){
                            $instalacion->codi_instalacion=$this->codiinstalacion[$contins];
                        }
                    }

                    if(isset($this->inst_fecha[$contins])){
                        if($this->inst_fecha[$contins]!=""){
                            $fechaformato = $this->inst_fecha[$contins].'-01';
                            $instalacion->fecha=$fechaformato;
                        }else{
                            $instalacion->fecha="";
                        }
                    }

                    if(isset($this->inst_mep[$contins])){
                        if($this->inst_mep[$contins]!=""){
                            $instalacion->mep=$this->inst_mep[$contins];
                        }
                    }

                    if(isset($this->inst_ecs[$contins])){
                        if($this->inst_ecs[$contins]!=""){
                            $instalacion->ecs=$this->inst_ecs[$contins];
                        }
                    }

                    if(isset($this->inst_ecc[$contins])){
                        if($this->inst_ecc[$contins]!=""){
                            $instalacion->ecc=$this->inst_ecc[$contins];
                        }
                    }
                    if(isset($this->dime_largo[$contins])){
                        if($this->dime_largo[$contins]!=""){
                            $instalacion->dime_largo=$this->dime_largo[$contins];
                        }else{
                            $instalacion->dime_largo=0;
                        }
                    }
                    if(isset($this->dime_ancho[$contins])){
                        if($this->dime_ancho[$contins]!=""){
                            $instalacion->dime_ancho=$this->dime_ancho[$contins];
                        }else{
                            $instalacion->dime_ancho=0;
                        }
                    }
                    if(isset($this->dime_alto[$contins])){
                        if($this->dime_alto[$contins]!=""){
                            $instalacion->dime_alto=$this->dime_alto[$contins];
                        }else{
                            $instalacion->dime_alto=0;
                        }
                    }

                    if(isset($this->inst_prod_total[$contins])){
                        if($this->inst_prod_total[$contins]!=""){
                            $instalacion->prod_total=$this->inst_prod_total[$contins];
                        }
                    }

                    if(isset($this->inst_uni_med[$contins])){
                        if($this->inst_uni_med[$contins]!="" || isset($this->inst_uni_med[$contins])){
                            $instalacion->uni_med=$this->inst_uni_med[$contins];
                        }
                    }

                    if(isset($this->inst_uca[$contins])){
                        if($this->inst_uca[$contins]!=""){
                            $instalacion->uca=$this->inst_uca[$contins];
                        }
                    }
                    $instalacion->save();
                    $contins++;
            }

            if($this->tipo_partida!=""||$this->nume_partida!=""||$this->fojas!=""||$this->asiento!=""||$this->fecha_inscripcion!=""||$this->codi_decla_fabrica!=""){
                $sunarp=new Sunarp();
                $sunarp->id_ficha=$ficha->id_ficha;
                $sunarp->tipo_partida=$this->tipo_partida;
                $sunarp->nume_partida=$this->nume_partida;
                $sunarp->fojas=$this->fojas;
                $sunarp->asiento=$this->asiento;

                if(isset($this->fecha_inscripcion)){
                    if($this->fecha_inscripcion!=""){
                        $sunarp->fecha_inscripcion=$this->fecha_inscripcion;
                    }
                }
                $sunarp->codi_decla_fabrica=$this->codi_decla_fabrica;
                $sunarp->asie_fabrica=$this->asie_fabrica;
                if(isset($this->fecha_fabrica)){
                    if($this->fecha_fabrica!=""){
                        $sunarp->fecha_fabrica=$this->fecha_fabrica;
                    }
                }
                $sunarp->save();
            }

            $contedif=0;
            $edificios=$this->porcentaje;
            if($edificios!=""){
                while($contedif < count($edificios))
                {
                    $edificio=new RecapEdificio();
                    $edificio->id_ficha=$ficha->id_ficha;
                    $edificio->edificio=$this->edificiobbc[$contedif];
                    if(isset($this->porcentaje[$contedif])){
                        $edificio->total_porcentaje=$this->porcentaje[$contedif];
                    }
                    if(isset($this->atc[$contedif])){
                        if($this->atc[$contedif] !=""){
                            $edificio->total_atc=$this->atc[$contedif];
                        }
                        else{
                            $edificio->total_atc=0;
                        }
                    }
                    if(isset($this->acc[$contedif])){
                        if($this->acc[$contedif] !=""){
                            $edificio->total_acc=$this->acc[$contedif];
                        }
                        else{
                            $edificio->total_acc=0;
                        }
                    }
                    if(isset($this->aoic[$contedif])){
                        if($this->aoic[$contedif] !=""){
                            $edificio->total_aoic=$this->aoic[$contedif];
                        }
                        else{
                            $edificio->total_aoic=0;
                        }
                    }
                    $edificio->id_recap=$contedif+1;
                    $edificio->save();
                    $contedif++;
                }
            }

            $contbbc=0;
            $recapbbcc=$this->rbctotalporcentaje;
            if($recapbbcc!=""){
                while($contbbc < count($recapbbcc))
                {
                    $recapbbcc2= new RecapBbcc();
                    $recapbbcc2->id_ficha=$ficha->id_ficha;
                    $recapbbcc2->edifica=$this->rbcedifica[$contbbc];
                    $recapbbcc2->entrada=$this->rbcentrada[$contbbc];
                    $recapbbcc2->nume_piso=$this->rbcnume_piso[$contbbc];
                    $recapbbcc2->unidad=$this->rbcunidad[$contbbc];
                    if(isset($this->rbctotalporcentaje[$contbbc])){
                        $recapbbcc2->porcentaje=$this->rbctotalporcentaje[$contbbc];
                    }

                    if(isset($this->rbctotalporcentaje[$contbbc])){
                        if($this->rbctotalaoic[$contbbc] !=""){
                            $recapbbcc2->porcentaje=$this->rbctotalporcentaje[$contbbc];
                        }else{
                            $recapbbcc2->porcentaje=0;
                        }
                    }


                    if(isset($this->rbctotalatc[$contbbc])){
                        if($this->rbctotalatc[$contbbc] != ""){
                            $recapbbcc2->atc=$this->rbctotalatc[$contbbc];
                        }
                        $recapbbcc2->atc=0;

                    }
                    if(isset($this->rbctotalacc[$contbbc])){
                        if($this->rbctotalacc[$contbbc] !=""){
                            $recapbbcc2->acc=$this->rbctotalacc[$contbbc];
                        }
                        $recapbbcc2->acc=0;

                    }

                    if(isset($this->rbctotalaoic[$contbbc])){
                        if($this->rbctotalaoic[$contbbc] !=""){
                            $recapbbcc2->aoic=$this->rbctotalaoic[$contbbc];
                        }else{
                            $recapbbcc2->aoic=0;
                        }
                    }
                    $recapbbcc2->nume_registro= $contbbc+1;
                    $recapbbcc2->save();
                    $contbbc++;
                }
            }

            if($this->rbcnotaria!=""){
                $registrolegal=new RegistroLegal();
                $registrolegal->id_ficha=$ficha->id_ficha;
                $registrolegal->id_notaria=$this->rbcnotaria;
                $registrolegal->kardex=$this->rbckardex;
                $registrolegal->fecha_escritura=$this->rbcfechaesc;
                if(isset($this->rbcfechaesc)){
                    $registrolegal->fecha_escritura=$this->rbcfechaesc;
                }
                $registrolegal->save();
            }
            DB::commit();
        }
        catch(Exception $e){
            DB::rollBack();
        }
        return redirect()->route('reporte.reportelista')
        ->with('success', 'Ficha Bien Comun Agregado Correctamente.');
    }
    
    //CAMBIOS WANCHAQ
    public function updatedmzna($value)
    {
        $this->mzna = str_pad($value, 3, '0', STR_PAD_LEFT);
    }
    public function updatedlote($value)
    {
        $this->lote = str_pad($value, 3, '0', STR_PAD_LEFT);
    }
    public function updatededifica($value)
    {
        $this->edifica = str_pad($value, 2, '0', STR_PAD_LEFT);
    }
    
}
