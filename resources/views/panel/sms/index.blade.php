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
            <input type="text" name="numerocell" id="numerocell" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label>{{ __('locale.Message') }}</label>
          <textarea name="description" id="description" class="form-control" rows="5" maxlength="120"></textarea>
          <small class="form-text text-muted">{{ __('locale.Límite de caracteres 120') }}</small>
        </div>
        <div class="text-center mt-3">
          <div>{{ __('locale.Cantidad de SMS disponibles') }}: <span id="sms-available">{{ $smsdisponible }}</span></div>
          <button type="submit" class="btn btn-primary mt-2">{{ __('locale.Enviar') }}</button>
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
    const numerocell = document.getElementById('numerocell').value;
    const description = document.getElementById('description').value;

    const contacts = [];
    document.querySelectorAll('.contact-checkbox:checked').forEach(cb => {
      contacts.push({ id: cb.dataset.id, nom: cb.dataset.nom, cel: cb.dataset.cel });
    });

    const data = {
      _token: document.querySelector('input[name="_token"]').value,
      tipo: tipo,
      numerocell: numerocell,
      description: description,
      contact: contacts
    };

    fetch('{{ route('sms.enviar') }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data._token },
      body: JSON.stringify(data)
    }).then(r => r.json()).then(res => {
      alert('{{ __('locale.Resultado') }}: ' + JSON.stringify(res));
    }).catch(err => {
      console.error(err);
      alert('{{ __('locale.Error enviando SMS') }}');
    });
  });
});
</script>
@endsection
