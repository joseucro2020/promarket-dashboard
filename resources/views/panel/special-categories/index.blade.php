@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Special Categories'))

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
            <h4 class="mb-0">{{ __('locale.Special Categories') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <a href="{{ route('special-categories.create') }}" class="dt-button create-new btn btn-primary">
                <i data-feather="plus"></i> {{ __('locale.Add New') }}
              </a>
            </div>
          </div>
        </div>

        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover w-100 module-list-table special-categories-table" id="specialCategoriesTable">
          <thead>
            <tr>
              <th>{{ __('locale.Name') }}</th>
              <th>{{ __('locale.Order') }}</th>
              <th>{{ __('locale.Products to Show') }}</th>
              <th>{{ __('locale.Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($categories as $cat)
              <tr data-id="{{ $cat->id }}"
                  data-name="{{ $cat->name }}"
                  data-order="{{ $cat->order }}"
                  data-slider_quantity="{{ $cat->slider_quantity }}"
                  data-tipo_order="{{ $cat->tipo_order }}"
                  data-tipo_special="{{ $cat->tipo_special }}"
                  data-status="{{ $cat->status }}">
                <td>{{ $cat->id }} - {{ $cat->name }}</td>
                <td>{{ $cat->order }}</td>
                <td>{{ $cat->slider_quantity }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="custom-control custom-switch custom-switch-success mr-1">
                      <input type="checkbox" class="custom-control-input" id="sc_status_{{ $cat->id }}" {{ (int)$cat->status === 1 ? 'checked' : '' }} onchange="window.__toggleSpecialCategoryStatus({{ $cat->id }})" />
                      <label class="custom-control-label" for="sc_status_{{ $cat->id }}"></label>
                    </div>

                    <a href="{{ route('special-categories.edit', $cat->id) }}" class="btn btn-icon btn-flat-success" title="{{ __('locale.Edit') }}">
                      <i data-feather="edit"></i>
                    </a>

                    <form action="{{ url('panel/categorias-especiales/'.$cat->id) }}" method="POST" class="ml-25" onsubmit="return confirm('{{ __('locale.Are you sure?') }}')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-icon btn-flat-danger" title="{{ __('locale.Delete') }}">
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
    $('.special-categories-table').DataTable({
      responsive: true,
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
      order: [[1, 'asc']],
      columnDefs: [{ orderable: false, targets: -1 }],
      drawCallback: function() { if (feather) { feather.replace({ width: 14, height: 14 }); } }
    });
  });

  window.__toggleSpecialCategoryStatus = async function(id) {
    const csrfToken = '{{ csrf_token() }}';
    const url = `{{ url('panel/categorias-especiales') }}/${id}/status`;
    const formData = new FormData();
    formData.append('_token', csrfToken);

    const res = await fetch(url, { method: 'POST', body: formData });
    if (!res.ok) {
      alert('{{ __('locale.Error') }}');
      return;
    }
  }
</script>
@endsection
