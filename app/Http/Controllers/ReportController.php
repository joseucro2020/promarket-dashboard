<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Referral;
use App\Models\Replenishment;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Illuminate\Support\Str;
use App\Exports\DynamicReportExport;

class ReportController extends Controller
{
    public function lucro()
    {
        return view('panel.reports.profit-share');
    }

    public function index($report)
    {
        $products = [];
        $productsList = [];

        if ($report == 'products') {
            $products = DB::table('products')
                ->join('product_colors', 'products.id', '=', 'product_colors.product_id')
                ->join('product_amount', 'product_colors.id', '=', 'product_amount.product_color_id')
                ->leftJoin('taxes', 'products.taxe_id', '=', 'taxes.id')
                ->whereIn('products.status', ['1', '0'])
                ->whereNull('product_amount.deleted_at')
                ->select(
                    'products.name',
                    'products.id as idproduc',
                    'product_amount.presentation',
                    'product_amount.price',
                    'product_amount.cost',
                    'product_amount.id',
                    'product_amount.unit',
                    'product_amount.amount',
                    'taxes.name as impuesto',
                    'taxes.percentage as porcentaje'
                )
                ->get();
        }

        if ($report == 'top-products') {
            $productsList = Product::query()
                ->select('id', 'name')
                ->whereIn('status', ['1', '0'])
                ->orderBy('name')
                ->get();
        }

        return view('panel.reports.' . $report, [
            'products' => $products,
            'productsList' => $productsList,
        ]);
    }

    public function purchases($type, $from, $to)
    {
        $query = $this->parseTypeQuery($type);

        function sumFormat($field, $alias)
        {
            return "SUM($field) AS $alias";
        }

        return Purchase::select(
            DB::raw($query . ' AS label'),
            DB::raw(sumFormat('purchases.subtotal_bruto', 'purchases')),
            DB::raw(sumFormat('purchases.subtotal_bruto * exchange_rates.change', 'purchases_bs')),
            DB::raw(sumFormat('purchases.subtotal', 'purchases_neta')),
            DB::raw(sumFormat('purchases.subtotal * exchange_rates.change', 'purchases_neta_bs')),
            DB::raw(sumFormat('purchases.utilidad_bruta', 'utility')),
            DB::raw(sumFormat('purchases.utilidad_bruta * exchange_rates.change', 'utility_bs')),
            DB::raw(sumFormat('purchases.utilidad', 'utility_neta')),
            DB::raw(sumFormat('purchases.utilidad * exchange_rates.change', 'utility_neta_bs')),
            DB::raw("ROUND(SUM(purchases.utilidad_bruta) / SUM(purchases.subtotal_bruto) * 100, 2) AS utility_percentage"),
            DB::raw("ROUND(SUM(purchases.utilidad) / SUM(purchases.subtotal) * 100, 2) AS utility_neta_percentage")
        )
            ->whereBetween(DB::raw('date(purchases.created_at)'), [
                $from,
                $to
            ])
            ->join('exchange_rates', 'exchange_rates.id', '=', 'purchases.exchange_rate_id')
            ->groupBy(DB::raw($query))
            ->where('status', Purchase::STATUS_COMPLETED)
            ->get();
    }

    public function orders($from, $to)
    {
        $pending = Purchase::STATUS_ONHOLD;
        $processing = Purchase::STATUS_PROCESSING;
        $completed = Purchase::STATUS_COMPLETED;

        return Purchase::select(
            DB::raw('COUNT(purchases.id) as orders'),
            DB::raw('date(created_at) as label'),
            DB::raw("SUM(CASE WHEN status = $pending THEN 1 ELSE 0 END) as pending"),
            DB::raw("SUM(CASE WHEN status = $processing THEN 1 ELSE 0 END) as processing"),
            DB::raw("SUM(CASE WHEN status = $completed THEN 1 ELSE 0 END) as completed")
        )
            ->whereBetween(DB::raw('date(purchases.created_at)'), [
                $from,
                $to
            ])
            ->groupBy(DB::raw('date(purchases.created_at)'))
            ->whereIn('status', [$pending, $processing, $completed])
            ->get();
    }

    public function getUnitType($unit)
    {
        switch ($unit) {
            case 1:
                return 'Gr';
            case 2:
                return 'Kg';
            case 3:
                return 'Ml';
            case 4:
                return 'L';
            case 5:
                return 'Cm';
            default:
                return null;
        }
    }

    public function products($from, $to, $idProd = null)
    {
        $products = Product::select(
            'products.id',
            'products.name',
            'products.variable',
            'products.price_1',
            'products.price_2',
            'products.created_at',
            'products.updated_at',
            'products.company_id',
            'purchase_details.product_amount_id',
            'product_amount.unit',
            'product_amount.presentation',
            'product_amount.amount as amount',
            'product_amount.cost as cost',
            'product_amount.umbral as umbral',
            'product_amount.min as min',
            'product_amount.max as max',
            'product_amount.price as price',
            'categories.id as idcategories',
            'categories.name as namecategories',
            'subcategories.id as idsubcategories',
            'subcategories.name as namesubcategories',
            'categories.id_father as padre',
            DB::raw('SUM(purchase_details.quantity) as purchases_number'),
                DB::raw("CASE products.status \
                    WHEN '0' THEN 'INACTIVO' \
                    WHEN '1' THEN 'ACTIVO' \
                    ELSE 'ELIMINADO' END \
                    AS status")
        )
            ->whereBetween(DB::raw('date(purchases.created_at)'), [
                $from,
                $to
            ])
            ->when(!empty($idProd), function ($q) use ($idProd) {
                $q->where('products.id', $idProd);
            })
            ->join('product_colors', 'product_colors.product_id', '=', 'products.id')
            ->join('product_amount', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->join('purchase_details', 'purchase_details.product_amount_id', '=', 'product_amount.id')
            ->join('purchases', 'purchases.id', '=', 'purchase_details.purchase_id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('subcategories', 'products.subcategory_id',  '=', 'subcategories.id')
            ->groupBy(['product_amount.presentation', 'products.id', 'products.status'])
            ->where('purchases.status', Purchase::STATUS_COMPLETED)
            ->orderBy(DB::raw('SUM(purchase_details.quantity)'), 'DESC')
            ->get();
        foreach ($products as $key => $product) {
            $unit = $this->getUnitType($product['unit']);
            $pre = '';
            if (is_null($unit)) {
                $unit = '';
            }
            if (isset($product['presentation']) && $product['presentation'] > 0.00) {
                $pre = $product['presentation'];
            }
            $product['presentation_formatted'] = $product['name'] . ' ' . $pre . ' ' . $unit;
        }
        return $products;
    }

    public function products2021($from, $to, $idProd = null)
    {
        $products = Product::select(
            'products.id',
            'products.name',
            'purchase_details.product_amount_id',
            'product_amount.unit',
            'product_amount.presentation',
            'product_amount.amount as amount',
            'product_amount.cost as cost',
            DB::raw('SUM(purchase_details.quantity) as purchases_number')

        )
            ->whereBetween(DB::raw('date(purchases.created_at)'), [
                $from,
                $to
            ])
            ->when(!empty($idProd), function ($q) use ($idProd) {
                $q->where('products.id', $idProd);
            })
            ->join('product_colors', 'product_colors.product_id', '=', 'products.id')
            ->join('product_amount', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->join('purchase_details', 'purchase_details.product_amount_id', '=', 'product_amount.id')
            ->join('purchases', 'purchases.id', '=', 'purchase_details.purchase_id')
            ->groupBy('product_amount.presentation', 'products.id')
            ->where('purchases.status', Purchase::STATUS_COMPLETED)
            ->orderBy(DB::raw('SUM(purchase_details.quantity)'), 'DESC')
            ->get();


        foreach ($products as $key => $product) {
            $unit = $this->getUnitType($product['unit']);
            $pre = '';
            if (is_null($unit)) {
                $unit = '';
            }
            if (isset($product['presentation']) && $product['presentation'] > 0.00) {
                $pre = $product['presentation'];
            }
            $product['presentation_formatted'] = $product['name'] . ' ' . $pre . ' ' . $unit;
        }

        return $products;
    }

    public function categories($from, $to)
    {
        $products = Product::select(
            'categories.name as name',
            'subcategories.name as subname',
            DB::raw('SUM(purchase_details.quantity) as purchases_number'),
            DB::raw('FORMAT(SUM(purchase_details.price * purchase_details.quantity),2) as purchases_sales')
        )
            ->whereBetween(DB::raw('date(purchases.created_at)'), [
                $from,
                $to
            ])
            ->join('product_colors', 'product_colors.product_id', '=', 'products.id')
            ->join('product_amount', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->join('purchase_details', 'purchase_details.product_amount_id', '=', 'product_amount.id')
            ->join('purchases', 'purchases.id', '=', 'purchase_details.purchase_id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('subcategories', 'products.subcategory_id',  '=', 'subcategories.id')
            ->where('purchases.status', Purchase::STATUS_COMPLETED)
            ->groupBy(['categories.name', 'subcategories.name'])
            ->orderBy(DB::raw('SUM(purchase_details.quantity)'), 'DESC')
            ->get();

        return $products;
    }

    public function referrals()
    {
        $referrals = Referral::with([
            'referrer' => function ($queryReferrer) {
                $queryReferrer->select('id', 'name', 'identificacion', 'persona');
            },
            'referred' => function ($queryReferred) {
                $queryReferred->select('id', 'name', 'identificacion', 'persona');
            },
            'coupon' => function ($queryCoupon) {
                $queryCoupon->select('id', 'code');
            }
        ])
            ->latest()
            ->get();

        return $referrals;
    }

    public function users($from, $to)
    {
        $users = User::select(
            'users.name as name',
            'users.email as email',
            DB::raw('date(users.created_at) as date'),
            DB::raw('min(purchases.created_at) as primer')
        )
            ->join('purchases', 'users.id', '=', 'purchases.user_id')
            ->where('users.status', '=', '1')
            ->whereBetween(DB::raw('date(users.created_at)'), [
                $from,
                $to
            ])
            ->groupBy(['users.name', 'users.email', 'users.created_at'])
            ->orderBy(DB::raw('date(users.created_at)'))
            ->get();

        return $users;
    }

    public function excel($report, Request $request)
    {
        $view = 'panel.excel.' . $report;

        $data = [
            'data' => $request->input('data', []),
            'type' => $request->input('report_type', ''),
            'to' => $request->input('to', ''),
            'from' => $request->input('from', ''),
            'months' => $this->getMonths(),
        ];

        return Excel::download(new DynamicReportExport($view, $data), 'Reporte.xls');
    }    

    public function pdf($report, Request $request)
    {
        $file = PDF::loadView('panel.pdf.' . $report, [
            'data'   => $request->data,
            'type'   => $request->has('report_type') ? $request->report_type : '',
            'months' => $this->getMonths(),
            'from'   => $request->from ? Carbon::parse($request->from)->format('d-m-Y') : null,
            'to'     => $request->to ? Carbon::parse($request->to)->format('d-m-Y') : null,
        ]);

        $file_name = 'REPORTE-' . Carbon::now()->format('d-m-Y') . strtoupper(Str::random(10));

        return $file->stream($file_name . '.pdf', array("Attachment" => false))->header('Content-Type', 'application/pdf');
    }

    private function parseTypeQuery($type)
    {
        switch ($type) {
            case 'daily':
                return 'date(purchases.created_at)';
            case 'weekly':
                return 'YEARWEEK(purchases.created_at, 1)';
            case 'monthly':
                return 'MONTH(purchases.created_at)';
            case 'yearly':
                return 'YEAR(purchases.created_at)';
            default:
                return 'date(purchases.created_at)';
        }
    }

    private function getMonths()
    {
        return [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
    }

    public function mermafilter(Request $request)
    {
        $reps =  Replenishment::select('replenishments.*')
            ->join('product_amount', 'replenishments.product_presentation', '=', 'product_amount.id')
            ->join('products', 'product_amount.product_color_id', '=', 'products.id')
            ->whereNull('replenishments.deleted_at')
            ->with(['presentation.product', 'user', 'purchase'])
            ->orderBy('replenishments.id', 'DESC');
        if (isset($request->from)) {
            $start = Carbon::parse($request->from)->format('Y-m-d 00:00:00');
            $reps = $reps->where('replenishments.created_at', '>=', $start);
        }
        if (isset($request->to)) {
            $end = Carbon::parse($request->to)->format('Y-m-d 23:59:59');
            $reps = $reps->where('replenishments.created_at', '<=', $end);
        }
        if (isset($request->ajuste)) {
            $reps = $reps->where('replenishments.fit_type', '=', $request->ajuste);
        }
        $reps = $reps->paginate(500);
        return response()->json([
            'reps' => $reps
        ]);
    }

    public function mermaexcel($report, Request $request)
    {
        $reps =  Replenishment::select('replenishments.*')
            ->join('product_amount', 'replenishments.product_presentation', '=', 'product_amount.id')
            ->join('products', 'product_amount.product_color_id', '=', 'products.id')
            ->whereNull('replenishments.deleted_at')
            ->with(['presentation.product', 'user', 'purchase'])
            ->orderBy('replenishments.id', 'DESC');
        if (isset($request->from)) {
            $start = Carbon::parse($request->from)->format('Y-m-d 00:00:00');
            $reps = $reps->where('replenishments.created_at', '>=', $start);
        }
        if (isset($request->to)) {
            $end = Carbon::parse($request->to)->format('Y-m-d 23:59:59');
            $reps = $reps->where('replenishments.created_at', '<=', $end);
        }
        if (isset($request->ajuste)) {
            $reps = $reps->where('replenishments.fit_type', '=', $request->ajuste);
        }
        $data = $reps->get();
        $today = Carbon::parse()->format('d-m-Y h:i A');
        $file = Excel::create('historico-merma', function ($excel) use ($request, $today) {
            $excel->setCreator('dparragam')->setCompany('Viveres&Abarrotes');
            $excel->setDescription('Histórico de Merma');
            $excel->sheet('Listado', function ($sheet) use ($request, $today) {

                $sheet->setWidth('A', 10);
                $sheet->setWidth('B', 50);
                $sheet->setWidth('C', 20);
                $sheet->setWidth('D', 20);
                $sheet->setWidth('E', 20);
                $sheet->setWidth('F', 20);
                $sheet->setWidth('G', 20);

                $sheet->loadView('panel.excel.merma-report')->with([
                    'reps' => $request->data,
                    'today' => $today,
                ]);
            });
        })->download();
    }

    public function lucrofilter(Request $request)
    {
        $lucro = Category::select(
            'categories.id_father',
            'father.name as type',
            DB::raw('SUM(purchase_details.quantity) as units_sold'),
            DB::raw('SUM(purchase_details.price * purchase_details.quantity) as amount_sold'),
            DB::raw('SUM((purchase_details.price - product_amount.cost) * purchase_details.quantity) as profit')
        )
            ->join('categories as father', 'categories.id_father', '=', 'father.id')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('product_colors', 'products.id', '=', 'product_colors.product_id')
            ->join('product_amount', 'product_colors.id', '=', 'product_amount.product_color_id')
            ->join('purchase_details', 'product_amount.id', '=', 'purchase_details.product_amount_id')
            ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->groupBy(['categories.id_father', 'father.name'])
            ->where('categories.status', '1')
            ->where('purchases.status', '3');
        if (isset($request->from)) {
            $start = Carbon::parse($request->from)->format('Y-m-d 00:00:00');
            $lucro = $lucro->where('purchases.created_at', '>=', $start);
        }
        if (isset($request->to)) {
            $end = Carbon::parse($request->to)->format('Y-m-d 23:59:59');
            $lucro = $lucro->where('purchases.created_at', '<=', $end);
        }
        $lucro = $lucro->get();

        $comparativo = Category::select(
            'categories.id_father',
            'father.name as type',
            DB::raw('SUM(purchase_details.quantity) as units_sold'),
            DB::raw('SUM(purchase_details.price * purchase_details.quantity) as amount_sold'),
            DB::raw('SUM((purchase_details.price - product_amount.cost) * purchase_details.quantity) as profit')
        )
            ->join('categories as father', 'categories.id_father', '=', 'father.id')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('product_colors', 'products.id', '=', 'product_colors.product_id')
            ->join('product_amount', 'product_colors.id', '=', 'product_amount.product_color_id')
            ->join('purchase_details', 'product_amount.id', '=', 'purchase_details.product_amount_id')
            ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->groupBy(['categories.id_father', 'father.name'])
            ->where('categories.status', '1')
            ->where('purchases.status', '3');

        if (isset($request->fromcom)) {
            $start = Carbon::parse($request->fromcom)->format('Y-m-d 00:00:00');
            $comparativo = $comparativo->where('purchases.created_at', '>=', $start);
        }

        if (isset($request->tocom)) {
            $end = Carbon::parse($request->tocom)->format('Y-m-d 23:59:59');
            $comparativo = $comparativo->where('purchases.created_at', '<=', $end);
        }

        $comparativo = $comparativo->get();

        return response()->json([
            'lucro' => $lucro,
            'comparativo' => $comparativo,
        ]);
    }
}
