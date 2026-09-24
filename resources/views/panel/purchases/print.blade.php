<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Pdf Pedidos</title>
  <style>
    * {
      font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
      font-size: 12px;
    }

    body {
      margin: 20px;
    }

    .header {
      margin-top: 1rem;
      text-align: center;
    }

    .img {
      text-align: center;
    }

    .img .logo {
      width: 180px;
      height: auto;
      margin: 0 auto;
    }

    .text-center,
    .title {
      text-align: center;
    }

    .text-left {
      text-align: left;
    }

    .text-uppercase {
      text-transform: uppercase;
    }

    .border-top {
      border-top: 1px dotted #000;
      padding-top: 5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }

    table.bordered {
      border: 1px solid #000;
    }

    table.bordered td,
    table.bordered th {
      border: 1px solid #000;
      padding: 5px;
    }

    th {
      background: #f2f2f2;
    }

    .money {
      font-weight: bold;
    }

    p.text-center {
      margin-top: 30px;
      font-size: 14px;
    }
  </style>
</head>

<body>
  @php
    use App\Libraries\CalcPrice;
    use App\Libraries\Money;
    use App\Libraries\Total;

    $compra = $purchase;
    $user = data_get($purchase, 'user');
    $bankAccount = data_get($purchase, 'transfer.bankAccount');
    
    $logoSrc = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPoAAABDCAYAAAC1KRiAAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NEI2Rjc0QTI5Qjg2MTFFQUE2NkFDMUJBMDQ2ODE1REIiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NEI2Rjc0QTM5Qjg2MTFFQUE2NkFDMUJBMDQ2ODE1REIiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo0QjZGNzRBMDlCODYxMUVBQTY2QUMxQkEwNDY4MTVEQiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo0QjZGNzRBMTlCODYxMUVBQTY2QUMxQkEwNDY4MTVEQiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pja1F8IAAC5FSURBVHja7F0JPJTf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';
  @endphp
  <div class="header">
    <div class="img">
        <img class="logo" src="{{ $logoSrc }}">
    </div>
  </div>
  <div class="invoice-box">
    <table>
      <tr class="top">
        <td class="title" colspan="2">
          <h4 class="text-center text-uppercase">ORDEN: #{{ data_get($compra, 'id', '—') }}</h4>
        </td>
      </tr>
      <tr>
        <td style="vertical-align: top; width: 50%;">
          <table>
            <tr>
              <td class="border-top">
                <h5 class="text-left">DATOS DEL CLIENTE</h5>
              </td>
            </tr>
            <tr>
              <td>
                <strong>{{ data_get($user, 'persona') == 1 ? 'Nombre y Apellido' : 'Razón Social' }}: </strong>
                {{ data_get($user, 'persona') == 1 ? data_get($user, 'name') : (data_get($user, 'empresa') ?: data_get($user, 'name')) }}<br>
                <strong>{{ data_get($user, 'persona') == 1 ? 'Cédula de Identidad' : 'Rif' }}:</strong>
                {{ data_get($user, 'document_type') ?: (data_get($user, 'persona') == 1 ? 'V' : 'J') }}
                {{ data_get($user, 'identificacion') }}<br>
                <strong>Teléfono: </strong> {{ data_get($user, 'telefono') }}<br>
                <strong>Correo electrónico: </strong> {{ data_get($user, 'email') }}<br>
                @if (data_get($user, 'fiscal') && data_get($user, 'persona') == 2)
                  <strong>Dirección: </strong> {{ data_get($user, 'fiscal') }} <br>
                @endif
                <strong>Estatus: {{ data_get($compra, 'status_text') }}</strong>
              </td>
            </tr>
          </table>
        </td>
        <td style="vertical-align: top; width: 50%;">
          <table>
            <tr>
              <td class="border-top">
                <h5 class="text-left">DATOS DE LA PERSONA QUE RECIBE</h5>
              </td>
            </tr>
            <tr>
              <td>
                <strong>Recibe:</strong> {{ data_get($user, 'name') }}<br>
                <strong>{{ data_get($user, 'persona') == 1 ? 'Cédula de Identidad:' : 'Rif:' }}</strong>
                {{ data_get($user, 'identificacion') }}<br>
                @if (data_get($compra, 'delivery.phone'))
                  <strong>Teléfono: </strong> {{ data_get($compra, 'delivery.phone') }} <br>
                @endif
                @if (data_get($compra, 'delivery.municipality'))
                  <strong>Municipio:</strong> {{ data_get($compra, 'delivery.municipality.name') }}
                  @if (data_get($user, 'parish'))
                    <strong>Sector:</strong> {{ data_get($user, 'parish.name') }} <br>
                  @endif
                @endif
                @if (data_get($compra, 'delivery.state'))
                  <strong>Estado: </strong> {{ data_get($compra, 'delivery.state.nombre') }} <br>
                @endif
                @if (data_get($compra, 'delivery.note'))
                  <strong>Nota: </strong> {{ data_get($compra, 'delivery.note') }} <br>
                @endif
                <strong>Dirección de entrega:</strong>
                {{ data_get($compra, 'delivery.address_line2') ? data_get($compra, 'delivery.address_line2') . ', ' : '' }}
                {{ data_get($compra, 'delivery.address') }} <br>
                <strong>Fecha de Pedido:</strong>
                {{ data_get($compra, 'created_at') ? \Carbon\Carbon::parse(data_get($compra, 'created_at'))->format('d-m-Y H:i A') : '—' }} <br>
                <strong>Fecha de Entrega:</strong>
                {{ data_get($compra, 'delivery.date_formated') ? \Carbon\Carbon::parse(data_get($compra, 'delivery.date_formated'))->format('d-m-Y H:i A') : '—' }} -
                <strong>Turno:</strong> {{ data_get($compra, 'delivery.turn_formated') }}
                @if (data_get($compra, 'delivery.pay_with'))
                  <br><strong>Monto a pagar por el cliente:</strong>
                  {{ data_get($compra, 'delivery.pay_with') }}
                @endif
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <table>
      <tr class="information">
        <td class="border-top">
          <h4 class="text-left text-uppercase">INFORMACION DE TU PEDIDO</h4>
        </td>
      </tr>
    </table>
    <table class="bordered">
      <tr>
        <th>Descripción</th>
        <th>Impuesto</th>
        <th>Cantidad</th>
        <th>Costo</th>
        <th>Total</th>
      </tr>
      @foreach (data_get($compra, 'details', []) as $item)
        @php
          $selectedQuantity = (float) data_get($item, 'selected_quantity', 0);
          $selectedGrams = (float) data_get($item, 'selected_grams', 0);
          $hasSelectedGrams = $selectedGrams > 0;
          $baseQuantity = (float) data_get($item, 'quantity', 0);
          $effectiveQuantity = $hasSelectedGrams && $selectedQuantity > 0 ? $selectedQuantity : $baseQuantity;
          $priceByCurrency = CalcPrice::getByCurrency(data_get($item, 'price'), data_get($item, 'coin'), data_get($compra, 'exchange.change', 1), data_get($compra, 'currency'));
          $lineTotal = $priceByCurrency * $effectiveQuantity;
          $gramsFormatted = rtrim(rtrim(number_format($selectedGrams, 3, '.', ''), '0'), '.');
        @endphp
        <tr>
          <td>
            @if (data_get($item, 'product') == null)
              {{ data_get($item, 'discount_description') }}
            @else
              {{ \App::getLocale() == 'es' ? data_get($item, 'product.name') : data_get($item, 'product.name_english') }}
              {{ data_get($item, 'presentation') }}
              {{ data_get($item, 'unit') }}
              @if ($hasSelectedGrams)
                ({{ $gramsFormatted }} g)
              @endif
              {{ data_get($item, 'discounts_text') }}
            @endif
          </td>
          <td class="text-center">
            @if (data_get($item, 'product') != null)
              {{ data_get($item, 'product.taxe.name') ?: 'Exento' }}
            @endif
          </td>
          <td class="text-center">
            {{ $effectiveQuantity == (int) $effectiveQuantity ? (int) $effectiveQuantity : rtrim(rtrim(number_format($effectiveQuantity, 3, '.', ''), '0'), '.') }}
            @if ($hasSelectedGrams)
              <br>{{ $gramsFormatted }} g
            @endif
          </td>
          <td class="text-center">
            {{ Money::getByCurrency($priceByCurrency, data_get($compra, 'currency')) }}
          </td>
          <td class="text-right">
            {{ Money::getByCurrency($lineTotal, data_get($compra, 'currency')) }}
          </td>
        </tr>
      @endforeach
      @if (data_get($compra, 'coupon_id'))
        <tr>
          <th colspan="3">Total Cupón</th>
          <td></td>
          <td>
            {{ Money::getByCurrency(Total::getByCurrency($compra) - Total::getByCurrency($compra, true), data_get($compra, 'currency')) }}
          </td>

        </tr>
      @endif
      <tr>
        <th colspan="3">Subtotal</th>
        <td></td>
        <td>{{ Money::getByCurrency(Total::getByCurrency($compra), data_get($compra, 'currency')) }}</td>

      </tr>
      <tr>
        <th colspan="3">Propina</th>
        <td></td>
        <td>{{ Money::getByCurrency(CalcPrice::getByCurrency(data_get($compra, 'tip_money', 0), data_get($compra, 'details.0.coin', '1'), data_get($compra, 'exchange.change', 1), data_get($compra, 'currency')), data_get($compra, 'currency')) }}
        </td>
      </tr>
      <tr>
        <th colspan="3">Costo de Envio</th>
        <td></td>
        <td>{{ Money::getByCurrency(CalcPrice::getByCurrency(data_get($compra, 'shipping_fee', 0), data_get($compra, 'details.0.coin', '1'), data_get($compra, 'exchange.change', 1), data_get($compra, 'currency')), data_get($compra, 'currency')) }}
        </td>
      </tr>
      <tr>
        <th colspan="3">Tipo de pago</th>
        <td></td>
        <td>
          @php
            $rawPaymentType = optional(data_get($compra, 'transfer'))->payment_type ?? '';
            $paymentTypeStr = is_string($rawPaymentType) ? trim(strtolower($rawPaymentType)) : (string) $rawPaymentType;
            $depositsCount = data_get($compra, 'deposits') ? data_get($compra, 'deposits')->count() : 0;
            $isMulti = $depositsCount > 1 || in_array($paymentTypeStr, ['multi', 'm', 'multi pago', 'multipago'], true);
            $showTransferGateway = $rawPaymentType !== null && $rawPaymentType !== '';
          @endphp

          @if ($isMulti)
            {{ 'Multi Pago' }}{{ data_get($compra, 'use_balance') == '1' ? ' + Saldo' : '' }}
          @else
            {{ 'Pago único' }}
          @endif

        </td>
      </tr>

      @if (!$isMulti)
        <tr>
          <th colspan="3">Método de pago</th>
          <td>
          </td>
          @if (!$showTransferGateway)
            <td>{{ data_get($compra, 'text_payment_type') }} {{ data_get($compra, 'use_balance') == '1' ? ' + Saldo' : '' }}</td>
          @else
            <td>
              {{ data_get($compra, 'transfer.gateway.name') }}
            </td>
          @endif
        </tr>
        <tr>
          @if (!$showTransferGateway)
            <td colspan="5">
              {{ data_get($compra, 'payment_type') == '3' && data_get($compra, 'transfer.name') ? ' ' . data_get($compra, 'transfer.name') : '' }}
              @if (data_get($compra, 'payment_type') != '3' && isset($bankAccount))
                {{ data_get($bankAccount, 'bank.name') ? ' ' . data_get($bankAccount, 'bank.name') : '' }}
                {{ data_get($bankAccount, 'number') ? ' ' . data_get($bankAccount, 'number') : '' }}
              @endif
              {{ data_get($compra, 'payment_type') != '5' && data_get($compra, 'transfer.number') ? ' #' . data_get($compra, 'transfer.number') : '' }}
            </td>
          @else
            <td colspan="5">
              <table class="">
                <tr>
                  <th style="width: 5%">#</th>
                  <th style="width: 20%">Método</th>
                  <th style="width: 15%">Monto</th>
                  <th style="width: 22%">Cuenta</th>
                  <th style="width: 23%">Detalles</th>
                </tr>
                @foreach (data_get($compra, 'deposits', []) as $index => $deposit)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ data_get($deposit, 'gateway.name') ?? data_get($deposit, 'method_code') }}</td>
                    <td>
                      @if (is_numeric(data_get($deposit, 'final_amo')))
                        {{ number_format(data_get($deposit, 'final_amo'), 2, '.', ',') . data_get($deposit, 'method_currency') }}
                      @else
                        {{ data_get($deposit, 'final_amo', '—') }}
                      @endif
                    </td>
                    <td>
                      @if (is_array(data_get($deposit, 'account')) && count(data_get($deposit, 'account')))
                        @php($__first = true)
                        @foreach (data_get($deposit, 'account') as $k => $v)
                          @if (!is_null($v) && $v !== '')
                            @if (!$__first)
                              <br>
                            @endif
                            {{ ucfirst($k) }}: {{ $v }}
                            @php($__first = false)
                          @endif
                        @endforeach
                      @else
                        —
                      @endif
                    </td>
                    <td>
                      @if (is_array(data_get($deposit, 'fields')) && count(data_get($deposit, 'fields')))
                        @php($__firstF = true)
                        @foreach (data_get($deposit, 'fields') as $k => $v)
                          @if (!is_null($v) && $v !== '')
                            @if (!$__firstF)
                              <br>
                            @endif
                            {{ ucfirst($k) }}: {{ $v }}
                            @php($__firstF = false)
                          @endif
                        @endforeach
                      @else
                        —
                      @endif
                    </td>
                  </tr>
                @endforeach
              </table>
            </td>
          @endif
        </tr>
      @else
        <tr>
          <td colspan="5">
            DEPÓSITOS DE LA COMPRA
          </td>
        </tr>
        <tr>
          <td colspan="5">
            @if (data_get($compra, 'deposits') && data_get($compra, 'deposits')->count())
              <table class="">
                <tr>
                  <th style="width: 5%">#</th>
                  <th style="width: 20%">Método</th>
                  <th style="width: 15%">Monto</th>
                  <th style="width: 22%">Cuenta</th>
                  <th style="width: 23%">Campos</th>
                </tr>
                @foreach (data_get($compra, 'deposits', []) as $index => $deposit)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ data_get($deposit, 'gateway.name') ?? data_get($deposit, 'method_code') }}</td>
                    <td>
                      @if (is_numeric(data_get($deposit, 'final_amo')))
                        {{ number_format(data_get($deposit, 'final_amo'), 2, '.', ',') . data_get($deposit, 'method_currency') }}
                      @else
                        {{ data_get($deposit, 'final_amo', '—') }}
                      @endif
                    </td>
                    <td>
                      @if (is_array(data_get($deposit, 'account')) && count(data_get($deposit, 'account')))
                        @php($__first = true)
                        @foreach (data_get($deposit, 'account') as $k => $v)
                          @if (!is_null($v) && $v !== '')
                            @if (!$__first)
                              <br>
                            @endif
                            {{ ucfirst($k) }}: {{ $v }}
                            @php($__first = false)
                          @endif
                        @endforeach
                      @else
                        —
                      @endif
                    </td>
                    <td>
                      @if (is_array(data_get($deposit, 'fields')) && count(data_get($deposit, 'fields')))
                        @php($__firstF = true)
                        @foreach (data_get($deposit, 'fields') as $k => $v)
                          @if (!is_null($v) && $v !== '')
                            @if (!$__firstF)
                              <br>
                            @endif
                            {{ ucfirst($k) }}: {{ $v }}
                            @php($__firstF = false)
                          @endif
                        @endforeach
                      @else
                        —
                      @endif
                    </td>
                  </tr>
                @endforeach
              </table>
            @endif
          </td>
        </tr>
      @endif

      <tr>
        <th colspan="3">Total</th>
        <td></td>
        <td class="money">
          {{ Money::getByCurrency(
            Total::getByCurrency($compra) +
              CalcPrice::getByCurrency(
                data_get($compra, 'shipping_fee', 0),
                data_get($compra, 'details.0.coin', '1'),
                data_get($compra, 'exchange.change', 1),
                data_get($compra, 'currency'),
              ) +
              CalcPrice::getByCurrency(
                data_get($compra, 'tip_money', 0),
                data_get($compra, 'details.0.coin', '1'),
                data_get($compra, 'exchange.change', 1),
                data_get($compra, 'currency'),
              ),
            data_get($compra, 'currency'),
          ) }}
        </td>
      </tr>
      @if (data_get($compra, 'use_balance') == '1')
        <tr>
          <th colspan="3">Pago con Saldo</th>
          <td></td>
          <td>
            -
            {{ Money::getByCurrency(data_get($compra, 'amount_balance', 0), data_get($compra, 'currency')) }}
          </td>
        </tr>
        <tr>
          <th colspan="3">Total</th>
          <td></td>
          <td class="money" colspan="">
            {{ Money::getByCurrency(
              Total::getByCurrency($compra) +
                (CalcPrice::getByCurrency(
                  data_get($compra, 'shipping_fee', 0),
                  data_get($compra, 'details.0.coin', '1'),
                  data_get($compra, 'exchange.change', 1),
                  data_get($compra, 'currency'),
                ) +
                  CalcPrice::getByCurrency(
                    data_get($compra, 'tip_money', 0),
                    data_get($compra, 'details.0.coin', '1'),
                    data_get($compra, 'exchange.change', 1),
                    data_get($compra, 'currency'),
                  )) -
                CalcPrice::getByCurrency(
                  data_get($compra, 'amount_balance', 0),
                  data_get($compra, 'details.0.coin', '1'),
                  data_get($compra, 'exchange.change', 1),
                  data_get($compra, 'currency'),
                ),
              data_get($compra, 'currency'),
            ) }}
          </td>
        </tr>
      @endif
    </table>
    <p class="text-center">
      Atentamente, <br />
      Tu Equipo ProMArKet Latino
    </p>
  </div>
</body>

</html>
