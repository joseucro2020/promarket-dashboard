@extends('layouts/contentLayoutMaster')

@section('title', __('Affiliates Report'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('content')
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom p-1">
          <div class="head-label">
            <h4 class="mb-0">{{ __('Affiliates Report') }}</h4>
          </div>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <button id="exportCsv" class="btn btn-danger mr-2">{{ __('locale.Export') }}</button>
            <button id="exportPdf" class="btn btn-danger">{{ __('locale.Export PDF') }}</button>
          </div>

          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 affiliates-table" id="affiliatesTable">
              <thead>
                <tr>
                  <th>{{ __('locale.Pro Seller') }}</th>
                  <th>{{ __('locale.Client') }}</th>
                  <th>{{ __('locale.Client Identification') }}</th>
                  <th>{{ __('locale.Client Identification Type') }}</th>
                  <th>{{ __('locale.Coupon') }}</th>
                </tr>
              </thead>
              <tbody id="reportBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.bootstrap4.min.js')) }}"></script>
@endsection

@section('page-script')
<script>
async function fetchAffiliates(){
  const url = `{{ url('panel/reports/affiliates/data') }}`;
  const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
  if(!res.ok){
    const txt = await res.text();
    throw new Error(txt || `HTTP ${res.status}`);
  }
  return await res.json();
}

function rowToCells(r){
  const proSeller = r?.referrer?.name ?? '';
  const client = r?.referred?.name ?? '';
  const identification = r?.referred?.identificacion ?? '';
  const idType = r?.referred?.persona ?? '';
  const coupon = r?.coupon?.code ?? '';
  return [proSeller, client, identification, idType, coupon];
}

function renderTable(rows){
  const tbody = document.getElementById('reportBody');
  tbody.innerHTML = '';
  rows.forEach(r => {
    const cells = rowToCells(r);
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${cells[0]}</td>
      <td>${cells[1]}</td>
      <td>${cells[2]}</td>
      <td>${cells[3]}</td>
      <td>${cells[4]}</td>
    `;
    tbody.appendChild(tr);
  });
}

function initDataTable(){
  if (!window.$ || !$.fn || !$.fn.DataTable) return;

  // Evitar doble init
  if ($.fn.DataTable.isDataTable('#affiliatesTable')) {
    $('#affiliatesTable').DataTable().destroy();
  }

  $('#affiliatesTable').DataTable({
    responsive: true,
    dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
    order: [[0, 'asc']],
    drawCallback: function() { if (feather) { feather.replace({ width: 14, height: 14 }); } }
  });
}

document.addEventListener('DOMContentLoaded', async function(){
  try {
    const data = await fetchAffiliates();
    renderTable(Array.isArray(data) ? data : []);
    initDataTable();
  } catch (e) {
    console.error(e);
    alert('Error cargando reporte. Revisa consola / logs.');
  }

  document.getElementById('exportCsv').addEventListener('click', function(){
    const rows = Array.from(document.querySelectorAll('#affiliatesTable tr'));
    const csv = rows
      .map(r => Array.from(r.querySelectorAll('th,td'))
        .map(cell => `"${cell.innerText.replace(/"/g,'""')}"`)
        .join(','))
      .join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'affiliates_report.csv';
    link.click();
  });

  document.getElementById('exportPdf').addEventListener('click', function(){
    window.print();
  });
});
</script>
@endsection
