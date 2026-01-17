@extends('layouts/contentLayoutMaster')

@section('title', __('Payment Gateway'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('page-style')
  <style>
    .pg-title { font-style: italic; }
    .pg-add {
      display: inline-flex;
      align-items: center;
      gap: .6rem;
      font-weight: 600;
    }
    .pg-add .pg-add-icon {
      width: 40px;
      height: 40px;
      border-radius: 999px;
      background: #1a73e8;
      color: #fff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 6px 14px rgba(26,115,232,.25);
    }
    .pg-add .pg-add-icon i { width: 18px; height: 18px; }

    .pg-icon-img {
      width: 28px;
      height: 28px;
      object-fit: contain;
      border-radius: 6px;
      border: 1px solid rgba(0,0,0,.08);
      background: #fff;
      padding: 3px;
    }

    .pg-status {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      color: #6e6b7b;
    }
    .pg-status-dot {
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: #bbb;
    }
    .pg-status.active .pg-status-dot { background: #28c76f; }
    .pg-status.inactive .pg-status-dot { background: #ea5455; }
  </style>
@endsection

@section('content')
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h2 class="text-center pg-title mb-3">{{ __('Payment Gateways') }}</h2>

          <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
            <a href="{{ route('payment-gateway.create') }}" class="pg-add text-decoration-none">
              <span class="pg-add-icon"><i data-feather="plus"></i></span>
              <span>{{ __('Add new') }}</span>
            </a>
          </div>

          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <div class="table-responsive">
            <table class="table table-striped table-hover w-100 payment-gateways-table">
              <thead>
                <tr>
                  <th>{{ __('ID') }}</th>
                  <th>{{ __('Name') }}</th>
                  <th>{{ __('Provider') }}</th>
                  <th>{{ __('Type') }}</th>
                  <th>{{ __('Currency') }}</th>
                  <th>{{ __('Icon') }}</th>
                  <th>{{ __('Description') }}</th>
                  <th>{{ __('Order') }}</th>
                  <th>{{ __('Status') }}</th>
                  <th class="text-end">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($gateways as $g)
                  @php
                    $availableTypes = is_array($g->available_types) ? $g->available_types : [];
                    $computedType = $g->type ?: (count($availableTypes) > 1 ? 'multi' : 'unique');
                    $isActive = (int)$g->status === 1;
                  @endphp
                  <tr>
                    <td>{{ $g->id }}</td>
                    <td>{{ $g->name }}</td>
                    <td>{{ $g->provider }}</td>
                    <td>{{ $computedType }}</td>
                    <td>{{ $g->currency }}</td>
                    <td>
                      @if(!empty($g->icon))
                        <img class="pg-icon-img" src="{{ asset($g->icon) }}" alt="{{ $g->name }}">
                      @else
                        <span class="text-muted">{{ __('No icon') }}</span>
                      @endif
                    </td>
                    <td>{{ $g->description }}</td>
                    <td>{{ $g->order }}</td>
                    <td>
                      <span class="pg-status {{ $isActive ? 'active' : 'inactive' }}">
                        <span class="pg-status-dot"></span>
                        <span>{{ $isActive ? __('Active') : __('Inactive') }}</span>
                      </span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center col-actions justify-content-end" style="min-width:150px;">
                        <a href="{{ route('payment-gateway.edit', $g->id) }}" class="mr-1" data-toggle="tooltip" title="{{ __('Edit') }}">
                          <i data-feather="edit-2"></i>
                        </a>

                        <form class="m-0 mr-1" action="{{ route('payment-gateway.status', $g->id) }}" method="POST">
                          @csrf
                          <button type="submit" class="btn btn-icon btn-flat-{{ $isActive ? 'success' : 'secondary' }}" data-toggle="tooltip" title="{{ __('Toggle status') }}">
                            <i data-feather="{{ $isActive ? 'toggle-right' : 'toggle-left' }}"></i>
                          </button>
                        </form>

                        <form class="m-0" action="{{ route('payment-gateway.destroy', $g->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-icon btn-flat-danger" data-toggle="tooltip" title="{{ __('Delete') }}">
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
        language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
        drawCallback: function() {
          if (feather) {
            feather.replace({ width: 14, height: 14 });
          }
        }
      });

      if (feather) {
        feather.replace({ width: 14, height: 14 });
      }
    });
  </script>
@endsection
