@extends('layouts/contentLayoutMaster')

@section('title', __('Shipping Fees'))

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
            <h4 class="mb-0">{{ __('Shipping Fees') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <!-- reserved for future action buttons -->
            </div>
          </div>
        </div>
        <div class="card-body">
          @if(session('success'))
            <div id="shipping-fees-success" style="display:none;">{{ session('success') }}</div>
          @endif
          <div class="table-responsive">
            <table id="shipping-fees-table" class="table table-striped table-bordered table-hover w-100 shipping-fees-table">
              <thead>
                <tr>
                  <th>{{ __('Amount') }}</th>
                  <th>{{ __('Type') }}</th>
                  <th class="text-end">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($shippingFees as $fee)
                <tr>
                  <td data-order="{{ $fee->updated_at ? $fee->updated_at->format('Y-m-d H:i:s') : '' }}">{{ number_format($fee->amount, 2) }}</td>
                  <td>{{ $fee->type == 1 ? __('National') : __('Regional') }}</td>
                  <td>
                    <div class="d-flex align-items-center col-actions justify-content-end" style="min-width:140px;">
                      <form action="{{ route('shipping-fees.update', $fee->id) }}" method="POST" class="m-0 d-flex align-items-center confirm-save" data-message="{{ __('Save this fee?') }}">
                        @csrf
                        @method('PUT')
                        <input type="number" step="0.01" name="amount" value="{{ $fee->amount }}" class="form-control form-control-sm d-inline-block me-1" style="width:120px;">
                        <button class="btn btn-icon btn-flat-primary" type="submit" data-toggle="tooltip" data-placement="top" title="{{ __('Save') }}">
                          <i data-feather="save"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
                @empty
                  <tr>
                    <td colspan="3" class="text-center">{{ __('No shipping fees configured yet.') }}</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <hr />
          <div class="mt-4">
            <h4 class="text-center">{{ __('Minimum Purchase') }}</h4>
            <form action="{{ route('shipping-fees.minimum') }}" method="POST" class="text-center confirm-save" data-message="{{ __('Save minimum purchase?') }}">
              @csrf
              <div class="form-group">
                <label>{{ __('Enter minimum purchase amount $') }}</label>
                <input type="number" step="0.01" name="minimum_purchase" value="{{ $minimumPurchase }}" class="form-control d-inline-block" style="width:300px;">
              </div>
              <button class="btn btn-warning">{{ __('Save') }}</button>
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    $(function() {
      $('.shipping-fees-table').DataTable({
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

      // show toast if redirected after update/create and highlight first row
      const successEl = document.getElementById('shipping-fees-success');
      if (successEl) {
        const msg = successEl.textContent.trim();
        if (msg) {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: msg,
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true
          });
        }

        const firstRow = document.querySelector('.shipping-fees-table tbody tr');
        if (firstRow) {
          firstRow.style.transition = 'background-color 0.5s ease';
          firstRow.style.backgroundColor = '#e6ffed';
          setTimeout(()=>{ firstRow.style.backgroundColor = ''; }, 3000);
        }
      }

      // attach SweetAlert confirmation to save forms
      document.querySelectorAll('form.confirm-save').forEach(function(form){
        form.addEventListener('submit', function(e){
          e.preventDefault();
          const msg = form.getAttribute('data-message') || '{{ __('Are you sure?') }}';
          Swal.fire({
            title: msg,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ __('Yes') }}',
            cancelButtonText: '{{ __('Cancel') }}'
          }).then((result) => {
            if (result.isConfirmed) {
              const btn = form.querySelector('button[type="submit"]');
              if (btn) {
                btn.disabled = true;
                const original = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ' + (btn.innerText || '{{ __('Save') }}');
              }
              form.submit();
            }
          });
        });
      });
    });
  </script>
@endsection
