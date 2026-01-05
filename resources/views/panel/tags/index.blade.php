@extends('layouts/contentLayoutMaster')

@section('title', __('Tags'))

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
            <h4 class="mb-0">{{ __('Tags') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <button type="button" class="dt-button create-new btn btn-primary" data-toggle="modal" data-target="#tagModal" onclick="window.__openTagCreate()">
                <i data-feather="plus"></i> {{ __('Add New') }}
              </button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 module-list-table tags-table">
              <thead>
                <tr>
                  <th>{{ __('Name') }}</th>
                  <th class="text-end">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($tags as $t)
                  <tr data-id="{{ $t->id }}" data-name="{{ $t->name }}" data-status="{{ $t->status }}">
                    <td>{{ $t->name }}</td>
                    <td>
                      <div class="d-flex align-items-center col-actions justify-content-end" style="min-width:150px;">
                        <div class="custom-control custom-switch custom-switch-success mr-1">
                          <input type="checkbox" class="custom-control-input" id="tag_status_{{ $t->id }}" {{ (int)$t->status === 1 ? 'checked' : '' }} onchange="window.__toggleTagStatus({{ $t->id }})" />
                          <label class="custom-control-label" for="tag_status_{{ $t->id }}"></label>
                        </div>

                        <button type="button" class="btn btn-icon btn-flat-success mr-1" data-toggle="modal" data-target="#tagModal" onclick="window.__openTagEdit({{ $t->id }})" title="{{ __('Edit') }}">
                          <i data-feather="edit"></i>
                        </button>

                        <form class="m-0" action="{{ url('panel/etiquetas/'.$t->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-icon btn-flat-danger" title="{{ __('Delete') }}">
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

<div class="modal fade" id="tagModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tagModalTitle">{{ __('Add New') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="tagForm">
          @csrf
          <input type="hidden" id="tag_id" value="">
          <div class="form-group">
            <label for="tag_name">{{ __('Name') }}</label>
            <input type="text" class="form-control" id="tag_name" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="button" class="btn btn-primary" onclick="window.__saveTag()">{{ __('Save') }}</button>
      </div>
    </div>
  </div>
</div>
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
  const csrfToken = '{{ csrf_token() }}';

  $(function() {
    $('.tags-table').DataTable({
      responsive: true,
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
      order: [],
      columnDefs: [{ orderable: false, targets: -1 }],
      drawCallback: function() { if (feather) { feather.replace({ width: 14, height: 14 }); } }
    });
  });

  window.__openTagCreate = function() {
    document.getElementById('tagModalTitle').innerText = '{{ __('Add New') }}';
    document.getElementById('tag_id').value = '';
    document.getElementById('tag_name').value = '';
  }

  window.__openTagEdit = function(id) {
    document.getElementById('tagModalTitle').innerText = '{{ __('Edit') }}';
    const row = document.querySelector('tr[data-id="' + id + '"]');
    document.getElementById('tag_id').value = id;
    document.getElementById('tag_name').value = row?.dataset?.name || '';
  }

  window.__saveTag = async function() {
    const id = document.getElementById('tag_id').value;
    const name = document.getElementById('tag_name').value;

    const urlBase = '{{ url('panel/etiquetas') }}';
    const url = id ? `${urlBase}/${id}` : urlBase;

    const formData = new FormData();
    formData.append('_token', csrfToken);
    if (id) formData.append('_method', 'PUT');
    formData.append('name', name);

    const res = await fetch(url, { method: 'POST', body: formData });
    if (!res.ok) {
      alert('{{ __('Error') }}');
      return;
    }

    location.reload();
  }

  window.__toggleTagStatus = async function(id) {
    const url = `{{ url('panel/etiquetas') }}/${id}/status`;
    const formData = new FormData();
    formData.append('_token', csrfToken);

    const res = await fetch(url, { method: 'POST', body: formData });
    if (!res.ok) {
      alert('{{ __('Error') }}');
      return;
    }
  }
</script>
@endsection
