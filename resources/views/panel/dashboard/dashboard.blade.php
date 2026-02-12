
@extends('layouts/contentLayoutMaster')

@section('title', 'Dashboard Ecommerce')

@section('vendor-style')
  {{-- vendor css files --}}
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/charts/apexcharts.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/toastr.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
@endsection
@section('page-style')
  {{-- Page css files --}}
  <link rel="stylesheet" href="{{ asset(mix('css/base/pages/dashboard-ecommerce.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/charts/chart-apex.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/extensions/ext-component-toastr.css')) }}">
@endsection

@section('content')
<!-- Dashboard Ecommerce Starts -->
<section id="dashboard-ecommerce">
  <div class="row match-height">
    <!-- Medal Card -->
    <div class="col-xl-4 col-md-6 col-12">
      <div class="card card-congratulation-medal">
        <div class="card-body">
          <h5>Congratulations 🎉 John!</h5>
          <p class="card-text font-small-3">You have won gold medal</p>
          <h3 class="mb-75 mt-2 pt-50">
            <a href="javascript:void(0);">$48.9k</a>
          </h3>
          <button type="button" class="btn btn-primary">View Sales</button>
          <img src="{{asset('images/illustration/badge.svg')}}" class="congratulation-medal" alt="Medal Pic" />
        </div>
      </div>

      <!-- Sales Report Help Modal -->
      <div class="modal fade" id="sales-report-help-modal" tabindex="-1" role="dialog" aria-labelledby="salesReportHelpLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="salesReportHelpLabel">Cálculo y definición del reporte de ventas</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p class="mb-2">{{ __('Sales Report Help Intro') }}</p>
              <ul class="pl-3 mb-2">
                <li><strong>{{ __('Sales Report Help Income Label') }}:</strong> {{ __('Sales Report Help Income') }}</li>
                <li><strong>{{ __('Sales Report Help Expenses Label') }}:</strong> {{ __('Sales Report Help Expenses') }}</li>
                <li><strong>{{ __('Sales Report Help Net Label') }}:</strong> {{ __('Sales Report Help Net') }}</li>
                <li><strong>{{ __('Sales Report Help Chart Label') }}:</strong> {{ __('Sales Report Help Chart') }}</li>
              </ul>

              <h6 class="mt-2">{{ __('Sales Report Help VerificationTitle') }}</h6>
              <ol class="pl-3 mb-0 small text-muted">
                <li>{{ __('Sales Report Help VerificationStep1') }}</li>
                <li>{{ __('Sales Report Help VerificationStep2') }}</li>
                <li>{{ __('Sales Report Help VerificationStep3') }}</li>
              </ol>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Medal Card -->

    <!-- Statistics Card -->
    <div class="col-xl-8 col-md-6 col-12">
      <div class="card card-statistics">
        <div class="card-header">
          <h4 class="card-title">{{ __("Statistics") }}</h4>
          <div class="d-flex align-items-center">
            <p class="card-text font-small-2 mr-25 mb-0">Updated 1 month ago</p>
          </div>
        </div>
        <div class="card-body statistics-body">
          <div class="row">
            <div class="col-xl-3 col-sm-6 col-12 mb-2 mb-xl-0">
              <div class="media">
                <div class="avatar bg-light-primary mr-2">
                  <div class="avatar-content">
                    <i data-feather="trending-up" class="avatar-icon"></i>
                  </div>
                </div>
                <div class="media-body my-auto">
                  <h4 class="font-weight-bolder mb-0">{{ number_format($sales) }}</h4>
                  <p class="card-text font-small-3 mb-0">{{ __("Sales") }}</p>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 mb-2 mb-xl-0">
              <div class="media">
                <div class="avatar bg-light-info mr-2">
                  <div class="avatar-content">
                    <i data-feather="user" class="avatar-icon"></i>
                  </div>
                </div>
                <div class="media-body my-auto">
                  <h4 class="font-weight-bolder mb-0">{{ number_format($customers) }}</h4>
                  <p class="card-text font-small-3 mb-0">{{ __("Customers") }}</p>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 mb-2 mb-sm-0">
              <div class="media">
                <div class="avatar bg-light-danger mr-2">
                  <div class="avatar-content">
                    <i data-feather="box" class="avatar-icon"></i>
                  </div>
                </div>
                <div class="media-body my-auto">
                  <h4 class="font-weight-bolder mb-0">{{ number_format($products) }}</h4>
                  <p class="card-text font-small-3 mb-0">{{ __("Products") }}</p>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
              <div class="media">
                <div class="avatar bg-light-success mr-2">
                  <div class="avatar-content">
                    <i data-feather="user-plus" class="avatar-icon"></i>
                  </div>
                </div>
                <div class="media-body my-auto">
                  <h4 class="font-weight-bolder mb-0">{{ number_format($revenue) }}</h4>
                  <p class="card-text font-small-3 mb-0">{{ __("Revenue") }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Statistics Card -->
  </div>

  <div class="row match-height">
    <div class="col-lg-4 col-12">
      <div class="row match-height">
        <!-- Bar Chart - Orders -->
        <div class="col-lg-6 col-md-3 col-6">
          <div class="card">
            <div class="card-body pb-50">
              <h6>{{ __("Orders") }}</h6>
              <h2 class="font-weight-bolder mb-1">{{ number_format($sales) }}</h2>
              <div id="statistics-order-chart"></div>
            </div>
          </div>
        </div>
        <!--/ Bar Chart - Orders -->

        <!-- Line Chart - Profit -->
        <div class="col-lg-6 col-md-3 col-6">
              <div class="card card-tiny-line-stats">
            <div class="card-body pb-50">
              <h6>{{ __("Profit") }}</h6>
              <h2 class="font-weight-bolder mb-1" title="{{ number_format($profit, 2) }}">{{ \App\Helpers\Helper::abbreviateNumber($profit) }}</h2>
              <div id="statistics-profit-chart"></div>
            </div>
          </div>
        </div>
        <!--/ Line Chart - Profit -->

        <!-- Earnings Card -->
        <div class="col-lg-12 col-md-6 col-12">
          <div class="card earnings-card">
            <div class="card-body">
              <div class="row">
                <div class="col-6">
                  <h4 class="card-title mb-1">{{ __("Earnings") }}</h4>
                  <div class="font-small-2">{{ __("This Month") }}</div>
                  <h5 class="mb-1" title="{{ number_format($earningsCurrent, 2) }}">{{ \App\Helpers\Helper::abbreviateNumber($earningsCurrent) }}</h5>
                  <p class="card-text text-muted font-small-2">
                    @if(is_null($earningsPercent))
                      <span class="font-weight-bolder">--</span><span> {{ __('No data for previous month') }}.</span>
                    @else
                      <span class="font-weight-bolder {{ $earningsPercent >= 0 ? 'text-success' : 'text-danger' }}">{{ abs($earningsPercent) }}%</span>
                      <span>{{ $earningsPercent >= 0 ? __(' more earnings than last month.') : __(' less earnings than last month.') }}</span>
                      @if($earningsDirection == 'up')
                        <i data-feather="trending-up" class="ml-50 text-success"></i>
                      @else
                        <i data-feather="trending-down" class="ml-50 text-danger"></i>
                      @endif
                    @endif
                  </p>
                </div>
                <div class="col-6">
                  <div id="earnings-chart"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--/ Earnings Card -->
      </div>
    </div>

    <!-- Revenue Report Card -->
    <div class="col-lg-8 col-12">
      <div class="card card-revenue-budget">
        <div class="row mx-0">
          <div class="col-md-8 col-12 revenue-report-wrapper">
            @php
              $currentYear = date('Y');
              $selectedYear = request('year', $currentYear);
            @endphp
            
            <div class="d-sm-flex justify-content-between align-items-center mb-3">
              <h4 class="card-title mb-50 mb-sm-0">{{ __('Sales Report (Year)') }}</h4>
              <i
                data-feather="help-circle"
                class="font-medium-3 text-muted cursor-pointer"
                data-toggle="modal"
                data-target="#sales-report-help-modal"
                aria-label="Cómo se calcula este reporte"
                title="Cómo se calcula este reporte"
              ></i>
              <div class="d-flex align-items-center">
                <div class="d-flex align-items-center mr-2">
                  <span class="bullet bullet-primary font-small-3 mr-50 cursor-pointer"></span>
                  <span>{{ __('Income') }}</span>
                </div>
                <div class="d-flex align-items-center ml-75">
                  <span class="bullet bullet-warning font-small-3 mr-50 cursor-pointer"></span>
                  <span>{{ __('Expenses') }}</span>
                </div>
              </div>
            </div>
            <div id="revenue-report-chart"></div>
          </div>
          <div class="col-md-4 col-12 budget-wrapper">
            <div class="btn-group">
              <button
                type="button"
                class="btn btn-outline-primary btn-sm dropdown-toggle budget-dropdown"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
              >
                {{ $selectedYear }}
              </button>
              <div class="dropdown-menu">
                @for($y = $currentYear; $y > $currentYear - 4; $y--)
                  <a class="dropdown-item" href="{{ route('dashboard-home', ['year' => $y]) }}">{{ $y }}</a>
                @endfor
              </div>
            </div>
            <h2 class="mb-25" title="{{ number_format($revenueReportTotalEarning, 2) }}">{{ \App\Helpers\Helper::abbreviateNumber($revenueReportTotalEarning) }}</h2>
            <div class="d-flex justify-content-center">
              <span class="font-weight-bolder mr-25">{{ __('Net') }}:</span>
              <span title="{{ number_format($revenueReportNet, 2) }}">{{ \App\Helpers\Helper::abbreviateNumber($revenueReportNet) }}</span>
            </div>
            <div id="budget-chart"></div>
            <button type="button" class="btn btn-primary">{{ __('View Details') }}</button>
          </div>
        </div>
      </div>
    </div>
    <!--/ Revenue Report Card -->
  </div>

  <div class="row match-height">
    
    <!-- Top Customers Table Card -->
    <div class="col-lg-8 col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ __('Top 10 Customers') }}</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 top-customers-table">
              <thead>
                <tr>
                  <th>{{ __('Customer (Email - ID)') }}</th>
                  <th>{{ __('Top Category') }}</th>
                  <th class="text-end">{{ __('Historical Orders') }}</th>
                  <th class="text-end">{{ __('Historical Sales') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($topCustomers as $customer)
                  <tr>
                    <td>
                      {{ $customer->email }}@if($customer->identificacion) - {{ $customer->identificacion }}@endif
                    </td>
                    <td>{{ $customer->top_category ?? __('No data') }}</td>
                    <td class="text-end">{{ number_format($customer->orders_count) }}</td>
                    <td class="text-end" title="{{ number_format($customer->total_sales, 2) }}">
                      {{ \App\Helpers\Helper::abbreviateNumber($customer->total_sales) }}
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center">{{ __('No records found.') }}</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!--/ Top Customers Table Card -->

    <!-- Transaction Card -->
    <div class="col-lg-4 col-md-6 col-12">
      <div class="card card-transaction">
        <div class="card-header">
          <h4 class="card-title">{{ __('Transactions') }}</h4>
        </div>
        <div class="card-body">
          @forelse($paymentMethodPercentages as $item)
            <div class="transaction-item">
              <div class="media">
                <div class="avatar {{ $item->bg_class }} rounded">
                  <div class="avatar-content">
                    <i data-feather="{{ $item->icon }}" class="avatar-icon font-medium-3"></i>
                  </div>
                </div>
                <div class="media-body">
                  <h6 class="transaction-title">{{ $item->label }}</h6>
                  <small>{{ __('Payment Method Share') }}</small>
                </div>
              </div>
              <div class="font-weight-bolder {{ $item->text_class }}">{{ $item->percent }}%</div>
            </div>
          @empty
            <div class="text-center text-muted py-2">{{ __('No records found.') }}</div>
          @endforelse
        </div>
      </div>
    </div>
    <!--/ Transaction Card -->

    

    <!-- Top Products Table Card -->
    <div class="col-lg-8 col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ __('Top 10 Products') }}</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 top-products-table">
              <thead>
                <tr>
                  <th>{{ __('Product') }}</th>
                  <th class="text-end">{{ __('Units Sold') }}</th>
                  <th class="text-end">{{ __('Last Sale (MM-DD-YYYY)') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($topProducts as $product)
                  <tr>
                    <td>{{ $product->name }}</td>
                    <td class="text-end">{{ number_format($product->units_sold) }}</td>
                    <td class="text-end">
                      @if(!empty($product->last_sale_at))
                        {{ \Carbon\Carbon::parse($product->last_sale_at)->format('m-d-Y') }}
                      @else
                        --
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="3" class="text-center">{{ __('No records found.') }}</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!--/ Top Products Table Card -->

    <!-- Developer Meetup Card -->
    {{-- <div class="col-lg-4 col-md-6 col-12">
      <div class="card card-developer-meetup">
        <div class="meetup-img-wrapper rounded-top text-center">
          <img src="{{asset('images/illustration/email.svg')}}" alt="Meeting Pic" height="170" />
        </div>
        <div class="card-body">
          <div class="meetup-header d-flex align-items-center">
            <div class="meetup-day">
              <h6 class="mb-0">THU</h6>
              <h3 class="mb-0">24</h3>
            </div>
            <div class="my-auto">
              <h4 class="card-title mb-25">Developer Meetup</h4>
              <p class="card-text mb-0">Meet world popular developers</p>
            </div>
          </div>
          <div class="media">
            <div class="avatar bg-light-primary rounded mr-1">
              <div class="avatar-content">
                <i data-feather="calendar" class="avatar-icon font-medium-3"></i>
              </div>
            </div>
            <div class="media-body">
              <h6 class="mb-0">Sat, May 25, 2020</h6>
              <small>10:AM to 6:PM</small>
            </div>
          </div>
          <div class="media mt-2">
            <div class="avatar bg-light-primary rounded mr-1">
              <div class="avatar-content">
                <i data-feather="map-pin" class="avatar-icon font-medium-3"></i>
              </div>
            </div>
            <div class="media-body">
              <h6 class="mb-0">Central Park</h6>
              <small>Manhattan, New york City</small>
            </div>
          </div>
          <div class="avatar-group">
            <div
              data-toggle="tooltip"
              data-popup="tooltip-custom"
              data-placement="bottom"
              data-original-title="Billy Hopkins"
              class="avatar pull-up"
            >
              <img src="{{asset('images/portrait/small/avatar-s-9.jpg')}}" alt="Avatar" width="33" height="33" />
            </div>
            <div
              data-toggle="tooltip"
              data-popup="tooltip-custom"
              data-placement="bottom"
              data-original-title="Amy Carson"
              class="avatar pull-up"
            >
              <img src="{{asset('images/portrait/small/avatar-s-6.jpg')}}" alt="Avatar" width="33" height="33" />
            </div>
            <div
              data-toggle="tooltip"
              data-popup="tooltip-custom"
              data-placement="bottom"
              data-original-title="Brandon Miles"
              class="avatar pull-up"
            >
              <img src="{{asset('images/portrait/small/avatar-s-8.jpg')}}" alt="Avatar" width="33" height="33" />
            </div>
            <div
              data-toggle="tooltip"
              data-popup="tooltip-custom"
              data-placement="bottom"
              data-original-title="Daisy Weber"
              class="avatar pull-up"
            >
              <img
                src="{{asset('images/portrait/small/avatar-s-20.jpg')}}"
                alt="Avatar"
                width="33"
                height="33"
              />
            </div>
            <div
              data-toggle="tooltip"
              data-popup="tooltip-custom"
              data-placement="bottom"
              data-original-title="Jenny Looper"
              class="avatar pull-up"
            >
              <img
                src="{{asset('images/portrait/small/avatar-s-20.jpg')}}"
                alt="Avatar"
                width="33"
                height="33"
              />
            </div>
            <h6 class="align-self-center cursor-pointer ml-50 mb-0">+42</h6>
          </div>
        </div>
      </div>
    </div> --}}
    <!--/ Developer Meetup Card -->

    <!-- Browser States Card -->
    {{-- <div class="col-lg-4 col-md-6 col-12">
      <div class="card card-browser-states">
        <div class="card-header">
          <div>
            <h4 class="card-title">Browser States</h4>
            <p class="card-text font-small-2">Counter August 2020</p>
          </div>
          <div class="dropdown chart-dropdown">
            <i data-feather="more-vertical" class="font-medium-3 cursor-pointer" data-toggle="dropdown"></i>
            <div class="dropdown-menu dropdown-menu-right">
              <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
              <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
              <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="browser-states">
            <div class="media">
              <img
                src="{{asset('images/icons/google-chrome.png')}}"
                class="rounded mr-1"
                height="30"
                alt="Google Chrome"
              />
              <h6 class="align-self-center mb-0">Google Chrome</h6>
            </div>
            <div class="d-flex align-items-center">
              <div class="font-weight-bold text-body-heading mr-1">54.4%</div>
              <div id="browser-state-chart-primary"></div>
            </div>
          </div>
          <div class="browser-states">
            <div class="media">
              <img
                src="{{asset('images/icons/mozila-firefox.png')}}"
                class="rounded mr-1"
                height="30"
                alt="Mozila Firefox"
              />
              <h6 class="align-self-center mb-0">Mozila Firefox</h6>
            </div>
            <div class="d-flex align-items-center">
              <div class="font-weight-bold text-body-heading mr-1">6.1%</div>
              <div id="browser-state-chart-warning"></div>
            </div>
          </div>
          <div class="browser-states">
            <div class="media">
              <img
                src="{{asset('images/icons/apple-safari.png')}}"
                class="rounded mr-1"
                height="30"
                alt="Apple Safari"
              />
              <h6 class="align-self-center mb-0">Apple Safari</h6>
            </div>
            <div class="d-flex align-items-center">
              <div class="font-weight-bold text-body-heading mr-1">14.6%</div>
              <div id="browser-state-chart-secondary"></div>
            </div>
          </div>
          <div class="browser-states">
            <div class="media">
              <img
                src="{{asset('images/icons/internet-explorer.png')}}"
                class="rounded mr-1"
                height="30"
                alt="Internet Explorer"
              />
              <h6 class="align-self-center mb-0">Internet Explorer</h6>
            </div>
            <div class="d-flex align-items-center">
              <div class="font-weight-bold text-body-heading mr-1">4.2%</div>
              <div id="browser-state-chart-info"></div>
            </div>
          </div>
          <div class="browser-states">
            <div class="media">
              <img src="{{asset('images/icons/opera.png')}}" class="rounded mr-1" height="30" alt="Opera Mini" />
              <h6 class="align-self-center mb-0">Opera Mini</h6>
            </div>
            <div class="d-flex align-items-center">
              <div class="font-weight-bold text-body-heading mr-1">8.4%</div>
              <div id="browser-state-chart-danger"></div>
            </div>
          </div>
        </div>
      </div>
    </div> --}}
    <!--/ Browser States Card -->

    <!-- Goal Overview Card -->
    {{-- <div class="col-lg-4 col-md-6 col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="card-title">Goal Overview</h4>
          <i data-feather="help-circle" class="font-medium-3 text-muted cursor-pointer"></i>
        </div>
        <div class="card-body p-0">
          <div id="goal-overview-radial-bar-chart" class="my-2"></div>
          <div class="row border-top text-center mx-0">
            <div class="col-6 border-right py-1">
              <p class="card-text text-muted mb-0">Completed</p>
              <h3 class="font-weight-bolder mb-0">786,617</h3>
            </div>
            <div class="col-6 py-1">
              <p class="card-text text-muted mb-0">In Progress</p>
              <h3 class="font-weight-bolder mb-0">13,561</h3>
            </div>
          </div>
        </div>
      </div>
    </div> --}}
    <!--/ Goal Overview Card -->

    
  </div>
</section>
<!-- Dashboard Ecommerce ends -->
@endsection

@section('vendor-script')
  {{-- vendor files --}}
  <script src="{{ asset(mix('vendors/js/charts/apexcharts.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/extensions/toastr.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection
@section('page-script')
  {{-- Page js files --}}
  <script>
    window.dashboardEarnings = {!! json_encode(['labels' => $earningsLabels ?? [], 'series' => $earningsSeries ?? []]) !!};
    window.revenueReport = {!! json_encode([
      'labels' => $revenueReportLabels ?? [],
      'earning' => $revenueReportEarning ?? [],
      'expense' => $revenueReportExpense ?? [],
      'names' => [__('Income'), __('Expenses')]
    ]) !!};
  </script>
  <script>
    $(document).ready(function () {
      var locale = "{{ app()->getLocale() }}";
      var languageConfig = {};
      if (locale === 'es') {
        languageConfig = { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' };
      }

      $('.top-customers-table').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        order: [[3, 'desc']],
        language: languageConfig,
        drawCallback: function () {
          if (feather) {
            feather.replace({ width: 14, height: 14 });
          }
        }
      });

      $('.top-products-table').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        order: [[1, 'desc']],
        language: languageConfig,
        drawCallback: function () {
          if (feather) {
            feather.replace({ width: 14, height: 14 });
          }
        }
      });
    });
  </script>
  <script src="{{ asset(mix('js/scripts/pages/dashboard-ecommerce.js')) }}"></script>
@endsection
