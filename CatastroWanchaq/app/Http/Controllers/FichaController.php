<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Institucion;
use App\Models\Uso;
use App\Models\Ficha;
use App\Models\UniCat;
use App\Models\Puerta;
use App\Models\Persona;
use App\Models\DomicilioTitular;
use App\Models\ExoneracionPredio;
use App\Models\FichaIndividual;
use App\Models\Lindero;
use App\Models\ServicioBasico;
use App\Models\Construccion;
use App\Models\Instalacion;
use App\Models\DocumentoAdjunto;
use App\Models\RegistroLegal;
use App\Models\Sunarp;
use App\Models\Litigante;
use App\Models\Lote;
use App\Models\Sectore;
use App\Models\Titular;
use App\Models\Edificaciones;
use App\Models\Via;
use App\Models\HabUrbana;
use App\Models\TablaCodigo;
use Carbon\Carbon;
use App\Models\GenerarNumeracion;
use App\Models\GenerarCertificado;
use DB;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LotesPropietariosExports;

class FichaController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:pdf.individual')->only('fichaIndividual');
        $this->middleware('can:pdf.cotitularidad')->only('fichaCotitularidad');
        $this->middleware('can:pdf.economica')->only('fichaEconomica');
        $this->middleware('can:pdf.bienescomunes')->only('fichaInformativa');
        $this->middleware('can:pdf.informativa')->only('informativa');
        $this->middleware('can:pdf.bienesculturales')->only('fichabienesCulturales');
        $this->middleware('can:pdf.rural')->only('fichaRural');
        $this->middleware('can:ficha.createindividual')->only('createindividual');
        $this->middleware('can:ficha.destroyindividual')->only('destroyindividual');
        $this->middleware('can:ficha.editindividual')->only('editindividual');
        $this->middleware('can:ficha.editrentasindividual')->only('editrentasindividual');
    }
    public function index()
    {
        $fichas = Ficha::all();
        return view('pages.fichas.index', compact('fichas'));
    }

    public function generarCombo($variable)
    {
        $coleccion = TablaCodigo::where('id_tabla', '=', $variable)->get();
        return $coleccion;
    }

    public function fichacreateotra(Ficha $ficha)
    {
        return view('pages.fichas.fichacreateotra', compact('ficha'));
    }


    public function createindividual()
    {
        $usos = Uso::all();
        $tecnicos = Persona::where('tipo_funcion', 3)->get();
        $supervisores = Persona::where('tipo_funcion', 2)->get();
        $verificadores = Persona::where('tipo_funcion', 4)->get();
        return view('pages.fichas.create', compact('usos', 'tecnicos', 'supervisores', 'verificadores'));
    }

    public function destroyindividual(Ficha $fichaanterior)
    {
        foreach ($fichaanterior->litigantes as $litigante) {
            $litigante->delete();
        }
        if ($fichaanterior->sunarp != "") {
            $fichaanterior->sunarp->delete();
        }
        foreach ($fichaanterior->documento_adjuntos as $documento) {
            $documento->delete();
        }
        foreach ($fichaanterior->instalacions as $instalacion) {
            $instalacion->delete();
        }
        foreach ($fichaanterior->construccions as $construccion) {
            $construccion->delete();
        }
        if ($fichaanterior->serviciobasico != "") {
            $fichaanterior->serviciobasico->delete();
        }
        if ($fichaanterior->lindero != "") {
            $fichaanterior->lindero->delete();
        }
        if ($fichaanterior->fichaindividual != "") {
            $fichaanterior->fichaindividual->delete();
        }
        if ($fichaanterior->domiciliotitular != "") {
            $fichaanterior->domiciliotitular->delete();
        }
        if ($fichaanterior->domiciliotitular != "") {
            $fichaanterior->domiciliotitular->delete();
        }
        foreach ($fichaanterior->exoneraciontitulars as $exo) {
            $exo->delete();
        }
        foreach ($fichaanterior->titulars as $titular) {
            $titular->delete();
        }
        foreach ($fichaanterior->puertas as $puerta) {
            $puerta->fichas()->detach($fichaanterior->id_ficha);
            $puerta->delete();
        }
        $fichaanterior->fichasUnicatRelacionadas()->delete();
        $fichaanterior->delete();

        return redirect()->back()->with('success', 'Ficha Eliminado Correctamente!');
    }

    public function editindividual(Request $request)
    {


        $fichaanterior = Ficha::where('id_ficha', $request->fichaanterior)->first();
        return view('pages.fichas.editindividual', compact('fichaanterior'));
    }
    public function editrentasindividual(Request $request)
    {


        $fichaanterior = Ficha::where('id_ficha', $request->fichaanterior)->first();
        return view('pages.fichas.editrentasindividual', compact('fichaanterior'));
    }


    public function createbienesculturales()
    {
        $usos = Uso::all();
        $tecnicos = Persona::where('tipo_funcion', 3)->get();
        $supervisores = Persona::where('tipo_funcion', 2)->get();
        $verificadores = Persona::where('tipo_funcion', 4)->get();
        return view('pages.fichas.createbienesculturales', compact('usos', 'tecnicos', 'supervisores', 'verificadores'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $ubigeo = Institucion::first();
            /*VALIDACIONES*/
            $requ = \Validator::make($request->all(), [
                'nume_ficha'                    => 'required|max:7|unique:ficha_individuals',
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
                'codi_cont_rentas'              => 'nullable|max:15',
                'codi_pred_rentas'              => 'nullable|max:15',
                'tipoHabi'                      => 'required',
                'zona_dist'                     => 'nullable|max:30',
                'mzna_dist'                     => 'nullable|max:15',
                'lote_dist'                     => 'nullable|max:5',
                'sub_lote_dist'                 => 'nullable|max:6',
                'via_id.*'                     => 'required',
                'tipo_puerta.*'                  => 'required',
                'nume_muni.*'                   => 'nullable|max:20',
                'cond_nume.*'                   => 'nullable',
            ]);
            /*VALIDACIONES*/

            if ($requ->fails()) {
                return Redirect::back()->with('error_code', 5)->withErrors($requ->errors())->withInput();
            }



            $lote = new Lote();
            $lote->id_lote = str_pad($ubigeo->id_institucion, 6, '0', STR_PAD_LEFT) . '' . str_pad($request->sector, 2, '0', STR_PAD_LEFT) . '' . str_pad($request->mzna, 3, '0', STR_PAD_LEFT) . '' . str_pad($request->lote, 3, '0', STR_PAD_LEFT);
            $lote->id_mzna = str_pad($ubigeo->id_institucion, 6, '0', STR_PAD_LEFT) . '' . str_pad($request->sector, 2, '0', STR_PAD_LEFT) . '' . str_pad($request->mzna, 3, '0', STR_PAD_LEFT);
            $lote->codi_lote = str_pad($request->lote, 3, '0', STR_PAD_LEFT);
            $lote->id_hab_urba = $request->hab_urbana_id;
            $lote->mzna_dist = strtoupper($request->mzna_dist);
            $lote->lote_dist = $request->lote_dist;
            $lote->sub_lote_dist = $request->sub_lote_dist;
            $lote->estructuracion = $request->estructuracion;/*Vacio*/
            $lote->zonificacion = $request->zonificacion;/*Vacio*/
            $lote->cuc = $request->cuc2;/*Vacio*/
            $lote->zona_dist = $request->zona_dist;
            $lote->save();

            $edificacion = new Edificaciones();
            $edificacion->id_edificacion = str_pad($lote->id_lote, 14, '0', STR_PAD_LEFT) . '' . str_pad($request->edifica, 2, '0', STR_PAD_LEFT);
            $edificacion->id_lote = str_pad($lote->id_lote, 14, '0', STR_PAD_LEFT);
            $edificacion->codi_edificacion = str_pad($request->edifica, 2, '0', STR_PAD_LEFT);
            $edificacion->tipo_edificacion = $request->tipo_edificacion;
            $edificacion->nomb_edificacion = strtoupper($request->nomb_edificacion);/*Vacio*/
            $edificacion->clasificacion = $request->clasificacion;
            $edificacion->save();

            $unicat = new UniCat();
            $unicat->id_uni_cat = str_pad($edificacion->id_edificacion, 16, '0', STR_PAD_LEFT) . '' . str_pad($request->entrada, 2, '0', STR_PAD_LEFT) . '' . str_pad($request->piso, 2, '0', STR_PAD_LEFT) . '' . str_pad($request->unidad, 3, '0', STR_PAD_LEFT);
            $unicat->id_lote = $lote->id_lote;
            $unicat->id_edificacion = $edificacion->id_edificacion;
            $unicat->codi_entrada = str_pad($request->entrada, 2, '0', STR_PAD_LEFT);
            $unicat->codi_piso = str_pad($request->piso, 2, '0', STR_PAD_LEFT);
            $unicat->codi_unidad = str_pad($request->unidad, 3, '0', STR_PAD_LEFT);
            $unicat->tipo_interior = $request->tipo_interior;
            $unicat->cuc = str_pad($request->cuc, 12, '0', STR_PAD_LEFT);
            $unicat->cuc_antecedente = $request->cuc_antecedente;/*Vacio*/
            $unicat->codi_hoja_catastral = $request->codi_hoja_catastral;/*Vacio*/
            $unicat->codi_pred_rentas = $request->codi_pred_rentas;
            $unicat->nume_interior = $request->nume_interior;
            $unicat->unid_acum_rentas = $request->unid_acum_rentas;/*Vacio*/
            $unicat->codi_cont_rentas = $request->codi_cont_rentas;
            $unicat->save();

            $mytime = Carbon::now('America/Lima');

            $date = $mytime->format('Y');
            if ($request->num_documento_declarante != '') {
                $buscarpersona = Persona::where('nume_doc', $request->num_documento_declarante)->where('tipo_funcion', 5)->first();
                if ($buscarpersona != "") {
                    $declarante = $buscarpersona;
                } else {
                    $declarante = new Persona();
                    $declarante->id_persona = $request->num_documento_declarante . '5102';
                    $declarante->nume_doc = $request->num_documento_declarante;
                    $declarante->tipo_doc = "02";
                    $declarante->tipo_persona = 1;
                    $declarante->nombres = strtoupper($request->nombres_declarante);
                    $declarante->ape_paterno = strtoupper($request->apellido_paterno_declarante);
                    $declarante->ape_materno = strtoupper($request->apellido_materno_declarante);
                    $declarante->tipo_persona_juridica = $request->tipo_persona_juridica;/*Vacio*/
                    $declarante->tipo_funcion = 5;
                    $declarante->save();
                }
            } else {
                $declarante = "";
            }


            $ficha = new Ficha();
            $ficha->id_ficha = $date . '' . str_pad($ubigeo->id_institucion, 6, '0', STR_PAD_LEFT) . '01' . str_pad($request->nume_ficha, 7, '0', STR_PAD_LEFT);
            $ficha->tipo_ficha = "01";
            $ficha->nume_ficha = str_pad($request->nume_ficha, 7, '0', STR_PAD_LEFT);
            $ficha->id_lote = $lote->id_lote;
            $ficha->dc = $request->dc;
            $ficha->nume_ficha_lote = $request->nume_ficha_lote . '-' . $request->nume_ficha_lote2;
            if ($declarante == "") {
                $ficha->id_declarante = $request->num_documento_declarante;
            } else {
                $ficha->id_declarante = $declarante->id_persona;
            }
            $ficha->fecha_declarante = $request->fecha_declarante;
            $ficha->id_supervisor = $request->supervisor;
            $ficha->fecha_supervision = $request->fecha_supervision;
            $ficha->id_tecnico = $request->tecnico;
            $ficha->fecha_levantamiento = $request->fecha_levantamiento;
            $ficha->id_verificador = $request->verificador;
            $ficha->fecha_verificacion = $request->fecha_verificacion;
            $ficha->nume_registro = $request->nume_registro;
            $ficha->id_uni_cat = $unicat->id_uni_cat;
            $ficha->id_usuario = \Auth::user()->id_usuario;
            $ficha->fecha_grabado = $mytime->toDateTimeString();
            $ficha->activo = 1;
            $ficha->save();

            $puertas = $request->via_id;
            $contpuertas = 0;
            while ($contpuertas < count($puertas)) {
                $puerta = new Puerta();
                $puerta->id_puerta = $lote->id_lote . '' . $request->tipo_puerta[$contpuertas] . '' . $contpuertas;
                $puerta->id_lote = $lote->id_lote;
                $puerta->codi_puerta = $request->tipo_puerta[$contpuertas];
                $puerta->tipo_puerta = $request->tipo_puerta[$contpuertas];
                $puerta->nume_muni = $request->nume_muni[$contpuertas];
                $puerta->cond_nume = $request->cond_nume[$contpuertas];
                $puerta->id_via = $request->via_id[$contpuertas];
                $puerta->nume_certificacion = $request->nume_certificacion;/*Vacio*/
                $puerta->save();

                $contpuertas++;
                $puerta->fichas()->sync(str_pad($ficha->id_ficha, 19, '0', STR_PAD_LEFT));
            }

            if ($request->tipo_persona == 1) {
                $buscarpersona2 = Persona::where('nume_doc', $request->nume_doc1)->where('tipo_funcion', 1)->first();
                if ($buscarpersona2 != "") {
                    $persona = $buscarpersona2;

                    $titular = new Titular();
                    $titular->id_ficha = $ficha->id_ficha;
                    $titular->id_persona = $persona->id_persona;
                    $titular->form_adquisicion = $request->form_adquisicion;
                    $titular->fecha_adquisicion = $request->fecha_adquisicion;
                    $titular->porc_cotitular = 0.00;
                    $titular->esta_civil = $request->esta_civil1;
                    $titular->fax = $request->fax;
                    $titular->telf = $request->telefono_num;
                    $titular->anexo = $request->anexo;
                    $titular->email = $request->email;
                    $titular->nume_titular = "TITULAR N° 1";
                    $titular->codi_contribuyente = $request->codi_contribuyente;
                    $titular->cond_titular = $request->cond_titular;
                    $titular->save();

                    if ($request->ubicacionpersona == '01') {
                        $viaDomicilio = Via::where('id_via', $request->via_id[0])->first();
                        $habDomicilio = HabUrbana::where('id_hab_urba', $request->hab_urbana_id)->first();
                        $domicilio = new DomicilioTitular();
                        $domicilio->id_ficha = $ficha->id_ficha;
                        $domicilio->id_persona = $persona->id_persona;
                        $domicilio->codi_via = $viaDomicilio->codi_via;
                        $domicilio->tipo_via = $viaDomicilio->tipo_via;
                        $domicilio->nomb_via = $viaDomicilio->nomb_via;
                        $domicilio->nume_muni = $request->nume_muni[0];
                        $domicilio->nomb_edificacion = strtoupper($request->nomb_edificacion);
                        $domicilio->nume_interior = $request->nume_interior;
                        $domicilio->codi_hab_urba = $habDomicilio->codi_hab_urba;
                        $domicilio->nomb_hab_urba = $habDomicilio->nomb_hab_urba;
                        $domicilio->sector = $request->zona_dist;
                        $domicilio->mzna = $request->mzna_dist;
                        $domicilio->lote = $request->lote_dist;
                        $domicilio->sublote = $request->sub_lote_dist;
                        $domicilio->codi_dep = "08";
                        $domicilio->codi_pro = "01";
                        $domicilio->codi_dis = "08";
                        $domicilio->ubicacion = $request->ubicacionpersona;
                        $domicilio->save();
                    } elseif ($request->ubicacionpersona == '02') {
                        $domicilio = new DomicilioTitular();
                        $domicilio->id_ficha = $ficha->id_ficha;
                        $domicilio->id_persona = $persona->id_persona;
                        $domicilio->codi_via = $request->codigoviaotros;
                        $domicilio->tipo_via = strtoupper($request->tipoviaotros);
                        $domicilio->nomb_via = strtoupper($request->nombreviaotros);
                        $domicilio->nume_muni = $request->nmunicipalotros;
                        $domicilio->nomb_edificacion = strtoupper($request->nomb_edificacion);
                        $domicilio->nume_interior = $request->ninteriorotros;
                        $domicilio->codi_hab_urba = $request->codigohurbanootros;
                        $domicilio->nomb_hab_urba = $request->nombrehhurbanaotros;
                        $domicilio->sector = $request->zonaootros;
                        $domicilio->mzna = $request->manzanaotros;
                        $domicilio->lote = $request->loteotros;
                        $domicilio->sublote = $request->subloteotros;
                        $domicilio->codi_dep = $request->deparamentootros;
                        $domicilio->codi_pro = $request->provinciaotros;
                        $domicilio->codi_dis = $request->distritootros;
                        $domicilio->ubicacion = $request->ubicacionpersona;
                        $domicilio->save();
                    }
                } else {
                    $persona = new Persona();
                    if ($request->nume_doc1 == "") {
                        $cantidadpersona = Persona::where('tipo_persona', 1)->count() + 1;
                        $persona->id_persona = str_pad($cantidadpersona, 8, '0', STR_PAD_LEFT) . '11' . $request->tipo_doc1;
                        $persona->nume_doc = str_pad($cantidadpersona, 8, '0', STR_PAD_LEFT);
                    } else {
                        $persona->id_persona = str_pad($request->nume_doc1, 8, '0', STR_PAD_LEFT) . '11' . $request->tipo_doc1;
                        $persona->nume_doc = str_pad($request->nume_doc1, 8, '0', STR_PAD_LEFT);
                    }
                    $persona->tipo_doc = $request->tipo_doc1;
                    $persona->tipo_persona = 1;
                    $persona->nombres = strtoupper($request->nombres1);
                    $persona->ape_paterno = strtoupper($request->ape_paterno1);
                    $persona->ape_materno = strtoupper($request->ape_materno1);
                    $persona->tipo_persona_juridica = $request->tipo_persona_juridica;
                    $persona->tipo_funcion = 1;
                    $persona->razon_social = strtoupper($request->razon_social);
                    $persona->save();

                    $titular = new Titular();
                    $titular->id_ficha = $ficha->id_ficha;
                    $titular->id_persona = $persona->id_persona;
                    $titular->form_adquisicion = $request->form_adquisicion;
                    $titular->fecha_adquisicion = $request->fecha_adquisicion;
                    $titular->porc_cotitular = 0.00;
                    $titular->esta_civil = $request->esta_civil1;
                    $titular->fax = $request->fax;
                    $titular->telf = $request->telefono_num;
                    $titular->anexo = $request->anexo;
                    $titular->email = $request->email;
                    $titular->nume_titular = "TITULAR N° 1";
                    $titular->codi_contribuyente = $request->codi_contribuyente;
                    $titular->cond_titular = $request->cond_titular;
                    $titular->save();

                    if ($request->ubicacionpersona == '01') {
                        $viaDomicilio = Via::where('id_via', $request->via_id[0])->first();
                        $habDomicilio = HabUrbana::where('id_hab_urba', $request->hab_urbana_id)->first();
                        $domicilio = new DomicilioTitular();
                        $domicilio->id_ficha = $ficha->id_ficha;
                        $domicilio->id_persona = $persona->id_persona;
                        $domicilio->codi_via = $viaDomicilio->codi_via;
                        $domicilio->tipo_via = $viaDomicilio->tipo_via;
                        $domicilio->nomb_via = $viaDomicilio->nomb_via;
                        $domicilio->nume_muni = $request->nume_muni[0];
                        $domicilio->nomb_edificacion = strtoupper($request->nomb_edificacion);
                        $domicilio->nume_interior = $request->nume_interior;
                        $domicilio->codi_hab_urba = $habDomicilio->codi_hab_urba;
                        $domicilio->nomb_hab_urba = $habDomicilio->nomb_hab_urba;
                        $domicilio->sector = $request->zona_dist;
                        $domicilio->mzna = $request->mzna_dist;
                        $domicilio->lote = $request->lote_dist;
                        $domicilio->sublote = $request->sub_lote_dist;
                        $domicilio->codi_dep = "08";
                        $domicilio->codi_pro = "01";
                        $domicilio->codi_dis = "08";
                        $domicilio->ubicacion = $request->ubicacionpersona;
                        $domicilio->save();
                    } elseif ($request->ubicacionpersona == '02') {
                        $domicilio = new DomicilioTitular();
                        $domicilio->id_ficha = $ficha->id_ficha;
                        $domicilio->id_persona = $persona->id_persona;
                        $domicilio->codi_via = $request->codigoviaotros;
                        $domicilio->tipo_via = strtoupper($request->tipoviaotros);
                        $domicilio->nomb_via = strtoupper($request->nombreviaotros);
                        $domicilio->nume_muni = $request->nmunicipalotros;
                        $domicilio->nomb_edificacion = strtoupper($request->nomb_edificacion);
                        $domicilio->nume_interior = $request->ninteriorotros;
                        $domicilio->codi_hab_urba = $request->codigohurbanootros;
                        $domicilio->nomb_hab_urba = $request->nombrehhurbanaotros;
                        $domicilio->sector = $request->zonaootros;
                        $domicilio->mzna = $request->manzanaotros;
                        $domicilio->lote = $request->loteotros;
                        $domicilio->sublote = $request->subloteotros;
                        $domicilio->codi_dep = $request->deparamentootros;
                        $domicilio->codi_pro = $request->provinciaotros;
                        $domicilio->codi_dis = $request->distritootros;
                        $domicilio->ubicacion = $request->ubicacionpersona;
                        $domicilio->save();
                    }
                }
            } elseif ($request->tipo_persona == 2) {
                $buscarpersona3 = Persona::where('nume_doc', $request->nume_doc3)->where('tipo_funcion', 1)->first();
                if ($buscarpersona3 != "") {
                    $persona = $buscarpersona3;

                    $titular = new Titular();
                    $titular->id_ficha = $ficha->id_ficha;
                    $titular->id_persona = $persona->id_persona;
                    $titular->form_adquisicion = $request->form_adquisicion;
                    $titular->fecha_adquisicion = $request->fecha_adquisicion;
                    $titular->porc_cotitular = 0.00;
                    $titular->telf = $request->telefono_num;
                    $titular->anexo = $request->anexo;
                    $titular->email = $request->email;
                    $titular->cond_titular = $request->cond_titular;
                    $titular->save();

                    if ($request->ubicacionpersona == '01') {
                        $viaDomicilio = Via::where('id_via', $request->via_id[0])->first();
                        $habDomicilio = HabUrbana::where('id_hab_urba', $request->hab_urbana_id)->first();
                        $domicilio = new DomicilioTitular();
                        $domicilio->id_ficha = $ficha->id_ficha;
                        $domicilio->id_persona = $persona->id_persona;
                        $domicilio->codi_via = $viaDomicilio->codi_via;
                        $domicilio->tipo_via = $viaDomicilio->tipo_via;
                        $domicilio->nomb_via = $viaDomicilio->nomb_via;
                        $domicilio->nume_muni = $request->nume_muni[0];
                        $domicilio->nomb_edificacion = strtoupper($request->nomb_edificacion);
                        $domicilio->nume_interior = $request->nume_interior;
                        $domicilio->codi_hab_urba = $habDomicilio->codi_hab_urba;
                        $domicilio->nomb_hab_urba = $habDomicilio->nomb_hab_urba;
                        $domicilio->sector = $request->zona_dist;
                        $domicilio->mzna = $request->mzna_dist;
                        $domicilio->lote = $request->lote_dist;
                        $domicilio->sublote = $request->sub_lote_dist;
                        $domicilio->codi_dep = "08";
                        $domicilio->codi_pro = "01";
                        $domicilio->codi_dis = "08";
                        $domicilio->ubicacion = $request->ubicacionpersona;
                        $domicilio->save();
                    } elseif ($request->ubicacionpersona == '02') {
                        $domicilio = new DomicilioTitular();
                        $domicilio->id_ficha = $ficha->id_ficha;
                        $domicilio->id_persona = $persona->id_persona;
                        $domicilio->codi_via = $request->codigoviaotros;
                        $domicilio->tipo_via = strtoupper($request->tipoviaotros);
                        $domicilio->nomb_via = strtoupper($request->nombreviaotros);
                        $domicilio->nume_muni = $request->nmunicipalotros;
                        $domicilio->nomb_edificacion = strtoupper($request->nomb_edificacion);
                        $domicilio->nume_interior = $request->ninteriorotros;
                        $domicilio->codi_hab_urba = $request->codigohurbanootros;
                        $domicilio->nomb_hab_urba = $request->nombrehhurbanaotros;
                        $domicilio->sector = $request->zonaootros;
                        $domicilio->mzna = $request->manzanaotros;
                        $domicilio->lote = $request->loteotros;
                        $domicilio->sublote = $request->subloteotros;
                        $domicilio->codi_dep = $request->deparamentootros;
                        $domicilio->codi_pro = $request->provinciaotros;
                        $domicilio->codi_dis = $request->distritootros;
                        $domicilio->ubicacion = $request->ubicacionpersona;
                        $domicilio->save();
                    }
                } else {
                    $persona = new Persona();
                    if ($request->nume_doc3 == "") {
                        $cantidadpersona = Persona::where('tipo_persona', 2)->count() + 1;
                        $persona->id_persona = str_pad($cantidadpersona, 11, '0', STR_PAD_LEFT) . '1200';
                        $persona->nume_doc = str_pad($cantidadpersona, 11, '0', STR_PAD_LEFT);
                    } else {
                        $persona->id_persona = str_pad($request->nume_doc3, 11, '0', STR_PAD_LEFT) . '1200';
                        $persona->nume_doc = str_pad($request->nume_doc3, 11, '0', STR_PAD_LEFT);
                    }
                    $persona->tipo_doc = '00';
                    $persona->tipo_persona = 2;
                    $persona->tipo_persona_juridica = $request->tipo_persona_juridica;
                    $persona->tipo_funcion = 1;
                    $persona->razon_social = strtoupper($request->razon_social);
                    $persona->save();

                    $titular = new Titular();
                    $titular->id_ficha = $ficha->id_ficha;
                    $titular->id_persona = $persona->id_persona;
                    $titular->form_adquisicion = $request->form_adquisicion;
                    $titular->fecha_adquisicion = $request->fecha_adquisicion;
                    $titular->porc_cotitular = 0.00;
                    $titular->telf = $request->telefono_num;
                    $titular->anexo = $request->anexo;
                    $titular->email = $request->email;
                    $titular->cond_titular = $request->cond_titular;
                    $titular->save();

                    if ($request->ubicacionpersona == '01') {
                        $viaDomicilio = Via::where('id_via', $request->via_id[0])->first();
                        $habDomicilio = HabUrbana::where('id_hab_urba', $request->hab_urbana_id)->first();
                        $domicilio = new DomicilioTitular();
                        $domicilio->id_ficha = $ficha->id_ficha;
                        $domicilio->id_persona = $persona->id_persona;
                        $domicilio->codi_via = $viaDomicilio->codi_via;
                        $domicilio->tipo_via = $viaDomicilio->tipo_via;
                        $domicilio->nomb_via = $viaDomicilio->nomb_via;
                        $domicilio->nume_muni = $request->nume_muni[0];
                        $domicilio->nomb_edificacion = strtoupper($request->nomb_edificacion);
                        $domicilio->nume_interior = $request->nume_interior;
                        $domicilio->codi_hab_urba = $habDomicilio->codi_hab_urba;
                        $domicilio->nomb_hab_urba = $habDomicilio->nomb_hab_urba;
                        $domicilio->sector = $request->zona_dist;
                        $domicilio->mzna = $request->mzna_dist;
                        $domicilio->lote = $request->lote_dist;
                        $domicilio->sublote = $request->sub_lote_dist;
                        $domicilio->codi_dep = "08";
                        $domicilio->codi_pro = "01";
                        $domicilio->codi_dis = "08";
                        $domicilio->ubicacion = $request->ubicacionpersona;
                        $domicilio->save();
                    } elseif ($request->ubicacionpersona == '02') {
                        $domicilio = new DomicilioTitular();
                        $domicilio->id_ficha = $ficha->id_ficha;
                        $domicilio->id_persona = $persona->id_persona;
                        $domicilio->codi_via = $request->codigoviaotros;
                        $domicilio->tipo_via = strtoupper($request->tipoviaotros);
                        $domicilio->nomb_via = strtoupper($request->nombreviaotros);
                        $domicilio->nume_muni = $request->nmunicipalotros;
                        $domicilio->nomb_edificacion = strtoupper($request->nomb_edificacion);
                        $domicilio->nume_interior = $request->ninteriorotros;
                        $domicilio->codi_hab_urba = $request->codigohurbanootros;
                        $domicilio->nomb_hab_urba = $request->nombrehhurbanaotros;
                        $domicilio->sector = $request->zonaootros;
                        $domicilio->mzna = $request->manzanaotros;
                        $domicilio->lote = $request->loteotros;
                        $domicilio->sublote = $request->subloteotros;
                        $domicilio->codi_dep = $request->deparamentootros;
                        $domicilio->codi_pro = $request->provinciaotros;
                        $domicilio->codi_dis = $request->distritootros;
                        $domicilio->ubicacion = $request->ubicacionpersona;
                        $domicilio->save();
                    }
                }
            }
            if ($request->esta_civil1 == '02' || $request->esta_civil1 == '04') {
                $buscarpersona4 = Persona::where('nume_doc', $request->nume_doc2)->where('tipo_funcion', 1)->first();
                if ($buscarpersona4 != "") {
                    $persona2 = $buscarpersona4;

                    $titular = new Titular();
                    $titular->id_ficha = $ficha->id_ficha;
                    $titular->id_persona = $persona2->id_persona;
                    $titular->form_adquisicion = $request->form_adquisicion;
                    $titular->fecha_adquisicion = $request->fecha_adquisicion;
                    $titular->porc_cotitular = 0.00;
                    $titular->esta_civil = $request->esta_civil1;
                    $titular->fax = $request->fax;
                    $titular->telf = $request->telefono_num;
                    $titular->anexo = $request->anexo;
                    $titular->email = $request->email;
                    $titular->nume_titular = "TITULAR N° 2";
                    $titular->codi_contribuyente = $request->codi_contribuyente;
                    $titular->cond_titular = $request->cond_titular;
                    $titular->save();
                } else {
                    $persona2 = new Persona();
                    if ($request->nume_doc3 == "") {
                        $cantidadpersona = Persona::where('tipo_persona', 1)->count() + 1;
                        $persona2->id_persona = str_pad($cantidadpersona, 8, '0', STR_PAD_LEFT) . '1200';
                        $persona2->nume_doc = str_pad($cantidadpersona, 8, '0', STR_PAD_LEFT);
                    } else {
                        $persona2->id_persona = str_pad($request->nume_doc2, 8, '0', STR_PAD_LEFT) . '1200';
                        $persona2->nume_doc = str_pad($request->nume_doc2, 8, '0', STR_PAD_LEFT);
                    }
                    $persona2->tipo_doc = $request->tipo_doc2;
                    $persona2->tipo_persona = 1;
                    $persona2->nombres = strtoupper($request->nombres2);
                    $persona2->ape_paterno = strtoupper($request->ape_paterno2);
                    $persona2->ape_materno = strtoupper($request->ape_materno2);
                    $persona2->tipo_persona_juridica = $request->tipo_persona_juridica;
                    $persona2->tipo_funcion = 1;
                    $persona2->save();

                    $titular = new Titular();
                    $titular->id_ficha = $ficha->id_ficha;
                    $titular->id_persona = $persona2->id_persona;
                    $titular->form_adquisicion = $request->form_adquisicion;
                    $titular->fecha_adquisicion = $request->fecha_adquisicion;
                    $titular->porc_cotitular = 0.00;
                    $titular->esta_civil = $request->esta_civil1;
                    $titular->fax = $request->fax;
                    $titular->telf = $request->telefono_num;
                    $titular->anexo = $request->anexo;
                    $titular->email = $request->email;
                    $titular->nume_titular = "TITULAR N° 2";
                    $titular->codi_contribuyente = $request->codi_contribuyente;
                    $titular->cond_titular = $request->cond_titular;
                    $titular->save();
                }
            }

            $fichaindividual = new FichaIndividual();
            $fichaindividual->id_ficha = $ficha->id_ficha;
            $fichaindividual->codi_uso = $request->codi_uso;
            $fichaindividual->cont_en = $request->cont_en;
            $fichaindividual->clasificacion = $request->clasificacion;
            $fichaindividual->area_titulo = $request->area_declarada;
            $fichaindividual->area_declarada = $request->area_declarada;
            $fichaindividual->area_verificada = $request->area_verificada1;
            $fichaindividual->porc_bc_terr_legal = $request->porc_bc_terr_legal;
            $fichaindividual->porc_bc_terr_fisc = $request->porc_bc_terr_fisc;
            $fichaindividual->porc_bc_const_legal = $request->porc_bc_const_legal;
            $fichaindividual->porc_bc_const_fisc = $request->porc_bc_const_fisc;
            $fichaindividual->evaluacion = $request->evaluacion;
            $fichaindividual->en_colindante = $request->en_colindante;
            $fichaindividual->en_jardin_aislamiento = $request->en_jardin_aislamiento;
            $fichaindividual->en_area_publica = $request->en_area_publica;
            $fichaindividual->en_area_intangible = $request->en_area_intangible;
            $fichaindividual->cond_declarante = $request->cond_declarante;
            $fichaindividual->esta_llenado = $request->esta_llenado;
            $fichaindividual->nume_habitantes = $request->nume_habitantes;
            $fichaindividual->nume_familias = $request->nume_familias;
            $fichaindividual->mantenimiento = $request->mantenimiento;
            $fichaindividual->observaciones = $request->observacion;
            $fichaindividual->nume_ficha = str_pad($request->nume_ficha, 7, '0', STR_PAD_LEFT);
            $fichaindividual->save();

            $lindero = new Lindero();
            $lindero->id_ficha = $ficha->id_ficha;
            $lindero->fren_campo = $request->fren_campo;
            $lindero->fren_titulo = $request->fren_titulo;
            $lindero->fren_colinda_campo = $request->fren_colinda_campo;
            $lindero->fren_colinda_titulo = $request->fren_colinda_titulo;
            $lindero->dere_campo = $request->dere_campo;
            $lindero->dere_titulo = $request->dere_titulo;
            $lindero->dere_colinda_campo = $request->dere_colinda_campo;
            $lindero->dere_colinda_titulo = $request->dere_colinda_titulo;
            $lindero->izqu_campo = $request->izqu_campo;
            $lindero->izqu_titulo = $request->izqu_titulo;
            $lindero->izqu_colinda_campo = $request->izqu_colinda_campo;
            $lindero->izqu_colinda_titulo = $request->izqu_colinda_titulo;
            $lindero->fond_titulo = $request->fond_titulo;
            $lindero->fond_campo = $request->fond_campo;
            $lindero->fond_colinda_campo = $request->fond_colinda_campo;
            $lindero->fond_colinda_titulo = $request->fond_colinda_titulo;
            $lindero->save();

            $servicios = new ServicioBasico();
            $servicios->id_ficha = $ficha->id_ficha;
            if ($request->luz == 'on') {
                $servicios->luz = 1;
            } else {
                $servicios->luz = 2;
            }
            if ($request->agua == 'on') {
                $servicios->agua = 1;
            } else {
                $servicios->agua = 2;
            }
            if ($request->telefono == 'on') {
                $servicios->telefono = 1;
            } else {
                $servicios->telefono = 2;
            }
            if ($request->desague == 'on') {
                $servicios->desague = 1;
            } else {
                $servicios->desague = 2;
            }
            if ($request->gas == 'on') {
                $servicios->gas = 1;
            } else {
                $servicios->gas = 2;
            }
            if ($request->internet == 'on') {
                $servicios->internet = 1;
            } else {
                $servicios->internet = 2;
            }
            if ($request->tvcable == 'on') {
                $servicios->tvcable = 1;
            } else {
                $servicios->tvcable = 2;
            }
            $servicios->save();

            $contcon = 0;
            $construcciones = $request->nume_piso;
            if ($construcciones != "") {
                while ($contcon < count($construcciones)) {
                    $construccion = new Construccion();
                    $construccion->id_construccion = $ficha->id_ficha . '' . $request->nume_piso[$contcon] . '' . $contcon + 1;
                    $construccion->id_ficha = $ficha->id_ficha;
                    $construccion->codi_construccion = $contcon + 1;
                    $construccion->nume_piso = $request->nume_piso[$contcon];
                    $construccion->fecha = $request->fecha[$contcon];
                    $construccion->mep = $request->mep[$contcon];
                    $construccion->ecs = $request->ecs[$contcon];
                    $construccion->ecc = $request->ecc[$contcon];
                    $construccion->estr_muro_col = $request->estr_muro_col[$contcon];
                    $construccion->estr_techo = $request->estr_techo[$contcon];
                    $construccion->acab_piso = $request->acab_piso[$contcon];
                    $construccion->acab_puerta_ven = $request->acab_puerta_ven[$contcon];
                    $construccion->acab_revest = $request->acab_revest[$contcon];
                    $construccion->acab_bano = $request->acab_bano[$contcon];
                    $construccion->inst_elect_sanita = $request->inst_elect_sanita[$contcon];
                    $construccion->area_declarada = 0.00;
                    $construccion->area_verificada = $request->area_verificada[$contcon];
                    $construccion->uca = $request->uca[$contcon];
                    $construccion->save();
                    $contcon++;
                }
            }

            $contins = 0;
            $instalaciones = $request->codi_instalacion;
            if ($instalaciones != "") {
                while ($contins < count($instalaciones)) {
                    $instalacion = new Instalacion();
                    $instalacion->id_instalacion = $ficha->id_ficha . '' . $request->codi_instalacion[$contins] . '' . $contins + 1;
                    $instalacion->id_ficha = $ficha->id_ficha;
                    $instalacion->codi_instalacion = $request->codi_instalacion[$contins];
                    $instalacion->codi_obra = $contins + 1;
                    $instalacion->fecha = $request->inst_fecha[$contins];
                    $instalacion->mep = $request->inst_mep[$contins];
                    $instalacion->ecs = $request->inst_ecs[$contins];
                    $instalacion->ecc = $request->inst_ecc[$contins];
                    $instalacion->prod_total = $request->inst_prod_total[$contins];
                    $instalacion->uni_med = $request->inst_uni_med[$contins];
                    $instalacion->uca = $request->inst_uca[$contins];
                    $instalacion->save();
                    $contins++;
                }
            }

            $contdoc = 0;
            $documentos = $request->tipo_doc;
            if ($documentos != "") {
                while ($contdoc < count($documentos)) {
                    $documento = new DocumentoAdjunto();
                    $documento->id_doc = $ficha->id_ficha . '' . $contdoc + 1;
                    $documento->id_ficha = $ficha->id_ficha;
                    $documento->codi_doc = $contdoc + 1;
                    $documento->tipo_doc = $request->tipo_doc[$contdoc];
                    $documento->nume_doc = $request->nume_doc[$contdoc];
                    $documento->area_autorizada = $request->area_autorizada[$contdoc];
                    $documento->fecha_doc = $request->fecha_doc[$contdoc];
                    $documento->save();
                    $contdoc++;
                }
            }

            $sunarp = new Sunarp();
            $sunarp->id_ficha = $ficha->id_ficha;
            $sunarp->tipo_partida = $request->tipo_partida;
            $sunarp->nume_partida = $request->nume_partida;
            $sunarp->fojas = $request->fojas;
            $sunarp->asiento = $request->asiento;
            $sunarp->fecha_inscripcion = $request->fecha_inscripcion;
            $sunarp->codi_decla_fabrica = $request->codi_decla_fabrica;
            $sunarp->asie_fabrica = $request->asie_fabrica;
            $sunarp->fecha_fabrica = $request->fecha_fabrica;
            $sunarp->save();

            $contlit = 0;
            $litigantes = $request->tipo_doc_litigante;
            if ($litigantes != "") {
                while ($contlit < count($litigantes)) {
                    if ($request->nume_doclitigante[$contlit] != '') {
                        $buscarpersona = Persona::where('nume_doc', $request->nume_doclitigante[$contlit])->first();
                        if ($buscarpersona != "") {
                            $litigantepersona = $buscarpersona;
                        } else {
                            $litigantepersona = new Persona();
                            $litigantepersona->id_persona = $request->nume_doclitigante[$contlit] . '61' . $request->nume_doclitigante[$contlit];
                            $litigantepersona->nume_doc = $request->nume_doclitigante[$contlit];
                            $litigantepersona->tipo_doc = $request->tipo_doc_litigante[$contlit];
                            $litigantepersona->tipo_persona = 1;
                            $litigantepersona->nombres = strtoupper($request->nombreslitigante[$contlit]);
                            $litigantepersona->ape_paterno = strtoupper($request->ape_paternolitigante[$contlit]);
                            $litigantepersona->ape_materno = strtoupper($request->ape_maternolitigante[$contlit]);
                            $litigantepersona->tipo_persona_juridica = $request->tipo_persona_juridica;/*Vacio*/
                            $litigantepersona->tipo_funcion = 6;
                            $litigantepersona->razon_social = $request->razon_social;/*Vacio*/
                            $litigantepersona->save();
                        }
                    }

                    $litigante = new Litigante();
                    $litigante->id_ficha = $ficha->id_ficha;
                    $litigante->id_persona = $litigantepersona->id_persona;
                    $litigante->codi_contribuye = $request->codi_contribuye[$contlit];
                    $litigante->save();
                    $contlit++;
                }
            }


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }

        return redirect()->route('reporte.reportelista')
            ->with('success', 'Ficha Individual Agregado Correctamente.');
    }

    public function fichaIndividual(Ficha $ficha)
    {
        $fileName = 'individual.pdf';
        $mpdf = new \Mpdf\Mpdf([
            'format' => [210, 297],
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        $logos = Institucion::first();
        $html = \View::make('pages.pdf.individual', compact('ficha', 'logos'));
        $html = $html->render();
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
    }

    public function fichaIndividuales($sector, $manzana, $tipo_ficha)
    {

        $fichas = Ficha::where('tipo_ficha', 'LIKE', $tipo_ficha);




        $fichas = $fichas->get();

        $fileName = 'individuales.pdf';
        $mpdf = new \Mpdf\Mpdf([
            'format' => [210, 297],
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        $logos = Institucion::first();
        $html = \View::make('pages.pdf.individuales', compact('sector', 'fichas', 'logos'));
        $html = $html->render();
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'D');
    }
    public function fichaEconomica(Ficha $ficha)
    {
        $fileName = 'economica.pdf';
        $mpdf = new \Mpdf\Mpdf([
            'format' => [210, 297],
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        $logos = Institucion::first();
        $html = \View::make('pages.pdf.economica', compact('ficha', 'logos'));
        $html = $html->render();
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
    }

    public function fichaCotitularidad(Ficha $ficha)
    {
        $fileName = 'cotitularidad.pdf';
        $mpdf = new \Mpdf\Mpdf([
            'format' => [210, 297],
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        $logos = Institucion::first();
        $html = \View::make('pages.pdf.cotitularidad', compact('ficha', 'logos'));
        $html = $html->render();
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
    }
    public function fichaBienescomunes(Ficha $ficha)
    {
        $fileName = 'bienescomunes.pdf';
        $mpdf = new \Mpdf\Mpdf([
            'format' => [210, 297],
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        $logos = Institucion::first();
        $lote = $ficha->id_lote;
        $total = fichaIndividual::whereHas('ficha', function ($query) use ($lote) {
            $query->where('id_lote', '=', $lote);
        })->sum('area_verificada');
        $totalconstrucciones = Construccion::where('id_ficha', $ficha->id_ficha)->sum('area_verificada');
        $totalinstalaciones = Instalacion::where('id_ficha', $ficha->id_ficha)->sum('prod_total');
        $html = \View::make('pages.pdf.bienescomunes', compact('ficha', 'logos', 'total', 'totalconstrucciones', 'totalinstalaciones'));
        $html = $html->render();
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
    }

    public function fichaInformativa(Ficha $ficha)
    {
        $mytime = Carbon::now('America/Lima');
        $fileName = 'informativa.pdf';
        $mpdf = new \Mpdf\Mpdf([
            'format' => [210, 297],
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 10,
            'margin_footer' => 10,
            'defaulfooterline' => 0,

        ]);
        $logos = Institucion::first();
        $fecha = date("d/m/Y", strtotime($mytime));
        $hora = date("H:m:s", strtotime($mytime));
        $html = \View::make('pages.pdf.informativa', compact('ficha', 'logos', 'fecha', 'hora'));
        $html = $html->render();
        $mpdf->setFooter('<div style="background-color: #4646A3;border: solid 1px #fff !important;color:#fff;margin:0;padding:0;line-height:14px;text-align: center">LA INFORMACION CONTENIDA EN EL PRESENTE NO GENERA NI RECONOCE DERECHOS DE PROPIEDAD</div><div style="border: solid 1px #fff !important;text-align: center; font-size:8px;">MEJORAMIENTO DEL SERVICIO DE INFORMACION PREDIAL URBANA DEL DISTRITO DE  PROVINCIA DE  - CUSCO</div>');
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
    }

    public function fichaNumeracion(GenerarNumeracion $ficha)
    {
        $mytime = Carbon::now('America/Lima');
        $fileName = 'numeracion.pdf';
        $usos = Uso::orderBy('codi_uso')->get();
        $mpdf = new \Mpdf\Mpdf([
            'format' => [210, 297],
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        $logos = Institucion::first();
        $html = \View::make('pages.pdf.numeracion', compact('ficha', 'logos', 'usos'));
        $html = $html->render();
        $mpdf->setFooter('<div style="background-color: #4646A3;border: solid 1px #fff !important;color:#fff;margin:0;padding:0;line-height:14px;text-align: center">LA INFORMACION CONTENIDA EN EL PRESENTE NO GENERA NI RECONOCE DERECHOS DE PROPIEDAD</div><div style="border: solid 1px #fff !important;text-align: center; font-size:8px;">LA INFORMACION CONTENIDA EN EL PRESENTE NO GENERA NI RECONOCE DERECHOS DE PROPIEDAD</div><div style="border: solid 1px #fff !important;text-align: center; font-size:8px;">MEJORAMIENTO DEL SERVICIO DE INFORMACION PREDIAL URBANA DEL DISTRITO DE  PROVINCIA DE  - CUSCO</div>');
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
    }





    public function certificadocatastral(GenerarCertificado $ficha)
    {
        $mytime = Carbon::now('America/Lima');
        $fileName = 'certificadocatastral.pdf';
        $connection = DB::connection('pgsqlgeo');
        $coordenadas = $connection->select(
            "
            SELECT geom_point.id_lote,
                ROUND(ST_X(geom_point.geom)::decimal, 2) AS x,
                ROUND(ST_Y(geom_point.geom)::decimal, 2) AS y,
                ST_SetSRID(ST_MakePoint(ST_X(geom_point.geom), ST_Y(geom_point.geom)), 32718) AS geom
            FROM
                (SELECT a.id_lote, (ST_DumpPoints(a.geom)).geom AS geom
                FROM geo.tg_lote a
                WHERE a.id_lote = :pId_lote) AS geom_point
            LIMIT (SELECT ST_NPoints(b.geom) - 1 FROM geo.tg_lote b WHERE b.id_lote = :pId_lote)",
            ['pId_lote' => $ficha->ficha->id_lote]
        );

        $extension = $connection->select("
        SELECT ST_XMin(extent) || ',' ||
            ST_YMin(extent) || ',' ||
            ST_XMax(extent) || ',' ||
            ST_YMax(extent) AS extension
        FROM (
            SELECT ST_Expand(ST_Extent(geom), 5) AS extent
            FROM geo.tg_lote
            WHERE id_lote= '" . $ficha->ficha->id_lote . "'
        ) AS subconsulta;
        ");

        $url = env('URL_MAP') . "/servicio/wms?service=WMS&request=GetMap&layers=lotes,id_lotes,vertices_lote&styles=&format=image%2Fpng&transparent=true&version=1.1.1&width=450&height=400&srs=EPSG%3A32718&bbox=" . $extension[0]->extension . "&id=" . $ficha->ficha->id_lote;

        $usos = Uso::orderBy('codi_uso')->get();
        $mpdf = new \Mpdf\Mpdf([
            'format' => [210, 297],
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        $logos = Institucion::first();
        $fecha = date("d/m/Y", strtotime($mytime));
        $hora = date("H:m:s", strtotime($mytime));
        $html = \View::make('pages.pdf.certificadocatastral', compact('url', 'ficha', 'logos', 'fecha', 'hora', 'usos', 'coordenadas'));
        $html = $html->render();
        $mpdf->setFooter('<div style="background-color: #4646A3;border: solid 1px #fff !important;color:#fff;margin:0;padding:0;line-height:14px;text-align: center">LA INFORMACION CONTENIDA EN EL PRESENTE NO GENERA NI RECONOCE DERECHOS DE PROPIEDAD</div><div style="border: solid 1px #fff !important;text-align: center; font-size:8px;">LA INFORMACION CONTENIDA EN EL PRESENTE NO GENERA NI RECONOCE DERECHOS DE PROPIEDAD</div><div style="border: solid 1px #fff !important;text-align: center; font-size:8px;">MEJORAMIENTO DEL SERVICIO DE INFORMACION PREDIAL URBANA DEL DISTRITO DE  PROVINCIA DE  - CUSCO</div>');
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
    }

    public function gerenciaadministracion(Ficha $ficha)
    {
        $mytime = Carbon::now('America/Lima');
        $fileName = 'gerenciaadministracion.pdf';
        $mpdf = new \Mpdf\Mpdf([
            'format' => [210, 297],
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        $logos = Institucion::first();
        $fecha = date("d/m/Y", strtotime($mytime));
        $hora = date("H:m:s", strtotime($mytime));
        $html = \View::make('pages.pdf.gerenciaadministracion', compact('ficha', 'logos', 'fecha', 'hora'));
        $html = $html->render();
        $mpdf->setFooter('<div style="background-color: #4646A3;border: solid 1px #fff !important;color:#fff;margin:0;padding:0;line-height:14px;text-align: center">LA INFORMACION CONTENIDA EN EL PRESENTE NO GENERA NI RECONOCE DERECHOS DE PROPIEDAD</div><div style="border: solid 1px #fff !important;text-align: center; font-size:8px;">LA INFORMACION CONTENIDA EN EL PRESENTE NO GENERA NI RECONOCE DERECHOS DE PROPIEDAD</div><div style="border: solid 1px #fff !important;text-align: center; font-size:8px;">MEJORAMIENTO DEL SERVICIO DE INFORMACION PREDIAL URBANA DEL DISTRITO DE  PROVINCIA DE  - CUSCO</div>');
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
    }

    public function informeeconomico(Ficha $ficha)
    {
        $mytime = Carbon::now('America/Lima');
        $fileName = 'gerenciaadministracion.pdf';
        $mpdf = new \Mpdf\Mpdf([
            'format' => [210, 297],
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        $logos = Institucion::first();
        $fecha = date("d/m/Y", strtotime($mytime));
        $hora = date("H:m:s", strtotime($mytime));
        $html = \View::make('pages.pdf.informeeconomico', compact('ficha', 'logos', 'fecha', 'hora'));
        $html = $html->render();
        $mpdf->setFooter('<div style="background-color: #4646A3;border: solid 1px #fff !important;color:#fff;margin:0;padding:0;line-height:14px;text-align: center">LA INFORMACION CONTENIDA EN EL PRESENTE NO GENERA NI RECONOCE DERECHOS DE PROPIEDAD</div><div style="border: solid 1px #fff !important;text-align: center; font-size:8px;">LA INFORMACION CONTENIDA EN EL PRESENTE NO GENERA NI RECONOCE DERECHOS DE PROPIEDAD</div><div style="border: solid 1px #fff !important;text-align: center; font-size:8px;">MEJORAMIENTO DEL SERVICIO DE INFORMACION PREDIAL URBANA DEL DISTRITO DE  PROVINCIA DE  - CUSCO</div>');
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
    }

    public function fichabienesCulturales(Ficha $ficha)
    {
        $fileName = 'bienesculturales.pdf';
        $mpdf = new \Mpdf\Mpdf([
            'format' => [210, 297],
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        $logos = Institucion::first();
        $html = \View::make('pages.pdf.bienesculturales', compact('ficha', 'logos'));
        $html = $html->render();
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
    }

    public function fichaRural(Ficha $ficha)
    {
        $fileName = 'rural.pdf';
        $mpdf = new \Mpdf\Mpdf([
            'format' => [210, 297],
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        $logos = Institucion::first();
        $html = \View::make('pages.pdf.rural', compact('ficha', 'logos'));
        $html = $html->render();
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
    }

    public function anexoficha($sector)
    {
        $mytime = Carbon::now('America/Lima');
        $fileName = 'anexoficha.pdf';
        $mpdf = new \Mpdf\Mpdf([
            'format' => [210, 297],
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);

        $sectores = Sectore::where('id_sector', $sector)->orderby('codi_sector')->first();
        $sector2 = $sector;
        $areaPorLote = UniCat::select('tf_uni_cat.id_lote')
            ->join('tf_fichas as tf', 'tf_uni_cat.id_uni_cat', '=', 'tf.id_uni_cat')
            ->leftJoin('tf_fichas_bienes_comunes as tb', 'tf.id_ficha', '=', 'tb.id_ficha')
            ->leftJoin('tf_fichas_individuales as ti', 'tf.id_ficha', '=', 'ti.id_ficha')
            ->leftJoin('tf_construcciones as tc', 'tf.id_ficha', '=', 'tc.id_ficha')
            ->selectRaw('
            SUM(
                CASE
                    WHEN tf.tipo_ficha = \'04\' THEN COALESCE(tb.area_verificada, 0)
                    WHEN tf.tipo_ficha = \'01\' THEN 
                        CASE 
                            WHEN COALESCE(tc.area_verificada, 0) > 0 THEN tc.area_verificada
                            ELSE COALESCE(ti.area_verificada, 0)
                        END
                    ELSE 0
                END
            ) as totalAreaPorLote
        ')
            ->groupBy('tf_uni_cat.id_lote')
            ->toSql();

        $areaPorPiso = UniCat::select('tf_uni_cat.id_lote', 'tf_uni_cat.id_edificacion', 'tf_uni_cat.codi_entrada', 'tf_uni_cat.codi_piso')
            ->join('tf_fichas as tf', 'tf_uni_cat.id_uni_cat', '=', 'tf.id_uni_cat')
            ->join('tf_edificaciones as te', 'tf_uni_cat.id_edificacion', '=', 'te.id_edificacion')
            ->leftJoin('tf_fichas_bienes_comunes as tb', 'tf.id_ficha', '=', 'tb.id_ficha')
            ->leftJoin('tf_fichas_individuales as ti', 'tf.id_ficha', '=', 'ti.id_ficha')
            ->leftJoin('tf_construcciones as tc', 'tf.id_ficha', '=', 'tc.id_ficha')
            ->selectRaw('
            SUM(
                CASE
                    WHEN tf.tipo_ficha = \'04\' THEN COALESCE(tb.area_verificada, 0)
                    WHEN tf.tipo_ficha = \'01\' THEN 
                        CASE 
                            WHEN COALESCE(tc.area_verificada, 0) > 0 THEN tc.area_verificada
                            ELSE COALESCE(ti.area_verificada, 0)
                        END
                    ELSE 0
                END
            ) as totalAreaPorPiso
        ')
            ->groupBy('tf_uni_cat.id_lote', 'tf_uni_cat.id_edificacion', 'tf_uni_cat.codi_entrada', 'tf_uni_cat.codi_piso')
            ->toSql();

        $titulares = UniCat::with([
            'edificacion',
            'lote',
            'lote.manzana',
            'lote.manzana.sectore',
            'titularesPersonalizados',
            'puertaPersonalizada',
            'areaIndividual'
        ])
            ->whereHas('fichas')
            ->whereHas('lote.manzana.sectore', function ($query) use ($sector2) {
                $query->where('id_sector', $sector2);
            })
            ->join(DB::raw('(' . $areaPorLote . ') as area_por_lote'), 'tf_uni_cat.id_lote', '=', 'area_por_lote.id_lote')
            ->join(DB::raw('(' . $areaPorPiso . ') as area_por_piso'), function ($join) {
                $join->on('tf_uni_cat.id_lote', '=', 'area_por_piso.id_lote')
                    ->on('tf_uni_cat.codi_entrada', '=', 'area_por_piso.codi_entrada')
                    ->on('tf_uni_cat.codi_piso', '=', 'area_por_piso.codi_piso')
                    ->on('tf_uni_cat.id_edificacion', '=', 'area_por_piso.id_edificacion');
            })
            ->select('tf_uni_cat.*', 'area_por_lote.totalareaporlote', 'area_por_piso.totalareaporpiso')
            ->distinct()
            ->orderBy('tf_uni_cat.cuc', 'asc')
            ->get();

        $numero = count($titulares);
        $total = 0;

        $logos = Institucion::first();
        $fecha = date("d/m/Y", strtotime($mytime));
        $hora = date("H:m:s", strtotime($mytime));
        $html = \View::make('pages.pdf.anexoficha', compact('titulares', 'sectores', 'sector2', 'numero', 'logos', 'fecha', 'hora'));
        $html = $html->render();
        $mpdf->WriteHTML($html);
        $mpdf->Output($fileName, 'I');
    }

    public function anexofichaExcel($sector)
    {
        $mytime = Carbon::now('America/Lima');
        $sectores = Sectore::orderby('codi_sector')->get();

        $sectores = Sectore::where('id_sector', $sector)->orderby('codi_sector')->first();
        $sector2 = $sector;
        $areaPorLote = UniCat::select('tf_uni_cat.id_lote')
            ->join('tf_fichas as tf', 'tf_uni_cat.id_uni_cat', '=', 'tf.id_uni_cat')
            ->leftJoin('tf_fichas_bienes_comunes as tb', 'tf.id_ficha', '=', 'tb.id_ficha')
            ->leftJoin('tf_fichas_individuales as ti', 'tf.id_ficha', '=', 'ti.id_ficha')
            ->leftJoin('tf_construcciones as tc', 'tf.id_ficha', '=', 'tc.id_ficha')
            ->selectRaw('
            SUM(
                CASE
                    WHEN tf.tipo_ficha = \'04\' THEN COALESCE(tb.area_verificada, 0)
                    WHEN tf.tipo_ficha = \'01\' THEN 
                        CASE 
                            WHEN COALESCE(tc.area_verificada, 0) > 0 THEN tc.area_verificada
                            ELSE COALESCE(ti.area_verificada, 0)
                        END
                    ELSE 0
                END
            ) as totalAreaPorLote
        ')
            ->groupBy('tf_uni_cat.id_lote')
            ->toSql();

        $areaPorPiso = UniCat::select('tf_uni_cat.id_lote', 'tf_uni_cat.id_edificacion', 'tf_uni_cat.codi_entrada', 'tf_uni_cat.codi_piso')
            ->join('tf_fichas as tf', 'tf_uni_cat.id_uni_cat', '=', 'tf.id_uni_cat')
            ->join('tf_edificaciones as te', 'tf_uni_cat.id_edificacion', '=', 'te.id_edificacion')
            ->leftJoin('tf_fichas_bienes_comunes as tb', 'tf.id_ficha', '=', 'tb.id_ficha')
            ->leftJoin('tf_fichas_individuales as ti', 'tf.id_ficha', '=', 'ti.id_ficha')
            ->leftJoin('tf_construcciones as tc', 'tf.id_ficha', '=', 'tc.id_ficha')
            ->selectRaw('
            SUM(
                CASE
                    WHEN tf.tipo_ficha = \'04\' THEN COALESCE(tb.area_verificada, 0)
                    WHEN tf.tipo_ficha = \'01\' THEN 
                        CASE 
                            WHEN COALESCE(tc.area_verificada, 0) > 0 THEN tc.area_verificada
                            ELSE COALESCE(ti.area_verificada, 0)
                        END
                    ELSE 0
                END
            ) as totalAreaPorPiso
        ')
            ->groupBy('tf_uni_cat.id_lote', 'tf_uni_cat.id_edificacion', 'tf_uni_cat.codi_entrada', 'tf_uni_cat.codi_piso')
            ->toSql();

        $titulares = UniCat::with([
            'edificacion',
            'lote',
            'lote.manzana',
            'lote.manzana.sectore',
            'titularesPersonalizados',
            'puertaPersonalizada',
            'areaIndividual'
        ])
            ->whereHas('fichas')
            ->whereHas('lote.manzana.sectore', function ($query) use ($sector2) {
                $query->where('id_sector', $sector2);
            })
            ->join(DB::raw('(' . $areaPorLote . ') as area_por_lote'), 'tf_uni_cat.id_lote', '=', 'area_por_lote.id_lote')
            ->join(DB::raw('(' . $areaPorPiso . ') as area_por_piso'), function ($join) {
                $join->on('tf_uni_cat.id_lote', '=', 'area_por_piso.id_lote')
                    ->on('tf_uni_cat.codi_entrada', '=', 'area_por_piso.codi_entrada')
                    ->on('tf_uni_cat.codi_piso', '=', 'area_por_piso.codi_piso')
                    ->on('tf_uni_cat.id_edificacion', '=', 'area_por_piso.id_edificacion');
            })
            ->select('tf_uni_cat.*', 'area_por_lote.totalareaporlote', 'area_por_piso.totalareaporpiso')
            ->distinct()
            ->orderBy('tf_uni_cat.cuc', 'asc')
            ->get();

        $numero = count($titulares);
        $total = 0;

        $logos = Institucion::first();
        $fecha = date("d/m/Y", strtotime($mytime));
        $hora = date("H:m:s", strtotime($mytime));
        return Excel::download(new LotesPropietariosExports($titulares, $sectores, $sector2, $numero, $logos, $fecha, $hora), 'anexoficha.xlsx');
    }
}
