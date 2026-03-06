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
            <input type="date" id="from" class="form-control" value="{{ now()->format('Y-m-d') }}" />
          </div>
          <div class="col-md-6">
            <label for="to">{{ __('locale.To') }}</label>
            <input type="date" id="to" class="form-control" value="{{ now()->format('Y-m-d') }}" />
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
            <input type="date" id="fromcom" class="form-control" value="{{ now()->format('Y-m-d') }}" />
          </div>
          <div class="col-md-6">
            <label for="tocom">{{ __('locale.To') }}</label>
            <input type="date" id="tocom" class="form-control" value="{{ now()->format('Y-m-d') }}" />
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

@section('page-script')
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
  const btnFilter = document.getElementById('btnFilter');
  const setLoading = (state) => {
    if (!btnFilter) return;
    if (state) {
      btnFilter.disabled = true;
      btnFilter.setAttribute('aria-busy', 'true');
      btnFilter.innerHTML = `<span class="spinner-border spinner-border-sm mr-50" role="status" aria-hidden="true"></span>{{ __('locale.Loading...') }}`;
      return;
    }
    btnFilter.disabled = false;
    btnFilter.removeAttribute('aria-busy');
    btnFilter.textContent = `{{ __('locale.Filter') }}`;
  };

  const from = document.getElementById('from').value;
  const to = document.getElementById('to').value;
  const fromcom = document.getElementById('fromcom').value;
  const tocom = document.getElementById('tocom').value;

  if(!from || !to || !fromcom || !tocom){
    alert('{{ __("Select date range") }}');
    return;
  }

  try {
    setLoading(true);
    const payload = await fetchProfitShare({ from, to, fromcom, tocom });
    renderTable('tbodyMain', payload?.lucro ?? []);
    renderTable('tbodyCompare', payload?.comparativo ?? []);
  } catch (e) {
    console.error(e);
    alert('Error cargando reporte. Revisa consola / logs.');
  } finally {
    setLoading(false);
  }
}

document.addEventListener('DOMContentLoaded', function(){
  const today = new Date();
  const yyyy = today.getFullYear();
  const mm = String(today.getMonth() + 1).padStart(2, '0');
  const dd = String(today.getDate()).padStart(2, '0');
  const localIso = `${yyyy}-${mm}-${dd}`;

  if(!document.getElementById('to').value) document.getElementById('to').value = localIso;
  if(!document.getElementById('from').value) document.getElementById('from').value = localIso;
  if(!document.getElementById('tocom').value) document.getElementById('tocom').value = localIso;
  if(!document.getElementById('fromcom').value) document.getElementById('fromcom').value = localIso;

  document.getElementById('btnFilter').addEventListener('click', doFilter);
  doFilter();
});
</script>
@endsection

@endsection
