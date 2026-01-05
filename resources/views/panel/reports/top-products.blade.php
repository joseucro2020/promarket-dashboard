@extends('layouts/contentLayoutMaster')

@section('title', __('Top Products Report'))

@section('content')
<div class="container">
  <h2 class="text-center mb-3">{{ __('Top Products Report') }}</h2>

  <form id="filterForm" class="row mb-3 align-items-end">
    <div class="col-md-3">
      <label for="init">{{ __('From') }}</label>
      <input type="date" id="init" name="init" class="form-control">
    </div>

    <div class="col-md-3">
      <label for="end">{{ __('To') }}</label>
      <input type="date" id="end" name="end" class="form-control">
    </div>

    <div class="col-md-4">
      <label for="product_id">{{ __('Products') }}</label>
      <select id="product_id" class="form-control">
        <option value="">{{ __('Select Products') }}</option>
        @foreach(($productsList ?? []) as $p)
          <option value="{{ $p->id }}">{{ $p->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-2 text-right">
      <button type="button" id="btnFilter" class="btn btn-danger mt-1">{{ __('Filter') }}</button>
    </div>
  </form>

  <div class="card mb-3">
    <div class="card-body">
      <canvas id="topProductsChart" height="180"></canvas>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="mb-3">
        <button id="exportCsv" class="btn btn-danger mr-2">{{ __('Export') }}</button>
        <button id="exportPdf" class="btn btn-danger">{{ __('Export PDF') }}</button>
      </div>

      <div class="table-responsive">
        <table class="table" id="reportTable">
          <thead>
            <tr>
              <th>{{ __('Product Name') }}</th>
              <th>{{ __('Units sold') }}</th>
              <th>{{ __('Availability') }}</th>
            </tr>
          </thead>
          <tbody id="reportBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let topProductsChart = null;

function renderChart(labels, values){
  const ctx = document.getElementById('topProductsChart').getContext('2d');
  if(topProductsChart) topProductsChart.destroy();

  topProductsChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: '{{ __("Products quantity") }}',
        data: values,
        backgroundColor: 'rgba(234,84,85,0.65)',
        borderColor: 'rgba(234,84,85,1)',
        borderWidth: 1
      }]
    },
    options: {
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
      plugins: { legend: { display: true } }
    }
  });
}

function populateTable(rows){
  const tbody = document.getElementById('reportBody');
  tbody.innerHTML = '';

  rows.forEach(r => {
    const name = r.presentation_formatted || r.name || '';
    const sold = Number(r.purchases_number || 0);
    const availability = (r.amount !== undefined && r.amount !== null) ? Number(r.amount) : '';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${name}</td>
      <td>${sold}</td>
      <td>${availability}</td>
    `;
    tbody.appendChild(tr);
  });
}

async function fetchTopProductsData(init, end, productId){
  const base = `{{ url('panel/reports/top-products/data') }}`;
  const url = productId
    ? `${base}/${encodeURIComponent(init)}/${encodeURIComponent(end)}/${encodeURIComponent(productId)}`
    : `${base}/${encodeURIComponent(init)}/${encodeURIComponent(end)}`;

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
  const productId = document.getElementById('product_id').value;

  if(!init || !end){
    alert('{{ __("Select date range") }}');
    return;
  }

  try {
    const data = await fetchTopProductsData(init, end, productId);
    const labels = data.map(r => String(r.presentation_formatted || r.name || ''));
    const values = data.map(r => Number(r.purchases_number || 0));
    renderChart(labels, values);
    populateTable(data);
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
    link.download = 'top_products_report.csv';
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
