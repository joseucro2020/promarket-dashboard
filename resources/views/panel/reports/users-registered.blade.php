@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Users Registered Report'))

@section('content')
<div class="container">
  <h2 class="text-center mb-3">{{ __('locale.Users Registered Report') }}</h2>

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
              <th>{{ __('locale.Name') }}</th>
              <th>{{ __('locale.Email') }}</th>
              <th>{{ __('locale.Registration date') }}</th>
              <th>{{ __('locale.First order') }}</th>
            </tr>
          </thead>
          <tbody id="reportBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@section('page-script')
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
    const data = await fetchUsersData(init, end);
    document.getElementById('totalRecords').innerText = Array.isArray(data) ? data.length : 0;
    populateTable(Array.isArray(data) ? data : []);
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
@endsection

@endsection
