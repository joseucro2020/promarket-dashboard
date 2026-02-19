@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Coupons'))

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
        <div class="card-header border-bottom p-1 d-flex justify-content-between align-items-center">
          <h4 class="mb-0">{{ __('locale.Coupons') }}</h4>
          <a href="{{ route('coupons.create') }}" class="btn btn-primary">
            <i data-feather="plus" class="me-50"></i>{{ __('locale.Add New') }}
          </a>
        </div>
        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 coupons-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>{{ __('locale.Seller PRO') }}</th>
                  <th>{{ __('locale.Seller Identification PRO') }}</th>
                  <th>{{ __('locale.Seller Type') }}</th>
                  <th>{{ __('locale.Coupon Code') }}</th>
                  <th>{{ __('locale.Uses per client') }}</th>
                  <th>{{ __('locale.Discount Percentage') }}</th>
                  <th>{{ __('locale.Coupon Status') }}</th>
                  <th class="text-end">{{ __('locale.Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($coupons as $coupon)
                  <tr>
                    <td>{{ $coupon->id }}</td>
                    <td>{{ $coupon->user->name ?? '-' }}</td>
                    <td>{{ $coupon->user->identificacion ?? '-' }}</td>
                    <td>{{ $coupon->user->persona ?? '-' }}</td>
                    <td>{{ $coupon->code }}</td>
                    <td>{{ $coupon->uses }}</td>
                    <td>{{ number_format($coupon->discount_percentage, 2) }}%</td>
                    <td>{{ $coupon->status_name }}</td>
                    <td>
                      <div class="d-flex align-items-center">
                        <form class="m-0 mr-1" action="{{ route('coupons.status', $coupon) }}" method="POST">
                          @csrf
                          <div class="custom-control custom-switch custom-switch-success">
                            <input type="checkbox" class="custom-control-input" id="coupon_status_{{ $coupon->id }}" {{ $coupon->status === \App\Models\Coupon::STATUS_ACTIVE ? 'checked' : '' }} onchange="this.form.submit()" />
                            <label class="custom-control-label" for="coupon_status_{{ $coupon->id }}"></label>
                          </div>
                        </form>
                        <a href="{{ route('coupons.edit', $coupon) }}" class="btn btn-icon btn-flat-success mr-1" data-toggle="tooltip" data-placement="top" title="{{ __('locale.Edit') }}">
                          <i data-feather="edit"></i>
                        </a>
                        <form class="m-0" action="{{ route('coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('{{ __('locale.Delete this coupon?') }}');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-icon btn-flat-danger" data-toggle="tooltip" data-placement="top" title="{{ __('locale.Delete') }}">
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
@endsection

@section('page-script')
  <script>
    $(document).ready(function() {
      $('.coupons-table').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        order: [[0, 'desc']],
        columnDefs: [
          { orderable: false, targets: -1 }
        ],
        language: {
          url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        },
        drawCallback: function() {
          if (feather) {
            feather.replace({ width: 14, height: 14 });
          }
        }
      });
    });
  </script>
@endsection
