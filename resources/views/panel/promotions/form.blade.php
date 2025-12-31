@extends('layouts/contentLayoutMaster')

@section('title', isset($promotion) ? __('Edit Promotion') : __('New Promotion'))

@section('content')
@php
  $promotion = $promotion ?? null;
  $initialProducts = old('products') ? json_decode(old('products'), true) : ($selectedProducts ?? []);
  if (!is_array($initialProducts)) {
    $initialProducts = [];
  }
  $categoriesForJs = $categories->map(function ($category) {
    return [
      'id' => $category->id,
      'name' => $category->name,
      'subcategories' => $category->subcategories->map(function ($sub) {
        return [
          'id' => $sub->id,
          'name' => $sub->name,
          'sub_subcategories' => $sub->sub_subcategories->map(function ($subSub) {
            return [
              'id' => $subSub->id,
              'name' => $subSub->name,
            ];
          })->toArray(),
        ];
      })->toArray(),
    ];
  })->toArray();
@endphp

<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ isset($promotion) ? __('Edit Promotion') : __('New Promotion') }}</h4>
        </div>
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          <form action="{{ isset($promotion) ? route('promotions.update', $promotion) : route('promotions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($promotion))
              @method('PUT')
            @endif

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="title">{{ __('Title') }}</label>
                  <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $promotion->title ?? '') }}" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="limit">{{ __('Uses per client') }}</label>
                  <input type="number" id="limit" name="limit" class="form-control" min="0" value="{{ old('limit', $promotion->limit ?? 0) }}" required>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="start_date">{{ __('Start Date') }}</label>
                  <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date', optional($promotion)->start_date?->format('Y-m-d')) }}" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="end_date">{{ __('End Date') }}</label>
                  <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date', optional($promotion)->end_date?->format('Y-m-d')) }}" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="discount_percentage">{{ __('Discount Percentage') }}</label>
                  <input type="number" step="0.01" id="discount_percentage" name="discount_percentage" class="form-control" value="{{ old('discount_percentage', $promotion->discount_percentage ?? 0) }}" required>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="order">{{ __('Order') }}</label>
                  <input type="number" min="0" id="order" name="order" class="form-control" value="{{ old('order', $promotion->order ?? 0) }}">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="image">{{ __('Main Image') }}</label>
                  <input type="file" id="image" name="image" class="form-control">
                  <input type="hidden" name="current_image" value="{{ $promotion->image ?? '' }}">
                  @if(isset($promotion) && $promotion->image)
                    <div class="mt-2">
                      <img src="{{ asset($promotion->image) }}" alt="{{ $promotion->title }}" class="img-fluid" style="max-height: 160px;">
                    </div>
                  @endif
                </div>
              </div>
            </div>

            <div class="card card-body border mt-3">
              <h5>{{ __('Product search') }}</h5>
              <div class="row">
                <div class="col-md-4">
                  <label>{{ __('Select Category') }}</label>
                  <select class="form-control" id="category-filter">
                    <option value="">{{ __('Select') }}</option>
                    @foreach($categories as $category)
                      <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <label>{{ __('Select Subcategory') }}</label>
                  <select class="form-control" id="subcategory-filter">
                    <option value="">{{ __('Select Subcategory') }}</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label>{{ __('Select Sub-Subcategory') }}</label>
                  <select class="form-control" id="subsubcategory-filter">
                    <option value="">{{ __('Select Sub-Subcategory') }}</option>
                  </select>
                </div>
              </div>
              <div class="row mt-3 align-items-end">
                <div class="col-md-7">
                  <label>{{ __('Select product') }}</label>
                  <select class="form-control" id="product-filter">
                    <option value="">{{ __('Select product') }}</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label>{{ __('Quantity') }}</label>
                  <input type="number" class="form-control" id="product-quantity" min="1" value="1">
                </div>
                <div class="col-md-2">
                  <button type="button" class="btn btn-primary w-100" id="add-product-btn">{{ __('Add Product') }}</button>
                </div>
              </div>
            </div>

            <div class="table-responsive mt-3">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>{{ __('Product') }}</th>
                    <th>{{ __('Presentation') }}</th>
                    <th>{{ __('Stock') }}</th>
                    <th>{{ __('Quantity') }}</th>
                    <th>{{ __('Actions') }}</th>
                  </tr>
                </thead>
                <tbody id="selected-products-body"></tbody>
              </table>
              @error('products')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>

            <input type="hidden" name="products" id="selected-products" value="{{ old('products', json_encode($initialProducts)) }}">

            <div class="mt-4 d-flex justify-content-end">
              <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary mr-2">{{ __('Back') }}</a>
              <button type="submit" class="btn btn-primary">{{ isset($promotion) ? __('Update') : __('Save') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const categories = @json($categoriesForJs);
      const productsRoute = "{{ route('promotions.products') }}";
      const selectedProductsInput = document.getElementById('selected-products');
      const selectedProductsBody = document.getElementById('selected-products-body');
      const addProductBtn = document.getElementById('add-product-btn');
      const productSelect = document.getElementById('product-filter');
      const quantityInput = document.getElementById('product-quantity');
      const categoryFilter = document.getElementById('category-filter');
      const subcategoryFilter = document.getElementById('subcategory-filter');
      const subsubcategoryFilter = document.getElementById('subsubcategory-filter');

      let availableProducts = [];
      let selectedProducts = @json($initialProducts);

      function populateSelect(selectElement, items, placeholder) {
        selectElement.innerHTML = `<option value="">${placeholder}</option>`;
        items.forEach(item => {
          const option = document.createElement('option');
          option.value = item.id;
          option.textContent = item.name;
          selectElement.appendChild(option);
        });
      }

      function findCategory(id) {
        return categories.find(cat => cat.id == id);
      }

      function findSubcategory(category, id) {
        if (!category) return null;
        return category.subcategories.find(sub => sub.id == id);
      }

      function fetchProducts() {
        const params = new URLSearchParams({
          category_id: categoryFilter.value,
          subcategory_id: subcategoryFilter.value,
          subsubcategory_id: subsubcategoryFilter.value,
        });

        fetch(`${productsRoute}?${params.toString()}`)
          .then(response => response.json())
          .then(data => {
            availableProducts = data.data;
            renderProductOptions();
          });
      }

      function renderProductOptions() {
        productSelect.innerHTML = `<option value="">{{ __('Select product') }}</option>`;
        availableProducts.forEach(product => {
          const option = document.createElement('option');
          option.value = product.id;
          option.textContent = `${product.name} (${product.presentation ?? '-'})`;
          option.dataset.presentation = product.presentation;
          option.dataset.available = product.amount;
          option.dataset.name = product.name;
          productSelect.appendChild(option);
        });
      }

      function renderSelectedProducts() {
        selectedProductsBody.innerHTML = '';

        if (!selectedProducts.length) {
          const emptyRow = document.createElement('tr');
          emptyRow.innerHTML = `<td colspan="6" class="text-center">{{ __('No products selected yet.') }}</td>`;
          selectedProductsBody.appendChild(emptyRow);
          syncHiddenInput();
          return;
        }

        selectedProducts.forEach((product, index) => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${index + 1}</td>
            <td>${product.name}</td>
            <td>${product.presentation ?? '-'}</td>
            <td>${product.available ?? 0}</td>
            <td>
              <input type="number" class="form-control selected-product-qty" data-id="${product.id}" min="1" value="${product.total}">
            </td>
            <td>
              <button type="button" class="btn btn-icon btn-flat-danger selected-product-remove" data-id="${product.id}">
                <i data-feather="trash-2"></i>
              </button>
            </td>
          `;
          selectedProductsBody.appendChild(row);
        });

        if (window.feather) {
          feather.replace({ width: 14, height: 14 });
        }

        syncHiddenInput();
      }

      function syncHiddenInput() {
        const payload = selectedProducts.map(product => ({
          id: product.id,
          total: product.total
        }));
        selectedProductsInput.value = JSON.stringify(payload);
      }

      function handleAddProduct() {
        const productId = productSelect.value;
        const quantity = parseInt(quantityInput.value, 10);
        if (!productId || quantity < 1) {
          return;
        }

        const metadata = availableProducts.find(product => product.id == productId);
        if (!metadata) {
          return;
        }

        const existing = selectedProducts.find(product => product.id == productId);
        if (existing) {
          existing.total = existing.total + quantity;
        } else {
          selectedProducts.push({
            id: metadata.id,
            name: metadata.name,
            presentation: metadata.presentation,
            available: metadata.amount,
            total: quantity,
          });
        }

        renderSelectedProducts();
      }

      document.addEventListener('click', function (event) {
        if (event.target.matches('.selected-product-remove, .selected-product-remove *')) {
          const button = event.target.closest('.selected-product-remove');
          const productId = button.dataset.id;
          selectedProducts = selectedProducts.filter(product => product.id != productId);
          renderSelectedProducts();
        }
      });

        document.addEventListener('change', function (event) {
        if (event.target.matches('.selected-product-qty')) {
          const productId = event.target.dataset.id;
          const value = Math.max(1, parseInt(event.target.value, 10) || 1);
          const product = selectedProducts.find(item => item.id == productId);
          if (product) {
            product.total = value;
            renderSelectedProducts();
          }
        }
      });

      addProductBtn.addEventListener('click', handleAddProduct);

        categoryFilter.addEventListener('change', function () {
          const category = findCategory(this.value);
          populateSelect(subcategoryFilter, category ? category.subcategories : [], '{{ __('Select Subcategory') }}');
          populateSelect(subsubcategoryFilter, [], '{{ __('Select Sub-Subcategory') }}');
          fetchProducts();
        });

      subcategoryFilter.addEventListener('change', function () {
        const category = findCategory(categoryFilter.value);
        const subcategory = findSubcategory(category, this.value);
        populateSelect(subsubcategoryFilter, subcategory ? subcategory.sub_subcategories : [], '{{ __('Select sub-subcategory') }}');
        fetchProducts();
      });

      subsubcategoryFilter.addEventListener('change', fetchProducts);

      renderSelectedProducts();
      fetchProducts();
    });
  </script>
@endsection
