import './style.css';
import L from 'leaflet';

L.Map.include({_initControlPos: function (){
	var corners = this._controlCorners = {}, l = 'leaflet-',container = this._controlContainer =L.DomUtil.create('div', l + 'control-container', this._container);

	function createCorner(vSide, hSide) {
		var className = l + vSide + ' ' + l + hSide;
		corners[vSide + hSide] = L.DomUtil.create('div', className, container);
	};

	createCorner('top', 'left');
	createCorner('top', 'right');
	createCorner('bottom', 'left');
	createCorner('bottom', 'right');
	createCorner('top', 'center');
	createCorner('middle', 'center');
	createCorner('middle', 'left');
	createCorner('middle', 'right');
	createCorner('bottom', 'center');
	}
});

var nombre_columnas = [
	{id: "nom_dpto", nombre: "Departamento: "},
	{id: "nom_prov", nombre: "Provincia: "},
	{id: "nom_dist", nombre: "Distrito: "},
	{id: "estado", nombre: "Estado: "},
	{id: "area_grafica", nombre: "Superficie (ha): "},
	{id: "id_dist", nombre: "Código distrito: "},
	{id: "id_prov", nombre: "Código provincia: "},
	{id: "id_dpto", nombre: "Código departamento: "},
	{id: "cod_sector", nombre: "Sector: "},
	{id: "cod_mzna", nombre: "Manzana: "},
	{id: "cod_lote", nombre: "Lote: "},
	{id: "fech_actua", nombre: "Fecha de actualización: "},
	{id: "id_lote", nombre: "Código lote: "},
	{id: "tipo_doc", nombre: "Documento: "},
	{id: "tipo_persona", nombre: "Tipo: "},
	{id: "ciudadano_razon_social", nombre: "Ciudadano / razón social: "},
	{id: "fotografia", nombre: "Fotografía: "},
	{id: "nume_doc", nombre: "Número de documento: "},
	{id: "area_grafi", nombre: "Superficie (m2): "},
	{id: "peri_grafi", nombre: "Perímetro (m): "},
	];

var url = 'http://192.168.1.16',
url_wms = url + '/servicio/wms',
url_wfs = url + '/servicio/wfs',
latitud = -13.53168205546946, longitud = -71.95067643617946,
osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '&copy; <a href="https://www.gob.pe/muniwanchaq">Municipalidad distrital de Wanchaq</a>', maxZoom:20}),
esri_satelite =L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {maxZoom: 20}),
marker = null, ubicar_mapa= false,
labels = [],
icono_rojo = L.icon({iconUrl: './assets/images/icon-green50.png',shadowUrl: './assets/images/icon-green50_sombra.png',iconAnchor: [10, 46], popupAnchor:  [0, -46]}),
lotes_wfs = null;

var mapa = L.map('map', {center: [latitud, longitud], layers: [osm], zoom: (screen.width < 1280 ? 14 :15), minZoom: 10, maxZoom: 20,zoomControl: false});

L.control.navbar({position:'topleft'}).addTo(mapa);
L.control.zoom({position:'topleft'}).addTo(mapa);
L.control.scale().addTo(mapa);
L.control.mousePosition({position:'bottomleft', emptystring: ' ', numDigits: '3'}).addTo(mapa);
L.Control.Watermark = L.Control.extend({onAdd: function(map) {var img = L.DomUtil.create('img');img.src = './assets/images/logo_mdw.jpg';img.style.width = '240px';img.style.height= '48px';return img;},onRemove: function(map) {}});
L.control.watermark = function(opts) {return new L.Control.Watermark(opts);};
L.control.watermark({ position: 'topright' }).addTo(mapa); //topleft, topright, bottomleft, bottomright
var miniMap = new L.Control.MiniMap(esri_satelite, {collapsedWidth:'19px', collapsedHeight:'19px', toggleDisplay: true, minimized: true, position: 'bottomleft'}).addTo(mapa);
var boton_leyenda = L.easyButton({states: [{stateName: 'add-markers', icon: 'fa fa-arrow-down', title: 'Ocultar leyenda', onClick: function(control) {$('.legend').hide();control.state('remove-markers');}},{icon: 'fa fa-arrow-up', stateName: 'remove-markers', onClick: function(control) {$('.legend').show();control.state('add-markers');},title: 'Mostrar leyenda',}], position: 'bottomright'});
boton_leyenda.addTo(mapa);

function getFeatureInfo(latlng, nombre_capa, direccion)
{
	var punto = mapa.latLngToContainerPoint(latlng, mapa.getZoom()),
	tamano = mapa.getSize(),
	//crs = this.mapa.options.crs,
	crs = L.CRS.EPSG4326,
	sw = crs.project(mapa.getBounds().getSouthWest()),
	ne = crs.project(mapa.getBounds().getNorthEast());

	var params = {
		request: 'GetFeatureInfo',
		service: 'WMS',
		srs: crs.code,
		styles: '',
		transparent: 'TRUE',
		version: '1.1.1',
		format: 'image/png',
		bbox: sw.x + ',' + sw.y + ',' + ne.x + ',' + ne.y,
		height: tamano.y,
		width: tamano.x,
		layers: nombre_capa,
		query_layers: nombre_capa,
		info_format: 'text/plain',
		feature_count: 10,
		maxfeatures:10
	};

	params[params.version === '1.1.1' ? 'i' : 'x'] = punto.x;
	params[params.version === '1.1.1' ? 'j' : 'y'] = punto.y;

	return direccion + L.Util.getParamString(params, url, true);
}

mapa.on('click', function(e){
	var latlng = e.latlng;
	$('#div_tabla').show();
	if(ubicar_mapa)
	{
		if(marker != null) mapa.removeLayer(marker);
		marker = L.marker([latlng.lat, latlng.lng], {draggable:true, title:'Mi ubicación', icon: icono_rojo}).addTo(mapa);

		mapa.eachLayer(function(layer){
			if(layer.wmsParams != undefined)
			{
				//console.log(layer.wmsParams);
				var nombre = layer.wmsParams['layers'];
				var capa = layer.wmsParams['layers'];
				var direccion = layer._url;

				var url_datos = getFeatureInfo(marker.getLatLng(), nombre, direccion);
				if(url_datos.indexOf('&?') != -1) url_datos= url_datos.replace('&?','&');
				console.log(url_datos);
				if(nombre =='lotes')
				{
					var informacion = null;
					if(screen.width < 1280) informacion= L.popup({maxWidth:450});
					else informacion= L.popup({maxWidth:650});
					var tabla='';
					informacion.setLatLng(latlng);					
					$.ajax({
					url: url_datos,
					contentType: 'text/plain',
					success: function(data, status, xhr){
						var err = typeof data === 'string' ? null : data;
						var variable, titulo='', filas='', id, activo_fotografia=true, activo_id_lote=true, img=null, existe_img=null;
						var lineas = data.split('\n');

						for(var i=2;i<lineas.length-1;i++)
						{
							variable=lineas[i].replace(/^\s*/,'').replace(/\s*$/,'').replace(/ = /,'=').replace(/'/g,'').replace(/Layer/g,'').split('=');
							
							if(variable[0].indexOf('Feature') == -1)
							{
								if(i === 2)
								{
									if(variable[0] =='Search returned no results.')
										titulo = '<blockquote>'+ capa.toUpperCase() + ': La búsqueda no arrojó resultados.</blockquote>';
									else titulo = '<blockquote>'+ capa.toUpperCase() + '</blockquote>';
								}
								else
								{
									id = nombre_columnas.find(e => e.id === variable[0]);
									id = (id=== undefined)? variable[0] : id.nombre;

									if(variable[0] == 'fotografia')
									{
										var fotografia =  url + ':80/storage/img/imageneslotes/'+ variable[1];

										//existe_img = existe_url(fotografia);
										if(true)
										{
											if(activo_fotografia)
											{
												if(screen.width < 1280) img= '<img src="+ fotografia +" alt="Imagen" width="250" height="200">';
													else img= '<img src="+ fotografia +" alt="Imagen" width="350" height="300">';
											}
											activo_fotografia= false;
										}
									}
									else
									{
										if(activo_id_lote)
										{
											if(variable[0] == 'id_lote')
											{
												activo_id_lote=false;
												filas += '<tr><th><strong>'+id+'</strong></th><td><strong>'+variable[1]+'</strong></td></tr>';
											}
										}
										else
										{
										 	if(variable[0] == 'id_lote') filas +='<tr><th>&nbsp;</th><td>&nbsp;</td></tr>';
											else if(variable[1]!='') filas += '<tr><th>'+id+'</th><td>'+variable[1]+'</td></tr>';
										}
									}
									if(i == lineas.length-1) filas += '<br>'
								}
							}
						}
						if(existe_img) filas += '<tr><th>Fotografía: </th><td>' +img+ '</td></tr>';

						tabla = '<h6><strong>'+titulo+'</strong></h6><table class="striped"><tbody>'+filas+'</tbody></table>';
						informacion.setContent(tabla);
						informacion.bringToFront();
						informacion.openOn(mapa);
					},
					error: function(xhr, status, error) {
						console.log(error);
					}
				});

				}
			}
		});
	}
});

var boton_identificador = L.easyButton({states: [{stateName: 'add-markers', icon: 'fa fa-info fa-2x', title: 'Identificar información', onClick: function(control) { ubicar_mapa=true;control.state('remove-markers');
	$('.leaflet-container').css('cursor','help');
		}},{icon: 'fa fa-info-circle fa-2x', stateName: 'remove-markers', onClick: function(control) {
			ubicar_mapa=false;
			control.state('add-markers');
			$('.leaflet-container').css('cursor','pointer');
			if(marker != null) mapa.removeLayer(marker);
		},title: 'desactivar identificador de información',}], position: 'topleft'});
boton_identificador.addTo(mapa);

var colores=
{
	limite_administrativo: new Array('<strong>Cartografía base</strong>',
		'<i style="background:#26A69A"></i> Límite provincial',
		'<i style="background:#2980B9"></i> Límite distrital',
		'<i style="background:#FF00FF"></i> Puntos geodésicos',
		),
	predios_urbanos: new Array('<strong>Predios urbanos</strong>',
		'<i style="background:#0000FF"></i> Sectores',
		'<i style="background:#FF0000"></i> Manzanas',
		'<i style="background:#646B63"></i> Lotes',
		'<i style="background:#00BFFF"></i> Habilitación Urbana',
		/*'<i style="background:#93CD1E"></i> Área de terreno común',
		'<i style="background:#F2BCA0"></i> Edificaciones',
		'<i style="background:#FF69B4"></i> Puerta',
		'<i style="background:#900067"></i> Pisos',
		'<i style="background:#004E4F"></i> Servidumbre',
		'<i style="background:#f44336"></i> Área invadida',
		'<i style="background:#9E2D23"></i> Comercio',		
		'<i style="background:#00C400"></i> Parques',*/
		),
	areas_circulacion: new Array('<strong>Área de circulación</strong>',
		'<i style="background:#303030"></i> Eje vial'),
};

var div = L.DomUtil.create('div', 'info legend');

L.LegendControl = L.Control.extend({
onAdd: function (map) {
		
		L.DomEvent.on(div, 'mousewheel', L.DomEvent.stopPropagation);
		labels.push(colores.limite_administrativo.join('<br>'));
		labels.push(colores.predios_urbanos.join('<br>'));
		labels.push(colores.areas_circulacion.join('<br>'));
		div.innerHTML = labels.join('<br>');
		return div;
	}
});
L.legendControl = function(e) {
	return new L.LegendControl(e);
};

L.legendControl({position: 'bottomright'}).addTo(mapa);

/*var opciones_impresion ={
	title:'Imprimir',
	documentTitle: 'MUNICIPALIDAD PROVINCIAL DE URUBAMBA',
	closePopupsOnPrint: false,
	printModes: [
		L.BrowserPrint.Mode.Landscape('A3',{pageSize: 'A3', title:'A3'}),
		L.BrowserPrint.Mode.Landscape('A4',{pageSize: 'A4', title: 'A4'}),
		L.BrowserPrint.Mode.Auto('B4',{title: 'Automático'}),
		L.BrowserPrint.Mode.Custom('B5',{title:'Selecionar área'})
	],
	manualMode: false
}

L.control.browserPrint(opciones_impresion).addTo(mapa);*/

mapa.on('browser-print-start', function(e){
	L.legendControl({position: 'bottomright'}).addTo(e.printMap);
});

// INEI
var limite_provincial = L.tileLayer.wms(url_wms, {'layers': 'limite_provincial', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20}).addTo(mapa);
var limite_distrital = L.tileLayer.wms(url_wms, {'layers': 'limite_distrital', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20}).addTo(mapa);

// Municipalidad Provincial de Urubamba
var puntos_geodesicos_mpu = new L.tileLayer.wms(url_wms, {'layers': 'puntos_geodesicos', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var sectores_mpu = L.tileLayer.wms(url_wms, {'layers': 'sectores', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20}).addTo(mapa);
var manzanas_mpu = L.tileLayer.wms(url_wms, {'layers': 'manzanas', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var lotes_mpu = L.tileLayer.wms(url_wms, {'layers': 'lotes', 'tileSize': 1024, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var habilitacion_urbana_mpu = L.tileLayer.wms(url_wms, {'layers': 'habilitacion_urbana', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var servicio_basicos_mpu = L.tileLayer.wms(url_wms, {'layers': 'servicio_basicos', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var clasificacion_predio_mpu = L.tileLayer.wms(url_wms, {'layers': 'clasificacion_predio', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var tipo_persona_mpu = L.tileLayer.wms(url_wms, {'layers': 'tipo_persona', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var area_terreno_comun_mpu = L.tileLayer.wms(url_wms, {'layers': 'area_terreno_comun', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var edificaciones_mpu = L.tileLayer.wms(url_wms, {'layers': 'edificaciones', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var puerta_mpu = L.tileLayer.wms(url_wms, {'layers': 'puerta', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var pisos_mpu = L.tileLayer.wms(url_wms, {'layers': 'pisos', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var servidumbre_mpu = L.tileLayer.wms(url_wms, {'layers': 'servidumbre', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var area_invadida_mpu = L.tileLayer.wms(url_wms, {'layers': 'area_invadida', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var comercio_mpu = L.tileLayer.wms(url_wms, {'layers': 'comercio', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var parques_mpu = L.tileLayer.wms(url_wms, {'layers': 'parques', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var eje_via_mpu = L.tileLayer.wms(url_wms, {'layers': 'eje_via', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
// SBN
var predios_sbn = L.tileLayer.wms(url_wms, {'layers': 'predios', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});

// MIDAGRI
var predio_rural_midagri = L.tileLayer.wms(url_wms, {'layers': 'predio_rural', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});
var comunidades_campesinas_midagri = L.tileLayer.wms(url_wms, {'layers': 'comunidades_campesinas', 'tileSize': 512, 'transparent': true, 'format': 'image/png', maxZoom: 20});

var mapas_tematicos ={
	'Límite provincial' : limite_provincial,
	'Límite distrital' : limite_distrital,	
	'Sectores': sectores_mpu,
	'Manzanas': manzanas_mpu,
	'Lotes': lotes_mpu,
	'Habilitación Urbana': habilitacion_urbana_mpu,
	"Servicios básicos": servicio_basicos_mpu,
	'Clasificación del predio': clasificacion_predio_mpu,
	'Tipo de persona': tipo_persona_mpu,	
	'Área de terreno común': area_terreno_comun_mpu,
	'Edificaciones': edificaciones_mpu,
	'Puerta': puerta_mpu,
	'Pisos': pisos_mpu,
	'Servidumbre': servidumbre_mpu,
	'Área invadida': area_invadida_mpu,
	'Comercio': comercio_mpu,
	'Parques': parques_mpu,	
	'Eje vial': eje_via_mpu,
	'Puntos geodésicos': puntos_geodesicos_mpu,
	'Predios SBN': predios_sbn,
	'Predio Rural MIDAGRI': predio_rural_midagri,
	'Comunidades Campesinas MIDAGRI': comunidades_campesinas_midagri,
};

var mapas_base ={
	'OpenStreetMap': osm,
	'Satélite ESRI': esri_satelite,
};

var control_capas = L.control.layers(mapas_base, mapas_tematicos, {position: 'topright', sortLayers: false, 'selectedBaseLayerIndex': 4}).addTo(mapa);
$('.leaflet-control-layers').hide();

$('input[type="radio"]').change(function(event){	
	if(this.id == 'osm')
	{
		$('.leaflet-control-layers-selector')[0].click();
	}
	else if(this.id == 'esri_satelite')
	{
		$('.leaflet-control-layers-selector')[1].click();
	}
});

$('input[type="checkbox"]').change(function(event){
	if(this.id == 'limite_provincial')
	{
		if(this.checked) 
		{
			limite_provincial.addTo(mapa);
			limite_provincial.bringToFront();
		}
		else limite_provincial.remove();
		
	}
	else if(this.id == 'limite_distrital')
	{
		if(this.checked)
		{
			limite_distrital.addTo(mapa);
			limite_distrital.bringToFront();
		}
		else limite_distrital.remove();
	}
	else if(this.id == 'sectores')
	{
		if(this.checked)
		{
			sectores_mpu.addTo(mapa);
			sectores_mpu.bringToFront();
			//$('#propiedades_sector').prop('disabled', false);

			$('#div1').toggle();

			var url_lotes_con_sin_ficha = url_wfs + '?VERSION=1.1.0&SERVICE=WFS&REQUEST=GetFeature&TYPENAME=lotes_con_sin_ficha&outputFormat=geojson';
			
			var ajax = $.ajax({
				url : url_lotes_con_sin_ficha,
				dataType : 'json',
				success : function(response){					
					if(response.features.length > 0)
					{
						const propiedades = response.features.map(r => r.properties);
						const sectores = propiedades.map(i => i.cod_sector);
						const datos_con_ficha = propiedades.map(i => i.con_ficha);
						const datos_sin_ficha = propiedades.map(i => i.sin_ficha);
						console.log(sectores);

						var opciones = {
							series: [{name: 'Con ficha',data: datos_con_ficha},{name: 'Sin ficha', data: datos_sin_ficha}],
							chart: {type: 'bar', height: 400, stacked: true},
							plotOptions: { bar: { horizontal: true, dataLabels: { total: { enabled: true, offsetX: 0, style: { fontSize: '13px', fontWeight: 900}}}}},
							stroke: {width: 1, colors: ['#fff']},
							title: {text: 'Sectores'},
							xaxis: {categories: sectores},
							yaxis: {title: {text: undefined}},
							tooltip: {y: {formatter: function(val){return val + ''}}},
							fill: {opacity: 1},
							colors: ['#646B63', '#FF6600'],
							legend: {position: 'top', horizontalAlign: 'left', offsetX: 40}
				        };

				        var chart = new ApexCharts(document.querySelector('#chart'), opciones);
				        chart.render();

						/*$.each(response.features, function (k, v) {
						  //geometry = v.geometry;
						  propiedad = v.properties;
						  categoria_sector[k] = propiedad.cod_sector;
						  //datos_con_ficha.push(parseInt(propiedad.con_ficha,8));
						  datos_sin_ficha.push(parseInt(propiedad.sin_ficha,8));
						  //console.log(k);
						});*/
					}					
				}
			});			
		}
		else 
		{
			sectores_mpu.remove();
			$('#div1').hide();
		}
	}
	else if(this.id == 'manzanas')
	{
		if(this.checked)
		{
			manzanas_mpu.addTo(mapa);
			manzanas_mpu.bringToFront();
		}
		else manzanas_mpu.remove();
	}
	else if(this.id == 'lotes')
	{
		if(this.checked) 
		{
			lotes_mpu.addTo(mapa);
			lotes_mpu.bringToFront();
			$('#filtro_lotes').prop('disabled', false);
		}
		else 
		{
			lotes_mpu.remove();
			lotes_mpu.setUrl(url_wms);
			$('#filtro_lotes').prop('disabled', true);
			$('#con_ficha_lotes').prop('checked', false);
			$('#sin_ficha_lotes').prop('checked', false);
			if(lotes_wfs != null) lotes_wfs.remove();
			$('#dni_ruc').val('');
			$('#mensaje_dni_ruc').html('');
		}
	}
	else if(this.id == 'con_ficha_lotes' || this.id == 'sin_ficha_lotes')
	{
		if(this.checked) 
		{
			$('#dni_ruc').val('');
			$('#mensaje_dni_ruc').html('');
		}
		else $('#dni_ruc').focus();
	}
	else if(this.id == 'habilitacion_urbana')
	{
		if(this.checked)
		{
			habilitacion_urbana_mpu.addTo(mapa);
			habilitacion_urbana_mpu.bringToFront();
			$('#filtro_habilitacion_urbana').prop('disabled', false);
		}
		else
		{
			habilitacion_urbana_mpu.remove();
			habilitacion_urbana_mpu.setUrl(url_wms);
			$('#filtro_habilitacion_urbana').prop('disabled', true);
			$('#formal').prop('checked', false);
			$('#informal').prop('checked', false);
		}
	}
	else if(this.id == 'servicio_basicos')
	{
		if(this.checked) 
		{
			servicio_basicos_mpu.addTo(mapa);
			servicio_basicos_mpu.bringToFront();
			$('#filtro_servicio_basicos').prop('disabled', false);
		}
		else
		{
			servicio_basicos_mpu.remove();
			servicio_basicos_mpu.setUrl(url_wms);
			$('#filtro_servicio_basicos').prop('disabled', true);
			$('#formulario_servicios_basicos input').each(function(){
				$(this).prop('checked', false);
			});
		}
	}
	else if(this.id == 'clasificacion_predio')
	{
		if(this.checked) 
		{
			clasificacion_predio_mpu.addTo(mapa);
			clasificacion_predio_mpu.bringToFront();
			$('#filtro_clasificacion_predio').prop('disabled', false);
		}
		else
		{
			clasificacion_predio_mpu.remove();
			clasificacion_predio_mpu.setUrl(url_wms);
			$('#filtro_clasificacion_predio').prop('disabled', true);
			$('#formulario_clasificacion_predio input').each(function(){
				$(this).prop('checked', false);
			});
		}
	}
	else if(this.id == 'tipo_persona')
	{
		if(this.checked) 
		{
			tipo_persona_mpu.addTo(mapa);
			tipo_persona_mpu.bringToFront();
			$('#filtro_tipo_persona').prop('disabled', false);
		}
		else
		{
			tipo_persona_mpu.remove();
			tipo_persona_mpu.setUrl(url_wms);
			$('#filtro_tipo_persona').prop('disabled', true);
			$('#persona_natural').prop('checked', false);
			$('#persona_juridica').prop('checked', false);
		}
	}
	else if(this.id == 'area_terreno_comun')
	{
		if(this.checked)
		{
			area_terreno_comun_mpu.addTo(mapa);
			area_terreno_comun_mpu.bringToFront();
		}
		else area_terreno_comun_mpu.remove();
	}
	else if(this.id == 'edificaciones')
	{
		if(this.checked)
		{
			edificaciones_mpu.addTo(mapa);
			edificaciones_mpu.bringToFront();
		}
		else edificaciones_mpu.remove();
	}
	else if(this.id == 'puerta')
	{
		if(this.checked)
		{
			puerta_mpu.addTo(mapa);
			puerta_mpu.bringToFront();
			$('#filtro_puerta').prop('disabled', false);
		}
		else 
		{
			puerta_mpu.remove();
			$('#filtro_puerta').prop('disabled', true);
			$('#formulario_puerta input').each(function(){
				$(this).prop('checked', false);
			});
		}
	}
	else if(this.id == 'pisos')
	{
		if(this.checked)
		{
			pisos_mpu.addTo(mapa);
			pisos_mpu.bringToFront();
			$('#filtro_pisos').prop('disabled', false);
		}
		else 
		{
			pisos_mpu.remove();
			$('#filtro_pisos').prop('disabled', true);
			$('#formulario_pisos input').each(function(){
				$(this).prop('checked', false);
			});
		}
	}
	else if(this.id == 'servidumbre')
	{
		if(this.checked)
		{
			servidumbre_mpu.addTo(mapa);
			servidumbre_mpu.bringToFront();
		}
		else servidumbre_mpu.remove();
	}
	else if(this.id == 'area_invadida')
	{
		if(this.checked)
		{
			area_invadida_mpu.addTo(mapa);
			area_invadida_mpu.bringToFront();
		}
		else area_invadida_mpu.remove();
	}
	else if(this.id == 'comercio')
	{
		if(this.checked)
		{
			comercio_mpu.addTo(mapa);
			comercio_mpu.bringToFront();
		}
		else comercio_mpu.remove();
	}	
	else if(this.id == 'parques')
	{
		if(this.checked)
		{
			parques_mpu.addTo(mapa);
			parques_mpu.bringToFront();
		}
		else parques_mpu.remove();
	}
	else if(this.id == 'eje_via')
	{
		if(this.checked)
		{
			eje_via_mpu.addTo(mapa);
			eje_via_mpu.bringToFront();
		}
		else eje_via_mpu.remove();
	}
	else if(this.id == 'puntos_geodesicos')
	{
		if(this.checked)
		{
			puntos_geodesicos_mpu.addTo(mapa);
			puntos_geodesicos_mpu.bringToFront();
		}
		else puntos_geodesicos_mpu.remove();
	}
	else if(this.id == 'predios_sbn')
	{
		if(this.checked)
		{
			predios_sbn.addTo(mapa);
			predios_sbn.bringToFront();
		}
		else predios_sbn.remove();
	}
	else if(this.id == 'predio_rural_midagri')
	{
		if(this.checked) 
		{
			predio_rural_midagri.addTo(mapa);
			predio_rural_midagri.bringToFront();

		}
		else predio_rural_midagri.remove();
	}
	else if(this.id == 'comunidades_campesinas_midagri')
	{
		if(this.checked)
		{
			comunidades_campesinas_midagri.addTo(mapa);
			comunidades_campesinas_midagri.bringToFront();
		}
		else comunidades_campesinas_midagri.remove();
	}
});

$('#mostrar_lotes').click(function(){
	var dni_ruc = $('#dni_ruc').val();
	if(dni_ruc.length > 0)
	{
		//var url_lotes = url_wfs + '?VERSION=1.1.0&SERVICE=WFS&REQUEST=GetFeature&TYPENAME=lotes&Filter=%3CFilter%3E%3CPropertyIsEqualTo%3E%3CPropertyName%3Enume_doc%3C/PropertyName%3E%3CLiteral%3E'+ dni_ruc +'%3C/Literal%3E%3C/PropertyIsEqualTo%3E%3C/Filter%3E&outputFormat=geojson&maxFeatures=10'
		var url_lotes = url_wfs + '?VERSION=1.1.0&SERVICE=WFS&REQUEST=GetFeature&TYPENAME=lotes&outputFormat=geojson&maxFeatures=10&Filter=%3CFilter%3E%3CPropertyIsLike%20wildcard=%27*%27%20singleChar=%27.%27%20escape=%27!%27%3E%3CPropertyName%3Eciudadano_razon_social%3C/PropertyName%3E%3CLiteral%3E*'+ dni_ruc +'*%3C/Literal%3E%3C/PropertyIsLike%3E%3C/Filter%3E';
		console.log(url_lotes);
		var ajax = $.ajax({
			url : url_lotes,
			dataType : 'json',
			success : function(response){
				console.log(response.features.length);
				if(response.features.length > 0)
				{
					if(lotes_wfs != null) lotes_wfs.remove();
					lotes_wfs = L.geoJson(response,{
						style: function(feature){
							return {
								opacity : 1,
								color: 'black',
								fillOpacity: 0.7,
								fillColor:'red'
							};
						}
					}).addTo(mapa);

					mapa.fitBounds(lotes_wfs.getBounds());
					$('#ficha_lotes').modal('hide');
					$('#dni_ruc').val('');
					$('#mensaje_dni_ruc').html('');
				}
				else {
					$('#dni_ruc').focus();
					var texto = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Resultado!</strong> No se encontró ningún DNI y/o RUC: ' + dni_ruc + '.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close"></button></div>';
					$('#mensaje_dni_ruc').html(texto);
				}
			}
		});
	}
	else
	{
		var codigo='';
		var con_ficha = $('#con_ficha_lotes').is(':checked');
		var sin_ficha = $('#sin_ficha_lotes').is(':checked');
		if(con_ficha && sin_ficha) codigo = '1,2';
		else
		{
			if(con_ficha) codigo= '1';
			else if (sin_ficha) codigo= '2';
		}
		var url_filtro = url_wms + '?codigo='+codigo+'&';

		lotes_mpu.remove();
		lotes_mpu.setUrl(url_filtro);
		lotes_mpu.addTo(mapa);
		lotes_mpu.bringToFront();
		$('#ficha_lotes').modal('hide');
	}
});

$('#mostrar_habilitacion_urbana').click(function(){
	var codigo='';
	var formal = $('#formal').is(':checked');
	var informal = $('#informal').is(':checked');
	if(formal && informal) codigo = '1,2';
	else
	{
		if(formal) codigo= '1';
		else if (informal) codigo= '2';
	}
	var url_filtro = url_wms + '?codigo='+codigo+'&';

	habilitacion_urbana_mpu.remove();
	habilitacion_urbana_mpu.setUrl(url_filtro);
	habilitacion_urbana_mpu.addTo(mapa);
	habilitacion_urbana_mpu.bringToFront();
	$('#ficha_habilitacion_urbana').modal('hide');
});

$('#mostrar_servicio_basicos').click(function(){
	var i=1,luz,agua,telefono,desague,gas,internet,tvcable;
	$('#formulario_servicios_basicos input').each(function(){
		//console.log(i + ': '+$(this).is(':checked'));
		if(i == 1) 
		{
			luz = ($(this).is(':checked')== true)? 'luz=1' : 'luz=2';
			i++;
		}
		else if(i == 2) 
		{
			agua = ($(this).is(':checked')== true)? 'agua=1' : 'agua=2';
			i++;
		}
		else if(i == 3) 
		{
			telefono = ($(this).is(':checked')== true)? 'telefono=1' : 'telefono=2';
			i++;
		}
		else if(i == 4) 
		{
			desague = ($(this).is(':checked')== true)? 'desague=1' : 'desague=2';
			i++;
		}
		else if(i == 5) 
		{
			gas = ($(this).is(':checked')== true)? 'gas=1' : 'gas=2';
			i++;
		}
		else if(i == 6) 
		{
			internet = ($(this).is(':checked')== true)? 'internet=1' : 'internet=2';
			i++;
		}
		else if(i == 7) 
		{
			tvcable = ($(this).is(':checked')== true)? 'tvcable=1' : 'tvcable=2';
			i++;
		}
	});

	var url_filtro = url_wms + '?' + luz + '&' + agua + '&' + telefono + '&' + desague + '&' + gas + '&' + internet + '&' + tvcable + '&';
	if(url_filtro.indexOf('||?') != -1) url_filtro= url_filtro.replace('&?','&');
	if(servicio_basicos_mpu == null)
	{
		lotes_mpu.remove();
		servicio_basicos_mpu = new L.TileLayer.WMS(url_filtro, {maxZoom: 20,layers: 'servicio_basicos', format: 'image/png', transparent: true, version: '1.1.1', name: 'servicio_basicos', alt: 'Servicio básicos'});
		servicio_basicos_mpu.addTo(mapa);
		servicio_basicos_mpu.bringToFront();
		$('#ficha_servicio_basicos').modal('hide');
	}
	else
	{
		lotes_mpu.remove();
		servicio_basicos_mpu.remove();
		servicio_basicos_mpu = new L.TileLayer.WMS(url_filtro, {maxZoom: 20,layers: 'servicio_basicos', format: 'image/png', transparent: true, version: '1.1.1', name: 'servicio_basicos', alt: 'Servicio básicos'});
		servicio_basicos_mpu.addTo(mapa);
		servicio_basicos_mpu.bringToFront();
		$('#ficha_servicio_basicos').modal('hide');
	}
});

$('#mostrar_clasificacion_predio').click(function(){
	var i=1,codigo='';
	$('#formulario_clasificacion_predio input').each(function(){
		if(i == 1) 
		{
			codigo += ($(this).is(':checked')== true)? '01,' : '';
			i++;
		}
		else if(i == 2) 
		{
			codigo += ($(this).is(':checked')== true)? '02,' : '';
			i++;
		}
		else if(i == 3) 
		{
			codigo += ($(this).is(':checked')== true)? '03,' : '';
			i++;
		}
		else if(i == 4) 
		{
			codigo += ($(this).is(':checked')== true)? '04,' : '';
			i++;
		}
		else if(i == 5) 
		{
			codigo += ($(this).is(':checked')== true)? '05,' : '';
			i++;
		}
	});

	var url_filtro = url_wms + '?codigo=' + codigo + '&';
	if(url_filtro.indexOf('?&') != -1) url_filtro= url_filtro.replace('?&','?');
	if(url_filtro.indexOf(',&') != -1) url_filtro= url_filtro.replace(',&','&');
	if(clasificacion_predio_mpu != null)
	{
		clasificacion_predio_mpu.remove();
		clasificacion_predio_mpu.setUrl(url_filtro);
		clasificacion_predio_mpu.addTo(mapa);
		clasificacion_predio_mpu.bringToFront();
		$('#ficha_clasificacion_predio').modal('hide');
	}
});

$('#mostrar_tipo_persona').click(function(){
	var codigo='';
	var persona_natural = $('#persona_natural').is(':checked');
	var persona_juridica = $('#persona_juridica').is(':checked');
	if(persona_natural && persona_juridica) codigo = '1,2';
	else
	{
		if(persona_natural) codigo= '1';
		else if (persona_juridica) codigo= '2';
	}
	var url_filtro = url_wms + '?codigo='+codigo+'&';
	tipo_persona_mpu.remove();
	tipo_persona_mpu.setUrl(url_filtro);
	tipo_persona_mpu.addTo(mapa);
	tipo_persona_mpu.bringToFront();
	$('#ficha_tipo_persona').modal('hide');
});

$('#mostrar_puerta').click(function(){
	var i=1,tipo='';
	$('#formulario_puerta input').each(function(){
		if(i == 1) 
		{
			tipo += ($(this).is(':checked')== true)? 'P,' : '';
			i++;
		}
		else if(i == 2) 
		{
			tipo += ($(this).is(':checked')== true)? 'S,' : '';
			i++;
		}
		else if(i == 3) 
		{
			tipo += ($(this).is(':checked')== true)? 'G,' : '';
			i++;
		}
	});

	//var data= $('#bcp').select2('data');
	//console.log(data[0].id);

	var url_filtro = url_wms + '?tipo=' + tipo + '&';
	//console.log(url_filtro);
	if(url_filtro.indexOf('?&') != -1) url_filtro= url_filtro.replace('?&','?');
	if(url_filtro.indexOf(',&') != -1) url_filtro= url_filtro.replace(',&','&');
	if(puerta_mpu != null)
	{
		puerta_mpu.remove();
		puerta_mpu.setUrl(url_filtro);
		puerta_mpu.addTo(mapa);
		puerta_mpu.bringToFront();
		$('#ficha_puerta').modal('hide');
	}
});

$('#mostrar_pisos').click(function(){
	var i=1,codigo='';
	$('#formulario_pisos input').each(function(){
		if(i == 1) 
		{
			codigo += ($(this).is(':checked')== true)? '01,' : '';
			i++;
		}
		else if(i == 2) 
		{
			codigo += ($(this).is(':checked')== true)? '02,' : '';
			i++;
		}
		else if(i == 3) 
		{
			codigo += ($(this).is(':checked')== true)? '03,' : '';
			i++;
		}
		else if(i == 4) 
		{
			codigo += ($(this).is(':checked')== true)? '04,' : '';
			i++;
		}
		else if(i == 5) 
		{
			codigo += ($(this).is(':checked')== true)? '05,' : '';
			i++;
		}
		else if(i == 6) 
		{
			codigo += ($(this).is(':checked')== true)? '06,' : '';
			i++;
		}
		else if(i == 7) 
		{
			codigo += ($(this).is(':checked')== true)? '07,' : '';
			i++;
		}
		else if(i == 8) 
		{
			codigo += ($(this).is(':checked')== true)? '08,' : '';
			i++;
		}
		
	});

	var url_filtro = url_wms + '?codigo=' + codigo + '&';
	if(url_filtro.indexOf('?&') != -1) url_filtro= url_filtro.replace('?&','?');
	if(url_filtro.indexOf(',&') != -1) url_filtro= url_filtro.replace(',&','&');
	console.log(url_filtro);
	if(pisos_mpu != null)
	{
		pisos_mpu.remove();
		pisos_mpu.setUrl(url_filtro);
		pisos_mpu.addTo(mapa);
		pisos_mpu.bringToFront();
		$('#ficha_pisos').modal('hide');
	}
});


$(function() {

});
