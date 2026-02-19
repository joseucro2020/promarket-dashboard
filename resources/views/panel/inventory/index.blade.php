@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Inventory Replenishment'))

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
            <h4 class="mb-0">{{ __('locale.Inventory Replenishment List') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
                <a href="{{ route('inventory.create') }}" class="dt-button create-new btn btn-primary">
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
            <table class="table table-striped table-bordered table-hover w-100 module-list-table inventory-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>{{ __('locale.User') }}</th>
                  <th>{{ __('locale.Product') }}</th>
                  <th>{{ __('locale.Type') }}</th>
                  <th>{{ __('locale.Quantity') }}</th>
                  <th>{{ __('locale.Date') }}</th>
                  <th class="text-end">{{ __('locale.Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($replenishments ?? [] as $replenishment)
                  <tr>
                    <td>{{ $replenishment->id }}</td>
                    <td>{{ $replenishment->user_name ?? '-' }}</td>
                    <td>{{ $replenishment->product_name ?? '-' }}</td>
                    <td>{{ $replenishment->type_label ?? '-' }}</td>
                    <td>{{ $replenishment->quantity ?? '-' }}</td>
                    <td>{{ $replenishment->created_at ? $replenishment->created_at->format('d-m-Y H:i') : '-' }}</td>
                    <td class="text-end">-</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center">{{ __('locale.No replenishments yet.') }}</td>
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
  <script src="{{ asset(mix('js/scripts/pages/app-module-list.js')) }}"></script>
@endsection
