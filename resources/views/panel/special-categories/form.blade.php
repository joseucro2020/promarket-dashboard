@extends('layouts/contentLayoutMaster')

@section('title', isset($specialCategory) ? __('locale.Edit Special Category') : __('locale.New Special Category'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('page-style')
  <style>
    .special-category-search-panel {
      border: 1px solid #ebe9f1;
      border-radius: .428rem;
      background: #fff;
      padding: 1.5rem;
    }

    .special-category-product-list {
      max-height: 360px;
      overflow-y: auto;
      border: 1px solid #ebe9f1;
      border-radius: .357rem;
      background: #fff;
    }

    .special-category-product-item {
      display: flex;
      align-items: center;
      gap: .75rem;
      margin: 0;
      padding: .9rem 1rem;
      border-bottom: 1px solid #ebe9f1;
      cursor: pointer;
    }

    .special-category-product-item:last-child {
      border-bottom: 0;
    }

    .special-category-product-item:hover {
      background: rgba(115, 103, 240, .04);
    }

    .special-category-product-item input {
      margin-top: 0;
      flex-shrink: 0;
    }

    .special-category-product-meta {
      font-size: .857rem;
      color: #6e6b7b;
    }

    .special-category-selected-box {
      min-height: 360px;
      border: 1px dashed #d8d6de;
      border-radius: .357rem;
      padding: 1rem;
      background: #fcfcfc;
    }

    .special-category-selected-item {
      display: inline-flex;
      align-items: center;
      padding: .45rem .75rem;
      margin: 0 .5rem .5rem 0;
      border-radius: 999px;
      background: rgba(115, 103, 240, .12);
      color: #5e5873;
      font-size: .857rem;
    }

    .special-category-search-icon {
      position: absolute;
      left: .9rem;
      top: 50%;
      transform: translateY(-50%);
      color: #6e6b7b;
      pointer-events: none;
    }

    .special-category-search-input {
      padding-left: 2.5rem;
    }
  </style>
@endsection

@section('content')
@php
  $selectedProductIds = collect();

  if (old('products')) {
    $selectedProductIds = collect(json_decode(old('products'), true) ?: []);
  } elseif (isset($specialCategory)) {
    $selectedProductIds = $specialCategory->products->pluck('id');
  }

  $selectedProductIds = $selectedProductIds->map(function ($id) {
    return (int) $id;
  })->values();

  $defaultSpecialType = old('tipo_special', $selectedSpecialType ?? array_key_first($specialTypeOptions));
  $defaultOrderType = old('tipo_order', $selectedOrderType ?? array_key_first($orderTypeOptions));

  $categoriesJson = $normalCategories->map(function ($category) {
    return [
      'id' => (int) $category->id,
      'name' => $category->name,
      'subcategories' => $category->subcategories->map(function ($subcategory) {
        return [
          'id' => (int) $subcategory->id,
          'name' => $subcategory->name,
          'sub_subcategories' => $subcategory->sub_subcategories->map(function ($subsubcategory) {
            return [
              'id' => (int) $subsubcategory->id,
              'name' => $subsubcategory->name,
            ];
          })->values()->all(),
        ];
      })->values()->all(),
    ];
  })->values();

  $productsJson = $products->map(function ($product) {
    return [
      'id' => (int) $product->id,
      'name' => $product->name,
      'category_id' => $product->category_id ? (int) $product->category_id : null,
      'subcategory_id' => $product->subcategory_id ? (int) $product->subcategory_id : null,
      'subsubcategory_id' => $product->subsubcategory_id ? (int) $product->subsubcategory_id : null,
    ];
  })->values();
@endphp

<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom">
          <h4 class="card-title mb-0">{{ isset($specialCategory) ? __('locale.Edit Special Category') : __('locale.New Special Category') }}</h4>
        </div>
        <div class="card-body pt-2">
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0 pl-1">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form method="POST" action="{{ isset($specialCategory) ? route('special-categories.update', $specialCategory->id) : route('special-categories.store') }}" id="specialCategoryForm">
            @csrf
            @if(isset($specialCategory))
              @method('PUT')
            @endif

            <input type="hidden" name="products" id="products" value='@json($selectedProductIds)'>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name">{{ __('locale.Name') }}</label>
                  <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $specialCategory->name ?? '') }}" required>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="slug">{{ __('locale.Slug') }}</label>
                  <input type="text" id="slug" class="form-control" value="{{ old('slug', $specialCategory->slug ?? '') }}" readonly>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="order">{{ __('locale.Order') }}</label>
                  <input type="number" id="order" name="order" class="form-control" min="1" value="{{ old('order', $specialCategory->order ?? $nextSpecialCategoryOrder) }}">
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label for="tipo_special">{{ __('locale.Special Category Type') }}</label>
                  <select id="tipo_special" name="tipo_special" class="form-control">
                    @foreach($specialTypeOptions as $value => $label)
                      <option value="{{ $value }}" {{ (string) $defaultSpecialType === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label for="tipo_order">{{ __('locale.Order Type') }}</label>
                  <select id="tipo_order" name="tipo_order" class="form-control">
                    @foreach($orderTypeOptions as $value => $label)
                      <option value="{{ $value }}" {{ (string) $defaultOrderType === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label for="slider_quantity">{{ __('locale.Carousel Quantity') }}</label>
                  <select id="slider_quantity" name="slider_quantity" class="form-control">
                    @foreach([6, 12, 18, 24, 30] as $quantity)
                      <option value="{{ $quantity }}" {{ (string) old('slider_quantity', $specialCategory->slider_quantity ?? 12) === (string) $quantity ? 'selected' : '' }}>{{ $quantity }}</option>
                    @endforeach
                  </select>
                  <small class="text-muted">{{ __('locale.Recommended maximum 30') }}</small>
                </div>
              </div>
            </div>

            <div class="row mb-2">
              <div class="col-md-3">
                <div class="form-group mb-0">
                  <label>{{ __('locale.Status') }}</label>
                  <div class="d-flex flex-wrap align-items-center mt-50">
                    <div class="custom-control custom-radio mr-2">
                      <input type="radio" id="status_active" name="status" class="custom-control-input" value="1" {{ old('status', $specialCategory->status ?? '1') == '1' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="status_active">{{ __('locale.Active') }}</label>
                    </div>
                    <div class="custom-control custom-radio">
                      <input type="radio" id="status_inactive" name="status" class="custom-control-input" value="0" {{ old('status', $specialCategory->status ?? '1') == '0' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="status_inactive">{{ __('locale.Inactive') }}</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="special-category-search-panel mb-2">
              <h4 class="mb-2">{{ __('locale.Searcher') }}</h4>

              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="filter_category">{{ __('locale.Select Category') }}</label>
                    <select id="filter_category" class="form-control">
                      <option value="">{{ __('locale.Select') }}</option>
                      @foreach($normalCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label for="filter_subcategory">{{ __('locale.Select Subcategory') }}</label>
                    <select id="filter_subcategory" class="form-control">
                      <option value="">{{ __('locale.Select') }}</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label for="filter_subsubcategory">{{ __('locale.Select Sub-Subcategory') }}</label>
                    <select id="filter_subsubcategory" class="form-control">
                      <option value="">{{ __('locale.Select') }}</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-7">
                <div class="card mb-0">
                  <div class="card-header border-bottom pb-1">
                    <h5 class="mb-0">{{ __('locale.Products') }}</h5>
                  </div>
                  <div class="card-body pt-1">
                    <div class="d-flex align-items-center mb-2">
                      <label class="mb-0 mr-2"><input type="checkbox" id="select-all-products"> {{ __('locale.Seleccionar Todos') }}</label>
                      <button type="button" id="add-selected-products" class="btn btn-sm btn-primary ml-auto">{{ __('locale.Add Selected') }}</button>
                    </div>

                    <div class="table-responsive">
                      <table class="table table-striped table-bordered w-100" id="availableProductsTable">
                        <thead>
                          <tr>
                            <th class="text-center" style="width: 70px;">{{ __('locale.Select') }}</th>
                            <th>{{ __('locale.Name') }}</th>
                            <th class="text-center" style="width: 90px;">{{ __('locale.Actions') }}</th>
                          </tr>
                        </thead>
                        <tbody></tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-5 mt-2 mt-lg-0">
                <div class="card mb-0">
                  <div class="card-header border-bottom pb-1 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">{{ __('locale.Selected Products') }}</h5>
                    <span class="badge badge-light-primary" id="selectedProductsCount">0</span>
                  </div>
                  <div class="card-body pt-1">
                    <div class="table-responsive">
                      <table class="table table-striped table-bordered w-100" id="selectedProductsTable">
                        <thead>
                          <tr>
                            <th style="width: 80px;">{{ __('locale.ID') }}</th>
                            <th>{{ __('locale.Name') }}</th>
                            <th class="text-center" style="width: 90px;">{{ __('locale.Actions') }}</th>
                          </tr>
                        </thead>
                        <tbody></tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-3 d-flex justify-content-end">
              <a href="{{ route('special-categories.index') }}" class="btn btn-outline-secondary mr-1">{{ __('locale.Back') }}</a>
              <button type="submit" class="btn btn-primary">{{ isset($specialCategory) ? __('locale.Update special category') : __('locale.Save special category') }}</button>
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
@endsection

@section('page-script')
  <script>
    (function () {
      const categories = @json($categoriesJson);

      const products = @json($productsJson);

      const strings = {
        select: @json(__('locale.Select')),
        noProducts: @json(__('locale.No products found.')),
      };

      const categorySelect = document.getElementById('filter_category');
      const subcategorySelect = document.getElementById('filter_subcategory');
      const subsubcategorySelect = document.getElementById('filter_subsubcategory');
      const selectAllProductsCheckbox = document.getElementById('select-all-products');
      const addSelectedProductsButton = document.getElementById('add-selected-products');
      const selectedCount = document.getElementById('selectedProductsCount');
      const productsInput = document.getElementById('products');
      const nameInput = document.getElementById('name');
      const slugInput = document.getElementById('slug');
      const availableTableElement = $('#availableProductsTable');
      const selectedTableElement = $('#selectedProductsTable');

      const selectedIds = new Set(JSON.parse(productsInput.value || '[]').map(function (value) {
        return Number(value);
      }));

      let availableProductsTable = null;
      let selectedProductsTable = null;

      function slugify(text) {
        return (text || '')
          .toString()
          .normalize('NFD')
          .replace(/[\u0300-\u036f]/g, '')
          .toLowerCase()
          .trim()
          .replace(/[^a-z0-9]+/g, '-')
          .replace(/^-+|-+$/g, '');
      }

      function syncSlug() {
        slugInput.value = slugify(nameInput.value);
      }

      function syncSelectedProductsInput() {
        productsInput.value = JSON.stringify(Array.from(selectedIds));
      }

      function escapeHtml(value) {
        return String(value)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }

      function renderSelectOptions(select, items) {
        const options = ['<option value="">' + strings.select + '</option>'];
        items.forEach(function (item) {
          options.push('<option value="' + item.id + '">' + item.name + '</option>');
        });
        select.innerHTML = options.join('');
      }

      function getSelectedCategory() {
        return categories.find(function (category) {
          return String(category.id) === categorySelect.value;
        });
      }

      function getSelectedSubcategory() {
        const category = getSelectedCategory();
        if (!category) {
          return null;
        }

        return category.subcategories.find(function (subcategory) {
          return String(subcategory.id) === subcategorySelect.value;
        }) || null;
      }

      function filterProducts() {
        const categoryId = categorySelect.value ? Number(categorySelect.value) : null;
        const subcategoryId = subcategorySelect.value ? Number(subcategorySelect.value) : null;
        const subsubcategoryId = subsubcategorySelect.value ? Number(subsubcategorySelect.value) : null;

        return products.filter(function (product) {
          const matchesCategory = !categoryId || product.category_id === categoryId;
          const matchesSubcategory = !subcategoryId || product.subcategory_id === subcategoryId;
          const matchesSubsubcategory = !subsubcategoryId || product.subsubcategory_id === subsubcategoryId;

          return matchesCategory && matchesSubcategory && matchesSubsubcategory;
        });
      }

      function renderSelectedProductsTable() {
        const selectedProducts = products.filter(function (product) {
          return selectedIds.has(product.id);
        });

        selectedCount.textContent = selectedProducts.length;
        syncSelectedProductsInput();

        const rows = selectedProducts.map(function (product) {
          return [
            product.id,
            escapeHtml(product.name),
            '<button type="button" class="btn btn-icon btn-flat-danger remove-selected-product" data-id="' + product.id + '" title="{{ __('locale.Delete') }}"><i data-feather="trash"></i></button>'
          ];
        });

        if (!selectedProductsTable) {
          selectedProductsTable = selectedTableElement.DataTable({
            responsive: true,
            paging: false,
            searching: false,
            info: false,
            order: [[0, 'asc']],
            language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
            columnDefs: [{ orderable: false, targets: -1 }],
            drawCallback: function () {
              if (window.feather) {
                feather.replace({ width: 14, height: 14 });
              }
            }
          });
        }

        selectedProductsTable.clear();
        selectedProductsTable.rows.add(rows).draw();
      }

      function renderAvailableProductsTable() {
        const filteredProducts = filterProducts();

        const rows = filteredProducts.map(function (product) {
          const checked = selectedIds.has(product.id) ? 'checked' : '';
          return [
            '<input type="checkbox" class="product-checkbox" value="' + product.id + '" ' + checked + '>',
            '#' + product.id + ' - ' + escapeHtml(product.name),
            '<button type="button" class="btn btn-icon btn-flat-primary add-single-product" data-id="' + product.id + '" title="{{ __('locale.Add') }}"><i data-feather="plus"></i></button>'
          ];
        });

        if (!availableProductsTable) {
          availableProductsTable = availableTableElement.DataTable({
            responsive: true,
            language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [0, 2] }],
            drawCallback: function () {
              if (window.feather) {
                feather.replace({ width: 14, height: 14 });
              }
            }
          });
        }

        availableProductsTable.clear();

        if (rows.length) {
          availableProductsTable.rows.add(rows);
        }

        availableProductsTable.draw();
        selectAllProductsCheckbox.checked = false;
      }

      function refreshTables() {
        renderAvailableProductsTable();
        renderSelectedProductsTable();
      }

      categorySelect.addEventListener('change', function () {
        const category = getSelectedCategory();
        renderSelectOptions(subcategorySelect, category ? category.subcategories : []);
        renderSelectOptions(subsubcategorySelect, []);
        refreshTables();
      });

      subcategorySelect.addEventListener('change', function () {
        const subcategory = getSelectedSubcategory();
        renderSelectOptions(subsubcategorySelect, subcategory ? subcategory.sub_subcategories : []);
        refreshTables();
      });

      subsubcategorySelect.addEventListener('change', refreshTables);

      availableTableElement.on('change', '.product-checkbox', function () {
        const productId = Number($(this).val());

        if ($(this).is(':checked')) {
          selectedIds.add(productId);
        } else {
          selectedIds.delete(productId);
        }

        renderSelectedProductsTable();
      });

      availableTableElement.on('click', '.add-single-product', function () {
        const productId = Number($(this).data('id'));
        selectedIds.add(productId);
        refreshTables();
      });

      selectedTableElement.on('click', '.remove-selected-product', function () {
        const productId = Number($(this).data('id'));
        selectedIds.delete(productId);
        refreshTables();
      });

      selectAllProductsCheckbox.addEventListener('change', function () {
        const checked = this.checked;
        availableTableElement.find('.product-checkbox').prop('checked', checked);
      });

      addSelectedProductsButton.addEventListener('click', function () {
        const checkedBoxes = availableTableElement.find('.product-checkbox:checked');

        checkedBoxes.each(function () {
          selectedIds.add(Number(this.value));
        });

        refreshTables();
      });

      nameInput.addEventListener('input', syncSlug);

      syncSlug();
      renderSelectOptions(subcategorySelect, []);
      renderSelectOptions(subsubcategorySelect, []);
      refreshTables();
    }());
  </script>
@endsection