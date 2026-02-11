@extends('layouts/contentLayoutMaster')

@section('title', __('States & Municipalities'))

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
            <h4 class="mb-0">{{ __('Sectors') }}: {{ $municipality->name }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <button type="button" class="dt-button create-new btn btn-primary mr-1" data-toggle="modal" data-target="#parishModal">
                <i data-feather="plus"></i> {{ __('Add Sector') }}
              </button>
              <a href="{{ route('states-municipalities.show', $municipality->estado_id) }}" class="btn btn-outline-secondary">
                <i data-feather="arrow-left"></i> {{ __('Back') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <div class="table-responsive">
            <table id="parishesTable" class="table table-striped table-bordered table-hover w-100">
              <thead>
                <tr>
                  <th>{{ __('Name') }}</th>
                  <th>{{ __('Registration date') }}</th>
                  <th class="text-end">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($parishes as $parish)
                  <tr>
                    <td>{{ $parish->name }}</td>
                    <td>{{ optional($parish->created_at)->format('d-m-Y') }}</td>
                    <td>
                      <div class="d-flex align-items-center">
                        <form class="m-0" action="{{ route('states-municipalities.parishes.destroy', $parish->id) }}" method="POST" onsubmit="return confirm('{{ __('Delete this sector?') }}');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-icon btn-flat-danger" data-toggle="tooltip" data-placement="top" title="{{ __('Delete') }}">
                            <i data-feather="trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="3" class="text-center">{{ __('No sectors yet.') }}</td>
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

{{-- Modal: Add Sector --}}
<div class="modal fade" id="parishModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('Add Sector') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" action="{{ route('states-municipalities.parishes.store', $municipality->id) }}">
        <div class="modal-body">
          @csrf

          <div class="form-group">
            <label for="parish_name">{{ __('Sector name') }}</label>
            <input type="text" class="form-control" id="parish_name" name="name" required />
          </div>
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
    $('#parishesTable').DataTable({
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

    if (window.feather) feather.replace({ width: 14, height: 14 });
  });
</script>
@endsection
