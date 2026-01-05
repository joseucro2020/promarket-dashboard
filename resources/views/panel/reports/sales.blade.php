@extends('layouts/contentLayoutMaster')

@section('title', __('Sales Report'))

@section('content')
<div class="container">
  <h2 class="text-center mb-3">{{ __('Sales Report') }}</h2>

  <form id="filterForm" class="row mb-3 align-items-end">
    <div class="col-md-3">
      <label for="type">{{ __('Type') }}</label>
      <select id="type" class="form-control">
        <option value="daily">{{ __('Daily') }}</option>
        <option value="weekly">{{ __('Weekly') }}</option>
        <option value="monthly">{{ __('Monthly') }}</option>
      </select>
    </div>
    <div class="col-md-3">
      <label for="init">{{ __('From') }}</label>
      <input type="date" id="init" name="init" class="form-control">
    </div>
    <div class="col-md-3">
      <label for="end">{{ __('To') }}</label>
      <input type="date" id="end" name="end" class="form-control">
    </div>
    <div class="col-md-3 text-right">
      <button type="button" id="btnFilter" class="btn btn-outline-danger mt-1">{{ __('Filter') }}</button>
    </div>
  </form>

  <div class="card mb-3">
    <div class="card-body">
      <canvas id="salesChart" height="180"></canvas>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="mb-3">
        <button id="exportCsv" class="btn btn-danger mr-2">{{ __('Export') }}</button>
        <button id="exportPdf" class="btn btn-outline-danger">{{ __('Export PDF') }}</button>
      </div>
      <div class="table-responsive">
        <table class="table table-striped" id="reportTable">
          <thead>
            <tr>
              <th>{{ __('Date') }}</th>
              <th>{{ __('Gross Sales $') }}</th>
              <th>{{ __('Gross Sales bs') }}</th>
              <th>{{ __('Gross Profit') }}</th>
              <th>{{ __('% Profit') }}</th>
              <th>{{ __('Net Sales $') }}</th>
              <th>{{ __('Net Sales bs') }}</th>
              <th>{{ __('Net Profit') }}</th>
            </tr>
          </thead>
          <tbody id="reportBody">
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Helper: format numbers
function fmt(num, currency='USD'){
  const n = Number(num || 0);
  if(currency === 'USD') return new Intl.NumberFormat('es-VE', { style: 'currency', currency: 'USD' }).format(n);
  // Bs: usamos VES como ISO (VEF está deprecado)
  return new Intl.NumberFormat('es-VE', { style: 'currency', currency: 'VES' }).format(n);
}

let salesChart = null;

function renderChart(labels, values){
  const ctx = document.getElementById('salesChart').getContext('2d');
  if(salesChart) salesChart.destroy();
  salesChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: '{{ __("Sales") }}',
        data: values,
        fill: true,
        borderColor: '#ff7043',
        backgroundColor: 'rgba(255,112,67,0.25)',
        pointRadius: 3,
        tension: 0.3
      }]
    },
    options: {
      scales: {
        x: { ticks: { autoSkip: true, maxRotation: 45, minRotation: 45 } },
        y: { beginAtZero: true }
      },
      plugins: { legend: { display: false } }
    }
  });
}

function populateTable(rows){
  const tbody = document.getElementById('reportBody');
  tbody.innerHTML = '';
  rows.forEach(r=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${r.label}</td>
      <td>${fmt(r.purchases,'USD')}</td>
      <td>${fmt(r.purchases_bs,'BS')}</td>
      <td>${fmt(r.utility,'USD')}</td>
      <td>${r.utility_percentage}%</td>
      <td>${fmt(r.purchases_neta,'USD')}</td>
      <td>${fmt(r.purchases_neta_bs,'BS')}</td>
      <td>${fmt(r.utility_neta,'USD')}</td>
    `;
    tbody.appendChild(tr);
  });
}

async function fetchSalesData(type, init, end){
  const base = `{{ url('panel/reports/sales/data') }}`;
  const url = `${base}/${encodeURIComponent(type)}/${encodeURIComponent(init)}/${encodeURIComponent(end)}`;
  const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
  if(!res.ok){
    const txt = await res.text();
    throw new Error(txt || `HTTP ${res.status}`);
  }
  return await res.json();
}

async function doFilter(){
  const type = document.getElementById('type').value;
  const init = document.getElementById('init').value;
  const end = document.getElementById('end').value;
  if(!init || !end){
    alert('{{ __("Select date range") }}');
    return;
  }

  try {
    const data = await fetchSalesData(type, init, end);
    const labels = data.map(r => String(r.label));
    const values = data.map(r => Number(r.purchases || 0));
    renderChart(labels, values);
    populateTable(data);
  } catch (e) {
    console.error(e);
    alert('Error cargando reporte. Revisa consola / logs.');
  }
}

document.addEventListener('DOMContentLoaded', function(){
  // set default dates: last 30 days
  const today = new Date();
  const prior = new Date(); prior.setDate(today.getDate() - 30);
  document.getElementById('end').value = today.toISOString().slice(0,10);
  document.getElementById('init').value = prior.toISOString().slice(0,10);
  document.getElementById('btnFilter').addEventListener('click', doFilter);
  document.getElementById('exportCsv').addEventListener('click', function(){
    // export table to CSV
    const rows = Array.from(document.querySelectorAll('#reportTable tr'));
    const csv = rows.map(r=>Array.from(r.querySelectorAll('th,td')).map(cell=>`"${cell.innerText.replace(/"/g,'""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'sales_report.csv';
    link.click();
  });
  document.getElementById('exportPdf').addEventListener('click', function(){
    window.print();
  });

  // initial render
  doFilter();
});
</script>
@endpush

@endsection
