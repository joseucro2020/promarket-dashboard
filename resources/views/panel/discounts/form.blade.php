@extends('layouts/contentLayoutMaster')

@section('title', isset($discount) ? __('locale.Edit Discount') : __('locale.New Discount'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
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
          <h4 class="card-title">{{ isset($discount) ? __('locale.Edit Discount') : __('locale.Create new discount') }}</h4>
          <p class="mb-0 text-muted"><small>{{ __('locale.Required fields') }} <span class="badge badge-danger align-text-top ml-1" aria-hidden="true">*</span></small></p>
        </div>
        <div class="card-body">
          <form id="discount-form" action="{{ isset($discount) ? route('discounts.update', $discount) : route('discounts.store') }}" method="POST">
            @csrf
            @if(isset($discount)) @method('PUT') @endif
            <div class="row">
              <div class="col-md-8">
                <div class="form-group">
                  <label for="name">{{ __('locale.Discount name') }} <span class="text-danger">*</span></label>
                  <input type="text" id="name" name="name" class="form-control" value="{{ $value_name }}">
                </div>
              </div>
              <div id="group-limit" class="col-md-4">
                <div class="form-group">
                  <label for="limit">{{ __('locale.Limit uses per client') }}</label>
                  <input type="number" id="limit" name="limit" class="form-control" min="0" value="{{ $value_limit }}">
                </div>
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="start">{{ __('locale.Start date') }} <span class="text-danger">*</span></label>
                  <input type="date" id="start" name="start" class="form-control" value="{{ $value_start }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="end">{{ __('locale.End date') }} <span class="text-danger">*</span></label>
                  <input type="date" id="end" name="end" class="form-control" value="{{ $value_end }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="percentage">{{ __('locale.Discount percentage') }} <span class="text-danger">*</span></label>
                  <input type="number" step="0.01" min="0" max="100" id="percentage" name="percentage" class="form-control" value="{{ $value_percentage }}">
                </div>
              </div>
            </div>

            <hr />

            <div class="row mt-2 mb-3">
              <div class="col-4 d-flex align-items-center">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="discount_mode" id="mode_qty" value="quantity" {{ ($selectedMode=='quantity') ? 'checked' : '' }}>
                  <label class="form-check-label" for="mode_qty">{{ __('locale.Discount by product quantity') }}</label>
                </div>
              </div>
              <div class="col-4 d-flex align-items-center">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="discount_mode" id="mode_amount" value="amount" {{ ($selectedMode=='amount') ? 'checked' : '' }}>
                  <label class="form-check-label" for="mode_amount">{{ __('locale.Discount by purchase amount') }}</label>
                </div>
              </div>
              <div class="col-4 d-flex align-items-center">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="discount_mode" id="mode_count" value="count" {{ ($selectedMode=='count') ? 'checked' : '' }}>
                  <label class="form-check-label" for="mode_count">{{ __('locale.Discount by number of purchases') }}</label>
                </div>
              </div>
            </div>

            <div class="row">
              <div id="col-quantity" class="col-md-6">
                <div class="form-group">
                  <div id="group-quantity">
                    <label id="label-quantity" for="quantity_products" data-count-label="{{ __('locale.Number of purchases required to apply discount') }}">{{ __('locale.Quantity of products to apply discount') }} <span class="text-danger">*</span></label>
                    <input type="number" id="quantity_products" name="quantity_products" class="form-control" min="0" value="{{ $value_quantity }}">
                  </div>
                  <div id="group-min-amount" style="display:none;">
                    <label for="min_amount">{{ __('locale.Minimum amount to apply discount') }} <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" id="min_amount" name="min_amount" class="form-control" min="0" value="{{ $value_min_amount }}">
                  </div>
                </div>
              </div>
              <div id="group-category" class="col-md-6">
                <div class="form-group">
                  <label for="categories_products">{{ __('locale.Select categories/products') }} <span class="text-danger">*</span></label>
                  <select id="categories_products" name="category_id" class="form-control select2" data-placeholder="{{ __('locale.Select categories/products') }}">
                    <option value="">{{ __('locale.Select') }}</option>
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
                    <h5 class="card-title mb-0">{{ __('locale.Select products') }}</h5>
                  </div>
                  <div class="card-body">
                    <div class="row mb-2">
                      <div class="col-md-12">
                        <div class="form-row">
                          <div class="col-md-6 mb-2">
                            <select id="filter-category" class="form-control select2" data-placeholder="{{ __('locale.Category') }}">
                              <option value="">{{ __('locale.All categories') }}</option>
                              @if(isset($categories))
                                @foreach($categories as $cat)
                                  <option value="{{ $cat->id }}">{{ e($cat->name) }}</option>
                                @endforeach
                              @endif
                            </select>
                          </div>
                          <div class="col-md-6 mb-2">
                            <select id="filter-subcategory" class="form-control select2" data-placeholder="{{ __('locale.Subcategory') }}">
                              <option value="">{{ __('locale.All subcategories') }}</option>
                              @if(isset($subcategories))
                                @foreach($subcategories as $sub)
                                  <option value="{{ $sub->id }}" data-category="{{ $sub->category_id }}">{{ e($sub->name) }}</option>
                                @endforeach
                              @endif
                            </select>
                          </div>
                        </div>
                        <div class="form-row">
                          <div class="col-md-12 mb-2">
                            <select id="filter-subsubcategory" class="form-control select2" data-placeholder="{{ __('locale.Sub-subcategory') }}">
                              <option value="">{{ __('locale.All sub-subcategories') }}</option>
                              @if(isset($subsub))
                                @foreach($subsub as $ss)
                                  <option value="{{ $ss->id }}" data-subcategory="{{ $ss->subcategory_id }}">{{ e($ss->name) }}</option>
                                @endforeach
                              @endif
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>

                    <table id="discounts-products-table" class="table table-striped table-bordered" style="width:100%">
                      <thead>
                        <tr>
                          <th class="text-center">{{ __('locale.Logo') }}</th>
                          <th>{{ __('locale.Product') }}</th>
                          <th>{{ __('locale.Category') }}</th>
                          <th class="text-end">{{ __('locale.Actions') }}</th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                    </table>

                    <small class="text-muted">{{ __('locale.Select products to include in this discount. Click Add to move products to the right column.') }}</small>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('locale.Selected products for discount') }}</h5>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-bordered selected-products-table w-100">
                        <thead>
                          <tr>
                            <th>{{ __('Product') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                          </tr>
                        </thead>
                        <tbody id="selected-products-column">
                          @if(isset($discount) && $selectedMode == 'quantity' && $discount->products->count())
                            @foreach($discount->products as $p)
                              <tr class="selected-product-row" data-id="{{ $p->id }}">
                                <td>{{ e($p->name) }}</td>
                                <td class="text-end">
                                  <button type="button" class="btn btn-icon btn-flat-danger remove-product" data-id="{{ $p->id }}" data-toggle="tooltip" data-placement="top" title="{{ __('locale.Remove') }}">
                                    <i data-feather="trash"></i>
                                  </button>
                                  <input type="hidden" name="products_id[]" value="{{ $p->id }}">
                                </td>
                              </tr>
                            @endforeach
                          @endif
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>



            <div class="mt-4 d-flex justify-content-end">
              <a href="{{ route('discounts.index') }}" class="btn btn-outline-secondary mr-2">{{ __('locale.Back') }}</a>
              <button type="submit" id="discount-submit" class="btn btn-primary" disabled>{{ isset($discount) ? __('locale.Update') : __('locale.Save') }}</button>
            </div>
          </form>
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
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection

@section('page-script')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    $(function() {
      // Init Select2
      $('#categories_products').select2({
        placeholder: '{{ __('locale.Select categories/products') }}',
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
        responsive: true,
        order: [[1, 'asc']],
        ajax: {
          url: '{{ route('discounts.products.data') }}',
          data: function(d) {
            d.category = $('#filter-category').val();
            d.subcategory = $('#filter-subcategory').val();
            d.subsubcategory = $('#filter-subsubcategory').val();
          }
        },
        columns: [
          { data: 'image', orderable: false, searchable: false },
          { data: 'name' },
          { data: 'category' },
          { data: 'select', orderable: false, searchable: false }
        ],
          language: {
          url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        },
        drawCallback: function() {
          if (window.feather) {
            feather.replace({ width: 14, height: 14 });
          }
        }
      });

      var selectedProductsTable = $('.selected-products-table').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
          { orderable: false, targets: -1 }
        ],
        language: {
          url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json',
          emptyTable: '{{ __('No products selected yet.') }}'
        },
        drawCallback: function() {
          if (window.feather) {
            feather.replace({ width: 14, height: 14 });
          }
        }
      });

      // Add product to discount (offers-form behavior)
      $(document).on('click', '.btn-add-product', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        if ($('input[name="products_id[]"][value="' + id + '"]').length) {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: '{{ __('Product already added') }}',
            showConfirmButton: false,
            timer: 2000
          });
          return;
        }

        const actionHtml = '<button type="button" class="btn btn-icon btn-flat-danger remove-product" data-id="' + id + '" data-toggle="tooltip" data-placement="top" title="{{ __('Remove') }}"><i data-feather="trash"></i></button>' +
          '<input type="hidden" name="products_id[]" value="' + id + '">';
        const rowNode = selectedProductsTable.row.add([name, actionHtml]).draw().node();
        $(rowNode).attr('data-id', id).addClass('selected-product-row');
        if (window.feather) {
          feather.replace({ width: 14, height: 14 });
        }
      });

      // Remove product from discount
      $(document).on('click', '.remove-product', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('input[name="products_id[]"][value="' + id + '"]').remove();
        var row = $('.selected-products-table tbody tr[data-id="' + id + '"]');
        if (row.length) {
          selectedProductsTable.row(row).remove().draw();
        }
      });

      // Dynamic subcategory/subsub filters (same behavior as offers form)
      const $subcat = $('#filter-subcategory');
      const $subsub = $('#filter-subsubcategory');
      const subcatOptions = $subcat.find('option').clone();
      const subsubOptions = $subsub.find('option').clone();

      $('#filter-category').on('change', function() {
        const cat = $(this).val();
        if (!cat) {
          $subcat.empty().append(subcatOptions);
          $subsub.empty().append(subsubOptions);
          productsTable.ajax.reload();
          return;
        }

        const url = '/panel/promociones/' + cat + '/subcategorias';
        $.getJSON(url, function(response) {
          $subcat.empty().append('<option value="">{{ __('All subcategories') }}</option>');
          response.subcategory.forEach(function(s) {
            $subcat.append('<option value="' + s.id + '">' + s.name + '</option>');
          });

          $subsub.empty().append('<option value="">{{ __('All sub-subcategories') }}</option>');
          productsTable.ajax.reload();
        });
      });

      $subcat.on('change', function() {
        const sub = $(this).val();
        if (!sub) {
          $subsub.empty().append(subsubOptions);
          productsTable.ajax.reload();
          return;
        }

        $subsub.empty().append(subsubOptions.filter(function() {
          const val = $(this).attr('value');
          if (!val) return true;
          return $(this).data('subcategory').toString() === sub.toString();
        }));

        productsTable.ajax.reload();
      });

      $('#filter-subsubcategory').on('change', function() {
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

        var categoryNeeded = $('#group-category').is(':visible');
        var ok = name && start && end && percentage && modeOk && (categoryNeeded ? category : true);
        $('#discount-submit').prop('disabled', !ok);
      }

      function toggleModeUI() {
        var mode = $('input[name="discount_mode"]:checked').val();
        var modeId = $('input[name="discount_mode"]:checked').attr('id');
        console.log('Discount mode selected:', { mode: mode, id: modeId });
        if (mode === 'amount') {
          // amount: show min amount, hide quantity, category, and products
          $('#group-quantity').hide();
          $('#group-min-amount').show();
          $('#group-category').hide();
          $('#col-quantity').removeClass('col-md-6').addClass('col-md-12');
          $('#group-limit').show();
          $('#products-picker').hide();
        } else if (mode === 'quantity') {
          // quantity: show quantity input and products segment only
          $('#group-min-amount').hide();
          $('#group-quantity').show();
          $('#group-category').hide();
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
                        var msg = (response && response.message) ? response.message : '{{ __('locale.Saved successfully') }}';
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
