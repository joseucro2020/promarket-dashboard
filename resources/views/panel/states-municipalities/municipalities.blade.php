@extends('layouts/contentLayoutMaster')

@section('title', __('locale.States & Municipalities'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
@endsection

@section('content')
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom p-1">
          <div class="head-label">
            <h4 class="mb-0">{{ __('locale.Municipalities') }}: {{ $state->nombre }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <a href="{{ url('panel/estados-municipios') }}" class="btn btn-outline-secondary">
                <i data-feather="arrow-left"></i> {{ __('locale.Back') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="municipalitiesTable" class="table table-striped table-bordered table-hover w-100">
              <thead>
                <tr>
                  <th>{{ __('locale.Name') }}</th>
                  <th>{{ __('locale.Parishes') }}</th>
                  <th class="text-end">{{ __('locale.Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($municipalities as $m)
                  <tr data-id="{{ $m->id }}">
                    <td>{{ $m->name }}</td>
                    <td>{{ $m->parishes->count() }}</td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="custom-control custom-switch custom-switch-success mr-1">
                          <input type="checkbox" class="custom-control-input municipality-status-toggle" id="municipality-status-{{ $m->id }}" {{ (int)($m->status ?? 0) === 1 ? 'checked' : '' }}>
                          <label class="custom-control-label" for="municipality-status-{{ $m->id }}"></label>
                        </div>
                        <a href="{{ route('states-municipalities.municipalities.parishes', $m->id) }}" class="btn btn-icon btn-flat-success mr-1" data-toggle="tooltip" data-placement="top" title="{{ __('locale.Sectors') }}">
                          <i data-feather="edit"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="3" class="text-center">{{ __('locale.No municipalities yet.') }}</td>
                  </tr>
                @endforelse
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
@endsection

@section('page-script')
<script>
  $(function () {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    $('#municipalitiesTable').DataTable({
      responsive: true,
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      order: [[0, 'asc']],
      columnDefs: [
        { orderable: false, targets: -1 }
      ],
      language: {
        url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
      },
      drawCallback: function () {
        if (window.feather) {
          feather.replace({ width: 14, height: 14 });
        }
      }
    });

    $(document).on('change', '.municipality-status-toggle', function () {
      var $tr = $(this).closest('tr');
      var id = $tr.data('id');

      $.ajax({
        url: '{{ url('panel/estados-municipios/municipios') }}/' + id + '/status',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        error: function () {
          window.location.reload();
        }
      });
    });

    if (window.feather) feather.replace({ width: 14, height: 14 });
  });
</script>
@endsection
