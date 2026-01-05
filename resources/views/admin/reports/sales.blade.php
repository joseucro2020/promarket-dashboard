@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Sales Report</h2>

  <form id="filterForm" class="form-inline mb-3">
    <div class="form-group mr-2">
      <label for="init" class="mr-2">From</label>
      <input type="date" id="init" name="init" class="form-control">
    </div>
    <div class="form-group mr-2">
      <label for="end" class="mr-2">To</label>
      <input type="date" id="end" name="end" class="form-control">
    </div>
    <button type="button" id="btnFilter" class="btn btn-primary">Filter</button>
    <button type="button" id="btnExport" class="btn btn-secondary ml-2">Export</button>
  </form>

  <div id="reportArea">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Date</th>
          <th>Total</th>
          <th>Count</th>
        </tr>
      </thead>
      <tbody id="reportBody">
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
document.getElementById('btnFilter').addEventListener('click', function(){
  const init = document.getElementById('init').value;
  const end = document.getElementById('end').value;
  fetch('{{ route('reports.sales.data') }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ init: init, end: end })
  }).then(r=>r.json()).then(data=>{
    const tbody = document.getElementById('reportBody');
    tbody.innerHTML = '';
    data.forEach(row=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${row.date}</td><td>${row.total}</td><td>${row.count}</td>`;
      tbody.appendChild(tr);
    });
  });
});

document.getElementById('btnExport').addEventListener('click', function(){
  const init = document.getElementById('init').value;
  const end = document.getElementById('end').value;
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '{{ route('reports.sales.export') }}';
  form.style.display = 'none';
  const token = document.createElement('input'); token.name = '_token'; token.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const i1 = document.createElement('input'); i1.name='init'; i1.value = init;
  const i2 = document.createElement('input'); i2.name='end'; i2.value = end;
  form.appendChild(token); form.appendChild(i1); form.appendChild(i2);
  document.body.appendChild(form);
  form.submit();
});
</script>
@endpush

@endsection
