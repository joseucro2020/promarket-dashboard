@extends('layouts/contentLayoutMaster')

@section('title', __('locale.New Inventory Replenishment'))

@section('vendor-style')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
@endsection

@section('content')
<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
          <div class="card-header">
          <h4 class="card-title">{{ __('locale.Add Replenishment') }}</h4>
        </div>
        <div class="card-body">
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form action="{{ route('inventory.store') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ __('locale.Product') }}</label>
                  <select name="product_id" id="inventory_product_id" class="form-control">
                    @if(!empty($selectedProduct))
                      <option
                        value="{{ $selectedProduct['id'] }}"
                        data-existing="{{ $selectedProduct['existing_stock'] ?? 0 }}"
                        selected
                      >{{ $selectedProduct['text'] }}</option>
                    @endif
                  </select>
                  <small class="text-muted">{{ __('locale.Search Inventory Product Help') }}</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>{{ __('locale.Replenishment Type') }}</label>
                  <select name="type" id="replenishment_type" class="form-control">
                    <option value="" disabled {{ old('type') === null ? 'selected' : '' }}>{{ __('locale.Select') }}</option>
                    <option value="0" {{ old('type') == '0' ? 'selected' : '' }}>{{ __('locale.Entry') }}</option>
                    <option value="1" {{ old('type') == '1' ? 'selected' : '' }}>{{ __('locale.Exit') }}</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>{{ __('locale.Adjustment Type') }}</label>
                  <select name="fit_type" class="form-control">
                    <option value="1" {{ old('fit_type') == '1' ? 'selected' : '' }}>{{ __('locale.Adjustment Defective Product') }}</option>
                    <option value="2" {{ old('fit_type') == '2' ? 'selected' : '' }}>{{ __('locale.Adjustment Expired Product') }}</option>
                    <option value="3" {{ old('fit_type') == '3' ? 'selected' : '' }}>{{ __('locale.Adjustment Out of Stock') }}</option>
                    <option value="4" {{ old('fit_type') == '4' ? 'selected' : '' }}>{{ __('locale.Adjustment Product Incident') }}</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('locale.Existing Quantity') }}</label>
                  <input type="number" id="existing_qty" name="existing" class="form-control" step="0.001" value="{{ old('existing', 0) }}" readonly>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('locale.Replenishment Quantity') }}</label>
                  <input type="number" id="replenishment_qty" name="quantity" class="form-control" step="0.001" min="0" value="{{ old('quantity', 0) }}">
                  <input type="hidden" name="fit_quantity" id="fit_quantity" value="{{ old('fit_quantity', old('quantity', 0)) }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('locale.Total Quantity') }}</label>
                  <input type="number" id="total_qty" name="final" class="form-control" step="0.001" value="{{ old('final', 0) }}" readonly>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>{{ __('locale.Reason') }}</label>
              <textarea name="reason" class="form-control" rows="3">{{ old('reason') }}</textarea>
            </div>
            <div class="mt-4 d-flex justify-content-end">
              <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary mr-2">{{ __('locale.Back') }}</a>
              <button type="submit" class="btn btn-primary">{{ __('locale.Save') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@section('vendor-script')
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection

@section('page-script')
  <script>
    $(function () {
      function asNumber(value) {
        var num = parseFloat(value);
        return isNaN(num) ? 0 : num;
      }

      function syncTotals() {
        var existing = asNumber($('#existing_qty').val());
        var replenishment = asNumber($('#replenishment_qty').val());
        var type = $('#replenishment_type').val();
        var total = type === '1' ? (existing - replenishment) : (existing + replenishment);

        $('#fit_quantity').val(replenishment.toFixed(3));
        $('#total_qty').val(total.toFixed(3));
      }

      $('#inventory_product_id').select2({
        width: '100%',
        allowClear: true,
        placeholder: @json(__('locale.Search Inventory Product Placeholder')),
        ajax: {
          url: @json(route('inventory.products.search')),
          dataType: 'json',
          delay: 300,
          data: function (params) {
            return {
              q: params.term || '',
              page: params.page || 1
            };
          },
          processResults: function (data, params) {
            params.page = params.page || 1;
            return {
              results: data.results || [],
              pagination: {
                more: data.pagination && data.pagination.more
              }
            };
          },
          cache: true
        },
        minimumInputLength: 1,
        language: {
          inputTooShort: function () {
            return @json(__('locale.Search Inventory Product Hint'));
          }
        }
      });

      $('#inventory_product_id').on('select2:select', function (event) {
        var data = event.params && event.params.data ? event.params.data : {};
        var existing = asNumber(data.existing_stock);
        $('#existing_qty').val(existing.toFixed(3));
        syncTotals();
      });

      $('#inventory_product_id').on('select2:clear', function () {
        $('#existing_qty').val('0.000');
        syncTotals();
      });

      $('#replenishment_qty, #replenishment_type').on('input change', syncTotals);

      var selectedOption = $('#inventory_product_id').find('option:selected');
      if (selectedOption.length) {
        var existing = asNumber(selectedOption.data('existing'));
        if (existing > 0 || !$('#existing_qty').val()) {
          $('#existing_qty').val(existing.toFixed(3));
        }
      }
      syncTotals();
    });
  </script>
@endsection
