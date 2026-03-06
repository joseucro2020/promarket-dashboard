@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Sales Report'))

@section('content')
<div class="container">
  <h2 class="text-center mb-3">{{ __('locale.Sales Report') }}</h2>

  <form id="filterForm" class="row mb-3 align-items-end">
    <div class="col-md-3">
      <label for="type">{{ __('locale.Type') }}</label>
      <select id="type" class="form-control">
        <option value="daily">{{ __('locale.Daily') }}</option>
        <option value="weekly">{{ __('locale.Weekly') }}</option>
        <option value="monthly">{{ __('locale.Monthly') }}</option>
      </select>
    </div>
    <div class="col-md-3">
      <label for="init">{{ __('locale.From') }}</label>
      <input type="date" id="init" name="init" class="form-control" value="{{ now()->format('Y-m-d') }}">
    </div>
    <div class="col-md-3">
      <label for="end">{{ __('locale.To') }}</label>
      <input type="date" id="end" name="end" class="form-control" value="{{ now()->format('Y-m-d') }}">
    </div>
    <div class="col-md-3 text-right">
      <button type="button" id="btnFilter" class="btn btn-outline-danger mt-1">{{ __('locale.Filter') }}</button>
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
        <button id="exportCsv" class="btn btn-danger mr-2">{{ __('locale.Export') }}</button>
        <button id="exportPdf" class="btn btn-outline-danger">{{ __('locale.Export PDF') }}</button>
      </div>
      <div class="table-responsive">
        <table class="table table-striped" id="reportTable">
          <thead>
            <tr>
              <th>{{ __('locale.Date') }}</th>
              <th>{{ __('locale.Gross Sales $') }}</th>
              <th>{{ __('locale.Gross Sales bs') }}</th>
              <th>{{ __('locale.Gross Profit') }}</th>
              <th>{{ __('locale.% Profit') }}</th>
              <th>{{ __('locale.Net Sales $') }}</th>
              <th>{{ __('locale.Net Sales bs') }}</th>
              <th>{{ __('locale.Net Profit') }}</th>
            </tr>
          </thead>
          <tbody id="reportBody">
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@section('page-script')
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
        label: '{{ __('locale.Sales') }}',
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

  const type = document.getElementById('type').value;
  const init = document.getElementById('init').value;
  const end = document.getElementById('end').value;
  if(!init || !end){
  alert('{{ __('locale.Select date range') }}');
    return;
  }

  try {
    setLoading(true);
    const data = await fetchSalesData(type, init, end);
    const labels = data.map(r => String(r.label));
    const values = data.map(r => Number(r.purchases || 0));
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
  // set default dates: today (use local date to avoid UTC shift)
  const today = new Date();
  const yyyy = today.getFullYear();
  const mm = String(today.getMonth() + 1).padStart(2, '0');
  const dd = String(today.getDate()).padStart(2, '0');
  const localIso = `${yyyy}-${mm}-${dd}`;
  if(!document.getElementById('end').value) document.getElementById('end').value = localIso;
  if(!document.getElementById('init').value) document.getElementById('init').value = localIso;
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
@endsection

@endsection
