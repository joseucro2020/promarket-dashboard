@extends('layouts/contentLayoutMaster')

@section('title', __('locale.SMS Sending'))

@section('content')
<section>
  <div class="row">
    <div class="col-md-4">
      <h5>{{ __('locale.Lista Contactos') }}</h5>
      <div class="list-group">
        @foreach($contact as $c)
          <label class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <div class="font-weight-bold">{{ $c->nom }}</div>
              <div>{{ $c->cel }}</div>
            </div>
            <input type="checkbox" class="contact-checkbox" data-id="{{ $c->id }}" data-nom="{{ $c->nom }}" data-cel="{{ $c->cel }}">
          </label>
        @endforeach
      </div>
    </div>
    <div class="col-md-8">
      <form id="sms-form">
        @csrf
        <div class="form-row">
          <div class="form-group col-md-6">
            <label>{{ __('locale.Tipos Envíos') }}</label>
            <select name="tipo" id="tipo" class="form-control">
              <option value="0">{{ __('locale.Todos') }}</option>
              <option value="1">{{ __('locale.Individual') }}</option>
            </select>
          </div>
          <div class="form-group col-md-6">
            <label>{{ __('locale.Número de teléfono') }}</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <select id="country_code" class="custom-select" style="width:100px;">
                  <option value="58" selected>+58</option>
                  <option value="57">+57</option>
                  <option value="1">+1</option>
                </select>
              </div>
              <input type="text" name="numerocell" id="numerocell" class="form-control" placeholder="82444470584">
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Proveedor</label>
            <select name="provider" id="provider" class="form-control">
              <option value="centauro">Centauro SMS (SMS)</option>
              <option value="wasender">Wasender (WhatsApp)</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>{{ __('locale.Message') }}</label>
          <textarea name="description" id="description" class="form-control" rows="5" maxlength="120"></textarea>
          <small class="form-text text-muted">{{ __('locale.Límite de caracteres 120') }}</small>
        </div>
        <div class="text-center mt-3">
          <div>{{ __('locale.Cantidad de SMS disponibles') }}: <span id="sms-available">{{ $smsdisponible }}</span></div>
          <div id="sms-result" style="display:none;" class="mt-2"></div>
          <button type="submit" id="sms-send-btn" class="btn btn-primary mt-2">{{ __('locale.Enviar') }}</button>
        </div>
      </form>
    </div>
  </div>
</section>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const form = document.getElementById('sms-form');
    form.addEventListener('submit', function(e){
    e.preventDefault();
    const tipo = document.getElementById('tipo').value;
      const countryCode = document.getElementById('country_code').value;
      let numerocell = document.getElementById('numerocell').value;
      const provider = document.getElementById('provider').value;
      // Normalize number: remove non-digits and leading zeros
      numerocell = numerocell.replace(/[^0-9]/g, '');
      if (numerocell.startsWith('0')) {
        numerocell = numerocell.replace(/^0+/, '');
      }
      // Prepend country code (without plus)
      const numerocellFull = countryCode + numerocell;
    const description = document.getElementById('description').value;

    const contacts = [];
    document.querySelectorAll('.contact-checkbox:checked').forEach(cb => {
      contacts.push({ id: cb.dataset.id, nom: cb.dataset.nom, cel: cb.dataset.cel });
    });

    const data = {
      _token: document.querySelector('input[name="_token"]').value,
      tipo: tipo,
      provider: provider,
      numerocell: numerocellFull,
      description: description,
      contact: contacts
    };
    const resultEl = document.getElementById('sms-result');
    const sendBtn = document.getElementById('sms-send-btn');
    const sendBtnOriginal = sendBtn.innerHTML;
    // Clear previous
    resultEl.style.display = 'none';
    resultEl.className = '';
    resultEl.innerHTML = '';

    // show loading state
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> ' + '{{ __('locale.Enviando...') }}';

    fetch('{{ route('sms.enviar') }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data._token },
      body: JSON.stringify(data)
    }).then(r => r.json()).then(res => {
      // Determine success
      const success = !!(res && ((res.result && res.result.success) || (res.sms && res.sms.response && res.sms.response.sms_enviado && res.sms.response.sms_enviado > 0) || (res.result && res.result.status == 'ok')));

      // Show success or error inline
      if (success) {
        resultEl.className = 'alert alert-success';
        resultEl.innerHTML = '{{ __('locale.Resultado') }}: ' + (res.result && res.result.message ? res.result.message : 'Enviado correctamente');
        // Clear form on success
        document.getElementById('description').value = '';
        document.getElementById('numerocell').value = '';
        document.getElementById('country_code').value = '58';
        document.getElementById('tipo').value = '0';
        document.getElementById('provider').value = 'centauro';
        // Uncheck contacts
        document.querySelectorAll('.contact-checkbox').forEach(cb => cb.checked = false);
      } else if (res && res.error) {
        resultEl.className = 'alert alert-danger';
        resultEl.innerHTML = '{{ __('locale.Error enviando SMS') }}: ' + res.error;
      } else {
        resultEl.className = 'alert alert-success';
        resultEl.innerHTML = '{{ __('locale.Resultado') }}: ' + JSON.stringify(res);
      }
      resultEl.style.display = 'block';
      // restore button
      sendBtn.disabled = false;
      sendBtn.innerHTML = sendBtnOriginal;
    }).catch(err => {
      console.error(err);
      resultEl.className = 'alert alert-danger';
      resultEl.innerHTML = '{{ __('locale.Error enviando SMS') }}';
      resultEl.style.display = 'block';
      // restore button
      sendBtn.disabled = false;
      sendBtn.innerHTML = sendBtnOriginal;
    });
    // restore button on success path is handled above
  });
});
</script>
@endsection
