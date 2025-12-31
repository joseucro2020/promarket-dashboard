@php
  $isEdit = isset($supplier);
  $action = $isEdit ? route('suppliers.update', $supplier->id) : route('suppliers.store');
  $method = $isEdit ? 'PUT' : 'POST';
@endphp

<div class="card">
  <div class="card-header">
    <h4 class="card-title">{{ $isEdit ? __('Editar Proveedor') : __('Crear Proveedor') }}</h4>
  </div>
  <div class="card-body">
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ $action }}">
      @csrf
      @if($isEdit)
        @method('PUT')
      @endif

      <div class="row">
        <div class="col-md-4 form-group">
          <label>{{ __('Tipo Persona') }}</label>
          <select name="tipo_prove" class="form-control">
            <option value="">Seleccione</option>
            <option value="natural" {{ old('tipo_prove', $supplier->tipo_prove ?? '') == 'natural' ? 'selected' : '' }}>{{ __('Natural') }}</option>
            <option value="juridica" {{ old('tipo_prove', $supplier->tipo_prove ?? '') == 'juridica' ? 'selected' : '' }}>{{ __('Juridica') }}</option>
          </select>
        </div>
        <div class="col-md-4 form-group">
          <label>CI/RIF</label>
          <input type="text" name="id_prove" class="form-control" value="{{ old('id_prove', $supplier->id_prove ?? '') }}">
        </div>
        <div class="col-md-4 form-group">
          <label>{{ __('Nombre / Razon Social') }}</label>
          <input type="text" name="nombre_prove" class="form-control" required value="{{ old('nombre_prove', $supplier->nombre_prove ?? '') }}">
        </div>
      </div>

      <div class="row">
        <div class="col-md-4 form-group">
          <label>{{ __('Procedencia') }}</label>
          <select name="proced_prove" class="form-control">
            <option value="">Seleccione</option>
            <option value="local" {{ old('proced_prove', $supplier->proced_prove ?? '') == 'local' ? 'selected' : '' }}>{{ __('Local') }}</option>
            <option value="importado" {{ old('proced_prove', $supplier->proced_prove ?? '') == 'importado' ? 'selected' : '' }}>{{ __('Importado') }}</option>
          </select>
        </div>
        <div class="col-md-4 form-group">
          <label>{{ __('Responsable') }}</label>
          <input type="text" name="rsp_prove" class="form-control" value="{{ old('rsp_prove', $supplier->rsp_prove ?? '') }}">
        </div>
        <div class="col-md-4 form-group">
          <label>{{ __('Status') }}</label>
          <select name="status_prove" class="form-control">
            <option value="1" {{ old('status_prove', $supplier->status_prove ?? '1') == '1' ? 'selected' : '' }}>{{ __('Activo') }}</option>
            <option value="2" {{ old('status_prove', $supplier->status_prove ?? '') == '2' ? 'selected' : '' }}>{{ __('Inactivo') }}</option>
          </select>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4 form-group">
          <label>{{ __('Pais') }}</label>
          <select name="pais_prove" class="form-control">
            <option value="">Seleccione</option>
            @if(isset($paises))
              @foreach($paises as $pais)
                <option value="{{ $pais->id }}" {{ old('pais_prove', $supplier->pais_prove ?? '') == $pais->id ? 'selected' : '' }}>{{ $pais->nombre ?? $pais->name ?? $pais->pais }}</option>
              @endforeach
            @endif
          </select>
        </div>
        <div class="col-md-4 form-group">
          <label>{{ __('Estado') }}</label>
          <select name="estado_prove" class="form-control">
            <option value="">Seleccione</option>
            @if(isset($states))
              @foreach($states as $st)
                <option value="{{ $st->id }}" {{ old('estado_prove', $supplier->estado_prove ?? '') == $st->id ? 'selected' : '' }}>{{ $st->nombre ?? $st->name }}</option>
              @endforeach
            @endif
          </select>
        </div>
        <div class="col-md-4 form-group">
          <label>{{ __('Municipio') }}</label>
          <select name="muni_prove" class="form-control">
            <option value="">Seleccione</option>
            @if(isset($municipalities))
              @foreach($municipalities as $m)
                <option value="{{ $m->id }}" {{ old('muni_prove', $supplier->muni_prove ?? '') == $m->id ? 'selected' : '' }}>{{ $m->name ?? $m->nombre }}</option>
              @endforeach
            @endif
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>{{ __('Direccion') }}</label>
        <textarea name="direcc_prove" class="form-control" rows="4">{{ old('direcc_prove', $supplier->direcc_prove ?? '') }}</textarea>
      </div>

      <div class="row">
        <div class="col-md-4 form-group">
          <label>{{ __('Codigo postal') }}</label>
          <input type="text" name="postal_prove" class="form-control" value="{{ old('postal_prove', $supplier->postal_prove ?? '') }}">
        </div>
        <div class="col-md-4 form-group">
          <label>{{ __('Telefono') }}</label>
          <input type="text" name="tlf_prove" class="form-control" value="{{ old('tlf_prove', $supplier->tlf_prove ?? '') }}">
        </div>
        <div class="col-md-4 form-group">
          <label>{{ __('Email') }}</label>
          <input type="email" name="email_prove" class="form-control" value="{{ old('email_prove', $supplier->email_prove ?? '') }}">
        </div>
      </div>

      <hr>
      <h5>{{ __('Vendedor') }}</h5>
      <div class="row">
        <div class="col-md-6 form-group">
          <label>{{ __('Nombre Vendedor') }}</label>
          <input type="text" name="seller_name" class="form-control" value="{{ old('seller_name', $supplier->seller_name ?? '') }}">
        </div>
        <div class="col-md-6 form-group">
          <label>{{ __('Telefono Vendedor') }}</label>
          <input type="text" name="seller_phone" class="form-control" value="{{ old('seller_phone', $supplier->seller_phone ?? '') }}">
        </div>
      </div>

      <div class="text-right">
        <button class="btn btn-primary">{{ $isEdit ? __('Actualizar') : __('Guardar') }}</button>
      </div>
    </form>
  </div>
</div>

@section('page-script')
  <script>
    (function(){
      const baseUrl = '{{ url('panel') }}';
      const $pais = $('select[name="pais_prove"]');
      const $estado = $('select[name="estado_prove"]');
      const $muni = $('select[name="muni_prove"]');

      const selectedState = @json(old('estado_prove', $supplier->estado_prove ?? null));
      const selectedMunicipality = @json(old('muni_prove', $supplier->muni_prove ?? null));

      function loadStates(countryId, selectStateId){
        $estado.empty().append('<option value="">Cargando...</option>');
        $muni.empty().append('<option value="">Seleccione</option>');
        if(!countryId){ $estado.empty().append('<option value="">Seleccione</option>'); return; }
        $.get(baseUrl + '/paises/' + countryId + '/estados').done(function(data){
          $estado.empty().append('<option value="">Seleccione</option>');
          data.forEach(function(s){
            const selected = selectStateId && selectStateId == s.id ? 'selected' : '';
            $estado.append('<option value="'+s.id+'" '+selected+'>'+ (s.nombre || s.name || s.nombre_estado || s.estado) +'</option>');
          });
          if(selectStateId){ $estado.trigger('change'); }
        }).fail(function(){
          $estado.empty().append('<option value="">Seleccione</option>');
        });
      }

      function loadMunicipalities(stateId, selectMuniId){
        $muni.empty().append('<option value="">Cargando...</option>');
        if(!stateId){ $muni.empty().append('<option value="">Seleccione</option>'); return; }
        $.get(baseUrl + '/estados/' + stateId + '/municipios').done(function(data){
          $muni.empty().append('<option value="">Seleccione</option>');
          data.forEach(function(m){
            const selected = selectMuniId && selectMuniId == m.id ? 'selected' : '';
            $muni.append('<option value="'+m.id+'" '+selected+'>'+ (m.name || m.nombre) +'</option>');
          });
        }).fail(function(){
          $muni.empty().append('<option value="">Seleccione</option>');
        });
      }

      $pais.on('change', function(){
        const countryId = $(this).val();
        loadStates(countryId, null);
      });

      $estado.on('change', function(){
        const stateId = $(this).val();
        loadMunicipalities(stateId, null);
      });

      // On load: if there is a selected country/state, populate dependent selects
      $(function(){
        const initialCountry = $pais.val();
        if(initialCountry){
          loadStates(initialCountry, selectedState);
        }
        if(selectedState){
          // when states loaded they will trigger change and load municipalities with selectedMunicipality
          // ensure municipalities load after states -> handled in loadStates via trigger('change') when selectStateId provided
          // but if states were not loaded via AJAX, attempt to load municipalities directly
          if(!initialCountry){ loadMunicipalities(selectedState, selectedMunicipality); }
        }
      });
    })();
  </script>
@endsection
