@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Waste History Report'))

@section('content')
<div class="container">
  <h2 class="text-center mb-3">{{ __('locale.Waste History Report') }}</h2>

  <form id="filterForm" class="row mb-3 align-items-end">
    <div class="col-md-4">
      <label for="init">{{ __('locale.From') }}</label>
      <input type="date" id="init" name="init" class="form-control">
    </div>

    <div class="col-md-4">
      <label for="end">{{ __('locale.To') }}</label>
      <input type="date" id="end" name="end" class="form-control">
    </div>

    <div class="col-md-4">
      <label for="adjustmentType">{{ __('locale.Adjustment Type') }}</label>
      <select id="adjustmentType" class="form-control">
        <option value="">{{ __('locale.All') }}</option>
      </select>

      <div class="text-right">
        <button type="button" id="btnFilter" class="btn btn-danger mt-1">{{ __('locale.Filter') }}</button>
      </div>
    </div>
  </form>

  <div class="card">
    <div class="card-body">
      <div class="mb-2">
        <strong>{{ __('locale.Total records') }}</strong> <span id="totalRecords">0</span>
      </div>

      <div class="mb-3">
        <button id="exportCsv" class="btn btn-danger mr-2">{{ __('locale.Export') }}</button>
        <button id="exportPdf" class="btn btn-danger">{{ __('locale.Export PDF') }}</button>
      </div>

      <div class="table-responsive">
        <table class="table" id="reportTable">
          <thead>
            <tr>
              <th>{{ __('locale.Date') }}</th>
              <th>{{ __('locale.Order') }}</th>
              <th>{{ __('locale.Product') }}</th>
              <th>{{ __('locale.Cost') }}</th>
              <th>{{ __('locale.Adjustment') }}</th>
              <th>{{ __('locale.Note') }}</th>
              <th>{{ __('locale.User') }}</th>
            </tr>
          </thead>
          <tbody id="reportBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function formatDate(isoString){
  if(!isoString) return '';
  const d = new Date(isoString);
  if(Number.isNaN(d.getTime())) return String(isoString);
  return d.toISOString().slice(0,10);
}

function textOrEmpty(value){
  return (value === null || value === undefined) ? '' : String(value);
}

function populateTable(rows){
  const tbody = document.getElementById('reportBody');
  tbody.innerHTML = '';

  rows.forEach(r => {
    const date = formatDate(r.created_at);
    const order = r.purchase?.id ?? r.purchase_id ?? '';
    const product = r.presentation?.product?.name ?? r.presentation?.presentation ?? '';
    const cost = r.presentation?.cost ?? r.cost ?? '';
    const adjustment = r.fit_type ?? '';
    const note = r.note ?? r.observation ?? r.description ?? '';
    const user = r.user?.name ?? r.user?.email ?? '';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${textOrEmpty(date)}</td>
      <td>${textOrEmpty(order)}</td>
      <td>${textOrEmpty(product)}</td>
      <td>${textOrEmpty(cost)}</td>
      <td>${textOrEmpty(adjustment)}</td>
      <td>${textOrEmpty(note)}</td>
      <td>${textOrEmpty(user)}</td>
    `;
    tbody.appendChild(tr);
  });
}

function hydrateAdjustmentOptions(rows){
  const select = document.getElementById('adjustmentType');
  if(!select) return;

  // Si ya hay opciones adicionales, no tocar.
  if(select.options.length > 1) return;

  const unique = new Set();
  rows.forEach(r => {
    if(r && r.fit_type !== null && r.fit_type !== undefined && String(r.fit_type).trim() !== '') {
      unique.add(String(r.fit_type));
    }
  });

  Array.from(unique).sort().forEach(v => {
    const opt = document.createElement('option');
    opt.value = v;
    opt.textContent = v;
    select.appendChild(opt);
  });
}

async function fetchWasteHistory(init, end, ajuste){
  const base = `{{ url('panel/reports/waste-history/data') }}`;
  const params = new URLSearchParams();
  if(init) params.set('from', init);
  if(end) params.set('to', end);
  if(ajuste) params.set('ajuste', ajuste);

  const url = `${base}?${params.toString()}`;
  const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
  if(!res.ok){
    const txt = await res.text();
    throw new Error(txt || `HTTP ${res.status}`);
  }
  return await res.json();
}

async function doFilter(){
  const init = document.getElementById('init').value;
  const end = document.getElementById('end').value;
  const ajuste = document.getElementById('adjustmentType').value;

  if(!init || !end){
    alert('{{ __('locale.Select date range') }}');
    return;
  }

  try {
    const payload = await fetchWasteHistory(init, end, ajuste);
    const reps = payload?.reps;
    const rows = Array.isArray(reps?.data) ? reps.data : [];

    document.getElementById('totalRecords').innerText = Number(reps?.total ?? rows.length);
    hydrateAdjustmentOptions(rows);
    populateTable(rows);
  } catch (e) {
    console.error(e);
    alert('Error cargando reporte. Revisa consola / logs.');
  }
}

document.addEventListener('DOMContentLoaded', function(){
  const today = new Date();
  const prior = new Date();
  prior.setDate(today.getDate() - 30);

  document.getElementById('end').value = today.toISOString().slice(0,10);
  document.getElementById('init').value = prior.toISOString().slice(0,10);

  document.getElementById('btnFilter').addEventListener('click', doFilter);

  document.getElementById('exportCsv').addEventListener('click', function(){
    const rows = Array.from(document.querySelectorAll('#reportTable tr'));
    const csv = rows
      .map(r => Array.from(r.querySelectorAll('th,td'))
        .map(cell => `"${cell.innerText.replace(/"/g,'""')}"`)
        .join(','))
      .join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'waste_history_report.csv';
    link.click();
  });

  document.getElementById('exportPdf').addEventListener('click', function(){
    window.print();
  });

  doFilter();
});
</script>
@endpush

@endsection
