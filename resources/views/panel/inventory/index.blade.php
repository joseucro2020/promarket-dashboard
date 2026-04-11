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

          <form method="GET" action="{{ route('inventory.index') }}" class="mb-2">
            <div class="row align-items-end">
              <div class="col-md-4 col-sm-12 mb-1">
                <label for="from">{{ __('locale.From') }}</label>
                <input
                  type="datetime-local"
                  id="from"
                  name="from"
                  class="form-control"
                  value="{{ old('from', $filters['from'] ?? now()->startOfDay()->format('Y-m-d\\TH:i')) }}"
                >
              </div>

              <div class="col-md-4 col-sm-12 mb-1">
                <label for="to">{{ __('locale.To') }}</label>
                <input
                  type="datetime-local"
                  id="to"
                  name="to"
                  class="form-control"
                  value="{{ old('to', $filters['to'] ?? now()->endOfDay()->format('Y-m-d\\TH:i')) }}"
                >
              </div>

              <div class="col-md-4 col-sm-12 mb-1">
                <label for="type">{{ __('locale.Type') }}</label>
                <select id="type" name="type" class="form-control">
                  <option value="">{{ __('locale.All') }}</option>
                  <option value="0" {{ (string)($filters['type'] ?? '') === '0' ? 'selected' : '' }}>{{ __('locale.Entry') }}</option>
                  <option value="1" {{ (string)($filters['type'] ?? '') === '1' ? 'selected' : '' }}>{{ __('locale.Exit') }}</option>
                </select>
              </div>
            </div>

            <div class="d-flex flex-wrap mt-1">
              <button type="submit" class="btn btn-danger mr-1 mb-1">{{ __('locale.Search') }}</button>
              <button type="button" id="btn-export-inventory-excel" class="btn btn-danger mr-1 mb-1">{{ __('locale.Export') }}</button>
              <button type="button" id="btn-export-inventory-pdf" class="btn btn-danger mb-1">{{ __('locale.Export PDF') }}</button>
            </div>
          </form>

          <form id="inventory-export-excel-form" method="POST" action="{{ route('inventory.export.excel') }}" style="display:none;">
            @csrf
            <input type="hidden" name="from" id="inventory-export-from" value="{{ $filters['from'] ?? '' }}">
            <input type="hidden" name="to" id="inventory-export-to" value="{{ $filters['to'] ?? '' }}">
            <input type="hidden" name="type" id="inventory-export-type" value="{{ $filters['type'] ?? '' }}">
          </form>

          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 module-list-table inventory-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>{{ __('locale.User') }}</th>
                  <th>{{ __('locale.Product') }}</th>
                  <th>{{ __('locale.Presentation') }}</th>
                  <th>{{ __('locale.Type') }}</th>
                  <th>{{ __('locale.Original Quantity') }}</th>
                  <th>{{ __('locale.Modified Quantity') }}</th>
                  <th>{{ __('locale.Final Quantity') }}</th>
                  <th>{{ __('locale.Date') }}</th>
                  <th>{{ __('locale.Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($replenishments ?? [] as $replenishment)
                  <tr>
                    <td>{{ $replenishment->id }}</td>
                    <td>{{ $replenishment->user_name ?? '-' }}</td>
                    <td>{{ $replenishment->product_name ?? '-' }}</td>
                    <td>{{ $replenishment->presentation_label ?? '0.00' }}</td>
                    <td>{{ $replenishment->type_label ?? '-' }}</td>
                    <td>{{ is_numeric($replenishment->existing_qty) ? rtrim(rtrim(number_format((float)$replenishment->existing_qty, 3, '.', ''), '0'), '.') : '-' }}</td>
                    <td>{{ is_numeric($replenishment->modified_qty) ? rtrim(rtrim(number_format((float)$replenishment->modified_qty, 3, '.', ''), '0'), '.') : '-' }}</td>
                    <td>{{ is_numeric($replenishment->final_qty) ? rtrim(rtrim(number_format((float)$replenishment->final_qty, 3, '.', ''), '0'), '.') : '-' }}</td>
                    <td>{{ $replenishment->created_at ? \Carbon\Carbon::parse($replenishment->created_at)->format('d-m-Y h:i A') : '-' }}</td>
                    <td>
                      <a
                        href="javascript:void(0);"
                        class="btn btn-icon btn-flat-primary js-view-replenishment"
                        title="{{ __('locale.View') }}"
                        data-id="{{ $replenishment->id }}"
                        data-user="{{ $replenishment->user_name ?? '-' }}"
                        data-product="{{ $replenishment->product_name ?? '-' }}"
                        data-presentation="{{ $replenishment->presentation_label ?? '0.00' }}"
                        data-type="{{ $replenishment->type_label ?? '-' }}"
                        data-adjustment="{{ $replenishment->adjustment_type_label ?? '-' }}"
                        data-existing="{{ is_numeric($replenishment->existing_qty) ? rtrim(rtrim(number_format((float)$replenishment->existing_qty, 3, '.', ''), '0'), '.') : '-' }}"
                        data-modified="{{ is_numeric($replenishment->modified_qty) ? rtrim(rtrim(number_format((float)$replenishment->modified_qty, 3, '.', ''), '0'), '.') : '-' }}"
                        data-final="{{ is_numeric($replenishment->final_qty) ? rtrim(rtrim(number_format((float)$replenishment->final_qty, 3, '.', ''), '0'), '.') : '-' }}"
                        data-date="{{ $replenishment->created_at ? \Carbon\Carbon::parse($replenishment->created_at)->format('d-m-Y h:i A') : '-' }}"
                        data-reason="{{ $replenishment->reason_text ?? '-' }}"
                      >
                        <i data-feather="eye"></i>
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="10" class="text-center">{{ __('locale.No replenishments yet.') }}</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="modal fade" id="inventoryDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">{{ __('locale.Details') }} #<span data-field="id">-</span></h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('locale.Close') }}">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="row">
                    <div class="col-md-6 mb-1"><strong>{{ __('locale.User') }}:</strong> <span data-field="user">-</span></div>
                    <div class="col-md-6 mb-1"><strong>{{ __('locale.Product') }}:</strong> <span data-field="product">-</span></div>
                    <div class="col-md-6 mb-1"><strong>{{ __('locale.Presentation') }}:</strong> <span data-field="presentation">-</span></div>
                    <div class="col-md-6 mb-1"><strong>{{ __('locale.Type') }}:</strong> <span data-field="type">-</span></div>
                    <div class="col-md-6 mb-1"><strong>{{ __('locale.Adjustment Type') }}:</strong> <span data-field="adjustment">-</span></div>
                    <div class="col-md-6 mb-1"><strong>{{ __('locale.Date') }}:</strong> <span data-field="date">-</span></div>
                    <div class="col-md-4 mb-1"><strong>{{ __('locale.Original Quantity') }}:</strong> <span data-field="existing">-</span></div>
                    <div class="col-md-4 mb-1"><strong>{{ __('locale.Modified Quantity') }}:</strong> <span data-field="modified">-</span></div>
                    <div class="col-md-4 mb-1"><strong>{{ __('locale.Final Quantity') }}:</strong> <span data-field="final">-</span></div>
                    <div class="col-12 mb-1"><strong>{{ __('locale.Reason') }}:</strong> <span data-field="reason">-</span></div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('locale.Close') }}</button>
                </div>
              </div>
            </div>
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
  <script>
    (function () {
      function syncInventoryExportFilters() {
        var fromInput = document.getElementById('from');
        var toInput = document.getElementById('to');
        var typeInput = document.getElementById('type');

        document.getElementById('inventory-export-from').value = fromInput ? fromInput.value : '';
        document.getElementById('inventory-export-to').value = toInput ? toInput.value : '';
        document.getElementById('inventory-export-type').value = typeInput ? typeInput.value : '';
      }

      var excelBtn = document.getElementById('btn-export-inventory-excel');
      if (excelBtn) {
        excelBtn.addEventListener('click', function () {
          syncInventoryExportFilters();
          document.getElementById('inventory-export-excel-form').submit();
        });
      }

      var pdfBtn = document.getElementById('btn-export-inventory-pdf');
      if (pdfBtn) {
        pdfBtn.addEventListener('click', function () {
          syncInventoryExportFilters();

          var params = new URLSearchParams({
            from: document.getElementById('inventory-export-from').value,
            to: document.getElementById('inventory-export-to').value,
            type: document.getElementById('inventory-export-type').value
          });

          var pdfUrl = "{{ route('inventory.export.pdf') }}" + '?' + params.toString();
          window.open(pdfUrl, '_blank');
        });
      }

      $(document).on('click', '.js-view-replenishment', function () {
        var $button = $(this);
        var $modal = $('#inventoryDetailModal');

        $modal.find('[data-field="id"]').text($button.data('id') || '-');
        $modal.find('[data-field="user"]').text($button.data('user') || '-');
        $modal.find('[data-field="product"]').text($button.data('product') || '-');
        $modal.find('[data-field="presentation"]').text($button.data('presentation') || '-');
        $modal.find('[data-field="type"]').text($button.data('type') || '-');
        $modal.find('[data-field="adjustment"]').text($button.data('adjustment') || '-');
        $modal.find('[data-field="existing"]').text($button.data('existing') || '-');
        $modal.find('[data-field="modified"]').text($button.data('modified') || '-');
        $modal.find('[data-field="final"]').text($button.data('final') || '-');
        $modal.find('[data-field="date"]').text($button.data('date') || '-');
        $modal.find('[data-field="reason"]').text($button.data('reason') || '-');

        $modal.modal('show');
      });
    })();
  </script>
@endsection
