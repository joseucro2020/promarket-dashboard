@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Payment Gateway'))

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
            <h4 class="mb-0">{{ __('locale.Payment Gateways List') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
                <a href="{{ route('payment-gateway.create') }}" class="dt-button create-new btn btn-primary">
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
            <table class="table table-striped table-bordered table-hover w-100 payment-gateways-table">
              <thead>
                <tr>
                  <th>{{ __('locale.ID') }}</th>
                  <th>{{ __('locale.Name') }}</th>
                  <th>{{ __('locale.Provider') }}</th>
                  <th>{{ __('locale.Type') }}</th>
                  <th>{{ __('locale.Currency') }}</th>
                  <th>{{ __('locale.Icon') }}</th>
                  <th>{{ __('locale.Description') }}</th>
                  <th class="text-center">{{ __('locale.Order') }}</th>
                  <th class="text-center">{{ __('locale.Status') }}</th>
                  <th class="text-end">{{ __('locale.Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($gateways as $g)
                  @php
                    $availableTypes = is_array($g->available_types) ? $g->available_types : [];
                    $computedType = $g->type ?: (count($availableTypes) > 1 ? 'multi' : 'unique');
                    $isActive = (int)$g->status === 1;
                    $statusBadge = $isActive ? 'badge badge-pill badge-light-success' : 'badge badge-pill badge-light-secondary';
                  @endphp
                  <tr>
                    <td>{{ $g->id }}</td>
                    <td>{{ $g->name }}</td>
                    <td>{{ $g->provider }}</td>
                    <td>{{ $computedType }}</td>
                    <td>{{ $g->currency }}</td>
                    <td>
                        @if(!empty($g->icon))
                        <img class="img-fluid rounded" style="width:28px;height:28px;object-fit:contain;border:1px solid rgba(0,0,0,.08);background:#fff;padding:3px;" src="{{ asset($g->icon) }}" alt="{{ $g->name }}">
                      @else
                        <span class="text-muted">{{ __('locale.No icon') }}</span>
                      @endif
                    </td>
                    <td>{{ $g->description }}</td>
                    <td class="text-center">{{ $g->order }}</td>
                      <td class="text-center">
                      <span class="{{ $statusBadge }}">{{ $isActive ? __('locale.Active') : __('locale.Inactive') }}</span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <form class="m-0 mr-1" action="{{ route('payment-gateway.status', $g->id) }}" method="POST">
                          @csrf
                          <div class="custom-control custom-switch custom-switch-success">
                            <input type="checkbox" class="custom-control-input" id="gateway_status_{{ $g->id }}" {{ $isActive ? 'checked' : '' }} onchange="this.form.submit()" />
                            <label class="custom-control-label" for="gateway_status_{{ $g->id }}"></label>
                          </div>
                        </form>

                        <a href="{{ route('payment-gateway.edit', $g->id) }}" class="btn btn-icon btn-flat-success mr-1" data-toggle="tooltip" title="{{ __('locale.Edit') }}">
                          <i data-feather="edit"></i>
                        </a>

                        <form class="m-0" action="{{ route('payment-gateway.destroy', $g->id) }}" method="POST" onsubmit="return confirm('{{ __('locale.Are you sure?') }}');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-icon btn-flat-danger" data-toggle="tooltip" title="{{ __('locale.Delete') }}">
                            <i data-feather="trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="10" class="text-center">{{ __('locale.No payment gateways yet.') }}</td>
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
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.bootstrap4.min.js')) }}"></script>
@endsection

@section('page-script')
  <script>
    $(function() {
      $('.payment-gateways-table').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        order: [[0, 'desc']],
        columnDefs: [
          { orderable: false, targets: [5, 9] }
        ],
        language: {
          url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        },
        drawCallback: function() {
          if (feather) {
            feather.replace({
              width: 14,
              height: 14
            });
          }
        }
      });
    });
  </script>
@endsection
