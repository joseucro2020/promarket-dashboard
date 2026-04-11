<?php

namespace App\Http\Controllers\Admin;

use App\Exports\InventoryReplenishmentExport;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Replenishment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class InventoryReplenishmentController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'from' => (string) $request->input('from', now()->startOfDay()->format('Y-m-d\TH:i')),
            'to' => (string) $request->input('to', now()->endOfDay()->format('Y-m-d\TH:i')),
            'type' => (string) $request->input('type', ''),
        ];

        $replenishments = $this->buildIndexQuery($filters)
            ->get()
            ->map(function ($replenishment) {
                $normalizedType = $this->normalizeMovementType($replenishment->movement_type);
                $replenishment->type_label = $normalizedType === 'entrada'
                    ? __('locale.Entry')
                    : ($normalizedType === 'salida' ? __('locale.Exit') : '-');

                $adjustmentTypeMap = [
                    '1' => __('locale.Adjustment Defective Product'),
                    '2' => __('locale.Adjustment Expired Product'),
                    '3' => __('locale.Adjustment Out of Stock'),
                    '4' => __('locale.Adjustment Product Incident'),
                ];
                $adjustmentCode = (string) ($replenishment->fit_type_code ?? '');
                $replenishment->adjustment_type_label = $adjustmentTypeMap[$adjustmentCode] ?? '-';

                $unitLabels = [
                    1 => 'Gr',
                    2 => 'Kg',
                    3 => 'Ml',
                    4 => 'L',
                    5 => 'Cm',
                ];

                $presentationValue = (float) ($replenishment->presentation_value ?? 0);
                $presentationUnit = (int) ($replenishment->presentation_unit ?? 0);
                $replenishment->presentation_label = $presentationValue > 0
                    ? rtrim(rtrim(number_format($presentationValue, 2, '.', ''), '0'), '.') . ' ' . ($unitLabels[$presentationUnit] ?? '')
                    : '0.00';

                return $replenishment;
            });

        return view('panel.inventory.index', [
            'replenishments' => $replenishments,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        $selectedProduct = null;
        $selectedProductId = old('product_id');

        if ($selectedProductId) {
            $product = Product::query()
                ->select('id', 'name', 'price_1')
                ->whereIn('status', ['1', '0'])
                ->where('id', $selectedProductId)
                ->first();

            if ($product) {
                $firstSku = DB::table('product_colors')
                    ->join('product_amount', 'product_amount.product_color_id', '=', 'product_colors.id')
                    ->whereColumn('product_colors.product_id', 'products.id')
                    ->whereNull('product_amount.deleted_at')
                    ->orderBy('product_amount.id')
                    ->limit(1)
                    ->select('product_amount.sku');

                $stockSubquery = DB::table('product_colors')
                    ->join('product_amount', 'product_amount.product_color_id', '=', 'product_colors.id')
                    ->whereColumn('product_colors.product_id', 'products.id')
                    ->whereNull('product_amount.deleted_at')
                    ->selectRaw('COALESCE(SUM(product_amount.amount), 0)');

                $productWithSku = Product::query()
                    ->select('products.id', 'products.name', 'products.price_1')
                    ->selectSub($firstSku, 'sku')
                    ->selectSub($stockSubquery, 'existing_stock')
                    ->where('products.id', $selectedProductId)
                    ->first();

                $sku = trim((string) optional($productWithSku)->sku);
                $selectedProduct = [
                    'id' => $product->id,
                    'text' => '#' . $product->id . ' - ' . $product->name
                        . ($sku !== '' ? ' | SKU: ' . $sku : '')
                        . ' | $' . number_format((float) $product->price_1, 2),
                    'existing_stock' => (float) (optional($productWithSku)->existing_stock ?? 0),
                ];
            }
        }

        return view('panel.inventory.create', ['selectedProduct' => $selectedProduct]);
    }

    public function searchProducts(Request $request)
    {
        $term = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $firstSkuSubquery = DB::table('product_colors')
            ->join('product_amount', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->whereColumn('product_colors.product_id', 'products.id')
            ->whereNull('product_amount.deleted_at')
            ->orderBy('product_amount.id')
            ->limit(1)
            ->select('product_amount.sku');

        $stockSubquery = DB::table('product_colors')
            ->join('product_amount', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->whereColumn('product_colors.product_id', 'products.id')
            ->whereNull('product_amount.deleted_at')
            ->selectRaw('COALESCE(SUM(product_amount.amount), 0)');

        $query = Product::query()
            ->select('products.id', 'products.name', 'products.description', 'products.price_1')
            ->selectSub($firstSkuSubquery, 'sku')
            ->selectSub($stockSubquery, 'existing_stock')
            ->whereIn('products.status', ['1', '0']);

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $like = '%' . $term . '%';
                $q->where('products.id', 'like', $like)
                    ->orWhere('products.name', 'like', $like)
                    ->orWhere('products.description', 'like', $like)
                    ->orWhere('products.price_1', 'like', $like)
                    ->orWhereExists(function ($sub) use ($like) {
                        $sub->select(DB::raw(1))
                            ->from('product_colors')
                            ->join('product_amount', 'product_amount.product_color_id', '=', 'product_colors.id')
                            ->whereColumn('product_colors.product_id', 'products.id')
                            ->whereNull('product_amount.deleted_at')
                            ->where(function ($skuQuery) use ($like) {
                                $skuQuery->where('product_amount.sku', 'like', $like)
                                    ->orWhere('product_amount.price', 'like', $like);
                            });
                    });
            });
        }

        $items = $query
            ->orderBy('products.name')
            ->skip($offset)
            ->take($perPage + 1)
            ->get();

        $hasMore = $items->count() > $perPage;
        $items = $items->take($perPage);

        $results = $items->map(function ($item) {
            $sku = trim((string) $item->sku);
            $description = trim((string) $item->description);

            $text = '#' . $item->id . ' - ' . $item->name;
            if ($sku !== '') {
                $text .= ' | SKU: ' . $sku;
            }
            if ($description !== '') {
                $text .= ' | ' . Str::limit($description, 45);
            }
            $text .= ' | $' . number_format((float) $item->price_1, 2);

            return [
                'id' => $item->id,
                'text' => $text,
                'existing_stock' => (float) ($item->existing_stock ?? 0),
            ];
        })->values();

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $hasMore,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'type' => 'required|in:0,1',
            'fit_type' => 'nullable|in:1,2,3,4',
            'quantity' => 'required|numeric|min:0',
            'fit_quantity' => 'nullable|numeric|min:0',
            'existing' => 'nullable|numeric',
            'final' => 'nullable|numeric',
            'reason' => 'nullable|string',
        ], [
            'product_id.required' => __('locale.Please select a product.'),
            'product_id.exists' => __('locale.The selected product is invalid.'),
            'type.in' => __('locale.Invalid replenishment type.'),
            'fit_type.in' => __('locale.Invalid adjustment type.'),
            'quantity.min' => __('locale.Replenishment quantity must be greater than or equal to zero.'),
        ]);

        DB::transaction(function () use ($data) {
            $replenishment = new Replenishment();

            $productPresentationId = $this->getFirstProductPresentationId((int) $data['product_id']);

            if (!$productPresentationId) {
                throw ValidationException::withMessages([
                    'product_id' => __('locale.This product has no active presentation to adjust stock.'),
                ]);
            }

            $presentationRow = DB::table('product_amount')
                ->where('id', $productPresentationId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first(['id', 'amount']);

            if (!$presentationRow) {
                throw ValidationException::withMessages([
                    'product_id' => __('locale.The selected presentation was not found.'),
                ]);
            }

            $currentAmount = (float) ($presentationRow->amount ?? 0);
            $fitQuantity = isset($data['fit_quantity']) ? (float) $data['fit_quantity'] : (float) $data['quantity'];

            $isExit = (string) $data['type'] === '1';

            if ($isExit && $fitQuantity > $currentAmount) {
                throw ValidationException::withMessages([
                    'quantity' => __('locale.Replenishment quantity exceeds available stock.'),
                ]);
            }

            $newAmount = $isExit
                ? ($currentAmount - $fitQuantity)
                : ($currentAmount + $fitQuantity);

            DB::table('product_amount')
                ->where('id', $productPresentationId)
                ->update([
                    'amount' => $newAmount,
                    'updated_at' => now(),
                ]);

            $this->setIfColumnExists($replenishment, 'product_presentation', $productPresentationId);
            $this->setIfColumnExists($replenishment, 'user_id', auth()->id() ?: 1);

            // Persist both new and legacy column names to support existing schema variants.
            $this->setIfColumnExists($replenishment, 'fit_type', $data['fit_type'] ?? null);
            $this->setIfColumnExists($replenishment, 'type', $data['type']);

            $this->setIfColumnExists($replenishment, 'fit_quantity', $fitQuantity);
            $this->setIfColumnExists($replenishment, 'quantity', $fitQuantity);
            $this->setIfColumnExists($replenishment, 'modified', $fitQuantity);

            // Persist values based on locked DB stock to avoid stale client-side numbers.
            $existing = $currentAmount;
            $final = $newAmount;

            $this->setIfColumnExists($replenishment, 'existing', $existing);
            $this->setIfColumnExists($replenishment, 'final', $final);
            $this->setIfColumnExists($replenishment, 'reason', $data['reason'] ?? null);
            $this->setIfColumnExists($replenishment, 'notes', $data['reason'] ?? null);

            $replenishment->save();
        });

        return redirect()->route('inventory.index')->with('success', __('Reposición registrada correctamente.'));
    }

    private function setIfColumnExists(Replenishment $replenishment, string $column, $value): void
    {
        if (Schema::hasColumn('replenishments', $column)) {
            $replenishment->{$column} = $value;
        }
    }

    private function getFirstProductPresentationId(int $productId): ?int
    {
        $id = DB::table('product_colors')
            ->join('product_amount', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->where('product_colors.product_id', $productId)
            ->whereNull('product_amount.deleted_at')
            ->orderBy('product_amount.id')
            ->value('product_amount.id');

        return $id ? (int) $id : null;
    }

    public function exportExcel(Request $request)
    {
        $filters = [
            'from' => (string) $request->input('from', ''),
            'to' => (string) $request->input('to', ''),
            'type' => (string) $request->input('type', ''),
        ];

        $replenishments = $this->buildIndexQuery($filters)
            ->get()
            ->map(function ($replenishment) {
                $normalizedType = $this->normalizeMovementType($replenishment->movement_type);
                $replenishment->type_label = $normalizedType === 'entrada'
                    ? __('locale.Entry')
                    : ($normalizedType === 'salida' ? __('locale.Exit') : '-');

                return $replenishment;
            });

        $today = now()->format('d-m-Y h:i A');
        $fileName = 'Reporte-Reposicion-Inventario-' . now()->format('d-m-Y(His)') . '.xlsx';

        return Excel::download(new InventoryReplenishmentExport($replenishments, $today), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $filters = [
            'from' => (string) $request->input('from', ''),
            'to' => (string) $request->input('to', ''),
            'type' => (string) $request->input('type', ''),
        ];

        $replenishments = $this->buildIndexQuery($filters)
            ->get()
            ->map(function ($replenishment) {
                $normalizedType = $this->normalizeMovementType($replenishment->movement_type);
                $replenishment->type_label = $normalizedType === 'entrada'
                    ? __('locale.Entry')
                    : ($normalizedType === 'salida' ? __('locale.Exit') : '-');

                return $replenishment;
            });

        $viewData = [
            'replenishments' => $replenishments,
            'generatedAt' => now()->format('d-m-Y h:i A'),
        ];

        try {
            $file = app('dompdf.wrapper');
            $file->setOption('isRemoteEnabled', true);
            $file->loadView('panel.inventory.export_pdf', $viewData);

            $fileName = 'Reporte-Reposicion-Inventario-' . now()->format('d-m-Y-His') . '-' . Str::upper(Str::random(6));

            return $file->stream($fileName . '.pdf', ['Attachment' => false])
                ->header('Content-Type', 'application/pdf');
        } catch (\Throwable $exception) {
            return view('panel.inventory.export_pdf', $viewData);
        }
    }

    private function buildIndexQuery(array $filters)
    {
        $movementExpr = $this->coalesceColumnExpression(
            $this->existingColumns(['type', 'fit_type']),
            "''"
        );

        $quantityExpr = $this->coalesceColumnExpression(
            $this->existingColumns(['fit_quantity', 'quantity', 'modified', 'final', 'existing']),
            '0'
        );

        $fitTypeExpr = $this->coalesceColumnExpression(
            $this->existingColumns(['fit_type']),
            "''"
        );

        $reasonExpr = $this->coalesceColumnExpression(
            $this->existingColumns(['reason', 'notes']),
            "''"
        );

        $existingExpr = $this->coalesceColumnExpression(
            $this->existingColumns(['existing']),
            '0'
        );

        $modifiedExpr = $this->coalesceColumnExpression(
            $this->existingColumns(['fit_quantity', 'modified', 'quantity']),
            '0'
        );

        $finalExpr = $this->coalesceColumnExpression(
            $this->existingColumns(['final']),
            '0'
        );

        $query = Replenishment::query()
            ->select([
                'replenishments.id',
                'replenishments.created_at',
                DB::raw($movementExpr . ' as movement_type'),
                DB::raw($quantityExpr . ' as quantity'),
                DB::raw($fitTypeExpr . ' as fit_type_code'),
                DB::raw($reasonExpr . ' as reason_text'),
                DB::raw($existingExpr . ' as existing_qty'),
                DB::raw($modifiedExpr . ' as modified_qty'),
                DB::raw($finalExpr . ' as final_qty'),
                DB::raw('COALESCE(product_amount.presentation, 0) as presentation_value'),
                DB::raw('COALESCE(product_amount.unit, 0) as presentation_unit'),
                DB::raw("COALESCE(users.name, '-') as user_name"),
                DB::raw("COALESCE(products.name, '-') as product_name"),
            ])
            ->leftJoin('users', 'users.id', '=', 'replenishments.user_id')
            ->leftJoin('product_amount', 'product_amount.id', '=', 'replenishments.product_presentation')
            ->leftJoin('products', 'products.id', '=', 'product_amount.product_color_id')
            ->whereNull('replenishments.deleted_at')
            ->orderByDesc('replenishments.id');

        if (!empty($filters['from'])) {
            try {
                $from = Carbon::parse($filters['from'])->format('Y-m-d H:i:s');
                $query->where('replenishments.created_at', '>=', $from);
            } catch (\Throwable $exception) {
                // Ignore invalid date filter to preserve list rendering.
            }
        }

        if (!empty($filters['to'])) {
            try {
                $to = Carbon::parse($filters['to'])->format('Y-m-d H:i:s');
                $query->where('replenishments.created_at', '<=', $to);
            } catch (\Throwable $exception) {
                // Ignore invalid date filter to preserve list rendering.
            }
        }

        $movementType = $this->normalizeMovementType($filters['type'] ?? '');
        if ($movementType !== '') {
            $query->whereRaw(
                'LOWER(TRIM(' . $movementExpr . ')) in (?, ?, ?)',
                $movementType === 'entrada'
                    ? ['entrada', 'entry', '0']
                    : ['salida', 'exit', '1']
            );
        }

        return $query;
    }

    private function normalizeMovementType($value): string
    {
        $type = Str::lower(trim((string) $value));
        if (in_array($type, ['0', 'entrada', 'entry', 'in'], true)) {
            return 'entrada';
        }
        if (in_array($type, ['1', 'salida', 'exit', 'out'], true)) {
            return 'salida';
        }

        return '';
    }

    private function existingColumns(array $candidates): array
    {
        return array_values(array_filter($candidates, function (string $column) {
            return Schema::hasColumn('replenishments', $column);
        }));
    }

    private function coalesceColumnExpression(array $columns, string $fallbackLiteral): string
    {
        if (empty($columns)) {
            return $fallbackLiteral;
        }

        $qualified = array_map(function (string $column) {
            return 'replenishments.' . $column;
        }, $columns);

        $qualified[] = $fallbackLiteral;

        return 'COALESCE(' . implode(', ', $qualified) . ')';
    }
}
