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
    public function index(Request $request)
    {
        // Serve JSON for DataTables server-side processing
        if ($request->ajax() || $request->has('draw')) {
            $columns = ['id', 'nro_doc', 'fecha', 'fecha_vto', 'cond_pago', 'supplier_name', 'almacen_id', 'status', 'created_at'];

            $baseQuery = BuyOrder::where('status', '<>', 4);

            $recordsTotal = $baseQuery->count();

            $query = BuyOrder::where('status', '<>', 4);

            // Global search
            $search = $request->input('search.value');
            if ($search) {
                $query = $query->where(function ($q) use ($search) {
                    $q->where('nro_doc', 'like', "%{$search}%")
                        ->orWhere('cond_pago', 'like', "%{$search}%")
                        ->orWhere('almacen_id', 'like', "%{$search}%");
                })->orWhereHas('supplier', function ($q) use ($search) {
                    $q->where('nombre_prove', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%");
                });
            }

            $recordsFiltered = $query->count();

            // Ordering
            $orderColIndex = (int) $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');
            $orderColumn = $columns[$orderColIndex] ?? 'id';

            if ($orderColumn === 'supplier_name') {
                $query = $query->join('suppliers', 'buy_orders.proveedor_id', '=', 'suppliers.id')
                    ->orderBy('suppliers.nombre_prove', $orderDir)
                    ->select('buy_orders.*');
            } else {
                $query = $query->orderBy($orderColumn, $orderDir);
            }

            // Pagination
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);

            $orders = $query->skip($start)->take($length)->with('supplier')->get();

            $data = $orders->map(function ($order) {
                $editUrl = route('buyorders.edit', $order->id);
                $destroyUrl = route('buyorders.destroy', $order->id);
                $supplierName = $order->supplier->nombre_prove ?? $order->supplier->name ?? '—';

                $actions = '<div class="d-flex align-items-center">';
                $actions .= '<a href="' . $editUrl . '" class="btn btn-icon btn-flat-success mr-1" data-toggle="tooltip" data-placement="top" title="' . __('Edit') . '">';
                $actions .= '<i data-feather="edit"></i></a>';
                $actions .= '<form class="m-0" action="' . $destroyUrl . '" method="POST" onsubmit="return confirm(\'' . __('Delete this order?') . '\');">';
                $actions .= '<input type="hidden" name="_token" value="' . csrf_token() . '">';
                $actions .= '<input type="hidden" name="_method" value="DELETE">';
                $actions .= '<button type="submit" class="btn btn-icon btn-flat-danger" data-toggle="tooltip" data-placement="top" title="' . __('Delete') . '">';
                $actions .= '<i data-feather="trash"></i></button></form></div>';

                return [
                    'id' => $order->id,
                    'nro_doc' => $order->nro_doc,
                    'fecha' => $order->fecha ? Carbon::parse($order->fecha)->format('d-m-Y') : '',
                    'fecha_vcto' => $order->fecha_vto ? Carbon::parse($order->fecha_vto)->format('d-m-Y') : '',
                    'cond_pago' => $order->cond_pago ?? '—',
                    'supplier' => $supplierName,
                    'almacen' => $order->almacen_id ?? '—',
                    'status' => $order->status ?? '—',
                    'created_at' => $order->created_at ? $order->created_at->format('d-m-Y H:i') : '',
                    'actions' => $actions
                ];
            });

            return response()->json([
                'draw' => (int) $request->input('draw'),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ]);
        }

        // Regular page load (view will initialize DataTable and fetch via AJAX)
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
            '$ Dolares' => '$ Dolares',
            'Bs. Bolivares' => 'Bs. Bolivares'
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
            $productId = $product->product_id ?? $product->id ?? null;
            $cantidad = $product->cantidad ?? $product->modified_qty ?? $product->final_qty ?? 0;
            $existing = $product->existing ?? $product->original_qty ?? 0;
            $costo = $product->costo ?? $product->cost ?? 0;
            $tneto = $product->tneto ?? $product->total_net ?? 0;
            $porciva = $product->porciva ?? $product->tax_percent ?? 0;
            $utilidad = $product->utilidad ?? $product->profit_percent ?? 0;
            $pventa = $product->pventa ?? $product->sale_price ?? 0;

            $newBuyOrderDetail = new BuyOrderDetail();
            $newBuyOrderDetail->order_id = $order->id;
            $newBuyOrderDetail->product_id = $productId;
            $newBuyOrderDetail->cantidad = $cantidad;
            $newBuyOrderDetail->existing = $existing;
            $newBuyOrderDetail->costo = $costo;
            $newBuyOrderDetail->total = $tneto;
            $newBuyOrderDetail->iva = $porciva;
            $newBuyOrderDetail->utilidad = $utilidad;
            $newBuyOrderDetail->precio = $pventa;
            $newBuyOrderDetail->save();

            $idproduc = null;
            if ($productId) {
                $amount = ProductAmount::find($productId);
                $idproduc = $amount ? $amount->product_color_id : null;
            }
            if ($idproduc) {
                $productColor = DB::table('product_colors')->where('id', $idproduc)->first();
                $productRealId = $productColor ? $productColor->product_id : null;
                if ($productRealId) {
                    $proveedor = ProductProveedor::where('products_id', $productRealId)
                        ->where('proveedor_id', $proveedor_id)
                        ->get();

                    if ($proveedor->isEmpty()) {
                        $productProveedor = new ProductProveedor();
                        $productProveedor->products_id = $productRealId;
                        $productProveedor->proveedor_id = $proveedor_id;
                        $productProveedor->save();
                    }
                }
            }
        }

        return redirect()
            ->route('buyorders.index')
            ->with('success', __('buyorder.saved_successfully'));
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
            '1' => '$ Dolares',
            '2' => 'Bs. Bolivares'
        ];

        $payment_conditions = [
            1 => 'Crédito',
            2 => 'Contado',
            3 => 'Prepagado'
        ];

        // dd($orderdetails);

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
        // Preserve existing status when the request does not provide one
        $order->status = $request->input('status', $order->status);
        $order->reason = $request->input('reason');
        $order->save();

        $products = json_decode($request->addRows);

        $res = BuyOrderDetail::where('order_id', $id)->delete();

        foreach ($products as $product) {
            $productId = $product->product_id ?? $product->id ?? null;
            $cantidad = $product->cantidad ?? $product->modified_qty ?? $product->final_qty ?? 0;
            $existing = $product->existing ?? $product->original_qty ?? 0;
            $costo = $product->costo ?? $product->cost ?? 0;
            $tneto = $product->tneto ?? $product->total_net ?? 0;
            $porciva = $product->porciva ?? $product->tax_percent ?? 0;
            $utilidad = $product->utilidad ?? $product->profit_percent ?? 0;
            $pventa = $product->pventa ?? $product->sale_price ?? 0;

            $newBuyOrderDetail = new BuyOrderDetail();
            $newBuyOrderDetail->order_id = $order->id;
            $newBuyOrderDetail->product_id = $productId;
            $newBuyOrderDetail->cantidad = $cantidad;
            $newBuyOrderDetail->existing = $existing;
            $newBuyOrderDetail->costo = $costo;
            $newBuyOrderDetail->total = $tneto;
            $newBuyOrderDetail->iva = $porciva;
            $newBuyOrderDetail->utilidad = $utilidad;
            $newBuyOrderDetail->precio = $pventa;
            $newBuyOrderDetail->save();
        }

        return redirect()
            ->route('buyorders.index')
            ->with('success', __('buyorder.updated_successfully'));
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
