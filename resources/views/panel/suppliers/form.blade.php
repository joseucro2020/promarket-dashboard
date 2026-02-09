@php
  $isEdit = isset($supplier);
  $action = $isEdit ? route('suppliers.update', $supplier->id) : route('suppliers.store');
  $method = $isEdit ? 'PUT' : 'POST';
@endphp

<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ $isEdit ? __('Edit Supplier') : __('New Supplier') }}</h4>
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
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('Person Type') }}</label>
                  <select name="tipo_prove" class="form-control">
                    <option value="">{{ __('Select') }}</option>
                    <option value="natural" {{ old('tipo_prove', $supplier->tipo_prove ?? '') == 'natural' ? 'selected' : '' }}>{{ __('Natural') }}</option>
                    <option value="juridica" {{ old('tipo_prove', $supplier->tipo_prove ?? '') == 'juridica' ? 'selected' : '' }}>{{ __('Legal') }}</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('ID / RIF') }}</label>
                  <input type="text" name="id_prove" class="form-control" value="{{ old('id_prove', $supplier->id_prove ?? '') }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('Name / Business Name') }}</label>
                  <input type="text" name="nombre_prove" class="form-control" required value="{{ old('nombre_prove', $supplier->nombre_prove ?? '') }}">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('Origin') }}</label>
                  <select name="proced_prove" class="form-control">
                    <option value="">{{ __('Select') }}</option>
                    <option value="local" {{ old('proced_prove', $supplier->proced_prove ?? '') == 'local' ? 'selected' : '' }}>{{ __('Local') }}</option>
                    <option value="importado" {{ old('proced_prove', $supplier->proced_prove ?? '') == 'importado' ? 'selected' : '' }}>{{ __('Imported') }}</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('Contact Person') }}</label>
                  <input type="text" name="rsp_prove" class="form-control" value="{{ old('rsp_prove', $supplier->rsp_prove ?? '') }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('Status') }}</label>
                  <select name="status_prove" class="form-control">
                    <option value="1" {{ old('status_prove', $supplier->status_prove ?? '1') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="2" {{ old('status_prove', $supplier->status_prove ?? '') == '2' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('Country') }}</label>
                  <select name="pais_prove" class="form-control">
                    <option value="">{{ __('Select') }}</option>
                    @if(isset($paises))
                      @foreach($paises as $pais)
                        <option value="{{ $pais->id }}" {{ old('pais_prove', $supplier->pais_prove ?? '') == $pais->id ? 'selected' : '' }}>{{ $pais->nombre ?? $pais->name ?? $pais->pais }}</option>
                      @endforeach
                    @endif
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('State') }}</label>
                  <select name="estado_prove" class="form-control">
                    <option value="">{{ __('Select') }}</option>
                    @if(isset($states))
                      @foreach($states as $st)
                        <option value="{{ $st->id }}" {{ old('estado_prove', $supplier->estado_prove ?? '') == $st->id ? 'selected' : '' }}>{{ $st->nombre ?? $st->name }}</option>
                      @endforeach
                    @endif
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('Municipality') }}</label>
                  <select name="muni_prove" class="form-control">
                    <option value="">{{ __('Select') }}</option>
                    @if(isset($municipalities))
                      @foreach($municipalities as $m)
                        <option value="{{ $m->id }}" {{ old('muni_prove', $supplier->muni_prove ?? '') == $m->id ? 'selected' : '' }}>{{ $m->name ?? $m->nombre }}</option>
                      @endforeach
                    @endif
                  </select>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>{{ __('Address') }}</label>
              <textarea name="direcc_prove" class="form-control" rows="4">{{ old('direcc_prove', $supplier->direcc_prove ?? '') }}</textarea>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('Postal Code') }}</label>
                  <input type="text" name="postal_prove" class="form-control" value="{{ old('postal_prove', $supplier->postal_prove ?? '') }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('Phone') }}</label>
                  <input type="text" name="tlf_prove" class="form-control" value="{{ old('tlf_prove', $supplier->tlf_prove ?? '') }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('Email') }}</label>
                  <input type="email" name="email_prove" class="form-control" value="{{ old('email_prove', $supplier->email_prove ?? '') }}">
                </div>
              </div>
            </div>

            <hr>
            <h5>{{ __('Sales contact') }}</h5>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ __('Sales Contact Name') }}</label>
                  <input type="text" name="seller_name" class="form-control" value="{{ old('seller_name', $supplier->seller_name ?? '') }}">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ __('Sales Contact Phone') }}</label>
                  <input type="text" name="seller_phone" class="form-control" value="{{ old('seller_phone', $supplier->seller_phone ?? '') }}">
                </div>
              </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
              <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary mr-2">{{ __('Back') }}</a>
              <button type="submit" class="btn btn-primary">{{ $isEdit ? __('Update') : __('Save') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

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
        $estado.empty().append('<option value="">{{ __('Loading...') }}</option>');
        $muni.empty().append('<option value="">{{ __('Select') }}</option>');
        if(!countryId){ $estado.empty().append('<option value="">{{ __('Select') }}</option>'); return; }
        $.get(baseUrl + '/paises/' + countryId + '/estados').done(function(data){
          $estado.empty().append('<option value="">{{ __('Select') }}</option>');
          data.forEach(function(s){
            const selected = selectStateId && selectStateId == s.id ? 'selected' : '';
            $estado.append('<option value="'+s.id+'" '+selected+'>'+ (s.nombre || s.name || s.nombre_estado || s.estado) +'</option>');
          });
          if(selectStateId){ $estado.trigger('change'); }
        }).fail(function(){
          $estado.empty().append('<option value="">{{ __('Select') }}</option>');
        });
      }

      function loadMunicipalities(stateId, selectMuniId){
        $muni.empty().append('<option value="">{{ __('Loading...') }}</option>');
        if(!stateId){ $muni.empty().append('<option value="">{{ __('Select') }}</option>'); return; }
        $.get(baseUrl + '/estados/' + stateId + '/municipios').done(function(data){
          $muni.empty().append('<option value="">{{ __('Select') }}</option>');
          data.forEach(function(m){
            const selected = selectMuniId && selectMuniId == m.id ? 'selected' : '';
            $muni.append('<option value="'+m.id+'" '+selected+'>'+ (m.name || m.nombre) +'</option>');
          });
        }).fail(function(){
          $muni.empty().append('<option value="">{{ __('Select') }}</option>');
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
