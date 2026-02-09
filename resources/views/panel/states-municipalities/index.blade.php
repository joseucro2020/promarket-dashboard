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
        <div class="card-header text-center p-3">
          <h2 class="mb-0 font-italic">{{ __('States') }}</h2>
        </div>
        <div class="card-body">
          <div class="border rounded p-3">

            <div class="d-flex align-items-center mb-2" style="gap: 10px;">
              <i data-feather="search" class="mr-1"></i>
              <input id="states-search" type="text" class="form-control border-0" placeholder="{{ __('Search') }}" style="max-width: 420px; border-bottom: 1px solid #ddd !important; border-radius: 0;" />
            </div>

            <div class="table-responsive">
              <table id="statesTable" class="table table-striped w-100">
                <thead>
                  <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Registration date') }}</th>
                    <th class="text-center">{{ __('Actions') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($states as $state)
                    <tr data-id="{{ $state->id }}" data-nombre="{{ $state->nombre }}" data-status="{{ (int) ($state->status ?? 0) }}">
                      <td>
                        <a href="{{ url('panel/estados-municipios') }}/{{ $state->id }}" class="text-body" style="text-decoration:none;">
                          {{ $state->nombre }}
                        </a>
                      </td>
                      <td>
                        {{ !empty($state->created_at) ? \Carbon\Carbon::parse($state->created_at)->format('d-m-Y') : '' }}
                      </td>
                      <td class="text-center">
                        <div class="d-inline-flex align-items-center">
                          <div class="custom-control custom-switch custom-switch-success mr-1">
                            <input type="checkbox" class="custom-control-input state-status-toggle" id="state-status-{{ $state->id }}" {{ (int)($state->status ?? 0) === 1 ? 'checked' : '' }}>
                            <label class="custom-control-label" for="state-status-{{ $state->id }}"></label>
                          </div>
                          <button type="button" class="btn btn-icon btn-flat-success mr-1 btn-edit-state" title="{{ __('Edit') }}">
                            <i data-feather="edit"></i>
                          </button>
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
  </div>
</section>

{{-- Modal: Edit State --}}
<div class="modal fade" id="stateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="stateModalTitle">{{ __('Edit') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="stateForm">
        <div class="modal-body">
          @csrf
          <input type="hidden" id="state_id" value="" />

          <div class="form-group">
            <label for="state_nombre">{{ __('Name') }}</label>
            <input type="text" class="form-control" id="state_nombre" name="nombre" required />
          </div>

          <div class="alert alert-danger d-none" id="stateFormError"></div>
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

    var table = $('#statesTable').DataTable({
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

    $('#states-search').on('keyup', function () {
      table.search(this.value).draw();
    });

    function resetStateModal() {
      $('#state_id').val('');
      $('#state_nombre').val('');
      $('#stateFormError').addClass('d-none').text('');
    }

    $('#stateModal').on('hidden.bs.modal', resetStateModal);

    $(document).on('click', '.btn-edit-state', function (e) {
      e.preventDefault();
      var $tr = $(this).closest('tr');

      $('#state_id').val($tr.data('id'));
      $('#state_nombre').val($tr.data('nombre'));
      $('#stateModal').modal('show');
    });

    $('#stateForm').on('submit', function (e) {
      e.preventDefault();

      var id = $('#state_id').val();
      $('#stateFormError').addClass('d-none').text('');

      $.ajax({
        url: '{{ url('panel/estados-municipios') }}/' + id,
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

          $('#stateFormError').removeClass('d-none').text(message);
        }
      });
    });

    $(document).on('change', '.state-status-toggle', function () {
      var $tr = $(this).closest('tr');
      var id = $tr.data('id');

      $.ajax({
        url: '{{ url('panel/estados-municipios') }}/' + id + '/status',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function () {
          // mantener simple/consistente
        },
        error: function () {
          window.location.reload();
        }
      });
    });

    if (window.feather) feather.replace({ width: 16, height: 16 });
  });
</script>
@endsection
