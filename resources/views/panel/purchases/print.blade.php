@extends('layouts/blankLayout')

@section('title', __('Print Order'))

@section('content')
  <div style="max-width:800px;margin:40px auto;font-family:Arial,Helvetica,sans-serif;">
      <div style="text-align:center;margin-bottom:20px;">
      <h2>ProMarket</h2>
      <p>{{ __('Order') }}: #{{ $purchase->id ?? '—' }}</p>
    </div>

    <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
      <div>
        <strong>{{ __('Customer Details') }}</strong>
        <p>{{ data_get($purchase,'user.name','-') }}<br>{{ data_get($purchase,'user.email','') }}</p>
      </div>
      <div>
        <strong>{{ __('Delivery Details') }}</strong>
        <p>{{ data_get($purchase,'delivery.address','-') }}</p>
      </div>
    </div>

    <table width="100%" border="1" cellspacing="0" cellpadding="6" style="border-collapse:collapse;">
      <thead>
        <tr>
          <th>Descripción</th>
          <th>Impuesto</th>
          <th>Cantidad</th>
          <th>Costo</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        @if(isset($purchase) && $purchase->details)
          @foreach($purchase->details as $d)
            <tr>
              <td>{{ $d->description ?? ($d->product_amount->product->name ?? '') }}</td>
              <td>{{ $d->tax ?? 'Exento' }}</td>
              <td>{{ $d->quantity }}</td>
              <td>{{ $d->price }}</td>
              <td>{{ number_format(($d->price * $d->quantity),2,',','.') }} Bs.</td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>

    <div style="text-align:right;margin-top:10px;">
      <p><strong>{{ __('SubTotal') }}:</strong> {{ data_get($purchase,'subtotal','0') }}</p>
      <p><strong>{{ __('Total') }}:</strong> {{ data_get($purchase,'total','0') }}</p>
    </div>
  </div>
@endsection
