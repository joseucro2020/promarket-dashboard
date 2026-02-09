@extends('layouts/contentLayoutMaster')

@section('title', __('Suppliers'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('page-style')
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('css/base/pages/app-user.css')) }}">
@endsection

@section('content')
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom p-1">
          <div class="head-label">
            <h4 class="mb-0">{{ __('Proveedores') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <a href="{{ route('suppliers.create') }}" class="dt-button create-new btn btn-primary">
                <i data-feather="plus"></i> {{ __('Add New') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 module-list-table suppliers-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>{{ __('Name') }}</th>
                  <th>{{ __('Country') }}</th>
                  <th>{{ __('Phone') }}</th>
                  <th>{{ __('Email') }}</th>
                  <th class="text-end">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($suppliers as $s)
                  <tr>
                    <td>{{ $s->id }}</td>
                    <td>{{ $s->nombre_prove }}</td>
                    <td>{{ $s->pais_prove }}</td>
                    <td>{{ $s->tlf_prove }}</td>
                    <td>{{ $s->email_prove }}</td>
                    <td>
                      <div class="d-flex align-items-center">
                        <form class="m-0 mr-1" action="{{ route('suppliers.status', $s->id) }}" method="POST">
                          @csrf
                          <div class="custom-control custom-switch custom-switch-success">
                            <input type="checkbox" class="custom-control-input" id="supplier_status_{{ $s->id }}" {{ $s->status_prove == '1' ? 'checked' : '' }} onchange="this.form.submit()" />
                            <label class="custom-control-label" for="supplier_status_{{ $s->id }}"></label>
                          </div>
                        </form>
                        <a href="{{ route('suppliers.edit', $s->id) }}" class="btn btn-icon btn-flat-success mr-1" data-toggle="tooltip" data-placement="top" title="{{ __('Edit') }}">
                          <i data-feather="edit"></i>
                        </a>
                        <form class="m-0" action="{{ route('suppliers.destroy', $s->id) }}" method="POST" onsubmit="return confirm('{{ __('Delete this supplier?') }}');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-icon btn-flat-danger" data-toggle="tooltip" data-placement="top" title="{{ __('Delete') }}">
                            <i data-feather="trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
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
  <script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
@endsection

@section('page-script')
  <script src="{{ asset(mix('js/scripts/pages/app-taxes-list.js')) }}"></script>
  <script>
    $(function() {
      $('.suppliers-table').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
        order: [[1, 'desc']],
        columnDefs: [{ orderable: false, targets: -1 }],
        drawCallback: function() { if (feather) { feather.replace({ width: 14, height: 14 }); } }
      });
    });
  </script>
@endsection
