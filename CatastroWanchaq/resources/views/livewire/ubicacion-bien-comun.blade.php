<div>
    <div class="row form-group">
    <h4 class="mb-4"> UBICACION DEL PREDIO CATASTRAL</h4>
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label d-inline-flex" > <div class="divcuadro">11</div> CÓDIGO VIA</label>
                <select class="form-select" id="via_id" name="via_id" data-width="100%" wire:model="tipoVia" data-live-search>
                    <option value="0">Seleccione</option>
                    @if($hab_urbana2!="")
                        @foreach($hab_urbana2->vias as $via)
                            <option value="{{str_pad($via->id_via,12,'0',STR_PAD_LEFT)}}">{{$via->codi_via}} {{$via->tipo_via}} {{$via->nomb_via}}</option>
                        @endforeach
                    @endif
                </select>
                @error('via_id')
                    <span class="error-message" style="color:red">{{ $message }}</span>
                @enderror
            </div>
        </div><!-- Col -->
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label d-inline-flex" > <div class="divcuadro">13</div>Tipo Via</label>
                <input type="text" class="form-control" placeholder="" name="nume_interior" value="{{$tipoVianombre}}">
                @error('nume_interior')
                    <span class="error-message" style="color:red">{{ $message }}</span>
                @enderror
            </div>
        </div><!-- Col -->
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label d-inline-flex" > <div class="divcuadro">13</div> Nombre Via</label>
                <input type="text" class="form-control" placeholder="" name="nume_interior" value="{{$tipoViatipo}}">
                @error('nume_interior')
                    <span class="error-message" style="color:red">{{ $message }}</span>
                @enderror
            </div>
        </div><!-- Col -->

        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label d-inline-flex" > <div class="divcuadro">11</div> TIPO PUERTA</label>
                <select class="form-select"  data-width="100%" data-live-search="true" name="tipo_puerta" id="tipo_puerta" wire:model="tipopuerta" >
                    <option value="" {{ old('tipo_puerta') == '' ? 'selected' : '' }}>SELECCIONE</option>
                    <option value="P" {{ old('tipo_puerta') == 'P' ? 'selected' : '' }}>PRINCIPAL</option>
                    <option value="S" {{ old('tipo_puerta') == 'S' ? 'selected' : '' }}>SECUNDARIO</option>
                    <option value="G" {{ old('tipo_puerta') == 'G' ? 'selected' : '' }}>GARAJE</option>
                    <option value="E" {{ old('tipo_puerta') == 'E' ? 'selected' : '' }}>ESTACIONAMIENTO</option>
                </select>
                @error('tipo_puerta')
                    <span class="error-message" style="color:red">{{ $message }}</span>
                                    @enderror
            </div>
        </div><!-- Col -->
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label d-inline-flex" > <div class="divcuadro">13</div> N° MUNICIPAL</label>
                <input type="text" class="form-control" placeholder="" name="nume_muni" id="nume_muni" wire:model="nume_muni">
                @error('nume_muni')
                    <span class="error-message" style="color:red">{{ $message }}</span>
                @enderror
            </div>
        </div><!-- Col -->
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label d-inline-flex" > <div class="divcuadro">11</div> COND NUMER.</label>
                <select class="form-select"  data-width="100%" data-live-search="true" name="cond_nume" id="cond_nume" wire:model="" >
                    <option value="" {{ old('cond_nume') == '' ? 'selected' : '' }}>SELECCIONE</option>
                    <option value="01" {{ old('cond_nume') == '01' ? 'selected' : '' }}>GENERADO POR LA MUNICIPALIDAD</option>
                    <option value="02" {{ old('cond_nume') == '02' ? 'selected' : '' }}>AUTOGENERADO POR EL TITULAR CATASTRAL</option>
                    <option value="03" {{ old('cond_nume') == '03' ? 'selected' : '' }}>GENERADO POR EL TÉCNICO CATASTRAL</option>
                    <option value="04" {{ old('cond_nume') == '04' ? 'selected' : '' }}>SIN NUMERO</option>
                </select>
                @error('cond_nume')
                    <span class="error-message" style="color:red">{{ $message }}</span>
                @enderror
            </div>
        </div><!-- Col -->
    </div><!-- Row -->











    <div class="row form-group">
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label d-inline-flex" > <div class="divcuadro">11</div> TIPO DE EDIFICACION</label>
                <select class="form-select"  data-width="100%" data-live-search="true" name="tipo_edificacion" id="tipo_edificacion" >
                    <option value="" {{ old('tipo_edificacion') == '' ? 'selected' : '' }}>SELECCIONE</option>
                    <option value="01" {{ old('tipo_edificacion') == '01' ? 'selected' : '' }}>BLOCK</option>
                    <option value="02" {{ old('tipo_edificacion') == '02' ? 'selected' : '' }}>CASA / CHALET</option>
                    <option value="03" {{ old('tipo_edificacion') == '03' ? 'selected' : '' }}>EDIFICIO</option>
                    <option value="04" {{ old('tipo_edificacion') == '04' ? 'selected' : '' }}>PABELLON</option>
                </select>
                @error('tipo_edificacion')
                    <span class="error-message" style="color:red">{{ $message }}</span>
                @enderror
            </div>
        </div><!-- Col -->
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label d-inline-flex" > <div class="divcuadro">11</div> TIPO DE INTERIOR</label>
                <select class="form-select"  data-width="100%" data-live-search="true" name="tipo_interior" id="tipo_interior" >
                    <option value="" {{ old('tipo_interior') == '' ? 'selected' : '' }}>SELECCIONE</option>
                    <option value="01" {{ old('tipo_interior') == '01' ? 'selected' : '' }}>DEPARTAMENTO</option>
                    <option value="02" {{ old('tipo_interior') == '02' ? 'selected' : '' }}>CASA / CHALET</option>
                    <option value="03" {{ old('tipo_interior') == '03' ? 'selected' : '' }}>OFICINA</option>
                    <option value="04" {{ old('tipo_interior') == '04' ? 'selected' : '' }}>ESTACIONAMIENTO</option>
                    <option value="05" {{ old('tipo_interior') == '05' ? 'selected' : '' }}>DEPÓSITO</option>
                    <option value="06" {{ old('tipo_interior') == '06' ? 'selected' : '' }}>TENDAL</option>
                    <option value="07" {{ old('tipo_interior') == '07' ? 'selected' : '' }}>TIENDA</option>
                    <option value="08" {{ old('tipo_interior') == '08' ? 'selected' : '' }}>PUESTO</option>
                    <option value="09" {{ old('tipo_interior') == '09' ? 'selected' : '' }}>STAND</option>
                    <option value="10" {{ old('tipo_interior') == '10' ? 'selected' : '' }}>PABELLON</option>
                </select>
                @error('tipo_interior')
                    <span class="error-message" style="color:red">{{ $message }}</span>
                @enderror
            </div>
        </div><!-- Col -->
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label d-inline-flex" > <div class="divcuadro">13</div> N° DE INTERIOR</label>
                <input type="text" class="form-control" placeholder="" name="nume_interior">
                @error('nume_interior')
                    <span class="error-message" style="color:red">{{ $message }}</span>
                @enderror
            </div>
        </div><!-- Col -->
    </div><!-- Row -->






    <div class="row form-group">
        <div class="col-md-2">
            <div class="mb-3">
                <div wire:ignore>
                    <label class="form-label d-inline-flex" > <div class="divcuadro">14</div> CÓDIGO URBANO</label>
                    <select class="form-select insumo_id" id="hab_urbana_id" name="hab_urbana_id" data-width="100%" wire:model="tipoHabi">
                        <option value="0">Seleccione</option>
                        @foreach($hab_urbanas as $hab_urbana)
                            <option value="{{str_pad($hab_urbana->id_hab_urba,10,'0',STR_PAD_LEFT)}}" {{ old('hab_urbana_id') == $hab_urbana->id ? 'selected' : '' }}>{{$hab_urbana->codi_hab_urba}} {{$hab_urbana->nomb_hab_urba}}</option>
                        @endforeach
                    </select>
                </div>
                <span class="text-danger">@error('hab_urbana_id'){{$message}}@enderror</span>
            </div>
        </div><!-- Col -->
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label d-inline-flex" > <div class="divcuadro">15</div> NOMBRE DE LA HABILITACIÓN URBANA</label>
                <input type="text" class="form-control" name="nomb_hab_urba" readonly placeholder="Nombre Habilitacion Urbana" wire:model="nomb_hab_urba">
                <span class="text-danger">@error('nomb_hab_urba'){{$message}}@enderror</span>
            </div>
        </div><!-- Col -->
        <div class="col-md-2">
            <div class="mb-2">
                <label class="form-label d-inline-flex" > <div class="divcuadro">16</div> ZONA/SECTOR/ETAPA</label>
                <input type="text" class="form-control" name="zona_dist" placeholder="Zona, Sector, Etapa" >
                <span class="text-danger">@error('zona_dist'){{$message}}@enderror</span>
            </div>
        </div><!-- Col -->
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label d-inline-flex" > <div class="divcuadro">17</div> MANZANA</label>
                <input type="text" class="form-control" name="mzna_dist" placeholder="Manzana"  >
                <span class="text-danger">@error('mzna_dist'){{$message}}@enderror</span>
            </div>
        </div><!-- Col -->
        <div class="col-md-1">
            <div class="mb-3">
                <label class="form-label d-inline-flex" > <div class="divcuadro">18</div> LOTE</label>
                <input type="text" class="form-control"  name="lote_dist" placeholder="Lote" >
                <span class="text-danger">@error('lote_dist'){{$message}}@enderror</span>
            </div>
        </div><!-- Col -->
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label d-inline-flex" > <div class="divcuadro">19</div> SUB-LOTE</label>
                <input type="text" class="form-control"  name="sub_lote_dist" placeholder="Sublote"  >
                <span class="text-danger">@error('sub_lote_dist'){{$message}}@enderror</span>
            </div>
        </div><!-- Col -->
    </div><!-- Row -->
</div>

@push('custom-scripts')
<script>
    document.addEventListener('livewire:load',function(){
        $('#hab_urbana_id').select2();
        $('#hab_urbana_id').on('change',function(){
            @this.set('tipoHabi',this.value);
        });
    })
</script>

@endpush
