@extends('layouts/contentLayoutMaster')

@section('title', __('Shipping Fees'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('content')
<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header text-center p-3">
          <h3 class="mb-0">{{ __('Shipping Fees') }}</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="shipping-fees-table" class="table table-striped table-bordered w-100 shipping-fees-table">
              <thead>
                <tr>
                  <th>{{ __('Amount') }}</th>
                  <th>{{ __('Type') }}</th>
                  <th class="text-end">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($shippingFees as $fee)
                <tr>
                  <td>{{ number_format($fee->amount, 2) }}</td>
                  <td>{{ $fee->type }}</td>
                  <td class="text-end">
                    <form action="{{ route('shipping-fees.update', $fee->id) }}" method="POST" class="d-inline-block">
                      @csrf
                      @method('PUT')
                      <input type="number" step="0.01" name="amount" value="{{ $fee->amount }}" class="form-control d-inline-block" style="width:120px; display:inline-block;">
                      <button class="btn btn-sm btn-primary" type="submit">{{ __('Guardar') }}</button>
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <hr />
          <div class="mt-4">
            <h4 class="text-center">{{ __('Minimum Purchase') }}</h4>
            <form action="{{ route('shipping-fees.minimum') }}" method="POST" class="text-center">
              @csrf
              <div class="form-group">
                <label>{{ __('Enter minimum purchase amount $') }}</label>
                <input type="number" step="0.01" name="minimum_purchase" value="{{ $minimumPurchase }}" class="form-control d-inline-block" style="width:300px;">
              </div>
              <button class="btn btn-warning">{{ __('Guardar') }}</button>
            </form>
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
    $(function() {
      $('#shipping-fees-table').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
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
