@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Orders'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('content')
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom p-1">
              <div class="head-label">
              <h4 class="mb-0">{{ __('locale.Orders') }}</h4>
            </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <a href="#" class="dt-button btn btn-outline-primary" id="btn-export">
                <i data-feather="download"></i> {{ __('locale.Export') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="mb-2">
            <div class="row">
              <div class="col-md-3 mb-1">
                <input type="date" id="date_from" class="form-control" placeholder="{{ __('locale.From') }}" value="{{ request('date_from', now()->toDateString()) }}">
              </div>
              <div class="col-md-3 mb-1">
                <input type="date" id="date_to" class="form-control" placeholder="{{ __('locale.To') }}" value="{{ request('date_to', now()->toDateString()) }}">
              </div>
              <div class="col-md-2 mb-1">
                <select id="filter_type" class="form-control">
                  <option value="">{{ __('locale.All') }}</option>
                  <option value="pending">{{ __('locale.Pending') }}</option>
                  <option value="processing">{{ __('locale.Processing') }}</option>
                  <option value="completed">{{ __('locale.Completed') }}</option>
                  <option value="rejected">{{ __('locale.Rejected') }}</option>
                </select>
              </div>
              <div class="col-md-2 mb-1">
                <input type="text" id="search_q" class="form-control" placeholder="{{ __('locale.Search') }}" value="{{ request('q','') }}">
              </div>
              <div class="col-md-2 mb-1">
                <button id="btn-consult" class="btn btn-primary">{{ __('locale.Consult') }}</button>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 purchases-table">
              <thead>
                  <tr>
                    <th>{{ __('locale.ID') }}</th>
                    <th>{{ __('locale.Date - Time') }}</th>
                    <th>{{ __('locale.Client') }}</th>
                    <th>{{ __('locale.Tip') }}</th>
                    <th>{{ __('locale.Amount') }}</th>
                    <th>{{ __('locale.Payment Type') }}</th>
                    <th>{{ __('locale.Payment Method') }}</th>
                    <th>{{ __('locale.Delivery Type') }}</th>
                    <th>{{ __('locale.Status') }}</th>
                    <th class="text-end">{{ __('locale.Actions') }}</th>
                  </tr>
                </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@include('panel.purchases._details')
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection

@section('page-script')
<script>
  $(function(){
    var table = $('.purchases-table').DataTable({
      responsive: true,
      language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
      data: [],
      columns: [
        { data: 'id' },
        { data: 'createdAt' },
        { data: 'clientName' },
        { data: 'tip' },
        { data: 'amount' },
        { data: 'paymentType' },
        { data: 'payName' },
        { data: 'deliveryType' },
        { data: 'statusType' },
        { data: null, orderable:false, searchable:false }
      ],
      columnDefs: [{
        targets: -1,
        render: function(data){
          var viewBtn = '<button type="button" class="btn btn-icon btn-flat-primary mr-1 view" data-id="'+data.id+'" data-toggle="tooltip" data-placement="top" title="'+"{{ __('locale.View') }}"+'"><i data-feather="eye"></i></button>';
          var viewCompanyBtn = '<button type="button" class="btn btn-icon btn-flat-primary mr-1 view-company" data-id="'+data.id+'" data-toggle="tooltip" data-placement="top" title="'+"{{ __('locale.View Details') }}"+'"><i data-feather="eye"></i></button>';
          var approveBtn = '<button type="button" class="btn btn-icon btn-flat-success mr-1 approve" data-id="'+data.id+'" data-toggle="tooltip" data-placement="top" title="'+"{{ __('locale.Approved') }}"+'"><i data-feather="check"></i></button>';
          var printBtn = '<a class="btn btn-icon btn-flat-dark mr-1" href="'+window.location.origin+'/panel/pedidos/'+data.id+'/print" target="_blank" data-toggle="tooltip" data-placement="top" title="'+"{{ __('locale.Print') }}"+'"><i data-feather="printer"></i></a>';
          var printCompanyBtn = '<a class="btn btn-icon btn-flat-dark mr-1" href="'+window.location.origin+'/panel/pedidos/'+data.id+'/print?company=1" target="_blank" data-toggle="tooltip" data-placement="top" title="'+"{{ __('locale.Print Order') }}"+'"><i data-feather="printer"></i></a>';
          var rejectBtn = '<button type="button" class="btn btn-icon btn-flat-danger reject" data-id="'+data.id+'" data-toggle="tooltip" data-placement="top" title="'+"{{ __('locale.Delete') }}"+'"><i data-feather="trash"></i></button>';
          return '<div class="d-flex align-items-center">' + viewBtn + viewCompanyBtn + approveBtn + printBtn + printCompanyBtn + rejectBtn + '</div>';
        }
      }]
    });

    function escapeHtml(value) {
      return $('<div>').text(value == null ? '' : value).html();
    }

    function formatDateTime(value) {
      if (!value) return '—';
      var date = new Date(value);
      if (isNaN(date.getTime())) return escapeHtml(value);
      var day = String(date.getDate()).padStart(2, '0');
      var month = String(date.getMonth() + 1).padStart(2, '0');
      var year = date.getFullYear();
      var hour = String(date.getHours()).padStart(2, '0');
      var minute = String(date.getMinutes()).padStart(2, '0');
      var second = String(date.getSeconds()).padStart(2, '0');
      return day + '-' + month + '-' + year + ' ' + hour + ':' + minute + ':' + second;
    }

    function formatDate(value) {
      if (!value) return '—';
      var date = new Date(value);
      if (isNaN(date.getTime())) return escapeHtml(value);
      var day = String(date.getDate()).padStart(2, '0');
      var month = String(date.getMonth() + 1).padStart(2, '0');
      var year = date.getFullYear();
      return day + '-' + month + '-' + year;
    }

    function formatMoney(value) {
      var amount = Number(value || 0);
      return '$ ' + amount.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function resolveDeliveryType(typeValue) {
      var type = parseInt(typeValue, 10);
      if (type === 1) return 'Nacional (Cobro a Destino)';
      if (type === 2) return 'Nacional (Envio a Tienda)';
      return 'Envío Regional';
    }

    function resolveTurn(turnValue) {
      var turn = parseInt(turnValue, 10);
      if (turn === 1) return 'Mañana';
      if (turn === 2) return 'Tarde';
      if (turn === 3) return 'Noche';
      return '—';
    }

    function renderPurchaseDetailsModal(res, includeTotals) {
      var clientName = escapeHtml(res.user && res.user.name ? res.user.name : '—');
      var paymentMethod = escapeHtml(res.text_payment_type || '—');
      var paymentAmount = formatMoney(res.total || 0);
      var turn = resolveTurn(res.delivery ? res.delivery.turn : null);
      var dateTime = formatDateTime(res.created_at);
      var deliveryType = escapeHtml(resolveDeliveryType(res.delivery ? res.delivery.type : null));
      var phone = escapeHtml((res.user && res.user.phone) || (res.delivery && res.delivery.phone) || '—');
      var address = escapeHtml(res.delivery && res.delivery.address ? res.delivery.address : '—');
      var deliveryDate = formatDate(res.delivery ? res.delivery.date : null);

      var html = '';
      html += '<div class="border-top pt-1">';
      html += '  <div class="row">';
      html += '    <div class="col-md-6 mb-1">';
      html += '      <p class="mb-50"><strong>{{ __('locale.Client') }}:</strong> ' + clientName + '</p>';
      html += '      <p class="mb-50"><strong>{{ __('locale.Payment Method') }}:</strong> ' + paymentMethod + '</p>';
      html += '      <p class="mb-50"><strong>{{ __('locale.Amount') }}:</strong> ' + paymentAmount + '</p>';
      html += '      <p class="mb-0"><strong>{{ __('locale.Turn') }}:</strong> ' + turn + '</p>';
      html += '    </div>';
      html += '    <div class="col-md-6 mb-1">';
      html += '      <p class="mb-50"><strong>{{ __('locale.Date - Time') }}:</strong> ' + dateTime + '</p>';
      html += '      <p class="mb-50"><strong>{{ __('locale.Delivery Type') }}:</strong> ' + deliveryType + '</p>';
      html += '      <p class="mb-0"><strong>{{ __('locale.Phone') }}:</strong> ' + phone + '</p>';
      html += '    </div>';
      html += '  </div>';
      html += '  <p class="mb-50"><strong>{{ __('locale.Address') }}:</strong> ' + address + '</p>';
      html += '  <p class="mb-1"><strong>{{ __('locale.Delivery Date') }}:</strong> ' + deliveryDate + '</p>';
      html += '</div>';

      html += '<div class="table-responsive mt-1">';
      html += '  <table class="table table-borderless mb-0">';
      html += '    <thead>';
      html += '      <tr>';
      html += '        <th>{{ __('locale.Description') }}</th>';
      html += '        <th>{{ __('locale.Presentation') }}</th>';
      html += '        <th>{{ __('locale.Tax') }}</th>';
      html += '        <th>{{ __('locale.Price') }}</th>';
      html += '        <th>{{ __('locale.Quantity') }}</th>';
      html += '        <th>{{ __('locale.SubTotal') }}</th>';
      html += '      </tr>';
      html += '    </thead>';
      html += '    <tbody>';

      if(res.details && res.details.length){
        res.details.forEach(function(d){
          var description = d.description || (d.producto && d.producto.name ? d.producto.name : '');
          description = escapeHtml(description + (d.discounts_text || ''));
          var presentation = escapeHtml((d.presentation || '') + (d.unit || ''));
          var tax = escapeHtml(d.tax || 'Exento');
          var price = Number(d.price || 0);
          var quantity = Number(d.quantity || 0);
          var subtotal = price * quantity;

          html += '      <tr>';
          html += '        <td>' + description + '</td>';
          html += '        <td>' + (presentation || '—') + '</td>';
          html += '        <td>' + tax + '</td>';
          html += '        <td>' + formatMoney(price) + '</td>';
          html += '        <td>' + quantity + '</td>';
          html += '        <td>' + formatMoney(subtotal) + '</td>';
          html += '      </tr>';
        });
      } else {
        html += '      <tr><td colspan="6" class="text-center text-muted">{{ __('locale.Not found') }}</td></tr>';
      }

      html += '    </tbody>';
      html += '  </table>';
      html += '</div>';

      if (includeTotals) {
        var subtotalValue = Number(
          res.subtotal ||
          res.sub_total ||
          res.amount_without_tip ||
          0
        );
        var tipValue = Number(
          res.tip ||
          res.propina ||
          0
        );
        var shippingValue = Number(
          res.shipping_cost ||
          res.shipping_fee ||
          res.shipping ||
          res.delivery_fee ||
          0
        );
        var totalValue = Number(res.total || 0);

        html += '<div class="table-responsive mt-1">';
        html += '  <table class="table table-borderless mb-0">';
        html += '    <tbody>';
        html += '      <tr><td class="text-right pr-2"><strong>{{ __('locale.SubTotal') }}</strong></td><td class="text-right" style="width: 170px;">' + formatMoney(subtotalValue) + '</td></tr>';
        html += '      <tr><td class="text-right pr-2"><strong>{{ __('locale.Tip') }}</strong></td><td class="text-right">' + formatMoney(tipValue) + '</td></tr>';
        html += '      <tr><td class="text-right pr-2"><strong>{{ __('locale.Shipping Cost') }}</strong></td><td class="text-right">' + formatMoney(shippingValue) + '</td></tr>';
        html += '      <tr><td class="text-right pr-2"><strong>{{ __('locale.Total') }}</strong></td><td class="text-right"><strong>' + formatMoney(totalValue) + '</strong></td></tr>';
        html += '    </tbody>';
        html += '  </table>';
        html += '</div>';
      }

      $('#purchaseDetailsModal .modal-body').html(html);
      $('#purchaseDetailsModal').modal('show');
    }

    function loadData(){
      var date_from = $('#date_from').val();
      var date_to = $('#date_to').val();
      var today = new Date().toISOString().slice(0, 10);

      if (!date_from) {
        date_from = today;
        $('#date_from').val(today);
      }

      if (!date_to) {
        date_to = today;
        $('#date_to').val(today);
      }

      var type = $('#filter_type').val();
      var q = $('#search_q').val();
      $.post("{{ url('panel/pedidos/date') }}", {_token:'{{ csrf_token() }}', date_from: date_from, date_to: date_to, type: type, q: q}, function(res){
        if(res && res.data){
          table.clear();
          table.rows.add(res.data);
          table.draw();
          feather.replace();
        }
      }, 'json');
    }

    loadData();

    // view button
    $(document).on('click', '.purchases-table .view', function(){
      var id = $(this).data('id');
      $.post("{{ route('purchases.getDetails') }}", {_token:'{{ csrf_token() }}', id: id}, function(res){
        if(res){
          $('#purchaseDetailsModal .modal-body').html('{{ __('locale.Loading') }}...');
          renderPurchaseDetailsModal(res, true);
        }
      }, 'json');
    });

    // view company button
    $(document).on('click', '.purchases-table .view-company', function(){
      var id = $(this).data('id');
      $.post("{{ route('purchases.getDetailsCompany') }}", {_token:'{{ csrf_token() }}', id: id}, function(res){
        if(res){
          $('#purchaseDetailsModal .modal-body').html('{{ __('locale.Loading') }}...');
          renderPurchaseDetailsModal(res, false);
        }
      }, 'json');
    });

    // approve button
    $(document).on('click', '.purchases-table .approve', function(){
      var id = $(this).data('id');
      if(!confirm('{{ __('locale.Approved') }}?')) return;
      $.post('/panel/pedidos/'+id+'/approve', {_token:'{{ csrf_token() }}', status: 1}, function(){
        loadData();
      });
    });

    // reject button
    $(document).on('click', '.purchases-table .reject', function(){
      var id = $(this).data('id');
      if(!confirm('{{ __('locale.Delete') }}?')) return;
      $.post('/panel/pedidos/'+id+'/reject', {_token:'{{ csrf_token() }}', status: 2}, function(){
        loadData();
      });
    });

    // Export
    $('#btn-export').on('click', function(e){
      e.preventDefault();
      var date_from = $('#date_from').val();
      var date_to = $('#date_to').val();
      var type = $('#filter_type').val();
      var q = $('#search_q').val();
      var form = $('<form method="POST" action="{{ route('purchases.export') }}" style="display:none;"></form>');
      form.append('<input name="_token" value="{{ csrf_token() }}">');
      form.append('<input name="date_from" value="'+(date_from||'')+'">');
      form.append('<input name="date_to" value="'+(date_to||'')+'">');
      form.append('<input name="type" value="'+(type||'')+'">');
      form.append('<input name="q" value="'+(q||'')+'">');
      $('body').append(form);
      form.submit();
    });

    // Consult button to reload data with filters
    $('#btn-consult').on('click', function(e){
      e.preventDefault();
      loadData();
    });

    // Trigger search on Enter
    $('#search_q').on('keypress', function(e){
      if(e.which == 13){ e.preventDefault(); loadData(); }
    });
  });
</script>
@endsection
