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
  @endphp
  <div class="header">
    <div class="img">
      <img class="logo" src="{{ $logoUrl ?? 'https://www.promarketlatino.com/img/logo-black.png' }}">
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
        <tr>
          <td>
            @if (data_get($item, 'product') == null)
              {{ data_get($item, 'discount_description') }}
            @else
              {{ \App::getLocale() == 'es' ? data_get($item, 'product.name') : data_get($item, 'product.name_english') }}
              {{ data_get($item, 'presentation') }}
              {{ data_get($item, 'unit') }}
              {{ data_get($item, 'discounts_text') }}
            @endif
          </td>
          <td class="text-center">
            @if (data_get($item, 'product') != null)
              {{ data_get($item, 'product.taxe.name') ?: 'Exento' }}
            @endif
          </td>
          <td class="text-center">{{ data_get($item, 'quantity') }}</td>
          <td class="text-center">
            {{ Money::getByCurrency(CalcPrice::getByCurrency(data_get($item, 'price'), data_get($item, 'coin'), data_get($compra, 'exchange.change', 1), data_get($compra, 'currency')), data_get($compra, 'currency')) }}
          </td>
          <td class="text-right">
            {{ Money::getByCurrency(CalcPrice::getByCurrency(data_get($item, 'price'), data_get($item, 'coin'), data_get($compra, 'exchange.change', 1), data_get($compra, 'currency')) * data_get($item, 'quantity', 0), data_get($compra, 'currency')) }}
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
