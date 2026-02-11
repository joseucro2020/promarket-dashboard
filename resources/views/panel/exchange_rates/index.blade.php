@extends('layouts/contentLayoutMaster')

@section('title', __('Exchange Rate'))

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
            <h4 class="mb-0">{{ __('Exchange Rates List') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <a href="{{ route('exchange-rates.create') }}" class="dt-button create-new btn btn-primary mr-1">
                <i data-feather="plus"></i> {{ __('Add New') }}
              </a>
              <form id="fetch-bcv-form" method="POST" action="{{ route('exchange-rates.fetch-now') }}" style="display:inline;">
                @csrf
                <button id="fetch-bcv-btn" type="button" class="btn btn-outline-info">
                  <i data-feather="refresh-cw"></i> {{ __('Fetch Rates Now') }}
                </button>
              </form>
            </div>
          </div>
        </div>
        <div class="card-body">
          @if(session('success'))
            <div id="exchange-rate-success" style="display:none;">{{ session('success') }}</div>
          @endif
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 exchange-rates-table">
              <thead>
                <tr>
                  <th>{{ __('Date Recorded') }}</th>
                  <th>{{ __('Currency From') }}</th>
                  <th>{{ __('Currency To') }}</th>
                  <th>{{ __('Rate') }}</th>
                  <th>{{ __('Notes') }}</th>
                  <th class="text-end">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($rates as $r)
                  <tr>
                    <td data-order="{{ $r->created_at ? $r->created_at->format('Y-m-d H:i:s') : '' }}">{{ $r->created_at ? $r->created_at->format('d-m-Y H:i') : '' }}</td>
                    <td>{{ $r->currency_from }}</td>
                    <td>{{ $r->currency_to }}</td>
                    <td>{{ $r->change }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($r->notes, 60) }}</td>
                    <td>
                      <div class="d-flex align-items-center">
                        <a href="{{ route('exchange-rates.edit', $r->id) }}" class="btn btn-icon btn-flat-success mr-1" data-toggle="tooltip" data-placement="top" title="{{ __('Edit') }}">
                          <i data-feather="edit"></i>
                        </a>
                        <form class="m-0" action="{{ route('exchange-rates.destroy', $r->id) }}" method="POST" onsubmit="return confirm('{{ __('Delete this rate?') }}')">
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
                    <td colspan="6" class="text-center">{{ __('No exchange rates recorded yet.') }}</td>
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    $(document).ready(function() {
      $('.exchange-rates-table').DataTable({
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
            feather.replace({
              width: 14,
              height: 14
            });
          }
        }
      });
      // Highlight newest row and show toast when redirected after update/create.
      const successEl = document.getElementById('exchange-rate-success');
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

        // Highlight first row in table briefly.
        const firstRow = document.querySelector('.exchange-rates-table tbody tr');
        if (firstRow) {
          firstRow.style.transition = 'background-color 0.5s ease';
          firstRow.style.backgroundColor = '#e6ffed';
          setTimeout(()=>{ firstRow.style.backgroundColor = ''; }, 3000);
        }
      }

      // Fetch-now button confirmation.
      const fetchBtn = document.getElementById('fetch-bcv-btn');
      const fetchForm = document.getElementById('fetch-bcv-form');
      if (fetchBtn && fetchForm) {
        fetchBtn.addEventListener('click', function(){
          Swal.fire({
            title: '{{ __('Fetch Rates Now') }}',
            text: '{{ __('This will fetch the latest rates from BCV now. Continue?') }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ __('Yes, fetch') }}',
            cancelButtonText: '{{ __('Cancel') }}'
          }).then((result) => {
            if (result.isConfirmed) {
              fetchBtn.disabled = true;
              fetchBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __('Fetching...') }}';
              fetchForm.submit();
            }
          });
        });
      }
    });
  </script>
@endsection
