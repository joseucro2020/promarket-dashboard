<div class="row">
  <div class="col-md-2">
    <div class="form-group">
      <label for="code">{{ __('buyorder.code') }}</label>
      <input type="text" name="code" id="code" class="form-control" placeholder="{{ __('buyorder.code') }}" value="{{ old('code', $buyorder->code ?? '') }}">
    </div>
  </div>
  <div class="col-md-2">
    <div class="form-group">
      <label for="date">{{ __('buyorder.date') }}</label>
      <input type="date" name="fecha" id="date" class="form-control" value="{{ old('fecha', isset($buyorder) && $buyorder->fecha ? \Carbon\Carbon::parse($buyorder->fecha)->format('Y-m-d') : '') }}">
    </div>
  </div>
  <div class="col-md-2">
    <div class="form-group">
      <label for="due_date">{{ __('buyorder.due_date') }}</label>
      <input type="date" name="fecha_vto" id="due_date" class="form-control" value="{{ old('fecha_vto', isset($buyorder) && $buyorder->fecha_vto ? \Carbon\Carbon::parse($buyorder->fecha_vto)->format('Y-m-d') : '') }}">
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-group">
      <label for="supplier_id">{{ __('buyorder.supplier') }}</label>
      <select name="proveedor_id" id="supplier_id" class="form-control">
        <option value="">{{ __('Select') }}</option>
        @foreach($suppliers as $supplier)
          <option value="{{ $supplier->id }}" {{ (old('proveedor_id', $buyorder->proveedor_id ?? '') == $supplier->id) ? 'selected' : '' }}>{{ $supplier->name }}</option>
        @endforeach
      </select>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-2">
    <div class="form-group">
      <label for="currency">{{ __('buyorder.currency') }}</label>
      <select name="moneda" id="currency" class="form-control">
        <option value="">{{ __('Select') }}</option>
        @foreach($currencies ?? [] as $key => $label)
          <option value="{{ $key }}" {{ (old('moneda', $buyorder->moneda ?? '') == $key) ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="payment_condition">{{ __('buyorder.payment_condition') }}</label>
      <select name="cond_pago" id="payment_condition" class="form-control">
        <option value="">{{ __('Select') }}</option>
        @foreach($payment_conditions ?? [] as $key => $label)
          <option value="{{ $key }}" {{ (old('cond_pago', $buyorder->cond_pago ?? '') == $key) ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <label for="document_number">{{ __('buyorder.document_number') }}</label>
      <input type="text" name="nro_doc" id="document_number" class="form-control" value="{{ old('nro_doc', $buyorder->nro_doc ?? '') }}">
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="form-group">
      <label for="observation">{{ __('buyorder.observation') }}</label>
      <textarea name="reason" id="observation" class="form-control" rows="3">{{ old('reason', $buyorder->reason ?? '') }}</textarea>
    </div>
  </div>
</div>

<hr>

<h5>{{ __('Items') }}</h5>
<div class="table-responsive">
  <table class="table table-sm table-bordered" id="items-table">
    <thead>
      <tr>
        <th>{{ __('Product') }}</th>
        <th width="100">{{ __('buyorder.qty') }}</th>
        <th width="150">{{ __('buyorder.unit_price') }}</th>
        <th width="150">{{ __('buyorder.total') }}</th>
        <th width="50"></th>
      </tr>
    </thead>
    <tbody>
      @if(old('items'))
        @foreach(old('items') as $i => $it)
          <tr>
            <td><input type="text" name="items[{{ $i }}][product_name]" value="{{ $it['product_name'] }}" class="form-control"></td>
            <td><input type="number" step="1" name="items[{{ $i }}][qty]" value="{{ $it['qty'] }}" class="form-control qty"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][unit_price]" value="{{ $it['unit_price'] }}" class="form-control unit_price"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][total]" value="{{ $it['total'] }}" class="form-control total" readonly></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item">-</button></td>
          </tr>
        @endforeach
      @elseif(isset($buyorder) && $buyorder->details)
        @foreach($buyorder->details as $i => $detail)
          <tr>
            <td><input type="text" name="items[{{ $i }}][product_name]" value="{{ $detail->product_name }}" class="form-control"></td>
            <td><input type="number" step="1" name="items[{{ $i }}][qty]" value="{{ $detail->qty }}" class="form-control qty"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][unit_price]" value="{{ $detail->unit_price }}" class="form-control unit_price"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][total]" value="{{ $detail->total }}" class="form-control total" readonly></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item">-</button></td>
          </tr>
        @endforeach
      @else
        <tr>
          <td><input type="text" name="items[0][product_name]" class="form-control"></td>
          <td><input type="number" step="1" name="items[0][qty]" class="form-control qty"></td>
          <td><input type="number" step="0.01" name="items[0][unit_price]" class="form-control unit_price"></td>
          <td><input type="number" step="0.01" name="items[0][total]" class="form-control total" readonly></td>
          <td><button type="button" class="btn btn-sm btn-danger remove-item">-</button></td>
        </tr>
      @endif
    </tbody>
  </table>
</div>
<div class="text-right mb-3">
  <button type="button" class="btn btn-sm btn-secondary" id="add-item">{{ __('buyorder.add_item') }}</button>
  <button type="button" class="btn btn-sm btn-info" id="recalc">{{ __('buyorder.recalculate') }}</button>
</div>

<div class="row">
  <div class="col-md-4 offset-md-8">
    <div class="form-group">
      <label>{{ __('buyorder.subtotal') }}</label>
      <input type="number" step="0.01" name="subtotal" id="subtotal" class="form-control" value="{{ old('subtotal', $buyorder->subtotal ?? '0.00') }}" readonly>
    </div>
    <div class="form-group">
      <label>{{ __('buyorder.tax') }}</label>
      <input type="number" step="0.01" name="tax" id="tax" class="form-control" value="{{ old('tax', $buyorder->tax ?? '0.00') }}">
    </div>
    <div class="form-group">
      <label>{{ __('buyorder.total') }}</label>
      <input type="number" step="0.01" name="total" id="total" class="form-control" value="{{ old('total', $buyorder->total ?? '0.00') }}" readonly>
    </div>
  </div>
</div>

@section('page-script')
<script>
  function recalc(){
    var subtotal = 0;
    $('#items-table tbody tr').each(function(){
      var qty = parseFloat($(this).find('.qty').val()) || 0;
      var up = parseFloat($(this).find('.unit_price').val()) || 0;
      var t = qty * up;
      $(this).find('.total').val(t.toFixed(2));
      subtotal += t;
    });
    $('#subtotal').val(subtotal.toFixed(2));
    var tax = parseFloat($('#tax').val()) || 0;
    var total = subtotal + tax;
    $('#total').val(total.toFixed(2));
  }

  $(function(){
    $(document).on('click', '#add-item', function(){
      var idx = $('#items-table tbody tr').length;
      var row = '<tr>'+
        '<td><input type="text" name="items['+idx+'][product_name]" class="form-control"></td>'+
        '<td><input type="number" step="1" name="items['+idx+'][qty]" class="form-control qty"></td>'+
        '<td><input type="number" step="0.01" name="items['+idx+'][unit_price]" class="form-control unit_price"></td>'+
        '<td><input type="number" step="0.01" name="items['+idx+'][total]" class="form-control total" readonly></td>'+
        '<td><button type="button" class="btn btn-sm btn-danger remove-item">-</button></td>'+
      '</tr>';
      $('#items-table tbody').append(row);
    });

    $(document).on('click', '.remove-item', function(){
      $(this).closest('tr').remove();
      recalc();
    });

    $(document).on('input', '.qty, .unit_price, #tax', function(){
      recalc();
    });

    $(document).on('click', '#recalc', function(){ recalc(); });

    recalc();
  });
</script>
@endsection
