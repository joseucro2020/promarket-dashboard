@extends('layouts/contentLayoutMaster')

@section('title', __('Users Registered Report'))

@section('content')
<div class="container">
  <h2 class="text-center mb-3">{{ __('Users Registered Report') }}</h2>

  <form id="filterForm" class="row mb-3 align-items-end">
    <div class="col-md-5">
      <label for="init">{{ __('From') }}</label>
      <input type="date" id="init" name="init" class="form-control">
    </div>

    <div class="col-md-5">
      <label for="end">{{ __('To') }}</label>
      <input type="date" id="end" name="end" class="form-control">
    </div>

    <div class="col-md-2 text-right">
      <button type="button" id="btnFilter" class="btn btn-danger mt-1">{{ __('Filter') }}</button>
    </div>
  </form>

  <div class="card">
    <div class="card-body">
      <div class="mb-2">
        <strong>{{ __('Total records') }}</strong> <span id="totalRecords">0</span>
      </div>

      <div class="mb-3">
        <button id="exportCsv" class="btn btn-danger mr-2">{{ __('Export') }}</button>
        <button id="exportPdf" class="btn btn-danger">{{ __('Export PDF') }}</button>
      </div>

      <div class="table-responsive">
        <table class="table" id="reportTable">
          <thead>
            <tr>
              <th>{{ __('Name') }}</th>
              <th>{{ __('Email') }}</th>
              <th>{{ __('Registration date') }}</th>
              <th>{{ __('First order') }}</th>
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
function populateTable(rows){
  const tbody = document.getElementById('reportBody');
  tbody.innerHTML = '';

  rows.forEach(r => {
    const name = r.name ?? '';
    const email = r.email ?? '';
    const regDate = r.date ?? '';
    const firstOrder = r.primer ?? '';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${name}</td>
      <td>${email}</td>
      <td>${regDate}</td>
      <td>${firstOrder}</td>
    `;
    tbody.appendChild(tr);
  });
}

async function fetchUsersData(init, end){
  const base = `{{ url('panel/reports/users-registered/data') }}`;
  const url = `${base}/${encodeURIComponent(init)}/${encodeURIComponent(end)}`;

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

  if(!init || !end){
    alert('{{ __("Select date range") }}');
    return;
  }

  try {
    const data = await fetchUsersData(init, end);
    document.getElementById('totalRecords').innerText = Array.isArray(data) ? data.length : 0;
    populateTable(Array.isArray(data) ? data : []);
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
        .map(cell => `\"${cell.innerText.replace(/\"/g,'\"\"')}\"`)
        .join(','))
      .join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'users_registered_report.csv';
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
