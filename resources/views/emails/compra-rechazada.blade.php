<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <title>
    Orden #{{ $compra->id }}
  </title>
  <link rel="StyleSheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,600,700" />
  <style>
    body { font-family: 'Roboto', Arial, sans-serif; color: #2c2c2c; margin: 0; padding: 0; background: #f6f6f6; }
    .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 24px; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-justify { text-align: left; }
    .title { margin: 8px 0 12px; font-size: 24px; font-weight: 600; }
    .contact-title { margin: 12px 0 8px; font-size: 15px; font-weight: 700; }
    .contact-text { margin: 4px 0; font-size: 14px; line-height: 1.5; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #e7e7e7; padding: 8px 10px; font-size: 13px; vertical-align: top; }
    thead th { background: #fafafa; }
    .money p, .text-justify p { margin: 0; }
    hr { border: 0; border-top: 1px solid #ececec; margin: 12px 0; }
    .footer { margin-top: 18px; font-size: 12px; color: #777; text-align: center; }
  </style>
</head>
<body>
  @php
    use App\Libraries\CalcPrice;
    use App\Libraries\Money;
    use App\Libraries\Total;
  @endphp
  <div class="container">
    <h1 class="title text-center">Orden #{{ $compra->id }}</h1>
    <div class="text-justify">
      <p>Hola, <b>{{ $user->name }}</b></p>
      <p class="mt-2">Tu orden Orden Nro. {{ $compra->payment_type == '4' ? $compra->transaction_code : $compra->transferNumber }}
        fue <b>RECHAZADA</b> por: {{ $compra->rejectReason }}, lamentamos el incoveniente y te invitamos a seguir comprando nuestros productos.
      </p>
      <p class="mt-2">Puedes monitorear el estatus de tu orden desde la opcion historial de pedido que se encuentra
        en tu perfil de usuario de la tienda.
      </p>
      <hr class="mt-2">
    </div>
    <div class="row mt-2">
      <div class="col-12">
        <p class="contact-title">
          DATOS DEL CLIENTE
        </p>
      </div>
      <div class="col-12">
      <p class="contact-text"><b>Nombre y Apellido:</b> {{ $user->name }} -
        <b>{{ $user->persona == 1 ? 'Cédula de Identidad:' : 'Rif:' }}</b>
        {{ $user->identificacion }}
      </p>
      </div>
      <div class="col-12">
        <p class="contact-text"><b>Teléfono:</b> {{ $user->telefono }} - <b>Correo electrónico:</b> {{ $user->email }}</p>
      </div>
    </div>
    <hr>
    <div class="row">
      <div class="col-12">
        <p class="contact-title">
          DATOS DE LA PERSONA QUE RECIBE
        </p>
      </div>
      <div class="col-12">
        <p class="contact-text"><b>Recibe:</b> {{ $user->name }} -
          <b>{{ $user->persona == 1 ? 'Cédula de Identidad:' : 'Rif:' }}</b> {{ $user->identificacion }} </p>
      </div>
      @if($user->municipality && $user->parish)
        <div class="col-12">
          <p class="contact-text"><b>Municipio:</b> {{ $user->municipality->name }} <b>Sector:</b> {{ $user->parish->name }}</p>
        </div>
      @endif
      <div class="col-12">
        <p class="contact-text"><b>Dirección de entrega:</b> {{ optional($compra->delivery)->address }}</p>
      </div>
      <div class="col-12">
        <p class="contact-text">
          <b>Fecha:</b> {{ optional($compra->delivery)->date_formated }}  -
          <b>Turno:</b> {{ optional($compra->delivery)->turn_formated }}
        </p>
      </div>
      @if(optional($compra->delivery)->note)
        <div class="col-12">
          <p class="contact-text"><b>Nota:</b> {{ optional($compra->delivery)->note }}</p>
        </div>
      @endif
    </div>
    <hr>
    <div class="row">
      <div class="col-12">
        <p class="contact-title">
          DATOS DE PAGO
        </p>
      </div>
      <div class="col-12">
        <p class="contact-text">
          <b>Método:</b> {{ $compra->text_payment_type }}
        </p>
      </div>
      @if($compra->payment_type == 1 || $compra->payment_type == 2)
        <div class="col-12">
          <p class="contact-text">
            <b>Banco:</b> {{ optional(optional(optional($compra->transfer)->bankAccount)->bank)->name }} -
            <b>{{ $compra->payment_type == 1 ? 'Nro. Cuenta:' : 'Tel:' }} </b>
            {{ $compra->payment_type == 1 ? optional(optional($compra->transfer)->bankAccount)->number : optional(optional($compra->transfer)->bankAccount)->phone }}
          </p>
        </div>
        <div class="col-12">
          <p class="contact-text">
            <b>Nro. Referencia:</b> {{ optional($compra->transfer)->number }}
          </p>
        </div>
      @endif
      @if($compra->payment_type == 3)
        <div class="col-12">
          <p class="contact-text">
            <b>Nombre:</b> {{ optional($compra->transfer)->name }}
          </p>
        </div>
        <div class="col-12">
          <p class="contact-text">
            <b>Nro. Referencia:</b> {{ optional($compra->transfer)->number }}
          </p>
        </div>
      @endif
      @if($compra->payment_type == 4)
        <div class="col-12">
          <p class="contact-text">
            <b>Código de transacción:</b> {{ $compra->transaction_code }}
          </p>
        </div>
      @endif
      @if($compra->payment_type == 5)
        <div class="col-12">
          <p class="contact-text">
            <b>El cliente paga con:</b> {{ Money::getByCurrency(
              CalcPrice::getByCurrency(
                optional($compra->delivery)->pay_with,
                $compra->currency,
                optional($compra->exchange)->change,
                $compra->currency), $compra->currency
            ) }}
          </p>
        </div>
      @endif
      @if($compra->payment_type == 6)
        <div class="col-12">
          <p class="contact-text">
            <b>Código de transacción:</b> {{ optional($compra->transfer)->number }}
          </p>
        </div>
      @endif
    </div>
    <hr>
    <div class="row text-justify">
      <div class="col-12">
        <p class="contact-title">
          INFORMACION DE TU PEDIDO
        </p>
      </div>
    </div>
    <table cellspacing="0" cellpadding="0">
      <thead align="left">
        <tr>
          <th>Descripción</th>
          <th class="text-center">Impuesto</th>
          <th class="text-center">Cantidad</th>
          <th class="text-center">Costo</th>
          <th class="text-right">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($compra->details as $item)
          <tr class="borde-blanco">
            <td>
              @if($item->product == null)
                {{ $item->discount_description }}
              @else
                {{ \App::getLocale() == 'es' ? $item->product->name : $item->product->name_english }}
                {{ $item->presentation }}
                {{ $item->unit }}
                {{ $item->discounts_text }}
              @endif
            </td>
            <td class="text-center">
              @if($item->product != null)
                {{ $item->product->taxe ? $item->product->taxe->name : 'Exento' }}
              @endif
            </td>
            <td class="text-center"><b>{{ $item->quantity }}</b></td>
            <td class="text-center"><b>{{ Money::getByCurrency(CalcPrice::getByCurrency($item->price, $item->coin, optional($compra->exchange)->change, $compra->currency), $compra->currency) }}</b></td>
            <td class="text-right"><b>{{ Money::getByCurrency(CalcPrice::getByCurrency($item->price, $item->coin, optional($compra->exchange)->change, $compra->currency) * $item->quantity, $compra->currency) }}</b></td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        @if ($compra->coupon_id)
          <tr class="text-justify">
            <td colspan="3">
              <p class="font-bold">Total Cupón</p>
            </td>
            <td class="money" colspan="">
              <p>{{ Money::getByCurrency(Total::getByCurrency($compra) - Total::getByCurrency($compra, true), $compra->currency) }}</p>
            </td>
          </tr>
        @endif
        <tr class="text-justify">
          <td colspan="3">
            <p class="font-bold">Subtotal</p>
          </td>
          <td class="money" colspan="">
            <p><b>{{ Money::getByCurrency(Total::getByCurrency($compra), $compra->currency) }}</b></p>
          </td>
        </tr>
        <tr class="text-justify">
          <td colspan="3">
            <p class="font-bold">Propina</p>
          </td>
          <td class="money" colspan="">
            <p><b>{{ Money::getByCurrency(CalcPrice::getByCurrency($compra->tip_money, data_get($compra, 'details.0.coin'), optional($compra->exchange)->change, $compra->currency), $compra->currency) }}</b></p>
          </td>
        </tr>
        <tr class="text-justify">
          <td colspan="3">
            <p class="font-bold">Costo de Envío</p>
          </td>
          <td class="money" colspan="">
            <p><b>{{ Money::getByCurrency(CalcPrice::getByCurrency($compra->shipping_fee, data_get($compra, 'details.0.coin'), optional($compra->exchange)->change, $compra->currency), $compra->currency) }}</b></p>
          </td>
        </tr>
        <tr class="text-justify">
          <td colspan="3">
            <p class="font-bold">Método de pago:</p>
          </td>
          <td class="money" colspan="">
            <p><b>{{ $compra->text_payment_type }} {{ $compra->use_balance == '1' ? ' + Saldo' : '' }}</b></p>
          </td>
        </tr>
        <tr class="text-justify">
          <td colspan="3">
            <p class="font-bold">Total</p>
          </td>
          <td class="money" colspan="">
            <p>
              <b>{{
                  Money::getByCurrency(
                  Total::getByCurrency($compra) +
                  CalcPrice::getByCurrency(
                    $compra->shipping_fee,
                    data_get($compra, 'details.0.coin'),
                    optional($compra->exchange)->change,
                    $compra->currency
                  ) + CalcPrice::getByCurrency(
                    $compra->tip_money,
                    data_get($compra, 'details.0.coin'),
                    optional($compra->exchange)->change,
                    $compra->currency
                  ),
                  $compra->currency
                )
              }}</b>
            </p>
          </td>
        </tr>
        @if ($compra->use_balance == '1')
          <tr class="text-justify">
            <td colspan="3">
              <p class="font-bold">Pago con Saldo</p>
            </td>
            <td class="money" colspan="">
              <p>
                - {{
                  Money::getByCurrency(
                    $compra->amount_balance,
                    $compra->currency
                  )
                }}
              </p>
            </td>
          </tr>
        @endif
        @if ($compra->use_balance == '1')
          <tr class="text-justify">
            <td colspan="3">
              <p class="font-bold">Total</p>
            </td>
            <td class="money" colspan="">
              <p>
                {{
                  Money::getByCurrency(
                    Total::getByCurrency($compra) +
                    (CalcPrice::getByCurrency(
                      $compra->shipping_fee,
                      data_get($compra, 'details.0.coin'),
                      optional($compra->exchange)->change,
                      $compra->currency
                    ) +
                    CalcPrice::getByCurrency(
                      $compra->tip_money,
                      data_get($compra, 'details.0.coin'),
                      optional($compra->exchange)->change,
                      $compra->currency
                    ))
                    -
                    CalcPrice::getByCurrency(
                      $compra->amount_balance,
                      data_get($compra, 'details.0.coin'),
                      optional($compra->exchange)->change,
                      $compra->currency
                    ),
                    $compra->currency
                  )
                }}
              </p>
            </td>
          </tr>
        @endif
      </tfoot>
    </table>
    <div class="footer">
      {{ config('app.name') }}
    </div>
  </div>
</body>
</html>
