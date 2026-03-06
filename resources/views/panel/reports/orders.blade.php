@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Orders Report'))

@section('content')
<div class="container">
  <h2 class="text-center mb-3">{{ __('locale.Orders Report') }}</h2>

  <form id="filterForm" class="row mb-3 align-items-end">
    <div class="col-md-5">
      <label for="init">{{ __('locale.From') }}</label>
      <input type="date" id="init" name="init" class="form-control" value="{{ now()->format('Y-m-d') }}">
    </div>
    <div class="col-md-5">
      <label for="end">{{ __('locale.To') }}</label>
      <input type="date" id="end" name="end" class="form-control" value="{{ now()->format('Y-m-d') }}">
    </div>
    <div class="col-md-2 text-right">
      <button type="button" id="btnFilter" class="btn btn-danger mt-1">{{ __('locale.Filter') }}</button>
    </div>
  </form>

  <div class="card mb-3">
    <div class="card-body">
      <canvas id="ordersChart" height="180"></canvas>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="mb-3">
        <button id="exportCsv" class="btn btn-danger mr-2">{{ __('locale.Export') }}</button>
        <button id="exportPdf" class="btn btn-danger">{{ __('locale.Export PDF') }}</button>
      </div>
      <div class="table-responsive">
        <table class="table" id="reportTable">
          <thead>
            <tr>
              <th>{{ __('locale.Date') }}</th>
              <th>{{ __('locale.# Orders') }}</th>
              <th>{{ __('locale.Pending') }}</th>
              <th>{{ __('locale.Approved') }}</th>
              <th>{{ __('locale.Completed') }}</th>
            </tr>
          </thead>
          <tbody id="reportBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let ordersChart = null;

function renderChart(labels, values){
  const ctx = document.getElementById('ordersChart').getContext('2d');
  if(ordersChart) ordersChart.destroy();
  ordersChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: '{{ __('locale.Orders') }}',
        data: values,
        backgroundColor: 'rgba(234,84,85,0.65)',
        borderColor: 'rgba(234,84,85,1)',
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } }
      },
      plugins: { legend: { display: true } }
    }
  });
}

function populateTable(rows){
  const tbody = document.getElementById('reportBody');
  tbody.innerHTML = '';
  rows.forEach(r => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${r.label ?? ''}</td>
      <td>${Number(r.orders ?? 0)}</td>
      <td>${Number(r.pending ?? 0)}</td>
      <td>${Number(r.processing ?? 0)}</td>
      <td>${Number(r.completed ?? 0)}</td>
    `;
    tbody.appendChild(tr);
  });
}

async function fetchOrdersData(init, end){
  const base = `{{ url('panel/reports/orders/data') }}`;
  const url = `${base}/${encodeURIComponent(init)}/${encodeURIComponent(end)}`;
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

  const init = document.getElementById('init').value;
  const end = document.getElementById('end').value;
  if(!init || !end){
    alert('{{ __('locale.Select date range') }}');
    return;
  }

  try {
    setLoading(true);
    const data = await fetchOrdersData(init, end);
    const labels = data.map(r => String(r.label ?? ''));
    const values = data.map(r => Number(r.orders ?? 0));
    renderChart(labels, values);
    populateTable(data);
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

  if(!document.getElementById('end').value) document.getElementById('end').value = localIso;
  if(!document.getElementById('init').value) document.getElementById('init').value = localIso;

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
    link.download = 'orders_report.csv';
    link.click();
  });

  document.getElementById('exportPdf').addEventListener('click', function(){
    window.print();
  });

  doFilter();
});
</script>
@endsection

@endsection
