@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Products'))

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
            <h4 class="mb-0">{{ __('locale.Products List') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
                <a href="{{ route('products.create') }}" class="dt-button create-new btn btn-primary">
                <i data-feather="plus"></i> {{ __('locale.Add New') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
            <div class="row mb-1">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filter-company">{{ __('locale.Company') }}</label>
                  <select class="form-control" id="filter-company">
                    <option value="">{{ __('locale.All') }}</option>
                    @foreach($companies as $companyId)
                      <option value="{{ $companyId }}">{{ $companyId }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filter-status">{{ __('locale.Status') }}</label>
                  <select class="form-control" id="filter-status">
                    <option value="">{{ __('locale.All') }}</option>
                    <option value="1">{{ __('locale.Active') }}</option>
                    <option value="0">{{ __('locale.Inactive') }}</option>
                    <option value="2">{{ __('locale.Deleted') }}</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filter-inventory">{{ __('locale.Inventory') }}</label>
                  <select class="form-control" id="filter-inventory">
                    <option value="">{{ __('locale.All') }}</option>
                    <option value="1">{{ __('locale.In stock') }}</option>
                    <option value="2">{{ __('locale.Low stock') }}</option>
                    <option value="0">{{ __('locale.Out of stock') }}</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="table-responsive">
                <table class="table" id="products-table">
                    <thead>
                        <tr>
                            <th>{{ __('locale.Actions') }}</th>
                            <th>ID</th>
                            <th>{{ __('locale.Photo') }}</th>
                            <th>{{ __('locale.Category') }}</th>
                            <th>{{ __('locale.Product') }}</th>
                            <th>{{ __('locale.Stock') }}</th>
                            <th>{{ __('locale.Threshold') }}</th>
                            <th>{{ __('locale.Tax') }}</th>
                            <th>{{ __('locale.Cost') }}</th>
                            <th>{{ __('locale.Price') }}</th>
                            <th>{{ __('locale.Profit') }}</th>
                            <th>{{ __('locale.Percentage') }}</th>
                            <th>{{ __('locale.Registration') }}</th>
                            <th>{{ __('locale.Modification') }}</th>
                            <th>PRO</th>
                        </tr>
                    </thead>
                    <tbody>
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
      @if(session('success'))
        if (window.Swal) {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: @json(session('success')),
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          });
        }
      @endif
      @if(session('error'))
        if (window.Swal) {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: @json(session('error')),
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
          });
        }
      @endif

      var productsTable = $('#products-table').DataTable({
          processing: true,
          serverSide: true,
          ajax: {
            url: "{{ route('products.index') }}",
            data: function (d) {
              d.company = $('#filter-company').val();
              d.status = $('#filter-status').val();
              d.inventory = $('#filter-inventory').val();
            }
          },
          columns: [
              { data: 'actions', name: 'actions', orderable: false, searchable: false },
              { data: 'id', name: 'id' },
              { data: 'image', name: 'image', orderable: false, searchable: false },
              { data: 'category', name: 'categories.name' },
              { data: 'name', name: 'name' },
              { data: 'stock', name: 'stock' },
              { data: 'threshold', name: 'threshold' },
              { data: 'tax', name: 'taxe.name' },
              { data: 'cost', name: 'price_2' },
              { data: 'price', name: 'price_1' },
              { data: 'profit', name: 'profit', searchable: false },
              { data: 'percentage', name: 'percentage', searchable: false },
              { data: 'created_at', name: 'created_at' },
              { data: 'updated_at', name: 'updated_at' },
              { data: 'pro', name: 'pro', orderable: false, searchable: false }
          ],
          responsive: true,
          dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
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

      $('#filter-company, #filter-status, #filter-inventory').on('change', function () {
        productsTable.ajax.reload();
      });

      const csrfToken = $('meta[name="csrf-token"]').attr('content');

      $('body').on('change', '.product-status-toggle', function () {
        const checkbox = this;
        const url = checkbox.dataset.url;
        const previous = !checkbox.checked;

        if (!url) {
          checkbox.checked = previous;
          return;
        }

        fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
          .then(async (response) => {
            if (!response.ok) {
              const payload = await response.json().catch(() => ({}));
              throw new Error(payload.message || '{{ __('An error occurred') }}');
            }
            return response.json();
          })
          .then(() => {
            if (window.Swal) {
              Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ __('Information updated successfully.') }}',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
              });
            }
          })
          .catch((error) => {
            checkbox.checked = previous;
            const message = error.message || '{{ __('An error occurred') }}';
            if (window.Swal) {
              Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: message,
                showConfirmButton: false,
                timer: 4500,
                timerProgressBar: true
              });
            } else {
              alert(message);
            }
          });
      });
    });
  </script>
@endsection
