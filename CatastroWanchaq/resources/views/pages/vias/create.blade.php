@extends('layout.master')
@push('plugin-styles')
    <link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
@endpush
@section('content')


<div class="row">
    <div class="col-md-12 grid-margin stretch-card" >
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <form action="{{route('vias.store')}}" method="post" class="form-horizontal" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <div class="row form-group">
                        <h4 class="mb-4"> Crear Via</h4>
                        
                        <div class="mb-3 col-md-4">
                            <label for="recipient-name" class="form-label">Codigo:</label>
                            <input type="text" class="form-control codi_via" id="codi_via" name="codi_via" value="{{old('codi_via')}}">
                            @error('codi_via')
                                <span class="error-message" style="color:red">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="mb-3 col-md-4">
                            <label for="recipient-name" class="form-label">Tipo de Via:</label>
                            <select class="form-select"  data-width="100%" data-live-search="true" name="tipo_via" id="tipo_via" >
                                <option value="AV" {{ old('tipo_via') == 'AV' ? 'selected' : '' }}>AV</option>
                                <option value="CA" {{ old('tipo_via') == 'CA' ? 'selected' : '' }}>CA</option>
                                <option value="JR" {{ old('tipo_via') == 'JR' ? 'selected' : '' }}>JR</option>
                                <option value="PJE" {{ old('tipo_via') == 'PJE' ? 'selected' : '' }}>PJE</option>
                                <option value="AL" {{ old('tipo_via') == 'AL' ? 'selected' : '' }}>AL</option>
                                <option value="CTRA" {{ old('tipo_via') == 'CTRA' ? 'selected' : '' }}>CTRA</option>
                                <option value="PRLG" {{ old('tipo_via') == 'PRLG' ? 'selected' : '' }}>PRLG</option>
                                <option value="PS" {{ old('tipo_via') == 'PS' ? 'selected' : '' }}>PS</option>
                                <option value="ML" {{ old('tipo_via') == 'ML' ? 'selected' : '' }}>ML</option>
                                <option value="CAM" {{ old('tipo_via') == 'CAM' ? 'selected' : '' }}>CAM</option>
                            </select>
                            @error('tipo_via')
                                <span class="error-message" style="color:red">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label for="recipient-name" class="form-label">Nombre:</label>
                            <input type="text" class="form-control nomb_via" id="nomb_via" name="nomb_via" value="{{old('nomb_via')}}">
                            @error('nomb_via')
                                <span class="error-message" style="color:red">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
$('#hab_urb_id').select2();

</script>
<script>
    document.getElementById('codi_via').addEventListener('blur', function() {
        let inputValue = this.value;
        
        // Solo aplica ceros si el valor no está vacío y tiene menos de 6 caracteres
        if (inputValue && inputValue.length < 6) {
            this.value = inputValue.padStart(6, '0'); // Agrega ceros a la izquierda hasta llegar a 6 dígitos
        }
    });
</script>


@endpush
