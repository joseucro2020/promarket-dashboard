<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use Carbon\Carbon;
use Excel;
use App\Exports\PurchaseExport;

class SalesController extends Controller
{
    public function index()
    {
        return view('panel.reports.sales');
    }

    public function data(Request $request)
    {
        $init = $request->init ? new Carbon($request->init) : null;
        $end = $request->end ? new Carbon($request->end) : null;

        $purchases = Purchase::when($init && $end, function ($q) use ($init, $end) {
            $q->whereBetween('created_at', [$init->format('Y-m-d 00:00:00'), $end->format('Y-m-d 23:59:59')]);
        })->orderBy('created_at', 'asc')->get();

        $grouped = $purchases->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('Y-m-d');
        });

        $data = $grouped->map(function ($items, $date) {
            $total = $items->sum('total');
            return [
                'date' => Carbon::parse($date)->format('d-m-Y'),
                'total' => round($total, 2),
                'count' => $items->count()
            ];
        })->values();

        return response()->json($data);
    }

    public function export(Request $request)
    {
        $init = $request->init ? new Carbon($request->init) : null;
        $end = $request->end ? new Carbon($request->end) : null;

        $purchases = Purchase::with(['user', 'exchange', 'details'])
            ->when($init && $end, function ($q) use ($init, $end) {
                $q->whereBetween('created_at', [$init->format('Y-m-d 00:00:00'), $end->format('Y-m-d 23:59:59')]);
            })->orderByDesc('id')->get();

        $today = now()->format('d-m-Y h:i A');

        $data = $purchases->map(function ($item) {
            $item['amount'] = isset($item->total) ? $item->total : 0;
            $item['createdAt'] = Carbon::parse($item->created_at)->format('d-m-Y h:i A');
            $item['clientName'] = data_get($item, 'user.name', '—');
            return $item;
        });

        return Excel::download(new PurchaseExport($data, $today), 'Sales_Report.xls');
    }
}
