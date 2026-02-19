@extends('layouts/contentLayoutMaster')

@section('title', __('Purchase Orders'))

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
            <h4 class="mb-0">{{ __('Ordenes de Compra') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <a href="{{ route('buyorders.create') }}" class="dt-button create-new btn btn-primary">
                <i data-feather="plus"></i> {{ __('Add New') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 buyorders-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>{{ __('Nro. Documento') }}</th>
                  <th>{{ __('Fecha') }}</th>
                  <th>{{ __('Fecha Vcto') }}</th>
                  <th>{{ __('Cond. Pago') }}</th>
                  <th>{{ __('Proveedor') }}</th>
                  <th>{{ __('Almacén') }}</th>
                  <th>{{ __('Estatus') }}</th>
                  <th>{{ __('Registro') }}</th>
                  <th class="text-end">{{ __('Acciones') }}</th>
                </tr>
              </thead>
              <tbody></tbody>
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
@endsection

@section('page-script')
  <script>
    $(function(){
      $('.buyorders-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
          url: '{{ route("buyorders.index") }}',
          type: 'GET'
        },
        columns: [
          { data: 'id', name: 'id' },
          { data: 'nro_doc', name: 'nro_doc' },
          { data: 'fecha', name: 'fecha' },
          { data: 'fecha_vcto', name: 'fecha_vto' },
          { data: 'cond_pago', name: 'cond_pago' },
          { data: 'supplier', name: 'supplier' },
          { data: 'almacen', name: 'almacen_id' },
          { data: 'status', name: 'status' },
          { data: 'created_at', name: 'created_at' },
          { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        order: [[2, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' }
      });
    });
  </script>
@endsection
