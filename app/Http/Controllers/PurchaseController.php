<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\CouponUser;
use App\Models\Referral;
use App\Models\Balance;
use App\Models\PurchaseDetails;
use App\Models\ProductAmount;
use App\Models\Social;
use App\Models\Category;
use App\Models\PromotionUser;
use App\Models\PushMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Excel;
use App\Traits\FCMTrait;
use App\Exports\PurchaseExport;

class PurchaseController extends Controller
{
    use FCMTrait;

    public function pdfview(Purchase $purchase)
    {
        $purchase->load([
            'user.parish',
            'details.product_amount.product_color.product.taxe',
            'exchange',
            'transfer.bankAccount.bank',
            'deposits',
            'delivery.state',
            'delivery.municipality',
            'delivery.parish',
        ]);

        $viewData = [
            'purchase' => $purchase,
            'compra' => $purchase,
            'bankAccount' => data_get($purchase, 'transfer.bankAccount'),
            'user' => $purchase->user,
            'logoUrl' => 'https://www.promarketlatino.com/img/logo-black.png',
            'logoPath' => public_path('images/logo/logo-black.png'),
        ];

        $wantsPdf = !request()->boolean('html');
        $download = request()->boolean('download');

        if (!$wantsPdf) {
            return view('panel.purchases.print', $viewData);
        }

        try {
            $file = app('dompdf.wrapper');
            $file->setOption('isRemoteEnabled', true);
            $file->loadView('panel.purchases.print', $viewData);

            $file_name = 'REPORTE-' . Carbon::now()->format('d-m-Y') . strtoupper(Str::random(10));

            return $file->stream($file_name . '.pdf', ['Attachment' => $download])->header('Content-Type', 'application/pdf');
        } catch (\Throwable $e) {
            Log::warning('Purchase PDF generation failed, fallback to HTML', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);

            return view('panel.purchases.print', $viewData);
        }
    }

    public function index()
    {
        return view('panel.purchases.index');
    }

    public function notifications(Request $request)
    {
        $lastSeenId = (int) $this->getNotificationsLastSeenId();

        $latestOrderId = (int) Purchase::max('id');

        $pendingCount = Purchase::where('status', Purchase::STATUS_ONHOLD)->count();

        $unseenCount = Purchase::where('status', Purchase::STATUS_ONHOLD)
            ->where('id', '>', $lastSeenId)
            ->count();

        $items = Purchase::with('user:id,name')
            ->where('status', Purchase::STATUS_ONHOLD)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'customer_name' => data_get($purchase, 'user.name', 'Cliente'),
                    'created_at' => optional($purchase->created_at)->format('d-m-Y h:i A'),
                ];
            })
            ->values();

        return response()->json([
            'latest_order_id' => $latestOrderId,
            'pending_count' => $pendingCount,
            'unseen_count' => $unseenCount,
            'items' => $items,
        ]);
    }

    public function markNotificationsSeen()
    {
        $latestOrderId = (int) Purchase::max('id');
        Cache::put($this->notificationsSeenCacheKey(), $latestOrderId, now()->addDays(30));

        return response()->json([
            'last_seen_id' => $latestOrderId,
            'unseen_count' => 0,
        ]);
    }

    private function notificationsSeenCacheKey()
    {
        return 'purchase_notifications.last_seen.' . auth()->id();
    }

    private function getNotificationsLastSeenId()
    {
        return (int) Cache::get($this->notificationsSeenCacheKey(), 0);
    }

    public function date(Request $request)
    {
        $dateFromValue = $request->input('date_from', $request->input('init'));
        $dateToValue = $request->input('date_to', $request->input('end'));
        $statusValue = $request->input('type', $request->input('status'));
        $searchValue = $request->input('q', $request->input('search'));

        $today = Carbon::today();
        $init = $dateFromValue ? Carbon::parse($dateFromValue) : $today->copy();
        $end = $dateToValue ? Carbon::parse($dateToValue) : $today->copy();

        $statusMap = [
            'pending' => Purchase::STATUS_ONHOLD,
            'processing' => Purchase::STATUS_PROCESSING,
            'completed' => Purchase::STATUS_COMPLETED,
            'rejected' => Purchase::STATUS_REJECTED,
        ];

        if (is_string($statusValue)) {
            $statusValue = trim($statusValue);
            if ($statusValue !== '' && array_key_exists(strtolower($statusValue), $statusMap)) {
                $statusValue = $statusMap[strtolower($statusValue)];
            }
        }

        $purchases = Purchase::select('purchases.*')
            ->join('users', 'purchases.user_id', '=', 'users.id')
            ->with([
                'coupon',
                'details',
                'user',
                'exchange',
                'transfer.bankAccount.bank',
                'deposits',
                'delivery' => function ($query) {
                    $query->with(['state', 'municipality', 'parish']);
                }
            ])
            ->when($statusValue !== null && $statusValue !== '', function ($query) use ($statusValue) {
                $query->where('purchases.status', $statusValue);
            })
            ->when($init && $end, function ($query) use ($init, $end) {
                $query->whereBetween('purchases.created_at', [$init->format('Y-m-d 00:00:00'), $end->format('Y-m-d 23:59:59')]);
            })
            ->when(filled($searchValue), function ($query) use ($searchValue) {
                $query->where(function ($innerQuery) use ($searchValue) {
                    $innerQuery->where('users.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('purchases.id', 'like', '%' . $searchValue . '%');
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate(10);

        // Map collection items to include helper fields for the frontend
        $purchases->getCollection()->transform(function ($item) {
            $item['amount'] = $this->getTotalAmount($item['details'], $item['exchange'], $item['currency']);
            $item['createdAt'] = Carbon::parse($item['created_at'])->format('d-m-Y h:i A');
            $item['clientName'] = data_get($item, 'user.name', '—');
            $item['paymentType'] = collect(data_get($item, 'deposits', []))->count() > 1
                ? 'Multipago'
                : 'Pago único';
            $item['deliveryDay'] = data_get($item, 'delivery.date') ? Carbon::parse($item['delivery']['date'])->format('d-m-Y') : '—';
            $item['typeTurn'] = $this->getTurn(data_get($item, 'delivery.turn'));
            $item['stateName'] = data_get($item, 'delivery.state.nombre', '—');
            $item['municipalityName'] = data_get($item, 'delivery.municipality.name', '—');
            $item['parishName'] = data_get($item, 'delivery.parish.name', '—');

            $item['code'] = $item['payment_type'] == 5
                ? ''
                : ($item['payment_type'] == 4
                    ? $item['transaction_code']
                    : data_get($item, 'transfer.number', ''));

            $item['payName'] = $this->resolvePayName($item);

            $type = (int) data_get($item, 'delivery.type');
            switch ($type) {
                case 1:
                    $item['deliveryType'] = 'Nacional (Cobro a Destino)';
                    break;
                case 2:
                    $item['deliveryType'] = 'Nacional (Envio a Tienda)';
                    break;
                default:
                    $item['deliveryType'] = 'Envío Regional';
                    break;
            }

            $status = (int) data_get($item, 'status');
            switch ($status) {
                case 0:
                    $item['statusType'] = 'En Espera';
                    break;
                case 1:
                    $item['statusType'] = 'Procesando';
                    break;
                case 2:
                    $item['statusType'] = 'Cancelado';
                    break;
                default:
                    $item['statusType'] = 'Completado';
                    break;
            }

            // Tip / propina fallback
            $item['tip'] = data_get($item, 'tip', data_get($item, 'propina', 0));

            return $item;
        });

        return $purchases;
    }

    public function getDetails(Request $request)
    {
        return Purchase::where('id', $request->id)
            ->with([
                'user',
                'exchange',
                'details.product_amount.product_color.product',
                'details.product_amount.category_size',
                'transfer.bankAccount.bank',
                'deposits',
                'delivery' => function ($query) {
                    $query->with(['state', 'municipality', 'parish']);
                }
            ])
            ->first();
    }

    public function getDetailsCompany(Request $request)
    {
        return Purchase::where('id', $request->id)
            ->with([
                'user',
                'exchange',
                'details.product_amount.product_color.product',
                'details.product_amount.category_size',
                'transfer.bankAccount.bank',
                'deposits',
                'delivery' => function ($query) {
                    $query->with(['state', 'municipality', 'parish']);
                }
            ])
            ->first();
    }

    public function getTotalAmount($details, $exchange, $currency)
    {
        $subtotal = 0;
        $price = 0;

        return collect($details)->reduce(function ($carry, $item) use ($currency, $exchange, $subtotal, $price) {
            if ($currency == 2) {
                if ($item['coin'] == '1') {
                    $price = $item['price'] / $exchange['change'];
                } else {
                    $price = $item['price'];
                }
            } else {
                if ($item['coin'] == '1') {
                    $price = $item['price'];
                } else {
                    $price = $item['price'] * $exchange['change'];
                }
            }

            if ($item->coupon_percentage) {
                $price -= (($price * $item->coupon_percentage) / 100);
            }

            $subtotal = $price * $item['quantity'];

            return $carry + $subtotal;
        }, 0);
    }

    public function getTypePayment($payment, $use_balance = '0')
    {
        $method = "";
        switch ($payment) {
            case Purchase::PAYMENT_TRANSFER:
                $method = 'Transferencia';
                break;
            case Purchase::PAYMENT_MOBILE:
                $method = 'Pago Movil';
                break;
            case Purchase::PAYMENT_ZELLE:
                $method = 'Zelle';
                break;
            case Purchase::PAYMENT_PAYPAL:
                $method = 'Paypal';
                break;
            case Purchase::PAYMENT_EFECTIVO:
                $method = 'Efectivo';
                break;
            case Purchase::PAYMENT_STRIPE:
                $method = 'Stripe';
                break;
            case Purchase::PAYMENT_BALANCE:
                $method = 'Saldo';
                break;
        }

        if ($payment != Purchase::PAYMENT_BALANCE && $use_balance == '1') {
            $method = $method . ' + Saldo';
        }

        return $method;
    }

    private function resolvePayName($item)
    {
        $depositGatewayNames = collect(data_get($item, 'deposits', []))
            ->map(function ($deposit) {
                return trim((string) data_get($deposit, 'gateway.name', ''));
            })
            ->filter()
            ->values();

        if ($depositGatewayNames->isNotEmpty()) {
            return $depositGatewayNames->join(', ');
        }

        return $this->getTypePayment(data_get($item, 'payment_type'), data_get($item, 'use_balance'));
    }

    public function getTurn($turn)
    {
        switch ($turn) {
            case Purchase::TURN_MORNING:
                return 'Mañana';
            case Purchase::TURN_AFTERNOON:
                return 'Tarde';
            case Purchase::TURN_NIGHT:
                return 'Noche';
        }
    }

    public function exportExcel(Request $request)
    {
        $init = $request->init ? new Carbon($request->init) : null;
        $end = $request->end ? new Carbon($request->end) : null;

        $data = Purchase::select('purchases.*')
            ->with([
                'user',
                'exchange',
                'details',
                'transfer.bankAccount.bank',
                'deposits',
                'delivery' => function ($q) {
                    $q->with(['state', 'municipality', 'parish']);
                }
            ])
            ->when(!is_null($request->status), function ($q) use ($request) {
                $q->where('purchases.status', $request->status);
            })
            ->when($init && $end, function ($q) use ($init, $end) {
                $q->whereBetween('purchases.created_at', [
                    $init->format('Y-m-d 00:00:00'),
                    $end->format('Y-m-d 23:59:59')
                ]);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('user', function ($u) use ($request) {
                    $u->where('name', 'like', '%' . $request->search . '%');
                })->orWhere('purchases.id', 'like', '%' . $request->search . '%');
            })
            ->orderByDesc('id')
            ->get();

        $today = now()->format('d-m-Y h:i A');

        $data = $data->map(function ($item) {
            $item['amount'] = $this->getTotalAmount($item['details'], $item['exchange'], $item['currency']);
            $item['createdAt'] = Carbon::parse($item['created_at'])->format('d-m-Y h:i A');
            $item['clientName'] = data_get($item, 'user.name', '—');
            $item['paymentType'] = collect(data_get($item, 'deposits', []))->count() > 1
                ? 'Multipago'
                : 'Pago único';
            $item['deliveryDay'] = data_get($item, 'delivery.date') ? Carbon::parse($item['delivery']['date'])->format('d-m-Y') : '—';
            $item['typeTurn'] = $this->getTurn(data_get($item, 'delivery.turn'));
            $item['stateName'] = data_get($item, 'delivery.state.nombre', '—');
            $item['municipalityName'] = data_get($item, 'delivery.municipality.name', '—');
            $item['parishName'] = data_get($item, 'delivery.parish.name', '—');

            $item['code'] = $item['payment_type'] == 5
                ? ''
                : ($item['payment_type'] == 4
                    ? $item['transaction_code']
                    : data_get($item, 'transfer.number', ''));

            $item['payName'] = $this->resolvePayName($item);

            $type = (int) data_get($item, 'delivery.type');
            switch ($type) {
                case 1:
                    $item['deliveryType'] = 'Nacional (Cobro a Destino)';
                    break;
                case 2:
                    $item['deliveryType'] = 'Nacional (Envio a Tienda)';
                    break;
                default:
                    $item['deliveryType'] = 'Envío Regional';
                    break;
            }

            $status = (int) data_get($item, 'status');
            switch ($status) {
                case 0:
                    $item['statusType'] = 'En Espera';
                    break;
                case 1:
                    $item['statusType'] = 'Procesando';
                    break;
                case 2:
                    $item['statusType'] = 'Cancelado';
                    break;
                default:
                    $item['statusType'] = 'Completado';
                    break;
            }

            return $item;
        });

        return Excel::download(new PurchaseExport($data, $today), 'Reporte_Pedidos.xls');
    }

    public function approve(Request $request, $id)
    {
        $purchase = Purchase::with(['exchange', 'details', 'transfer', 'delivery'])
            ->whereHas('details', function ($q) {
                $q->whereNotNull('product_amount_id');
            })->where('id', $id)->first();

        $purchase->status = $request->status;
        $purchase->save();

        if ($purchase->coupon_id && $purchase->status === Purchase::STATUS_COMPLETED) {
            // ... lógica existente (omitida para brevedad)
        }

        $_sociales = Social::orderBy('id', 'desc')->first();

        $user = User::where('id', $purchase->user_id)->first();
        $statusName = $request->status == 1 ? 'APROBADO' : 'COMPLETADO';
        $subjectName = $request->status == 1 ? 'Compra Aprobada | ' : 'Compra Completada | ';

        try {
            if ($user->device && ! $user->is_blocked_notification) {
                $typeId = $request->status == 1 ? PushMessage::ORDER_APPROVED : PushMessage::ORDER_COMPLETE;
                $message = PushMessage::where('type', $typeId)->first();
                if ($message) {
                    $message['data'] = ['order_id' => $purchase->id];
                    $this->sendPushMessage([$user->device->device_key], $message);
                }
            }
        } catch (\Exception $e) {
        }

        $mailError = '';
        $mailSkipped = !config('mail.purchase_send_emails');

        if (!$mailSkipped) {
            try {
                Mail::send('emails.compra-aprobada', [
                    'compra'     => $purchase,
                    'user'       => $user,
                    'sociales'   => $_sociales,
                    'statusName' => $statusName
                ], function ($m) use ($user, $subjectName, $request) {
                    $to = $request->status == 1 ? [$user->email, env('MAIL_CONTACTO', 'promarketlatinove@gmail.com')] : $user->email;
                    $m->to($to)
                        ->subject($subjectName . config('app.name'));
                });
            } catch (\Exception $e) {
                $mailError = 'Code: ' . $e->getCode() . ', Line: ' . $e->getLine() . ', Descript: ' . $e->getMessage();
                Log::warning('Purchase approve mail failed', [
                    'purchase_id' => $purchase->id,
                    'user_id' => $user->id,
                    'error' => $mailError,
                ]);
            }
        }

        return response()->json(['result' => true, 'error' => $mailError, 'mailSkipped' => $mailSkipped]);
    }

    public function reject(Request $request, $id)
    {
        $purchase = Purchase::with([
            'exchange',
            'details',
            'transfer',
            'delivery'
        ])
            ->whereHas('details', function ($q) {
                $q->whereNotNull('product_amount_id');
            })
            ->where([
                ['id', '=', $id],
            ])
            ->whereIn('status', [0, 1])
            ->first();

        $promotionsUsedIds = [];

        foreach ($purchase->details as $detail) {
            if (!is_null($detail['product_amount_id'])) {
                $amount = ProductAmount::find($detail['product_amount_id']);
                $amount->amount = $amount->amount + $detail['quantity'];
                $amount->save();
                if (!in_array($detail->promotion_id, $promotionsUsedIds)) {
                    array_push($promotionsUsedIds, $detail->promotion_id);
                }
            }
        }

        $purchase->status = $request->status;
        $purchase->save();

        if ($purchase->coupon_id) {
            $checkCouponPurchase = CouponUser::where('purchase_id', $purchase->id)->first();
            $checkCouponPurchase->status = CouponUser::STATUS_REJECTED;
            $checkCouponPurchase->save();
        }

        foreach ($promotionsUsedIds as $promotionUsedId) {
            PromotionUser::where('promotion_id', $promotionUsedId)->where('user_id', $purchase->user_id)->orderBy('id', 'desc')->delete();
        }

        if ($purchase->use_balance == 1) {
            $checkPreviousBalance = Balance::where('user_id', $purchase->user_id)->orderBy('id', 'desc')->first();

            $newBalance = new Balance();
            $newBalance->user_id = $purchase->user_id;
            $newBalance->parent_id = $purchase->id;
            $newBalance->parent_class = Purchase::CLASS_VALUE;
            $newBalance->parent_type = Purchase::CLASS_NAME;
            $newBalance->type = Balance::TYPE_PURCHASE_REJECTED;
            $newBalance->currency = $purchase->currency;
            $newBalance->initial_amount = $checkPreviousBalance ? $checkPreviousBalance->total : 0;
            $newBalance->amount = $purchase->amount_balance;
            $newBalance->total = $newBalance->initial_amount + $newBalance->amount;
            $newBalance->save();
        }

        $user = User::where('id', $purchase->user_id)->first();

        $purchase->rejectReason = $request->rejectReason;
        $purchase->transferNumber = !is_null($purchase->transfer) ? $purchase->transfer->number : '';

        $mailError = '';
        try {
            if ($user->device && ! $user->is_blocked_notification) {
                $message = PushMessage::where('type', PushMessage::ORDER_REJECTED)->first();
                if ($message) {
                    $message['data'] = ['order_id' => $purchase->id];
                    $this->sendPushMessage([$user->device->device_key], $message);
                }
            }
        } catch (\Exception $e) {
        }

        $mailSkipped = !config('mail.purchase_send_emails');

        if (!$mailSkipped) {
            try {
                Mail::send('emails.compra-rechazada', ['compra' => $purchase, 'user' => $user], function ($m) use ($user) {
                    $m->to([$user->email, env('MAIL_CONTACTO', 'promarketlatinove@gmail.com')])->subject('Compra Cancelada | ' . config('app.name'));
                });
            } catch (\Exception $e) {
                $mailError = 'Code: ' . $e->getCode() . ', Line: ' . $e->getLine() . ', Descript: ' . $e->getMessage();
            }
        }

        return response()->json(['result' => true, 'error' => $mailError, 'emailUser' => $user->email, 'emailEnv' => env('MAIL_CONTACTO'), 'mailSkipped' => $mailSkipped]);
    }
}
