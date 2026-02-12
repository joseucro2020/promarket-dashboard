<div class="row">
  <div class="col-md-2">
    <div class="form-group">
      <label for="code">{{ __('buyorder.code') }}</label>
      <input type="text" name="code" id="code" class="form-control" placeholder="{{ __('buyorder.code') }}" value="{{ old('code', $buyorder->code ?? '') }}" required>
    </div>
  </div>
  <div class="col-md-2">
    <div class="form-group">
      <label for="date">{{ __('buyorder.date') }}</label>
      <input type="date" name="fecha" id="date" class="form-control" value="{{ old('fecha', isset($buyorder) && $buyorder->fecha ? \Carbon\Carbon::parse($buyorder->fecha)->format('Y-m-d') : '') }}" required>
    </div>
  </div>
  <div class="col-md-2">
    <div class="form-group">
      <label for="due_date">{{ __('buyorder.due_date') }}</label>
      <input type="date" name="fecha_vto" id="due_date" class="form-control" value="{{ old('fecha_vto', isset($buyorder) && $buyorder->fecha_vto ? \Carbon\Carbon::parse($buyorder->fecha_vto)->format('Y-m-d') : '') }}" required>
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-group">
      <label for="supplier_id">{{ __('buyorder.supplier') }}</label>
      <select name="proveedor_id" id="supplier_id" class="form-control" required>
        <option value="">{{ __('Select') }}</option>
        @foreach($suppliers as $supplier)
          <option value="{{ $supplier->id }}" {{ (old('proveedor_id', $buyorder->proveedor_id ?? '') == $supplier->id) ? 'selected' : '' }}>{{ $supplier->nombre_prove ?? $supplier->name ?? $supplier->nombre ?? '—' }}</option>
        @endforeach
      </select>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-2">
    <div class="form-group">
      <label for="currency">{{ __('buyorder.currency') }}</label>
      <select name="moneda" id="currency" class="form-control" required>
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
      <select name="cond_pago" id="payment_condition" class="form-control" required>
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
      <input type="text" name="nro_doc" id="document_number" class="form-control" value="{{ old('nro_doc', $buyorder->nro_doc ?? '') }}" required>
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
  <table class="table table-striped table-bordered table-hover w-100 module-list-table buyorder-items-table" id="items-table">
    <thead>
      <tr>
        <th>{{ __('Product') }}</th>
        <th>{{ __('Cantidad Original') }}</th>
        <th>{{ __('Cantidad Modificada') }}</th>
        <th>{{ __('Cantidad Final') }}</th>
        <th>{{ __('Costo') }}</th>
        <th>{{ __('Total Neto') }}</th>
        <th>% {{ __('Iva') }}</th>
        <th>{{ __('Iva') }}</th>
        <th>% {{ __('Utilidad') }}</th>
        <th>{{ __('Precio Venta') }}</th>
        <th width="50"></th>
      </tr>
    </thead>
    <tbody>
      @if(old('items'))
        @foreach(old('items') as $i => $it)
          <tr>
            <td><input type="text" name="items[{{ $i }}][product_name]" value="{{ $it['product_name'] }}" class="form-control"></td>
            <td><input type="number" step="1" name="items[{{ $i }}][original_qty]" value="{{ $it['original_qty'] ?? 0 }}" class="form-control"></td>
            <td><input type="number" step="1" name="items[{{ $i }}][modified_qty]" value="{{ $it['modified_qty'] ?? 0 }}" class="form-control"></td>
            <td><input type="number" step="1" name="items[{{ $i }}][final_qty]" value="{{ $it['final_qty'] ?? 0 }}" class="form-control qty"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][cost]" value="{{ $it['cost'] ?? 0 }}" class="form-control cost"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][total_net]" value="{{ $it['total_net'] ?? 0 }}" class="form-control total" readonly></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][tax_percent]" value="{{ $it['tax_percent'] ?? 0 }}" class="form-control tax_percent" readonly></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][tax]" value="{{ $it['tax'] ?? 0 }}" class="form-control tax"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][profit_percent]" value="{{ $it['profit_percent'] ?? 0 }}" class="form-control profit_percent"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][sale_price]" value="{{ $it['sale_price'] ?? 0 }}" class="form-control unit_price"></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item">-</button></td>
          </tr>
        @endforeach
      @elseif(isset($buyorder) && $buyorder->detalles)
        @foreach($buyorder->detalles as $i => $detail)
          <tr>
            <td><input type="text" name="items[{{ $i }}][product_name]" value="{{ $detail->product_name }}" class="form-control"></td>
            <td><input type="number" step="1" name="items[{{ $i }}][original_qty]" value="{{ $detail->original_qty ?? 0 }}" class="form-control"></td>
            <td><input type="number" step="1" name="items[{{ $i }}][modified_qty]" value="{{ $detail->modified_qty ?? 0 }}" class="form-control"></td>
            <td><input type="number" step="1" name="items[{{ $i }}][final_qty]" value="{{ $detail->final_qty ?? $detail->qty }}" class="form-control qty"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][cost]" value="{{ $detail->cost ?? 0 }}" class="form-control cost"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][total_net]" value="{{ $detail->total ?? 0 }}" class="form-control total" readonly></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][tax_percent]" value="{{ $detail->tax_percent ?? 0 }}" class="form-control tax_percent" readonly></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][tax]" value="{{ $detail->tax ?? 0 }}" class="form-control tax"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][profit_percent]" value="{{ $detail->profit_percent ?? 0 }}" class="form-control profit_percent"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][sale_price]" value="{{ $detail->unit_price ?? 0 }}" class="form-control unit_price"></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item">-</button></td>
          </tr>
        @endforeach
      
      @endif
    </tbody>
  </table>
</div>
<div class="text-right mb-3">
  <button type="button" class="btn btn-sm btn-secondary" id="add-item">
    <i data-feather="plus"></i> {{ __('buyorder.add_item') }}
  </button>
  <button type="button" class="btn btn-sm btn-info" id="recalc">
    <i data-feather="refresh-cw"></i> {{ __('buyorder.recalculate') }}
  </button>
</div>

<!-- hidden payload expected by controller -->
<input type="hidden" name="addRows" id="addRows" />

<!-- Modal para agregar item -->
<div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="itemModalLabel">{{ __('Agregar Registro') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="item-modal-form">
          <div class="form-row">
            <div class="form-group col-md-6" style="position:relative;">
                <label>{{ __('Product') }}</label>
                <input type="text" id="modal_product_name" class="form-control" autocomplete="off" />
                <input type="hidden" id="modal_product_id" />
                <div id="modal_product_suggestions" class="list-group" style="position:absolute;z-index:1050;left:0;right:0;display:none;max-height:220px;overflow:auto;"></div>
              </div>
            <div class="form-group col-md-2">
              <label>{{ __('Existencia') }}</label>
              <input type="number" id="modal_existencia" class="form-control" value="0" />
            </div>
            <div class="form-group col-md-2">
              <label>{{ __('Cantidad') }}</label>
              <input type="number" id="modal_qty" class="form-control" value="0" />
            </div>
            <div class="form-group col-md-2">
              <label>{{ __('Costo') }}</label>
              <input type="number" step="0.01" id="modal_cost" class="form-control" value="0.00" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-3">
              <label>{{ __('Total Neto') }}</label>
              <input type="number" step="0.01" id="modal_total_net" class="form-control" readonly />
            </div>
            <div class="form-group col-md-2">
              <label>% IVA</label>
              <input type="number" step="0.01" id="modal_tax_percent" class="form-control" value="0" readonly />
            </div>
            <div class="form-group col-md-2">
              <label>{{ __('IVA') }}</label>
              <input type="number" step="0.01" id="modal_tax" class="form-control" readonly />
            </div>
            <div class="form-group col-md-2">
              <label>% Utilidad</label>
              <input type="number" step="0.01" id="modal_profit_percent" class="form-control" value="0" />
            </div>
            <div class="form-group col-md-3">
              <label>{{ __('Precio Venta') }}</label>
              <input type="number" step="0.01" id="modal_sale_price" class="form-control" value="0.00" />
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
        <button type="button" class="btn btn-primary" id="modal-add-confirm">{{ __('Continuar') }}</button>
      </div>
    </div>
  </div>
</div>

<!-- (Se usa SweetAlert2 para toasts; no se requiere contenedor HTML) -->

<div class="row">
  <div class="col-md-4 offset-md-8">
    <div class="form-group">
      <label>{{ __('buyorder.subtotal') }}</label>
      <input type="number" step="0.01" name="subtotal" id="subtotal" class="form-control" value="{{ old('subtotal', $buyorder->subtotal ?? '0.00') }}" readonly>
    </div>
    <div class="form-group">
      <label>{{ __('buyorder.tax') }}</label>
      <input type="number" step="0.01" name="tax" id="tax" class="form-control" value="{{ old('tax', $buyorder->tax ?? '0.00') }}" readonly>
    </div>
    <div class="form-group">
      <label>{{ __('buyorder.total') }}</label>
      <input type="number" step="0.01" name="total" id="total" class="form-control" value="{{ old('total', $buyorder->total ?? '0.00') }}" readonly>
    </div>
  </div>
</div>

@section('page-script')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    console.log('buyorder: page script loaded');
    // Intento localizar el formulario por action (por seguridad)
    var buyorderFormByAction = $('form[action="{{ route('buyorders.store') }}"]');
    if(buyorderFormByAction.length){
      console.log('buyorder: found form by action selector');
    } else {
      console.log('buyorder: form by action NOT found');
    }
    // Productos disponibles (vienen desde el controlador). Usar para autocompletar en modal.
    var buyProducts = @json($products ?? []);

    // Mostrar/ocultar toast usando SweetAlert2 (estándar de la aplicación)
    // IMPORTANT: No cambiar el estándar de diseño de la aplicación.
    // Los toasts deben mostrarse con SweetAlert2 (toast: true, position: 'top-end')
    // para mantener la consistencia visual global. Cualquier cambio debe
    // coordinarse con el equipo de UI/UX.
    function showToast(msg, icon){
      icon = icon || 'success';
      try{
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: icon,
          title: msg,
          showConfirmButton: false,
          timer: 5000,
          timerProgressBar: true
        });
      }catch(e){
        console.error('Swal error showing toast:', e);
      }
    }

    function hideToast(){
      try{ Swal.close(); }catch(e){ console.error('Swal close error:', e); }
    }

    // Escapar texto para insertar en HTML
    function escapeHtml(text){
      return (text+'')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    // Render sugerencias
    function renderProductSuggestions(items){
      var $box = $('#modal_product_suggestions');
      $box.empty();
      if(!items || items.length === 0){ $box.hide(); return; }
      items.forEach(function(p, i){
        var exist = getExistence(p) || 0;
        var display = (p.name || p.name) + (p.presentation ? ' — '+p.presentation : '') + (p.unit ? ' ('+p.unit+')' : '') + ' — Exist.: '+exist;
        var $el = $('<a href="#" class="list-group-item list-group-item-action">').text(display).data('product', p);
        $box.append($el);
      });
      $box.show();
    }

    // Obtiene la existencia numérica del objeto producto (varios alias)
    function getExistence(p){
      if(!p) return 0;
      var existKeys = ['amount','cantidad','unit_amount','existencia','stock','amount_available','amountAvailable','qty','cantidad_existencia','exist','amounts'];
      for(var k=0;k<existKeys.length;k++){
        var key = existKeys[k];
        if(p.hasOwnProperty(key) && p[key] !== null && p[key] !== undefined){
          var v = parseFloat(p[key]);
          if(!isNaN(v)) return v;
        }
      }
      if(p.amount !== undefined && p.amount !== null){
        var v2 = parseFloat(p.amount);
        if(!isNaN(v2)) return v2;
      }
      return 0;
    }

    // Filtrar por texto ingresado
    $(document).on('input', '#modal_product_name', function(e){
      var q = $(this).val().toLowerCase();
      if(q.length < 2){ $('#modal_product_suggestions').hide(); return; }
      var matches = buyProducts.filter(function(p){
        var name = (p.name || '').toString().toLowerCase();
        return name.indexOf(q) !== -1 && getExistence(p) > 0;
      }).slice(0, 15);
      renderProductSuggestions(matches);
    });

    // Selección de sugerencia
    $(document).on('click', '#modal_product_suggestions .list-group-item', function(e){
      e.preventDefault();
      var p = $(this).data('product');
      if(!p) return;
      console.log('buyorder: product selected ->', p);
      $('#modal_product_name').val(p.name || '');
      // guardar id del product_amount o id de producto
      var pid = p.id || p.idproduc || p.product_id || null;
      $('#modal_product_id').val(pid);
      // cantidad en stock: intentar varios campos posibles con detección amplia
      var existencia = 0;
      var existKeys = ['amount','cantidad','unit_amount','existencia','stock','amount_available','amountAvailable','qty','cantidad_existencia','exist','amounts'];
      for(var k=0;k<existKeys.length;k++){
        var key = existKeys[k];
        if(p.hasOwnProperty(key) && p[key] !== null && p[key] !== undefined){
          var v = parseFloat(p[key]);
          if(!isNaN(v)) { existencia = v; break; }
        }
      }
      // si sigue 0, intentar la propiedad 'amount' sin hasOwnProperty (caso objetos db)
      if(existencia === 0 && p.amount !== undefined && p.amount !== null){
        var v2 = parseFloat(p.amount);
        if(!isNaN(v2)) existencia = v2;
      }
      $('#modal_existencia').val(Number.isFinite(existencia) ? existencia : 0);
      var cost = parseFloat(p.cost || p.costo || 0) || 0;
      $('#modal_cost').val(cost.toFixed(2));
      var sale = parseFloat(p.price || p.precio || p.pventa || 0) || 0;
      $('#modal_sale_price').val(sale.toFixed(2));
      // impuesto si viene
      var porcentaje = parseFloat(p.porcentaje || p.percentage || p.tax_percent || 0) || 0;
      $('#modal_tax_percent').val(porcentaje);
      // utilidad % = (precio - costo)/costo * 100 (si costo > 0)
      var utilidad = 0;
      if(cost > 0){ utilidad = ((sale - cost) / cost) * 100; }
      $('#modal_profit_percent').val(utilidad ? utilidad.toFixed(2) : '0.00');
      modalRecalc();
      $('#modal_product_suggestions').hide();
    });

    // ocultar sugerencias al click fuera
    $(document).on('click', function(e){
      if(!$(e.target).closest('#modal_product_name, #modal_product_suggestions').length){
        $('#modal_product_suggestions').hide();
      }
    });

    // Abrir modal para agregar item — limpiar formulario antes de mostrar
    $(document).on('click', '#add-item', function(){
      hideToast();
      // Limpiar campos del modal para un nuevo registro
      $('#modal_product_name').val('');
      $('#modal_product_id').val('');
      $('#modal_existencia').val(0);
      $('#modal_qty').val(0);
      $('#modal_cost').val('0.00');
      $('#modal_total_net').val('');
      $('#modal_tax_percent').val(0);
      $('#modal_tax').val('');
      $('#modal_profit_percent').val('0.00');
      $('#modal_sale_price').val('0.00');
      // ocultar y vaciar sugerencias
      $('#modal_product_suggestions').hide().empty();
      // enfocar el campo de producto
      $('#modal_product_name').focus();
      $('#itemModal').modal('show');
    });

    // Calcular valores dentro del modal
    function modalRecalc(){
      var qty = parseFloat($('#modal_qty').val()) || 0;
      var sale = parseFloat($('#modal_sale_price').val()) || 0;
      var total_net = qty * sale;
      $('#modal_total_net').val(total_net.toFixed(2));
      var tax_percent = parseFloat($('#modal_tax_percent').val()) || 0;
      var tax = total_net * (tax_percent/100);
      $('#modal_tax').val(tax.toFixed(2));
    }

    $(document).on('input', '#modal_qty, #modal_sale_price, #modal_tax_percent', function(){ modalRecalc(); });
    // ocultar error al modificar cantidad o existencia
    $(document).on('input', '#modal_qty, #modal_existencia', function(){ hideToast(); });

    // Confirmar desde modal y añadir la fila
    $(document).on('click', '#modal-add-confirm', function(){
      var idx = $('#items-table tbody tr').length;
      var product = $('#modal_product_name').val() || '';
      var product_id = $('#modal_product_id').val() || '';
      var original_qty = parseFloat($('#modal_existencia').val()) || 0;
      var modified_qty = parseFloat($('#modal_qty').val()) || 0;
      var final_qty = modified_qty;
      var cost = parseFloat($('#modal_cost').val()) || 0;
      var total_net = parseFloat($('#modal_total_net').val()) || 0;
      var tax_percent = parseFloat($('#modal_tax_percent').val()) || 0;
      var tax = parseFloat($('#modal_tax').val()) || 0;
      var profit_percent = parseFloat($('#modal_profit_percent').val()) || 0;
      var sale_price = parseFloat($('#modal_sale_price').val()) || 0;

      // Validación: no permitir cantidad mayor a existencia
      if(modified_qty > original_qty){
        showToast('La cantidad no puede ser mayor a la existencia.');
        $('#modal_qty').focus();
        return;
      }

      // Construir fila con texto visible y campos ocultos para envío
      var row = '<tr>'+
        '<td>'+ escapeHtml(product) + '<input type="hidden" name="items['+idx+'][product_name]" value="'+ escapeHtml(product) +'">' +
                     '<input type="hidden" name="items['+idx+'][product_id]" value="'+ escapeHtml(product_id) +'"></td>' +
        '<td>'+ escapeHtml(original_qty) + '<input type="hidden" name="items['+idx+'][original_qty]" value="'+ escapeHtml(original_qty) +'"></td>' +
        '<td>'+ escapeHtml(modified_qty) + '<input type="hidden" name="items['+idx+'][modified_qty]" value="'+ escapeHtml(modified_qty) +'"></td>' +
        '<td>'+ escapeHtml(final_qty) + '<input type="hidden" name="items['+idx+'][final_qty]" value="'+ escapeHtml(final_qty) +'" class="qty"></td>' +
        '<td>'+ escapeHtml(cost.toFixed(2)) + '<input type="hidden" name="items['+idx+'][cost]" value="'+ escapeHtml(cost.toFixed(2)) +'"></td>' +
        '<td>'+ escapeHtml(total_net.toFixed(2)) + '<input type="hidden" name="items['+idx+'][total_net]" value="'+ escapeHtml(total_net.toFixed(2)) +'" class="total"></td>' +
        '<td>'+ escapeHtml(tax_percent) + '<input type="hidden" name="items['+idx+'][tax_percent]" value="'+ escapeHtml(tax_percent) +'" class="tax_percent"></td>' +
        '<td>'+ escapeHtml(tax.toFixed(2)) + '<input type="hidden" name="items['+idx+'][tax]" value="'+ escapeHtml(tax.toFixed(2)) +'" class="tax"></td>' +
        '<td>'+ escapeHtml(profit_percent) + '<input type="hidden" name="items['+idx+'][profit_percent]" value="'+ escapeHtml(profit_percent) +'" class="profit_percent"></td>' +
        '<td>'+ escapeHtml(sale_price.toFixed(2)) + '<input type="hidden" name="items['+idx+'][sale_price]" value="'+ escapeHtml(sale_price.toFixed(2)) +'" class="unit_price"></td>' +
        '<td><button type="button" class="btn btn-sm btn-danger remove-item">-</button></td>' +
      '</tr>';
      $('#items-table tbody').append(row);
      $('#itemModal').modal('hide');
      recalc();
    });

    $(document).on('click', '.remove-item', function(){
      $(this).closest('tr').remove();
      recalc();
    });

    $(document).on('input', '.qty, .unit_price, #tax', function(){
      recalc();
    });

    $(document).on('click', '#recalc', function(){ recalc(); });

    // Serializar filas en JSON para que el controlador espere 'addRows'
    function gatherItems(){
      var items = [];
      $('#items-table tbody tr').each(function(){
        // los valores visibles están en texto; los valores reales están en inputs hidden dentro de cada celda
        var $tr = $(this);
        var getHidden = function(name){
          var $input = $tr.find('input[name$="['+name+']"]');
          if($input.length) return $input.val();
          return '';
        };
        var item = {
          product_name:    getHidden('product_name') || $tr.find('td').eq(0).text().trim(),
          product_id:      getHidden('product_id') || '',
          original_qty:    parseFloat(getHidden('original_qty') || 0) || 0,
          modified_qty:    parseFloat(getHidden('modified_qty') || 0) || 0,
          final_qty:       parseFloat(getHidden('final_qty') || 0) || 0,
          cost:            parseFloat(getHidden('cost') || 0) || 0,
          total_net:       parseFloat(getHidden('total_net') || 0) || 0,
          tax_percent:     parseFloat(getHidden('tax_percent') || 0) || 0,
          tax:             parseFloat(getHidden('tax') || 0) || 0,
          profit_percent:  parseFloat(getHidden('profit_percent') || 0) || 0,
          sale_price:      parseFloat(getHidden('sale_price') || 0) || 0
        };
        items.push(item);
      });
      return items;
    }

    // Al enviar el formulario, construir addRows JSON esperado por el controlador
    var boundForm = $('#items-table').closest('form');
    // también ligar directamente por action si existe
    if(buyorderFormByAction.length){ boundForm = buyorderFormByAction; }
    boundForm.on('submit', function(e){
      console.log('buyorder: submit handler triggered');
      var items = gatherItems();
      console.log('buyorder: gathered items', items);
      $('#addRows').val(JSON.stringify(items));
      // Considerar sólo items válidos (con product_id o product_name)
      var validItems = items.filter(function(it){
        return (it.product_id && it.product_id.toString().trim() !== '') || (it.product_name && it.product_name.toString().trim() !== '');
      });
      console.log('buyorder: valid items count', validItems.length);
      if(validItems.length === 0){
        e.preventDefault();
        showToast('No hay items válidos para guardar.', 'error');
        return false;
      }
      // dejar enviar normalmente
      return true;
    });

    recalc();
  });
</script>
@endsection
