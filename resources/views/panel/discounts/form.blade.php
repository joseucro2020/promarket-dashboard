@extends('layouts/contentLayoutMaster')

@section('title', isset($discount) ? __('Edit Discount') : __('New Discount'))

@section('vendor-style')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
@php
  $discount = $discount ?? null;

  // Determine selected mode from old input or model `type`
  $selectedMode = old('discount_mode');
  if (!$selectedMode) {
    if (isset($discount->type)) {
      if ($discount->type === 'quantity_product') $selectedMode = 'quantity';
      elseif ($discount->type === 'minimum_purchase') $selectedMode = 'amount';
      elseif ($discount->type === 'quantity_purchase') $selectedMode = 'count';
      else $selectedMode = 'quantity';
    } else {
      $selectedMode = 'quantity';
    }
  }

  // Prepare values mapping from model fields to form fields
  $value_quantity = old('quantity_products', $discount->quantity_product ?? $discount->quantity_purchase ?? 0);
  $value_min_amount = old('min_amount', $discount->minimum_purchase ?? '');
  $value_category = old('category_id', $discount->category_id ?? '');
  $value_percentage = old('percentage', $discount->percentage ?? 0);
  $value_limit = old('limit', $discount->limit ?? 0);
  $value_name = old('name', $discount->name ?? '');
  $value_start = old('start', isset($discount->start) ? \Carbon\Carbon::parse($discount->start)->format('Y-m-d') : '');
  $value_end = old('end', isset($discount->end) ? \Carbon\Carbon::parse($discount->end)->format('Y-m-d') : '');
@endphp

<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
          <div class="card-header text-center">
          <h4 class="card-title">{{ isset($discount) ? __('Edit Discount') : __('Create new discount') }}</h4>
          <p class="mb-0 text-muted"><small>{{ __('Required fields') }} <span class="badge badge-danger align-text-top ml-1" aria-hidden="true">*</span></small></p>
        </div>
        <div class="card-body">
          <form id="discount-form" action="{{ isset($discount) ? route('discounts.update', $discount) : route('discounts.store') }}" method="POST">
            @csrf
            @if(isset($discount)) @method('PUT') @endif
            <div class="row">
              <div class="col-md-8">
                <div class="form-group">
                  <label for="name">{{ __('Discount name') }} <span class="text-danger">*</span></label>
                  <input type="text" id="name" name="name" class="form-control" value="{{ $value_name }}">
                </div>
              </div>
              <div id="group-limit" class="col-md-4">
                <div class="form-group">
                  <label for="limit">{{ __('Limit uses per client') }}</label>
                  <input type="number" id="limit" name="limit" class="form-control" min="0" value="{{ $value_limit }}">
                </div>
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="start">{{ __('Start date') }} <span class="text-danger">*</span></label>
                  <input type="date" id="start" name="start" class="form-control" value="{{ $value_start }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="end">{{ __('End date') }} <span class="text-danger">*</span></label>
                  <input type="date" id="end" name="end" class="form-control" value="{{ $value_end }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="percentage">{{ __('Discount percentage') }} <span class="text-danger">*</span></label>
                  <input type="number" step="0.01" min="0" max="100" id="percentage" name="percentage" class="form-control" value="{{ $value_percentage }}">
                </div>
              </div>
            </div>

            <hr />

            <div class="row mt-2 mb-3">
              <div class="col-4 d-flex align-items-center">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="discount_mode" id="mode_qty" value="quantity" {{ ($selectedMode=='quantity') ? 'checked' : '' }}>
                  <label class="form-check-label" for="mode_qty">{{ __('Discount by product quantity') }}</label>
                </div>
              </div>
              <div class="col-4 d-flex align-items-center">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="discount_mode" id="mode_amount" value="amount" {{ ($selectedMode=='amount') ? 'checked' : '' }}>
                  <label class="form-check-label" for="mode_amount">{{ __('Discount by purchase amount') }}</label>
                </div>
              </div>
              <div class="col-4 d-flex align-items-center">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="discount_mode" id="mode_count" value="count" {{ ($selectedMode=='count') ? 'checked' : '' }}>
                  <label class="form-check-label" for="mode_count">{{ __('Discount by number of purchases') }}</label>
                </div>
              </div>
            </div>

            <div class="row">
              <div id="col-quantity" class="col-md-6">
                <div class="form-group">
                  <div id="group-quantity">
                    <label id="label-quantity" for="quantity_products" data-count-label="{{ __('Number of purchases required to apply discount') }}">{{ __('Quantity of products to apply discount') }} <span class="text-danger">*</span></label>
                    <input type="number" id="quantity_products" name="quantity_products" class="form-control" min="0" value="{{ $value_quantity }}">
                  </div>
                  <div id="group-min-amount" style="display:none;">
                    <label for="min_amount">{{ __('Minimum amount') }} <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" id="min_amount" name="min_amount" class="form-control" min="0" value="{{ $value_min_amount }}">
                  </div>
                </div>
              </div>
              <div id="group-category" class="col-md-6">
                <div class="form-group">
                  <label for="categories_products">{{ __('Select categories/products') }} <span class="text-danger">*</span></label>
                  <select id="categories_products" name="category_id" class="form-control select2" data-placeholder="{{ __('Select categories/products') }}">
                    <option value="">{{ __('Select') }}</option>
                    @if(isset($categories))
                      @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ ($value_category == $cat->id) ? 'selected' : '' }}>{{ e($cat->name) }}</option>
                      @endforeach
                    @endif
                  </select>
                </div>
              </div>
            </div>

            <!-- Products picker: visible when discount_mode == quantity -->
            <div id="products-picker" class="row mt-3" style="{{ ($selectedMode=='quantity') ? '' : 'display:none;' }}">
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Select products') }}</h5>
                  </div>
                  <div class="card-body">
                    <div class="row mb-2">
                      <div class="col-md-12">
                        <div class="form-row">
                          <div class="col-md-6 mb-2">
                            <select id="filter-category" class="form-control select2" data-placeholder="{{ __('Category') }}">
                              <option value="">{{ __('All categories') }}</option>
                              @if(isset($categories))
                                @foreach($categories as $cat)
                                  <option value="{{ $cat->id }}">{{ e($cat->name) }}</option>
                                @endforeach
                              @endif
                            </select>
                          </div>
                          <div class="col-md-6 mb-2">
                            <select id="filter-subcategory" class="form-control select2" data-placeholder="{{ __('Subcategory') }}">
                              <option value="">{{ __('All subcategories') }}</option>
                            </select>
                          </div>
                        </div>
                        <div class="form-row">
                          <div class="col-md-12 mb-2">
                            <select id="filter-subsubcategory" class="form-control select2" data-placeholder="{{ __('Sub-subcategory') }}">
                              <option value="">{{ __('All sub-subcategories') }}</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>

                    <table id="discounts-products-table" class="table table-striped table-bordered" style="width:100%">
                      <thead>
                        <tr>
                          <th>{{ __('') }}</th>
                          <th>{{ __('Product') }}</th>
                          <th>{{ __('Category') }}</th>
                          <th>{{ __('Price') }}</th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                    </table>

                    <small class="text-muted">{{ __('Select products to include in this discount. Click Add to move products to the right column.') }}</small>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Selected products for discount') }}</h5>
                  </div>
                  <div class="card-body">
                    <div id="selected-products-column">
                      @if(isset($discount) && $selectedMode == 'quantity' && $discount->products->count())
                        @foreach($discount->products as $p)
                          <input type="hidden" name="products_id[]" value="{{ $p->id }}">
                          <div class="d-flex align-items-center mb-1 selected-product-row" data-id="{{ $p->id }}">
                            <span class="mr-2">{{ e($p->name) }}</span>
                            <a href="#" class="btn btn-sm btn-outline-danger remove-product" data-id="{{ $p->id }}">{{ __('Remove') }}</a>
                          </div>
                        @endforeach
                      @else
                        <p class="text-muted">{{ __('No products selected yet.') }}</p>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>

            @if(isset($discount) && $selectedMode == 'quantity' && $discount->products->count())
              <div id="selected-products" class="mt-2">
                @foreach($discount->products as $p)
                  <input type="hidden" name="products_id[]" value="{{ $p->id }}">
                  <span class="badge badge-primary mr-1 selected-product" data-id="{{ $p->id }}">{{ e($p->name) }} <a href="#" class="text-white ml-1 remove-product" data-id="{{ $p->id }}">&times;</a></span>
                @endforeach
              </div>
            @endif

            <div class="mt-4 d-flex justify-content-end">
              <a href="{{ route('discounts.index') }}" class="btn btn-outline-secondary mr-2">{{ __('Back') }}</a>
              <button type="submit" id="discount-submit" class="btn btn-primary" disabled>{{ isset($discount) ? __('Update') : __('Save') }}</button>
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    $(function() {
      // Init Select2
      $('#categories_products').select2({
        placeholder: '{{ __('Select categories/products') }}',
        allowClear: true,
        width: '100%',
        closeOnSelect: false
      });

      // Init product filters select2
      $('#filter-category, #filter-subcategory, #filter-subsubcategory').select2({
        width: '100%'
      });

      // DataTable for products (server-side)
      var productsTable = $('#discounts-products-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '{{ route('discounts.products.data') }}',
          data: function(d) {
            d.category = $('#filter-category').val();
            d.subcategory = $('#filter-subcategory').val();
            d.subsubcategory = $('#filter-subsubcategory').val();
          }
        },
        columns: [
          { data: 'select', orderable: false, searchable: false },
          { data: 'name' },
          { data: 'category' },
          { data: 'price' }
        ],
        drawCallback: function() {
          // attach click handlers for select buttons
          $('.btn-add-product').off('click').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            // prevent duplicates
            if ($('input[name="products_id[]"][value="' + id + '"]').length === 0) {
              $('<input>').attr({type: 'hidden', name: 'products_id[]', value: id}).appendTo('#discount-form');
              var row = $('<div class="d-flex align-items-center mb-1 selected-product-row" data-id="'+id+'"><span class="mr-2">'+name+'</span><a href="#" class="btn btn-sm btn-outline-danger remove-product" data-id="'+id+'">{{ __('Remove') }}</a></div>');
              if ($('#selected-products-column').length === 0) {
                // fallback: append to form
                $('<div id="selected-products-column" class="mt-2"></div>').appendTo('#discount-form');
              }
              // if message 'No products selected yet.' exists, remove it
              $('#selected-products-column').find('.text-muted').remove();
              $('#selected-products-column').append(row);
            }
          });
          // remove handler
          $('.remove-product').off('click').on('click', function(e){
            e.preventDefault();
            var id = $(this).data('id');
            $('input[name="products_id[]"][value="'+id+'"]').remove();
            $('.selected-product-row[data-id="'+id+'"]').remove();
          });
        }
      });

      // Reload subcategory options when category changes (simple client-side: server endpoint required for full data)
      $('#filter-category').on('change', function() {
        // clear children selects
        $('#filter-subcategory').html('<option value="">{{ __('All subcategories') }}</option>').trigger('change');
        $('#filter-subsubcategory').html('<option value="">{{ __('All sub-subcategories') }}</option>').trigger('change');
        productsTable.ajax.reload();
      });
      $('#filter-subcategory, #filter-subsubcategory').on('change', function() {
        productsTable.ajax.reload();
      });

      // Toggle submit button based on required fields
      function toggleSubmit() {
        var name = $('#name').val() && $('#name').val().trim().length > 0;
        var start = $('#start').val();
        var end = $('#end').val();
        var percentage = $('#percentage').val() !== undefined && $('#percentage').val() !== '';
        var category = $('#categories_products').val();
        var mode = $('input[name="discount_mode"]:checked').val();
        var qty = $('#quantity_products').val() !== undefined && $('#quantity_products').val() !== '';
        var minAmount = $('#min_amount').val() !== undefined && $('#min_amount').val() !== '';

        var modeOk = false;
        if (mode === 'quantity') modeOk = qty;
        else if (mode === 'amount') modeOk = minAmount;
        else modeOk = true; // count mode or others require no extra field

        var categoryNeeded = (mode !== 'amount');
        var ok = name && start && end && percentage && modeOk && (categoryNeeded ? category : true);
        $('#discount-submit').prop('disabled', !ok);
      }

      function toggleModeUI() {
        var mode = $('input[name="discount_mode"]:checked').val();
        if (mode === 'amount') {
          // amount: show min amount (half width), hide quantity and category
          $('#group-quantity').hide();
          $('#group-min-amount').show();
          $('#group-category').hide();
          $('#col-quantity').removeClass('col-md-12').addClass('col-md-6');
          $('#group-limit').show();
          $('#products-picker').hide();
        } else if (mode === 'quantity') {
          // quantity: show quantity and category side-by-side
          $('#group-min-amount').hide();
          $('#group-quantity').show();
          $('#group-category').show();
          $('#group-limit').show();
          $('#col-quantity').removeClass('col-md-12').addClass('col-md-6');
          // restore quantity label
          $('#label-quantity').text('{{ __('Quantity of products to apply discount') }}');
          $('#products-picker').show();
        } else if (mode === 'count') {
          // count: show quantity full width but label refers to number of purchases
          $('#group-min-amount').hide();
          $('#group-quantity').show();
          $('#group-category').hide();
          $('#col-quantity').removeClass('col-md-6').addClass('col-md-12');
          // swap label to purchases text
          $('#label-quantity').text($('#label-quantity').data('count-label'));
          // hide limit uses per client when counting purchases
          $('#group-limit').hide();
          $('#products-picker').hide();
        } else {
          // default: show category and hide others
          $('#group-min-amount').hide();
          $('#group-quantity').hide();
          $('#group-category').show();
          $('#col-quantity').removeClass('col-md-12').addClass('col-md-6');
          $('#group-limit').show();
          $('#products-picker').hide();
        }
        toggleSubmit();
      }

      $('#name, #start, #end, #percentage, #categories_products, #quantity_products, #min_amount').on('input change', toggleSubmit);
      $('input[name="discount_mode"]').on('change', toggleModeUI);
      toggleModeUI();

      // AJAX submit to avoid page reloads
      $('#discount-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var url = $form.attr('action');
        var method = ($form.find('input[name="_method"]').val() || 'POST').toUpperCase();
        var submitBtn = $('#discount-submit');
        submitBtn.prop('disabled', true);

        var formData = new FormData(this);

        $.ajax({
          url: url,
          type: method === 'GET' ? 'GET' : 'POST',
          data: formData,
          processData: false,
          contentType: false,
          headers: {
            'X-HTTP-Method-Override': method,
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $form.find('input[name="_token"]').val()
          },
          success: function(response) {
                        var msg = (response && response.message) ? response.message : '{{ __('Saved successfully') }}';
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: msg,
              showConfirmButton: false,
              timer: 2000
            }).then(function() {
              var redirect = (response && response.redirect) ? response.redirect : '{{ route('discounts.index') }}';
              window.location.href = redirect;
            });
          },
          error: function(xhr) {
            submitBtn.prop('disabled', false);
            if (xhr.status === 422) {
              var errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
              var errorsHtml = '<ul style="text-align:left;margin:0;padding-left:20px;">';
              Object.keys(errors).forEach(function(k) {
                errors[k].forEach(function(msg) {
                  errorsHtml += '<li>' + msg + '</li>';
                });
              });
              errorsHtml += '</ul>';
              Swal.fire({
                icon: 'error',
                title: '{{ __('Please fix the following errors') }}',
                html: errorsHtml,
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: '{{ __('An error occurred') }}',
                text: xhr.responseText || '{{ __('Please try again') }}'
              });
            }
          }
        });
      });
    });
  </script>
@endsection
