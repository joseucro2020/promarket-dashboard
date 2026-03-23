@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Update Client') . ' ' . $client->name)

@section('content')
<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ __('locale.Update Client') }}</h4>
        </div>
        <div class="card-body">
          <div id="client-edit-alert" class="alert alert-danger d-none"></div>

          <form id="clientEditForm" method="POST" action="{{ route('clients.update') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $client->id }}">

            <div class="row">
              <div class="col-md-6">
                <div class="card h-100">
                  <div class="card-header">
                    <h5 class="mb-0">{{ __('locale.Customer Details') }}</h5>
                  </div>
                  <div class="card-body">
                    <div class="form-group">
                      <label>{{ __('locale.Name') }}</label>
                      <input type="text" name="name" class="form-control" value="{{ old('name', $client->name) }}" required>
                    </div>

                    <div class="form-group">
                      <label>{{ __('locale.Email') }}</label>
                      <input type="email" name="email" class="form-control" value="{{ old('email', $client->email) }}" required>
                    </div>

                    <div class="form-group">
                      <label>{{ __('locale.Personality Type') }}</label>
                      <select name="type" id="client_type" class="form-control" required>
                        <option value="1" {{ old('type', $client->persona) == 1 ? 'selected' : '' }}>{{ __('locale.Natural') }}</option>
                        <option value="2" {{ old('type', $client->persona) == 2 ? 'selected' : '' }}>{{ __('locale.Legal') }}</option>
                      </select>
                    </div>

                    <div class="form-group mb-0">
                      <label>{{ __('locale.ID / RIF') }}</label>
                      <input type="text" name="identificacion" class="form-control" value="{{ old('identificacion', $client->identificacion) }}" required>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card h-100">
                  <div class="card-header">
                    <h5 class="mb-0">{{ __('locale.Contact Information') }}</h5>
                  </div>
                  <div class="card-body">
                    <div class="form-group">
                      <label>{{ __('locale.Phone') }}</label>
                      <div class="input-group">
                        <div class="input-group-prepend" style="max-width: 120px;">
                          @php
                            $defaultPhoneCode = old('telefono_country_code');
                            $phoneCountryCodes = ['58', '1', '52', '57', '54', '56', '51', '593', '595', '598', '591', '34', '507', '506', '503', '502', '504', '505', '53'];
                            if (!$defaultPhoneCode) {
                              $phoneDigits = preg_replace('/\D+/', '', (string) ($client->telefono_whatsapp ?? $client->telefono ?? ''));
                              $defaultPhoneCode = '58';
                              foreach ($phoneCountryCodes as $code) {
                                if (strpos($phoneDigits, $code) === 0) {
                                  $defaultPhoneCode = $code;
                                  break;
                                }
                              }
                            }
                          @endphp
                          <select name="telefono_country_code" class="form-control">
                            <option value="58" {{ $defaultPhoneCode === '58' ? 'selected' : '' }}>+58</option>
                            <option value="1" {{ $defaultPhoneCode === '1' ? 'selected' : '' }}>+1</option>
                            <option value="52" {{ $defaultPhoneCode === '52' ? 'selected' : '' }}>+52</option>
                            <option value="57" {{ $defaultPhoneCode === '57' ? 'selected' : '' }}>+57</option>
                            <option value="54" {{ $defaultPhoneCode === '54' ? 'selected' : '' }}>+54</option>
                            <option value="56" {{ $defaultPhoneCode === '56' ? 'selected' : '' }}>+56</option>
                            <option value="51" {{ $defaultPhoneCode === '51' ? 'selected' : '' }}>+51</option>
                            <option value="593" {{ $defaultPhoneCode === '593' ? 'selected' : '' }}>+593</option>
                            <option value="595" {{ $defaultPhoneCode === '595' ? 'selected' : '' }}>+595</option>
                            <option value="598" {{ $defaultPhoneCode === '598' ? 'selected' : '' }}>+598</option>
                            <option value="591" {{ $defaultPhoneCode === '591' ? 'selected' : '' }}>+591</option>
                            <option value="34" {{ $defaultPhoneCode === '34' ? 'selected' : '' }}>+34</option>
                            <option value="507" {{ $defaultPhoneCode === '507' ? 'selected' : '' }}>+507</option>
                            <option value="506" {{ $defaultPhoneCode === '506' ? 'selected' : '' }}>+506</option>
                            <option value="503" {{ $defaultPhoneCode === '503' ? 'selected' : '' }}>+503</option>
                            <option value="502" {{ $defaultPhoneCode === '502' ? 'selected' : '' }}>+502</option>
                            <option value="504" {{ $defaultPhoneCode === '504' ? 'selected' : '' }}>+504</option>
                            <option value="505" {{ $defaultPhoneCode === '505' ? 'selected' : '' }}>+505</option>
                            <option value="53" {{ $defaultPhoneCode === '53' ? 'selected' : '' }}>+53</option>
                          </select>
                        </div>
                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $client->telefono) }}" placeholder="4244470584">
                      </div>
                      <small class="text-muted">Formato WhatsApp internacional (código de país + número)</small>
                    </div>

                    <div class="form-group">
                      <label>{{ __('locale.Password') }}</label>
                      <input type="password" name="password" class="form-control" autocomplete="new-password">
                    </div>

                    <div class="form-group mb-0">
                      <label>{{ __('locale.Repeat Password') }}</label>
                      <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row mt-3" id="legal-fields" style="display:none;">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h5 class="mb-0">{{ __('locale.Legal') }}</h5>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group mb-md-0">
                          <label>{{ __('locale.Company') }}</label>
                          <input type="text" name="empresa" class="form-control" value="{{ old('empresa', $client->empresa) }}">
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group mb-0">
                          <label>{{ __('locale.Fiscal Address') }}</label>
                          <input type="text" name="fiscal" class="form-control" value="{{ old('fiscal', $client->fiscal) }}">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-6">
                <div class="card h-100">
                  <div class="card-header">
                    <h5 class="mb-0">{{ __('locale.Address') }}</h5>
                  </div>
                  <div class="card-body">
                    <div class="form-group">
                      <label>{{ __('locale.State') }}</label>
                      <select name="estado_id" id="estado_id" class="form-control" required>
                        <option value="">{{ __('locale.Select') }}</option>
                        @foreach($states as $state)
                          <option value="{{ $state->id }}" {{ old('estado_id', $client->estado_id) == $state->id ? 'selected' : '' }}>{{ $state->nombre }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="form-group">
                      <label>{{ __('locale.Municipality') }}</label>
                      <select name="municipality_id" id="municipality_id" class="form-control" required>
                        <option value="">{{ __('locale.Select') }}</option>
                        @foreach($municipalities as $municipality)
                          <option value="{{ $municipality->id }}" {{ old('municipality_id', $client->municipality_id) == $municipality->id ? 'selected' : '' }}>{{ $municipality->name }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="form-group mb-0">
                      <label>{{ __('locale.Sector') }}</label>
                      <select name="parish_id" id="parish_id" class="form-control" required>
                        <option value="">{{ __('locale.Select') }}</option>
                        @foreach($parishes as $parish)
                          <option value="{{ $parish->id }}" {{ old('parish_id', $client->parish_id) == $parish->id ? 'selected' : '' }}>{{ $parish->name }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card h-100">
                  <div class="card-header">
                    <h5 class="mb-0">{{ __('locale.Address') }}</h5>
                  </div>
                  <div class="card-body">
                    <div class="form-group">
                      <label>{{ __('locale.Address') }}</label>
                      <textarea name="direccion" class="form-control" rows="4" required>{{ old('direccion', $client->direccion) }}</textarea>
                    </div>

                    <div class="form-group mb-0">
                      <label>{{ __('locale.Reference Point') }}</label>
                      <textarea name="referencia" class="form-control" rows="4">{{ old('referencia', $client->referencia) }}</textarea>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
              <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary mr-2">{{ __('locale.Back') }}</a>
              <button type="submit" id="btn-update-client" class="btn btn-primary">{{ __('locale.Update') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@section('page-script')
<script>
  $(function(){
    var $type = $('#client_type');
    var $legalFields = $('#legal-fields');
    var $estado = $('#estado_id');
    var $municipio = $('#municipality_id');
    var $parish = $('#parish_id');
    var $alert = $('#client-edit-alert');
    var $submitBtn = $('#btn-update-client');
    var btnText = $submitBtn.text();

    function toggleLegalFields() {
      if ($type.val() === '2') {
        $legalFields.show();
      } else {
        $legalFields.hide();
      }
    }

    function loadMunicipalities(stateId, selectedId) {
      $municipio.html('<option value="">{{ __('locale.Loading') }}...</option>');
      $parish.html('<option value="">{{ __('locale.Select') }}</option>');

      if (!stateId) {
        $municipio.html('<option value="">{{ __('locale.Select') }}</option>');
        return;
      }

      $.get('{{ url('panel/estados') }}/' + stateId + '/municipios')
        .done(function(res){
          $municipio.html('<option value="">{{ __('locale.Select') }}</option>');
          (res || []).forEach(function(item){
            var selected = (selectedId && String(selectedId) === String(item.id)) ? 'selected' : '';
            $municipio.append('<option value="'+item.id+'" '+selected+'>'+ (item.name || item.nombre || '') +'</option>');
          });
        })
        .fail(function(){
          $municipio.html('<option value="">{{ __('locale.Select') }}</option>');
        });
    }

    function loadParishes(municipalityId, selectedId) {
      $parish.html('<option value="">{{ __('locale.Loading') }}...</option>');

      if (!municipalityId) {
        $parish.html('<option value="">{{ __('locale.Select') }}</option>');
        return;
      }

      $.get('{{ url('panel/clientes/municipios') }}/' + municipalityId + '/sectores')
        .done(function(res){
          $parish.html('<option value="">{{ __('locale.Select') }}</option>');
          (res || []).forEach(function(item){
            var selected = (selectedId && String(selectedId) === String(item.id)) ? 'selected' : '';
            $parish.append('<option value="'+item.id+'" '+selected+'>'+ (item.name || '') +'</option>');
          });
        })
        .fail(function(){
          $parish.html('<option value="">{{ __('locale.Select') }}</option>');
        });
    }

    $type.on('change', toggleLegalFields);

    $estado.on('change', function(){
      loadMunicipalities($(this).val(), null);
    });

    $municipio.on('change', function(){
      loadParishes($(this).val(), null);
    });

    $('#clientEditForm').on('submit', function(e){
      e.preventDefault();
      $alert.addClass('d-none').text('');
      $submitBtn.prop('disabled', true).text('{{ __('locale.Loading') }}...');

      $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json'
      }).done(function(res){
        if (res && res.result) {
          window.location.href = '{{ route('clients.index') }}';
          return;
        }

        $alert.removeClass('d-none').text('{{ __('locale.An error occurred') }}');
      }).fail(function(xhr){
        var message = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : '{{ __('locale.An error occurred') }}';
        $alert.removeClass('d-none').text(message);
      }).always(function(){
        $submitBtn.prop('disabled', false).text(btnText);
      });
    });

    toggleLegalFields();
    loadMunicipalities('{{ old('estado_id', $client->estado_id) }}', '{{ old('municipality_id', $client->municipality_id) }}');
    loadParishes('{{ old('municipality_id', $client->municipality_id) }}', '{{ old('parish_id', $client->parish_id) }}');
  });
</script>
@endsection
