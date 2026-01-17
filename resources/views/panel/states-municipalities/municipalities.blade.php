@extends('layouts/contentLayoutMaster')

@section('title', __('States & Municipalities'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
@endsection

@section('content')
<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom p-1">
          <div class="head-label">
            <h4 class="mb-0">{{ __('Municipalities') }}: {{ $state->nombre }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <a href="{{ url('panel/estados-municipios') }}" class="btn btn-outline-secondary">
                <i data-feather="arrow-left"></i> {{ __('Back') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center mb-2" style="gap: 10px;">
            <i data-feather="search" class="mr-1"></i>
            <input id="municipalities-search" type="text" class="form-control border-0" placeholder="{{ __('Search') }}" style="max-width: 420px; border-bottom: 1px solid #ddd !important; border-radius: 0;" />
          </div>

          <div class="table-responsive">
            <table id="municipalitiesTable" class="table table-striped w-100">
              <thead>
                <tr>
                  <th>{{ __('Name') }}</th>
                  <th>{{ __('Parishes') }}</th>
                  <th class="text-center">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($municipalities as $m)
                  <tr data-id="{{ $m->id }}" data-name="{{ $m->name }}">
                    <td>{{ $m->name }}</td>
                    <td>{{ $m->parishes->count() }}</td>
                    <td class="text-center">
                      <div class="d-inline-flex align-items-center" style="gap: 14px;">
                        <a href="#" class="btn-edit-municipality" title="{{ __('Edit') }}">
                          <i data-feather="edit-2" class="text-info"></i>
                        </a>
                        <div class="custom-control custom-switch custom-switch-success m-0">
                          <input type="checkbox" class="custom-control-input municipality-status-toggle" id="municipality-status-{{ $m->id }}" {{ (int)($m->status ?? 0) === 1 ? 'checked' : '' }}>
                          <label class="custom-control-label" for="municipality-status-{{ $m->id }}"></label>
                        </div>
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

{{-- Modal: Edit Municipality --}}
<div class="modal fade" id="municipalityModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('Edit') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="municipalityForm">
        <div class="modal-body">
          @csrf
          <input type="hidden" id="municipality_id" value="" />

          <div class="form-group">
            <label for="municipality_name">{{ __('Name') }}</label>
            <input type="text" class="form-control" id="municipality_name" name="name" required />
          </div>

          <div class="alert alert-danger d-none" id="municipalityFormError"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
          <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>
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

    var table = $('#municipalitiesTable').DataTable({
      responsive: true,
      paging: true,
      info: true,
      searching: true,
      lengthChange: false,
      dom: 't<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      order: [[0, 'asc']],
      columnDefs: [
        { orderable: false, targets: -1 }
      ],
      language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
      drawCallback: function () {
        if (window.feather) feather.replace({ width: 16, height: 16 });
      }
    });

    $('#municipalities-search').on('keyup', function () {
      table.search(this.value).draw();
    });

    function resetMunicipalityModal() {
      $('#municipality_id').val('');
      $('#municipality_name').val('');
      $('#municipalityFormError').addClass('d-none').text('');
    }

    $('#municipalityModal').on('hidden.bs.modal', resetMunicipalityModal);

    $(document).on('click', '.btn-edit-municipality', function (e) {
      e.preventDefault();
      var $tr = $(this).closest('tr');

      $('#municipality_id').val($tr.data('id'));
      $('#municipality_name').val($tr.data('name'));
      $('#municipalityModal').modal('show');
    });

    $('#municipalityForm').on('submit', function (e) {
      e.preventDefault();

      var id = $('#municipality_id').val();
      $('#municipalityFormError').addClass('d-none').text('');

      $.ajax({
        url: '{{ url('panel/estados-municipios/municipios') }}/' + id,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: $(this).serialize() + '&_method=PUT',
        success: function () {
          window.location.reload();
        },
        error: function (xhr) {
          var message = (xhr.responseJSON && (xhr.responseJSON.message || (xhr.responseJSON.errors && Object.values(xhr.responseJSON.errors)[0][0])))
            ? (xhr.responseJSON.message || (xhr.responseJSON.errors && Object.values(xhr.responseJSON.errors)[0][0]))
            : '{{ __('An error occurred') }}';

          $('#municipalityFormError').removeClass('d-none').text(message);
        }
      });
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

    if (window.feather) feather.replace({ width: 16, height: 16 });
  });
</script>
@endsection
