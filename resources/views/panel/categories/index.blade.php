@extends('layouts/contentLayoutMaster')

@section('title', __('Categories'))

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
            <h4 class="mb-0">{{ __('Categories') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <a href="{{ route('categories.create') }}" class="dt-button create-new btn btn-primary">
                <i data-feather="plus"></i> {{ __('Add New') }}
              </a>
            </div>
          </div>
        </div>

        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif

          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 module-list-table categories-table">
              <thead>
                <tr>
                  <th>{{ __('Category (Spanish)') }}</th>
                  <th>{{ __('Category (English)') }}</th>
                  <th class="text-end">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($categories as $c)
                  <tr>
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->name_english }}</td>
                    <td>
                      <div class="d-flex align-items-center col-actions justify-content-end" style="min-width:180px;">
                        <form class="m-0 mr-1" action="{{ route('categories.status', $c->id) }}" method="POST">
                          @csrf
                          <button type="submit" class="btn btn-icon btn-flat-{{ $c->status === '1' ? 'success' : 'secondary' }}" data-toggle="tooltip" data-placement="top" title="{{ __('Toggle status') }}">
                            <i data-feather="{{ $c->status === '1' ? 'toggle-right' : 'toggle-left' }}"></i>
                          </button>
                        </form>

                        <a href="{{ route('categories.show', $c->id) }}" class="mr-1" data-toggle="tooltip" data-placement="top" title="{{ __('View') }}">
                          <i data-feather="eye"></i>
                        </a>

                        <a href="{{ route('categories.edit', $c->id) }}" class="mr-1" data-toggle="tooltip" data-placement="top" title="{{ __('Edit') }}">
                          <i data-feather="edit-2"></i>
                        </a>

                        <form class="m-0" action="{{ route('categories.destroy', $c->id) }}" method="POST" onsubmit="return confirm('{{ __('Delete this category?') }}');">
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
<script>
  $(function() {
    $('.categories-table').DataTable({
      responsive: true,
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
      order: [[0, 'asc']],
      columnDefs: [{ orderable: false, targets: -1 }],
      drawCallback: function() { if (feather) { feather.replace({ width: 14, height: 14 }); } }
    });
  });
</script>
@endsection
