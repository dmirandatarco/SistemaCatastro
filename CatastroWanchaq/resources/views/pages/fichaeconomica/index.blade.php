@extends('layout.master')
@push('plugin-styles')
  <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
@endpush
@section('content')
<div class="row inbox-wrapper">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <h4 class="mb-3">Listado de Fichas</h4>
          <div class="mb-5">
            <button type="button" class="btn btn-primary mb-2 mb-md-0 " data-bs-toggle="modal" data-bs-target="#Agregar">
              <i data-feather="user-plus"></i><b> &nbsp; Crear Via</b>
            </button>
          </div>
          @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ $message }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close"></button>
            </div>
          @endif
          <div class="table-responsive">
            <table id="fichas" class="table">
              <thead>
                <tr >
                  <th>Ver</th>
                  <th>Codigo Unico Catastral</th>
                  <th>Numero de Ficha</th>
                  <th>Fecha de Registro</th>
                  <th>Tipo de Ficha</th>                
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                @foreach($fichas as $ficha)
                <tr>
                  <td>
                    <button type="button" class="btn btn-primary btn-icon edit" data-bs-toggle="modal" data-bs-target="#Editar" >
                      <i data-feather="edit"></i>
                    </button>
                  </td>
                  <td>{{$ficha->unicat->cuc}}</td>
                  <td>{{$ficha->nume_ficha}}</td>
                  <td>{{$ficha->fecha_declarante}}</td>
                  <td>{{$ficha->tipo_ficha}}</td>
                  <td>
                    <button type="button" class="btn btn-primary btn-icon edit" data-bs-toggle="modal" data-bs-target="#Editar" >
                      <i data-feather="edit"></i>
                    </button>
                    <a href="{{route('pdf.individual',$ficha)}}" target="_blank"> 
                      <button type="button" class="btn btn-primary btn-icon " data-bs-toggle="modal">
                        <i data-feather="printer"></i>
                      </button>
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



@endsection

@push('plugin-scripts')
  <script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
@endpush

@push('custom-scripts')
@if(!empty(Session::get('error_code')) && Session::get('error_code') == 5)
<script>
    $(function() {
        $('#Editar').modal('show');
    });
</script>       
@else
  @if(count($errors)>0)
  <script>
      $(function() {
          $('#Agregar').modal('show');
      });
  </script>       
  @endif 
@endif

<script>

var editar = document.getElementById('Editar');

editar.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget

  var id = button.getAttribute('data-id')
  var nomb_via = button.getAttribute('data-nomb_via')
  var tipo_via = button.getAttribute('data-tipo_via')
  var codi_via = button.getAttribute('data-codi_via')
  var id_ubi_geo = button.getAttribute('data-id_ubi_geo')

  var idModal = editar.querySelector('.id_via')
  var nomb_viaModal = editar.querySelector('.nomb_via')
  var tipo_viaModal = editar.querySelector('.tipo_via')
  var codi_viaModal = editar.querySelector('.codi_via')
  var id_ubi_geoModal = editar.querySelector('.id_ubi_geo')

  idModal.value = id;
  nomb_viaModal.value = nomb_via;
  tipo_viaModal.value = tipo_via;
  codi_viaModal.value = codi_via;
  id_ubi_geoModal.value = id_ubi_geo;
});

var eliminar = document.getElementById('Eliminar');

eliminar.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget

  var id = button.getAttribute('data-id')

  var idModal = eliminar.querySelector('.id_via_2')

  idModal.value = id;
});

$(function() {
  'use strict';

  $(function() {
    $('#fichas').DataTable({
      "aLengthMenu": [
        [10, 30, 50, -1],
        [10, 30, 50, "All"]
      ],
      "language": {
        "lengthMenu": "Mostrar  _MENU_  registros por paginas",
        "zeroRecords": "Nada encontrado - disculpa",
        "info": "Mostrando la página page _PAGE_ de _PAGES_",
        "infoEmpty": "No hay registros disponibles.",
        "infoFiltered": "(filtrado de _MAX_ registros totales)",
        "search": "Buscar:",
        "paginate":{
          "next": "Siguiente",
          "previous": "Anterior",
        }
      },
      "columnDefs": [
        { 
           
        }
      ]
    });
  });

});
</script>
@endpush