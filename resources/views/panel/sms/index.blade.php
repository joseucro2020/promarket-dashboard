@extends('layouts/contentLayoutMaster')

@section('title', __('SMS Sending'))

@section('content')
<section>
  <div class="row">
    <div class="col-md-4">
      <h5>Lista Contactos</h5>
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
            <label>Tipos Envíos</label>
            <select name="tipo" id="tipo" class="form-control">
              <option value="0">Todos</option>
              <option value="1">Individual</option>
            </select>
          </div>
          <div class="form-group col-md-6">
            <label>Número de teléfono</label>
            <input type="text" name="numerocell" id="numerocell" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label>Message</label>
          <textarea name="description" id="description" class="form-control" rows="5" maxlength="120"></textarea>
          <small class="form-text text-muted">Límite de caracteres 120</small>
        </div>
        <div class="text-center mt-3">
          <div>Cantidad de SMS disponibles: <span id="sms-available">{{ $smsdisponible }}</span></div>
          <button type="submit" class="btn btn-primary mt-2">Enviar</button>
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
      alert('Resultado: ' + JSON.stringify(res));
    }).catch(err => {
      console.error(err);
      alert('Error enviando SMS');
    });
  });
});
</script>
@endsection
