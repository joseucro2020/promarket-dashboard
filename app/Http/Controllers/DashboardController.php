<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\BuyOrder;
use App\Models\BuyOrderDetail;
use App\Models\Deposit;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
  public function index()
  {
    // $slowQueryThresholdMs = 200;
    // if (app()->environment('local')) {
    //   DB::listen(function ($query) use ($slowQueryThresholdMs) {
    //     if ($query->time < $slowQueryThresholdMs) {
    //       return;
    //     }

    //     Log::info('Slow query on dashboard', [
    //       'time_ms' => $query->time,
    //       'sql' => $query->sql,
    //       'bindings' => $query->bindings,
    //       'path' => request()->path(),
    //     ]);
    //   });
    // }

    // Obtener datos reales para el dashboard
    $sales = BuyOrder::count(); // Número de órdenes de compra
    $customers = User::count(); // Número de usuarios/clientes
    $products = Product::where('status', '1')->count(); // Número de productos publicados
    $revenue = User::where('created_at', '>=', now()->subMonth())->count(); // Número de nuevos clientes en el último mes
    $profit = BuyOrderDetail::sum('total'); // Suma total de ganancias

    // Earnings: ingresos del mes actual y comparación con el mes anterior
    $earningsCurrent = BuyOrderDetail::whereYear('created_at', now()->year)
      ->whereMonth('created_at', now()->month)
      ->sum('total');

    $prev = now()->subMonth();
    $earningsPrev = BuyOrderDetail::whereYear('created_at', $prev->year)
      ->whereMonth('created_at', $prev->month)
      ->sum('total');

    $earningsPercent = null;
    $earningsDirection = null; // 'up' or 'down' or null
    if ($earningsPrev > 0) {
      $earningsPercent = round((($earningsCurrent - $earningsPrev) / $earningsPrev) * 100, 1);
      $earningsDirection = $earningsPercent >= 0 ? 'up' : 'down';
    }

    // Series para el gráfico: últimos 6 meses (labels y valores)
    $earningsLabels = [];
    $earningsSeries = [];
    for ($i = 5; $i >= 0; $i--) {
      $date = Carbon::now()->subMonths($i);
      $earningsLabels[] = $date->format('M');
      $sum = BuyOrderDetail::whereYear('created_at', $date->year)
        ->whereMonth('created_at', $date->month)
        ->sum('total');
      $earningsSeries[] = (float) $sum;
    }

    // Revenue report: ingresos (total) y gastos (costo) por mes para el año actual
    $requestedYear = (int) request()->get('year', now()->year);
    $currentYear = now()->year;
    $reportYear = ($requestedYear >= ($currentYear - 10) && $requestedYear <= $currentYear)
      ? $requestedYear
      : $currentYear;
    $revenueReportLabels = [];
    $revenueReportEarning = [];
    $revenueReportExpense = [];
    $revenueReportTotalEarning = 0.0;
    $revenueReportTotalExpense = 0.0;
    for ($m = 1; $m <= 12; $m++) {
      $date = Carbon::createFromDate($reportYear, $m, 1);
      // etiqueta mes traducida corto
      $revenueReportLabels[] = $date->translatedFormat('M');
      $earning = BuyOrderDetail::whereYear('created_at', $reportYear)
        ->whereMonth('created_at', $m)
        ->sum('total');
      $expense = BuyOrderDetail::whereYear('created_at', $reportYear)
        ->whereMonth('created_at', $m)
        ->sum('costo');
      $revenueReportEarning[] = (float) $earning;
      // usar valores negativos para expense para que el chart muestre barras por debajo
      $revenueReportExpense[] = -1 * (float) $expense;
      $revenueReportTotalEarning += (float) $earning;
      $revenueReportTotalExpense += (float) $expense;
    }
    $revenueReportNet = $revenueReportTotalEarning - $revenueReportTotalExpense;

    // Top 10 mejores clientes (por ventas historicas)
    $buildTopCustomersBase = function ($status = null) {
      $cacheKey = 'dashboard.top_customers.' . ($status ?? 'all');

      return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($status) {
        $aggregated = DB::table('purchases as p')
          ->join('purchase_details as pd', 'pd.purchase_id', '=', 'p.id')
          ->select(
            'p.user_id',
            DB::raw('COUNT(DISTINCT p.id) as orders_count'),
            DB::raw('SUM(pd.price * pd.quantity) as total_sales')
          )
          ->when(!is_null($status), function ($query) use ($status) {
            $query->where('p.status', $status);
          })
          ->groupBy('p.user_id')
          ->orderByDesc('total_sales')
          ->limit(10);

        return DB::table('users as u')
          ->joinSub($aggregated, 't', function ($join) {
            $join->on('t.user_id', '=', 'u.id');
          })
          ->select('u.id', 'u.email', 'u.identificacion', 't.orders_count', 't.total_sales')
          ->orderByDesc('t.total_sales')
          ->get();
      });
    };

    $topCustomersStatus = Purchase::STATUS_COMPLETED;
    $topCustomersBase = $buildTopCustomersBase($topCustomersStatus);
    if ($topCustomersBase->isEmpty()) {
      $topCustomersStatus = null;
      $topCustomersBase = $buildTopCustomersBase();
    }

    $topCustomerIds = $topCustomersBase->pluck('id')->all();
    $categoryRows = [];
    if (!empty($topCustomerIds)) {
      $categoryQuery = DB::table('purchases')
        ->join('purchase_details', 'purchase_details.purchase_id', '=', 'purchases.id')
        ->join('product_amount', 'product_amount.id', '=', 'purchase_details.product_amount_id')
        ->join('product_colors', 'product_colors.id', '=', 'product_amount.product_color_id')
        ->join('products', 'products.id', '=', 'product_colors.product_id')
        ->join('categories', 'categories.id', '=', 'products.category_id')
        ->whereIn('purchases.user_id', $topCustomerIds)
        ->groupBy('purchases.user_id', 'categories.name')
        ->select(
          'purchases.user_id',
          'categories.name as category_name',
          DB::raw('SUM(purchase_details.quantity) as total_qty')
        )
        ->orderByDesc('total_qty')
        ->orderByDesc('total_qty');

      if (!is_null($topCustomersStatus)) {
        $categoryQuery->where('purchases.status', $topCustomersStatus);
      }

      $categoryRows = $categoryQuery->get();
    }

    $categoryByCustomer = collect($categoryRows)
      ->groupBy('user_id')
      ->map(function ($rows) {
        $top = $rows->sortByDesc('total_qty')->first();
        return $top ? $top->category_name : null;
      });

    $topCustomers = $topCustomersBase->map(function ($row) use ($categoryByCustomer) {
      return (object) [
        'email' => $row->email,
        'identificacion' => $row->identificacion,
        'orders_count' => (int) $row->orders_count,
        'total_sales' => (float) $row->total_sales,
        'top_category' => $categoryByCustomer->get($row->id, null)
      ];
    });

    // Top 10 productos con mayor unidades vendidas
    $buildTopProductsBase = function ($status = null) {
      $cacheKey = 'dashboard.top_products.' . ($status ?? 'all');

      return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($status) {
        $aggregated = DB::table('purchases as p')
          ->join('purchase_details as pd', 'pd.purchase_id', '=', 'p.id')
          ->join('product_amount as pa', 'pa.id', '=', 'pd.product_amount_id')
          ->join('product_colors as pc', 'pc.id', '=', 'pa.product_color_id')
          ->select(
            'pc.product_id',
            DB::raw('SUM(pd.quantity) as units_sold'),
            DB::raw('MAX(p.created_at) as last_sale_at')
          )
          ->when(!is_null($status), function ($query) use ($status) {
            $query->where('p.status', $status);
          })
          ->groupBy('pc.product_id')
          ->orderByDesc('units_sold')
          ->limit(10);

        return DB::table('products as pr')
          ->joinSub($aggregated, 't', function ($join) {
            $join->on('t.product_id', '=', 'pr.id');
          })
          ->select('pr.id', 'pr.name', 't.units_sold', 't.last_sale_at')
          ->orderByDesc('t.units_sold')
          ->get();
      });
    };

    $topProductsStatus = Purchase::STATUS_COMPLETED;
    $topProducts = $buildTopProductsBase($topProductsStatus);
    if ($topProducts->isEmpty()) {
      $topProductsStatus = null;
      $topProducts = $buildTopProductsBase();
    }

    // Porcentaje de uso por metodo de pago (desde deposits)
    $depositBase = Deposit::query()->select('id', 'method_code', 'status', 'account', 'gateway', 'fields', 'detail');
    $totalDeposits = (clone $depositBase)->count();

    $bankFromDeposit = function ($deposit) {
      $bank = data_get($deposit->account, 'banco')
        ?? data_get($deposit->account, 'bank')
        ?? data_get($deposit->gateway, 'banco')
        ?? data_get($deposit->gateway, 'bank')
        ?? data_get($deposit->fields, 'banco')
        ?? data_get($deposit->fields, 'bank')
        ?? '';

      if (!empty($bank)) {
        return strtolower((string) $bank);
      }

      return strtolower((string) $deposit->detail);
    };

    $stats = [
      'banesco_mobile' => 0,
      'provincial_mobile' => 0,
      'banesco_transfer' => 0,
      'provincial_transfer' => 0,
      'cashea' => 0,
      'zelle' => 0,
      'stripe' => 0,
      'paypal' => 0,
      'cash' => 0,
    ];

    foreach ($depositBase->get() as $deposit) {
      $method = strtolower((string) (
        data_get($deposit->gateway, 'code')
        ?? data_get($deposit->account, 'code')
        ?? $deposit->method_code
        ?? data_get($deposit->gateway, 'name')
        ?? data_get($deposit->account, 'name')
        ?? ''
      ));
      $bank = $bankFromDeposit($deposit);

      if ($method === 'pago_movil') {
        if (strpos($bank, 'banesco') !== false) {
          $stats['banesco_mobile']++;
        } elseif (strpos($bank, 'provincial') !== false || strpos($bank, 'bbva') !== false) {
          $stats['provincial_mobile']++;
        }
        continue;
      }

      if ($method === 'transferencia') {
        if (strpos($bank, 'banesco') !== false) {
          $stats['banesco_transfer']++;
        } elseif (strpos($bank, 'provincial') !== false || strpos($bank, 'bbva') !== false) {
          $stats['provincial_transfer']++;
        }
        continue;
      }

      if ($method === 'cashea') {
        $stats['cashea']++;
      } elseif ($method === 'zelle') {
        $stats['zelle']++;
      } elseif ($method === 'stripe') {
        $stats['stripe']++;
      } elseif ($method === 'paypal') {
        $stats['paypal']++;
      } elseif ($method === 'efectivo') {
        $stats['cash']++;
      }
    }

    $toPercent = function ($value) use ($totalDeposits) {
      if ($totalDeposits <= 0) {
        return 0;
      }
      return round(((int) $value / $totalDeposits) * 100, 1);
    };

    $paymentMethodPercentages = [
      (object) ['label' => __('Banesco (Mobile Payment)'), 'percent' => $toPercent($stats['banesco_mobile']), 'icon' => 'smartphone', 'bg_class' => 'bg-light-info', 'text_class' => 'text-primary'],
      (object) ['label' => __('Provincial (Mobile Payment)'), 'percent' => $toPercent($stats['provincial_mobile']), 'icon' => 'smartphone', 'bg_class' => 'bg-light-info', 'text_class' => 'text-primary'],
      (object) ['label' => __('Banesco (Transfer)'), 'percent' => $toPercent($stats['banesco_transfer']), 'icon' => 'repeat', 'bg_class' => 'bg-light-success', 'text_class' => 'text-success'],
      (object) ['label' => __('Provincial (Transfer)'), 'percent' => $toPercent($stats['provincial_transfer']), 'icon' => 'repeat', 'bg_class' => 'bg-light-success', 'text_class' => 'text-success'],
      (object) ['label' => __('Cashea'), 'percent' => $toPercent($stats['cashea']), 'icon' => 'shopping-bag', 'bg_class' => 'bg-light-warning', 'text_class' => 'text-warning'],
      (object) ['label' => __('Zelle'), 'percent' => $toPercent($stats['zelle']), 'icon' => 'send', 'bg_class' => 'bg-light-info', 'text_class' => 'text-info'],
      (object) ['label' => __('Stripe'), 'percent' => $toPercent($stats['stripe']), 'icon' => 'credit-card', 'bg_class' => 'bg-light-primary', 'text_class' => 'text-primary'],
      (object) ['label' => __('PayPal'), 'percent' => $toPercent($stats['paypal']), 'icon' => 'dollar-sign', 'bg_class' => 'bg-light-secondary', 'text_class' => 'text-secondary'],
      (object) ['label' => __('Cash'), 'percent' => $toPercent($stats['cash']), 'icon' => 'pocket', 'bg_class' => 'bg-light-danger', 'text_class' => 'text-danger'],
    ];

    return view('panel.dashboard.dashboard', compact(
      'sales',
      'customers',
      'products',
      'revenue',
      'profit',
      'earningsCurrent',
      'earningsPrev',
      'earningsPercent',
      'earningsDirection',
      'earningsLabels',
      'earningsSeries',
      'revenueReportLabels',
      'revenueReportEarning',
      'revenueReportExpense',
      'revenueReportTotalEarning',
      'revenueReportTotalExpense',
      'revenueReportNet',
      'topCustomers',
      'paymentMethodPercentages',
      'topProducts'
    ));
  }

  // Dashboard - Analytics
  public function dashboardAnalytics()
  {
    $pageConfigs = ['pageHeader' => false];

    return view('/content/dashboard/dashboard-analytics', ['pageConfigs' => $pageConfigs]);
  }

  // Dashboard - Ecommerce
  public function dashboardEcommerce()
  {
    $pageConfigs = ['pageHeader' => false];

    return view('/content/dashboard/dashboard-ecommerce', ['pageConfigs' => $pageConfigs]);
  }
}
