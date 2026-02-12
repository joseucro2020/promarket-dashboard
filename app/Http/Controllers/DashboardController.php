<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\BuyOrder;
use App\Models\BuyOrderDetail;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
      // Obtener datos reales para el dashboard
      $sales = BuyOrder::count(); // Número de órdenes de compra
      $customers = User::count(); // Número de usuarios/clientes
      $products = Product::where('status','1')->count(); // Número de productos publicados
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
        $query = DB::table('purchases')
          ->join('users', 'users.id', '=', 'purchases.user_id')
          ->join('purchase_details', 'purchase_details.purchase_id', '=', 'purchases.id')
          ->groupBy('users.id', 'users.email', 'users.identificacion')
          ->select(
            'users.id',
            'users.email',
            'users.identificacion',
            DB::raw('COUNT(DISTINCT purchases.id) as orders_count'),
            DB::raw('SUM(purchase_details.price * purchase_details.quantity) as total_sales')
          )
          ->orderByDesc('total_sales')
          ->limit(10);

        if (!is_null($status)) {
          $query->where('purchases.status', $status);
        }

        return $query->get();
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

      return view('panel.dashboard.dashboard', compact(
        'sales', 'customers', 'products', 'revenue', 'profit',
        'earningsCurrent', 'earningsPrev', 'earningsPercent', 'earningsDirection',
        'earningsLabels', 'earningsSeries',
        'revenueReportLabels', 'revenueReportEarning', 'revenueReportExpense',
        'revenueReportTotalEarning', 'revenueReportTotalExpense', 'revenueReportNet',
        'topCustomers'
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
