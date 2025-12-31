<?php

namespace App\Http\Controllers\Admin;

use App\Models\BuyOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BuyOrderDetail;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductAmount;
use App\Models\ProductProveedor;
use Carbon\Carbon;
use File;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class BuyOrderController extends Controller
{
    public function index()
    {
        return view('panel.buyorder.index');
    }

    public function create()
    {
        $proveedor = Supplier::where('status_prove', '1')->orderBy('nombre_prove', 'ASC')->get();

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

        $currencies = [
            'PYG' => 'PYG',
            'USD' => 'USD'
        ];

        $payment_conditions = [
            1 => 'Crédito',
            2 => 'Contado',
            3 => 'Prepagado'
        ];

        return view('panel.buyorder.create', [
            'suppliers' => $proveedor,
            'products' => $products,
            'currencies' => $currencies,
            'payment_conditions' => $payment_conditions
        ]);
    }

    public function store(Request $request)
    {
        $proveedor_id = $request->proveedor_id;
        $order = BuyOrder::create($request->all());
        $order->almacen_id = 1;
        $order->save();

        $products = json_decode($request->addRows);

        foreach ($products as $product) {
            $newBuyOrderDetail = new BuyOrderDetail();
            $newBuyOrderDetail->order_id = $order->id;
            $newBuyOrderDetail->product_id = $product->id;
            $newBuyOrderDetail->cantidad = $product->cantidad;
            $newBuyOrderDetail->existing = $product->existing;
            $newBuyOrderDetail->costo = $product->costo;
            $newBuyOrderDetail->total = $product->tneto;
            $newBuyOrderDetail->iva = $product->porciva;
            $newBuyOrderDetail->utilidad = $product->utilidad;
            $newBuyOrderDetail->precio = $product->pventa;
            $newBuyOrderDetail->save();

            $idproduc = $product->producto->idproduc ?? null;
            if ($idproduc) {
                $proveedor = ProductProveedor::where('products_id', $idproduc)
                    ->where('proveedor_id', $proveedor_id)
                    ->get();

                if ($proveedor->isEmpty()) {
                    $productProveedor = new ProductProveedor();
                    $productProveedor->products_id = $idproduc;
                    $productProveedor->proveedor_id = $proveedor_id;
                    $productProveedor->save();
                }
            }
        }

        return $products;
    }

    public function edit($id)
    {
        $order = BuyOrder::with([
            'detalles' => function ($detalles) {
                $detalles->with([
                    'product_amount' => function ($amount) {
                        $amount->with(['product']);
                    }
                ]);
            }
        ])
            ->with(['supplier'])
            ->find($id);

        $orderdetails = DB::table('buyorder_detail')
            ->join('product_amount', 'buyorder_detail.product_id', '=', 'product_amount.id')
            ->join('product_colors', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->join('products', 'product_colors.product_id', '=', 'products.id')
            ->whereIn('products.status', ['1', '0'])
            ->where('buyorder_detail.order_id', $id)
            ->select(
                'products.name',
                'buyorder_detail.cantidad',
                'buyorder_detail.existing',
                'buyorder_detail.costo',
                'buyorder_detail.total as tneto',
                'buyorder_detail.iva',
                'buyorder_detail.utilidad',
                'buyorder_detail.precio as pventa',
                'buyorder_detail.product_id as id',
                'product_amount.presentation',
                'product_amount.unit'
            )
            ->get();

        $proveedor = Supplier::where('status_prove', '1')->orderBy('nombre_prove', 'ASC')->get();

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

        $currencies = [
            'PYG' => 'PYG',
            'USD' => 'USD'
        ];

        $payment_conditions = [
            1 => 'Crédito',
            2 => 'Contado',
            3 => 'Prepagado'
        ];

        return view('panel.buyorder.edit', [
            'order' => $order,
            'orderdetails' => $orderdetails,
            'suppliers' => $proveedor,
            'products' => $products,
            'currencies' => $currencies,
            'payment_conditions' => $payment_conditions
        ]);
    }

    public function update(Request $request, $id)
    {
        $order =  BuyOrder::find($id);
        $order->cond_pago = $request->input('cond_pago');
        $order->fecha = $request->input('fecha');
        $order->fecha_vto = $request->input('fecha_vto');
        $order->nro_doc = $request->input('nro_doc');
        $order->moneda = $request->input('moneda');
        $order->proveedor_id = $request->input('proveedor_id');
        $order->almacen_id = $request->input('almacen_id');
        $order->status = $request->input('status');
        $order->reason = $request->input('reason');
        $order->save();

        $products = json_decode($request->addRows);

        $res = BuyOrderDetail::where('order_id', $id)->delete();

        foreach ($products as $product) {
            $newBuyOrderDetail = new BuyOrderDetail();
            $newBuyOrderDetail->order_id = $order->id;
            $newBuyOrderDetail->product_id = $product->id;
            $newBuyOrderDetail->cantidad = $product->cantidad;
            $newBuyOrderDetail->existing = $product->existing;
            $newBuyOrderDetail->costo = $product->costo;
            $newBuyOrderDetail->total = $product->tneto;
            $newBuyOrderDetail->iva = $product->porciva;
            $newBuyOrderDetail->utilidad = $product->utilidad;
            $newBuyOrderDetail->precio = $product->pventa;
            $newBuyOrderDetail->save();
        }

        return $products;
    }

    public function destroy($id)
    {
        $design = BuyOrder::find($id);
        $design->status = '4';
        $design->save();

        return response()->json(['result' => true, 'message' => 'Orden de compra eliminado exitosamnete']);
    }

    public function aprobar($id, $row)
    {
        DB::beginTransaction();

        try {
            $orderDetail =  BuyOrderDetail::where('order_id', $id)->get();

            foreach ($orderDetail as $key => $value) {
                $conts = ProductAmount::where('id', '=', $value->product_id)->get();

                $product = DB::table('product_amount')
                    ->join('product_colors', 'product_amount.product_color_id', '=', 'product_colors.id')
                    ->join('products', 'product_colors.product_id', '=', 'products.id')
                    ->where('product_amount.id', $value->product_id)
                    ->select(
                        'products.name',
                        'products.variable',
                        'products.id'
                    )
                    ->first();

                if ($product->variable == 0) {
                    $products = Product::where('id', $product->id)
                        ->first();
                    $products->price_1 = $value->precio;
                    $products->save();
                }

                foreach ($conts as $key => $cont) {
                    $cont->amount =  $cont->amount + $value->cantidad;
                    $cont->price = $value->precio;
                    $cont->cost = $value->costo;
                    $cont->update();
                }
            }

            $order =  BuyOrder::find($id);
            $order->status = 3;
            $order->save();

            DB::commit();

            return response()->json(
                [
                    'status' => 'ok',
                    'message' => 'Orden de compra APROBDA exitosamente!',
                    'data' => $orderDetail
                ],
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                [
                    'status' => 'failed',
                    'message' => 'Orden de compra no pudo ser APROBADA!' . $e,
                ],
                400
            );
        }
    }

    public function anular($id)
    {
        DB::beginTransaction();
        try {
            $order =  BuyOrder::find($id);
            $order->status = 2;
            $order->save();
            DB::commit();

            return response()->json(
                [
                    'status' => 'ok',
                    'message' => 'Orden de compra ANULADA exitosamente!',
                    'data' => $order
                ],
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                [
                    'status' => 'failed',
                    'message' => 'Orden de compra no pudo ser ANULADA!' . $e,
                ],
                400
            );
        }
    }

    public function exportExcel(Request $request)
    {
        $today = Carbon::parse()->format('d-m-Y h:i A');

        $init = $request->init ? new Carbon($request->init) : null;
        $end = $request->end ? new Carbon($request->end) : null;

        $orders = collect($request->data)->map(function ($item) {
            $item['amounro_docnt'] = $item['nro_doc'];
            $item['fecha'] = \Carbon\Carbon::parse($item['fecha'])->format('d-m-Y');
            $item['fecha_vto'] = \Carbon\Carbon::parse($item['fecha_vto'])->format('d-m-Y');

            if ($item['cond_pago'] == 1) {
                $item['condPago'] = 'Crédito';
            } else if ($item['cond_pago'] == 2) {
                $item['condPago'] = 'Contado';
            } else if ($item['cond_pago'] == 3) {
                $item['condPago'] = 'Prepagado';
            }

            $item['supplier'] = $item['supplier']['nombre_prove'];
            $item['almacen_id'] = $item['almacen_id'];

            if ($item['status'] == 1) {
                $item['statusOrder'] = 'Pendiente';
            } else if ($item['status'] == 2) {
                $item['statusOrder'] = 'Anulado';
            } else if ($item['status'] == 3) {
                $item['statusOrder'] = 'Aprobado';
            }

            return $item;
        });

        $file = Excel::create('Reporte', function ($excel) use ($orders, $today) {
            $excel->setCreator('dparragam')->setCompany('Viveres&Abarrotes');
            $excel->setDescription('Reporte de Ordenes de Compra');
            $excel->sheet('Listado', function ($sheet) use ($orders, $today) {

                $sheet->setWidth('A', 20);
                $sheet->setWidth('B', 20);
                $sheet->setWidth('C', 20);
                $sheet->setWidth('D', 20);
                $sheet->setWidth('E', 20);
                $sheet->setWidth('F', 20);
                $sheet->setWidth('G', 20);

                $sheet->loadView('admin.excel.order-compra')->with([
                    'orders' => $orders,
                    'today' => $today,
                ]);
            });
        })->download();

        return $file;
    }

    public function date(Request $request)
    {
        $init = $request->init ? new Carbon($request->init) : null;
        $end = $request->end ? new Carbon($request->end) : null;

        $orders = BuyOrder::select('*')
            ->with(['detalles', 'supplier'])
            ->when(!is_null($request->status), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when(!is_null($request->condicion), function ($query) use ($request) {
                $query->where('cond_pago', $request->condicion);
            })
            ->when($init && $end, function ($query) use ($init, $end) {
                $query->whereBetween('created_at', [$init->format('Y-m-d 00:00:00'), $end->format('Y-m-d 23:59:59')]);
            })

            ->where('status', '<>', 4)
            ->orderBy('id', 'DESC')
            ->orderBy('status', 'ASC')
            ->get();
        return $orders;
    }

    public function getDetails(Request $request)
    {
        return BuyOrder::where('id', $request->id)
            ->with([
                'detalles' => function ($detalles) {
                    $detalles->with([
                        'product_amount' => function ($amount) {
                            $amount->with(['product']);
                        }
                    ]);
                }
            ])
            ->first();
    }
}
