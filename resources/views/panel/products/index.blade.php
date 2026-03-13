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
                <a href="#" class="dt-button btn btn-outline-secondary mr-1" id="btn-clear-filters">
                <i data-feather="x-circle"></i> {{ __('locale.Clear Filters') }}
              </a>
                <a href="#" class="dt-button btn btn-outline-primary mr-1" id="btn-export-products">
                <i data-feather="download"></i> {{ __('locale.Export') }}
              </a>
                <a href="{{ route('products.create') }}" class="dt-button create-new btn btn-primary">
                <i data-feather="plus"></i> {{ __('locale.Add New') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
            <div class="row mb-1">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="filter-type">{{ __('locale.Type') }}</label>
                  <select class="form-control" id="filter-type">
                    <option value="">{{ __('locale.All') }}</option>
                    <option value="0">{{ __('locale.Simple') }}</option>
                    <option value="1">{{ __('locale.Variable') }}</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="filter-category">{{ __('locale.Category') }}</label>
                  <select class="form-control" id="filter-category">
                    <option value="">{{ __('locale.All') }}</option>
                    @foreach($categories as $category)
                      <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="filter-subcategory">{{ __('locale.Subcategory') }}</label>
                  <select class="form-control" id="filter-subcategory">
                    <option value="">{{ __('locale.All') }}</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="filter-subsubcategory">{{ __('locale.Sub Subcategory') }}</label>
                  <select class="form-control" id="filter-subsubcategory">
                    <option value="">{{ __('locale.All') }}</option>
                  </select>
                </div>
              </div>
            </div>
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

            <div class="row match-height mb-2">
              <div class="col-md-3 col-sm-6">
                <div class="card kpi-card border h-100 mb-1 mb-md-0 indicator-filter-card" id="indicator-low-stock-card" role="button" title="{{ __('locale.Filter') }}">
                  <div class="card-header d-flex justify-content-between align-items-start pb-1">
                    <h5 class="card-title mb-0">{{ __('locale.Low Stock Products') }}</h5>
                    <i data-feather="alert-triangle" class="text-warning"></i>
                  </div>
                  <div class="card-body pt-0">
                    <h2 class="font-weight-bolder mb-0" id="indicator-low-stock">{{ number_format($lowStockProductsCount ?? 0) }}</h2>
                  </div>
                </div>
              </div>

              <div class="col-md-3 col-sm-6">
                <div class="card kpi-card border h-100 mb-1 mb-md-0">
                  <div class="card-header d-flex justify-content-between align-items-start pb-1">
                    <h5 class="card-title mb-0">{{ __('locale.Products by Company') }}</h5>
                    <i data-feather="briefcase" class="text-primary"></i>
                  </div>
                  <div class="card-body pt-0">
                    <div style="max-height: 94px; overflow-y: auto;" id="indicator-company-list">
                      @forelse(($productsByCompanyCounts ?? []) as $companyCount)
                        @php
                          $companyId = (string) $companyCount['company_id'];
                          $companyLabel = $companyId === '1'
                            ? __('locale.Others')
                            : ($companyId === '2' ? __('locale.Kromi Market') : ($companyId === '0' ? __('locale.No company') : $companyId));
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-25 indicator-company-item"
                             data-company-id="{{ $companyId }}"
                             role="button"
                             title="{{ __('locale.Filter') }}">
                          <span class="text-truncate mr-50">
                            {{ $companyLabel }}
                          </span>
                          <span class="badge badge-light-primary">{{ $companyCount['total'] }}</span>
                        </div>
                      @empty
                        <span class="text-muted">{{ __('locale.No data') }}</span>
                      @endforelse
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-3 col-sm-6">
                <div class="card kpi-card border h-100 mb-1 mb-sm-0">
                  <div class="card-header d-flex justify-content-between align-items-start pb-1">
                    <h5 class="card-title mb-0">{{ __('locale.Products by Status') }}</h5>
                    <i data-feather="activity" class="text-success"></i>
                  </div>
                  <div class="card-body pt-0">
                    <div class="d-flex flex-wrap" id="indicator-status-wrapper">
                      <span class="badge badge-light-success mr-50 mb-50 indicator-status-item" data-status="1" id="indicator-status-active" role="button" title="{{ __('locale.Filter') }}">{{ __('locale.Active') }}: {{ (int) (($productsByStatusCounts['1'] ?? 0)) }}</span>
                      <span class="badge badge-light-secondary mr-50 mb-50 indicator-status-item" data-status="0" id="indicator-status-inactive" role="button" title="{{ __('locale.Filter') }}">{{ __('locale.Inactive') }}: {{ (int) (($productsByStatusCounts['0'] ?? 0)) }}</span>
                      <span class="badge badge-light-danger mb-50 indicator-status-item" data-status="2" id="indicator-status-deleted" role="button" title="{{ __('locale.Filter') }}">{{ __('locale.Deleted') }}: {{ (int) (($productsByStatusCounts['2'] ?? 0)) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-3 col-sm-6">
                <div class="card kpi-card border h-100 indicator-filter-card" id="indicator-without-images-card" role="button" title="{{ __('locale.Filter') }}">
                  <div class="card-header d-flex justify-content-between align-items-start pb-1">
                    <h5 class="card-title mb-0">{{ __('locale.Products Without Images') }}</h5>
                    <i data-feather="image" class="text-danger"></i>
                  </div>
                  <div class="card-body pt-0">
                    <h2 class="font-weight-bolder mb-0" id="indicator-without-images">{{ number_format($productsWithoutImagesCount ?? 0) }}</h2>
                  </div>
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
  <style>
    .kpi-loading {
      opacity: .65;
      pointer-events: none;
    }

    .kpi-card .card-title {
      font-size: 1rem;
    }

    .indicator-filter-card,
    .indicator-status-item {
      cursor: pointer;
    }

    .kpi-filter-active {
      border-color: #7367f0 !important;
      box-shadow: 0 0 0 0.15rem rgba(115, 103, 240, .15);
    }

    .kpi-skeleton-line {
      height: 10px;
      border-radius: 6px;
      background: linear-gradient(90deg, #e9ecef 25%, #f8f9fa 50%, #e9ecef 75%);
      background-size: 200% 100%;
      animation: kpiShimmer 1.2s infinite;
    }

    .kpi-skeleton-line.lg {
      height: 28px;
      width: 70%;
      margin-top: .25rem;
    }

    .kpi-skeleton-line.sm {
      height: 12px;
      width: 100%;
      margin-bottom: .35rem;
    }

    @keyframes kpiShimmer {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    .indicator-company-item {
      cursor: pointer;
    }

    .indicator-company-item:hover {
      background-color: rgba(115, 103, 240, .05);
      border-radius: .3rem;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    $(document).ready(function() {
      var categoryFilterTree = @json($categoryFilterTree);
      var allSubcategories = @json($allSubcategories);
      var allSubsubcategories = @json($allSubsubcategories);
      var indicatorsRequest = null;
      var withoutImagesOnly = false;

      function fillSelectOptions(selector, options) {
        var $select = $(selector);
        var currentValue = $select.val();
        var baseOption = '<option value="">{{ __('locale.All') }}</option>';
        var optionsHtml = options.map(function (item) {
          return '<option value="' + item.id + '">' + item.name + '</option>';
        }).join('');

        $select.html(baseOption + optionsHtml);

        if (currentValue && $select.find('option[value="' + currentValue + '"]').length) {
          $select.val(currentValue);
        }
      }

      function getSubcategoriesByCategory(categoryId) {
        if (!categoryId) {
          return allSubcategories;
        }

        var category = categoryFilterTree.find(function (item) {
          return String(item.id) === String(categoryId);
        });

        return category ? category.subcategories : [];
      }

      function getSubsubcategories(categoryId, subcategoryId) {
        var selectedSubcategoryId = String(subcategoryId || '');
        var subcategories = getSubcategoriesByCategory(categoryId);

        if (selectedSubcategoryId) {
          var subcategory = subcategories.find(function (item) {
            return String(item.id) === selectedSubcategoryId;
          });

          return subcategory ? subcategory.sub_subcategories : [];
        }

        if (!categoryId) {
          return allSubsubcategories;
        }

        return subcategories.reduce(function (accumulator, item) {
          return accumulator.concat(item.sub_subcategories || []);
        }, []);
      }

      function refreshSubcategoryOptions(resetValue) {
        var categoryId = $('#filter-category').val();
        if (resetValue) {
          $('#filter-subcategory').val('');
        }

        var subcategories = getSubcategoriesByCategory(categoryId);
        fillSelectOptions('#filter-subcategory', subcategories);
      }

      function refreshSubsubcategoryOptions(resetValue) {
        var categoryId = $('#filter-category').val();
        var subcategoryId = $('#filter-subcategory').val();
        if (resetValue) {
          $('#filter-subsubcategory').val('');
        }

        var subsubcategories = getSubsubcategories(categoryId, subcategoryId);
        fillSelectOptions('#filter-subsubcategory', subsubcategories);
      }

      function getIndicatorPayload() {
        return {
          _token: '{{ csrf_token() }}',
          company: $('#filter-company').val(),
          status: $('#filter-status').val(),
          inventory: $('#filter-inventory').val(),
          typeProduct: $('#filter-type').val(),
          category: $('#filter-category').val(),
          subcategory: $('#filter-subcategory').val(),
          subsubcategory: $('#filter-subsubcategory').val(),
          withoutImages: withoutImagesOnly ? 1 : '',
          search: $('#products-table_filter input[type="search"]').val() || ''
        };
      }

      function formatNumber(value) {
        return Number(value || 0).toLocaleString();
      }

      function setIndicatorsLoading(isLoading) {
        var kpiNumberSkeleton = '<div class="kpi-skeleton-line lg"></div>';
        var kpiStatusSkeleton = '<div class="kpi-skeleton-line sm" style="width:46%"></div><div class="kpi-skeleton-line sm" style="width:58%"></div><div class="kpi-skeleton-line sm" style="width:52%"></div>';
        var kpiCompanySkeleton = '<div class="kpi-skeleton-line sm" style="width:100%"></div><div class="kpi-skeleton-line sm" style="width:92%"></div><div class="kpi-skeleton-line sm" style="width:84%"></div>';
        $('.row.mb-2 .card').toggleClass('kpi-loading', isLoading);

        if (isLoading) {
          $('#indicator-low-stock').html(kpiNumberSkeleton);
          $('#indicator-without-images').html(kpiNumberSkeleton);
          $('#indicator-status-wrapper').html(kpiStatusSkeleton);
          $('#indicator-company-list').html(kpiCompanySkeleton);
        }
      }

      function renderCompanyIndicators(companies) {
        var noCompanyText = @json(__('locale.No company'));
        var noDataText = @json(__('locale.No data'));
        var othersText = @json(__('locale.Others'));
        var kromiMarketText = @json(__('locale.Kromi Market'));
        var filterTitleText = @json(__('locale.Filter'));
        var html = '';

        if (!Array.isArray(companies) || companies.length === 0) {
          html = '<span class="text-muted">' + noDataText + '</span>';
        } else {
          html = companies.map(function (item) {
            var companyId = String(item.company_id);
            var companyLabel = companyId === '1'
              ? othersText
              : (companyId === '2' ? kromiMarketText : (companyId === '0' ? noCompanyText : companyId));

            return '<div class="d-flex justify-content-between align-items-center mb-25 indicator-company-item" role="button" data-company-id="' + companyId + '" title="' + filterTitleText + '">'
              + '<span class="text-truncate mr-50">' + companyLabel + '</span>'
              + '<span class="badge badge-light-primary">' + formatNumber(item.total) + '</span>'
              + '</div>';
          }).join('');
        }

        $('#indicator-company-list').html(html);
      }

      function updateIndicators() {
        if (indicatorsRequest && indicatorsRequest.readyState !== 4) {
          indicatorsRequest.abort();
        }

        setIndicatorsLoading(true);

        indicatorsRequest = $.ajax({
          url: "{{ route('products.indicators') }}",
          type: 'POST',
          data: getIndicatorPayload(),
          success: function (response) {
            $('#indicator-low-stock').text(formatNumber(response.lowStockProductsCount));
            $('#indicator-without-images').text(formatNumber(response.productsWithoutImagesCount));

            var statusCounts = response.productsByStatusCounts || {};
            $('#indicator-status-wrapper').html(
              '<span class="badge badge-light-success mr-50 mb-50 indicator-status-item" role="button" data-status="1" id="indicator-status-active" title="{{ __('locale.Filter') }}">{{ __('locale.Active') }}: ' + formatNumber(statusCounts['1']) + '</span>'
              + '<span class="badge badge-light-secondary mr-50 mb-50 indicator-status-item" role="button" data-status="0" id="indicator-status-inactive" title="{{ __('locale.Filter') }}">{{ __('locale.Inactive') }}: ' + formatNumber(statusCounts['0']) + '</span>'
              + '<span class="badge badge-light-danger mb-50 indicator-status-item" role="button" data-status="2" id="indicator-status-deleted" title="{{ __('locale.Filter') }}">{{ __('locale.Deleted') }}: ' + formatNumber(statusCounts['2']) + '</span>'
            );

            renderCompanyIndicators(response.productsByCompanyCounts || []);

            if (feather) {
              feather.replace({ width: 14, height: 14 });
            }
          },
          complete: function () {
            setIndicatorsLoading(false);
          }
        });
      }

      function refreshIndicatorActiveState() {
        $('#indicator-low-stock-card, #indicator-without-images-card').removeClass('kpi-filter-active');
        $('.indicator-status-item').removeClass('kpi-filter-active');

        if (String($('#filter-inventory').val()) === '2') {
          $('#indicator-low-stock-card').addClass('kpi-filter-active');
        }

        if (withoutImagesOnly) {
          $('#indicator-without-images-card').addClass('kpi-filter-active');
        }

        var statusValue = String($('#filter-status').val() || '');
        if (statusValue !== '') {
          $('.indicator-status-item[data-status="' + statusValue + '"]').addClass('kpi-filter-active');
        }
      }

      refreshSubcategoryOptions(false);
      refreshSubsubcategoryOptions(false);

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
            url: "{{ route('products.get') }}",
            type: 'POST',
            data: function (d) {
              d._token = '{{ csrf_token() }}';
              d.company = $('#filter-company').val();
              d.status = $('#filter-status').val();
              d.inventory = $('#filter-inventory').val();
              d.typeProduct = $('#filter-type').val();
              d.category = $('#filter-category').val();
              d.subcategory = $('#filter-subcategory').val();
              d.subsubcategory = $('#filter-subsubcategory').val();
              d.withoutImages = withoutImagesOnly ? 1 : '';
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

      updateIndicators();

      $('#products-table').on('draw.dt', function () {
        updateIndicators();
      });

      $('#filter-company, #filter-status, #filter-inventory').on('change', function () {
        refreshIndicatorActiveState();
        productsTable.ajax.reload();
      });

      $('#filter-type').on('change', function () {
        refreshIndicatorActiveState();
        productsTable.ajax.reload();
      });

      $('#filter-category').on('change', function () {
        refreshSubcategoryOptions(true);
        refreshSubsubcategoryOptions(true);
        refreshIndicatorActiveState();
        productsTable.ajax.reload();
      });

      $('#filter-subcategory').on('change', function () {
        refreshSubsubcategoryOptions(true);
        refreshIndicatorActiveState();
        productsTable.ajax.reload();
      });

      $('#filter-subsubcategory').on('change', function () {
        refreshIndicatorActiveState();
        productsTable.ajax.reload();
      });

      $('body').on('click', '.indicator-company-item', function () {
        var companyId = String($(this).data('company-id') || '');
        var $companyFilter = $('#filter-company');

        if ($companyFilter.find('option[value="' + companyId + '"]').length) {
          $companyFilter.val(companyId).trigger('change');
        }
      });

      $('#indicator-low-stock-card').on('click', function () {
        $('#filter-inventory').val('2').trigger('change');
      });

      $('#indicator-without-images-card').on('click', function () {
        withoutImagesOnly = !withoutImagesOnly;
        refreshIndicatorActiveState();
        productsTable.ajax.reload();
      });

      $('body').on('click', '.indicator-status-item', function () {
        var status = String($(this).data('status') || '');
        if (status !== '') {
          $('#filter-status').val(status).trigger('change');
        }
      });

      $('#btn-export-products').on('click', function (e) {
        e.preventDefault();

        var searchValue = $('#products-table_filter input[type="search"]').val() || '';
        var form = $('<form method="POST" action="{{ route('products.export') }}" style="display:none;"></form>');
        form.append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
        form.append('<input type="hidden" name="company" value="' + ($('#filter-company').val() || '') + '">');
        form.append('<input type="hidden" name="status" value="' + ($('#filter-status').val() || '') + '">');
        form.append('<input type="hidden" name="inventory" value="' + ($('#filter-inventory').val() || '') + '">');
        form.append('<input type="hidden" name="typeProduct" value="' + ($('#filter-type').val() || '') + '">');
        form.append('<input type="hidden" name="category" value="' + ($('#filter-category').val() || '') + '">');
        form.append('<input type="hidden" name="subcategory" value="' + ($('#filter-subcategory').val() || '') + '">');
        form.append('<input type="hidden" name="subsubcategory" value="' + ($('#filter-subsubcategory').val() || '') + '">');
        form.append('<input type="hidden" name="search" value="' + $('<div/>').text(searchValue).html() + '">');

        $('body').append(form);
        form.submit();
      });

      $('#btn-clear-filters').on('click', function (e) {
        e.preventDefault();

        $('#filter-type').val('');
        $('#filter-category').val('');
        refreshSubcategoryOptions(true);
        refreshSubsubcategoryOptions(true);
        $('#filter-company').val('');
        $('#filter-status').val('');
        $('#filter-inventory').val('');
        withoutImagesOnly = false;

        var $searchInput = $('#products-table_filter input[type="search"]');
        if ($searchInput.length) {
          $searchInput.val('');
          productsTable.search('');
        }

        refreshIndicatorActiveState();
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

      refreshIndicatorActiveState();
    });
  </script>
@endsection
