@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Profit & Share Report'))

@section('content')
<div class="container">
  <h2 class="text-center mb-3">{{ __('locale.Profit & Share Report') }}</h2>

  <div class="row">
    <div class="col-md-6">
      <fieldset class="border p-2 h-100">
        <legend class="w-auto px-2 mb-0" style="font-size: 0.9rem;">{{ __('locale.Date Period') }}</legend>
        <div class="row mt-1">
          <div class="col-md-6">
            <label for="from">{{ __('locale.From') }}</label>
            <input type="date" id="from" class="form-control" />
          </div>
          <div class="col-md-6">
            <label for="to">{{ __('locale.To') }}</label>
            <input type="date" id="to" class="form-control" />
          </div>
        </div>
      </fieldset>
    </div>

    <div class="col-md-6">
      <fieldset class="border p-2 h-100">
        <legend class="w-auto px-2 mb-0" style="font-size: 0.9rem;">{{ __('locale.Comparative Date Period') }}</legend>
        <div class="row mt-1">
          <div class="col-md-6">
            <label for="fromcom">{{ __('locale.From') }}</label>
            <input type="date" id="fromcom" class="form-control" />
          </div>
          <div class="col-md-6">
            <label for="tocom">{{ __('locale.To') }}</label>
            <input type="date" id="tocom" class="form-control" />
          </div>
        </div>
      </fieldset>
    </div>
  </div>

  <div class="mt-2">
    <button type="button" id="btnFilter" class="btn btn-danger">{{ __('locale.Filter') }}</button>
  </div>

  <div class="row mt-2">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table" id="tableMain">
              <thead>
                <tr>
                  <th>{{ __('locale.Type') }}</th>
                  <th>{{ __('locale.Units Sold') }}</th>
                  <th>{{ __('locale.Participation') }}</th>
                  <th>{{ __('locale.Amount Sold') }}</th>
                  <th>{{ __('locale.Profit') }}</th>
                </tr>
              </thead>
              <tbody id="tbodyMain">
                <tr><td colspan="5">{{ __('locale.No records found.') }}</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table" id="tableCompare">
              <thead>
                <tr>
                  <th>{{ __('Type') }}</th>
                  <th>{{ __('Units Sold') }}</th>
                  <th>{{ __('Participation') }}</th>
                  <th>{{ __('Amount Sold') }}</th>
                  <th>{{ __('Profit') }}</th>
                </tr>
              </thead>
              <tbody id="tbodyCompare">
                <tr><td colspan="5">{{ __('No records found.') }}</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function num(value){
  const n = Number(value);
  return Number.isFinite(n) ? n : 0;
}

function money(value){
  const n = num(value);
  return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function renderTable(tbodyId, rows){
  const tbody = document.getElementById(tbodyId);
  tbody.innerHTML = '';

  if(!Array.isArray(rows) || rows.length === 0){
    const tr = document.createElement('tr');
    tr.innerHTML = `<td colspan="5">{{ __('No records found.') }}</td>`;
    tbody.appendChild(tr);
    return;
  }

  const totalAmount = rows.reduce((acc, r) => acc + num(r.amount_sold ?? r.monto_vendido ?? r.monto ?? r.montobs), 0);

  rows.forEach(r => {
    const amount = num(r.amount_sold ?? r.monto_vendido ?? r.monto ?? r.montobs);
    const participation = totalAmount > 0 ? (amount / totalAmount) * 100 : 0;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${String(r.type ?? r.tipo ?? r.id_father ?? '')}</td>
      <td>${num(r.units_sold ?? r.cantidad)}</td>
      <td>${participation.toFixed(2)}%</td>
      <td>${money(amount)}</td>
      <td>${money(r.profit ?? r.lucro)}</td>
    `;
    tbody.appendChild(tr);
  });
}

async function fetchProfitShare(params){
  const base = `{{ url('panel/reports/profit-share/data') }}`;
  const qs = new URLSearchParams(params);
  const url = `${base}?${qs.toString()}`;

  const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
  if(!res.ok){
    const txt = await res.text();
    throw new Error(txt || `HTTP ${res.status}`);
  }
  return await res.json();
}

async function doFilter(){
  const from = document.getElementById('from').value;
  const to = document.getElementById('to').value;
  const fromcom = document.getElementById('fromcom').value;
  const tocom = document.getElementById('tocom').value;

  if(!from || !to || !fromcom || !tocom){
    alert('{{ __("Select date range") }}');
    return;
  }

  try {
    const payload = await fetchProfitShare({ from, to, fromcom, tocom });
    renderTable('tbodyMain', payload?.lucro ?? []);
    renderTable('tbodyCompare', payload?.comparativo ?? []);
  } catch (e) {
    console.error(e);
    alert('Error cargando reporte. Revisa consola / logs.');
  }
}

document.addEventListener('DOMContentLoaded', function(){
  const today = new Date();
  const prior = new Date();
  prior.setDate(today.getDate() - 30);

  // Periodo principal: últimos 30 días
  document.getElementById('to').value = today.toISOString().slice(0,10);
  document.getElementById('from').value = prior.toISOString().slice(0,10);

  // Periodo comparativo: 30 días anteriores
  const prior2 = new Date(prior);
  prior2.setDate(prior.getDate() - 30);
  document.getElementById('tocom').value = prior.toISOString().slice(0,10);
  document.getElementById('fromcom').value = prior2.toISOString().slice(0,10);

  document.getElementById('btnFilter').addEventListener('click', doFilter);
});
</script>
@endpush

@endsection
