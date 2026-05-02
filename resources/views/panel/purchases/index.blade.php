@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Orders'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('content')
<section id="basic-datatable">
  <div class="row mt-2">
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
            <div class="row mt-2">
              <div class="col-md-3 mb-1">
                <input type="date" id="date_from" class="form-control" placeholder="{{ __('locale.From') }}" value="{{ request('date_from', now()->toDateString()) }}">
              </div>
              <div class="col-md-3 mb-1">
                <input type="date" id="date_to" class="form-control" placeholder="{{ __('locale.To') }}" value="{{ request('date_to', now()->toDateString()) }}">
              </div>
              <div class="col-md-4 mb-1">
                <select id="filter_type" class="form-control">
                  <option value="">{{ __('locale.All') }}</option>
                  <option value="pending">{{ __('locale.Pending') }}</option>
                  <option value="processing">{{ __('locale.Processing') }}</option>
                  <option value="completed">{{ __('locale.Completed') }}</option>
                  <option value="rejected">{{ __('locale.Rejected') }}</option>
                </select>
              </div>
              {{-- <div class="col-md-2 mb-1">
                <input type="text" id="search_q" class="form-control" placeholder="{{ __('locale.Search') }}" value="{{ request('q','') }}">
              </div> --}}
              <div class="col-md-2 mb-1">
                <button id="btn-consult" class="btn btn-primary">{{ __('locale.Consult') }}</button>
              </div>
            </div>
          </div>
          <div class="position-relative purchases-table-container">
            <div id="purchases-table-loading" class="d-none align-items-center justify-content-center" style="position:absolute;inset:0;background:rgba(255,255,255,.65);z-index:10;">
              <div class="text-center">
                <div class="spinner-border text-primary mb-50" role="status" aria-hidden="true"></div>
                <div class="font-small-3 text-primary">{{ __('locale.Loading') }}...</div>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  $(function(){
    var consultLabel = '{{ __('locale.Consult') }}';
    var loadingLabel = '{{ __('locale.Loading') }}...';
    var confirmTitle = '{{ __('locale.Are you sure?') }}';
    var confirmApproveLabel = '{{ __('locale.Do you really want to approve order') }}';
    var confirmCompleteLabel = '{{ __('locale.Do you really want to complete order') }}';
    var confirmRejectLabel = '{{ __('locale.Do you really want to reject order') }}';
    var rejectReasonLabel = '{{ __('locale.Enter cancellation reason for order') }}';
    var rejectReasonPlaceholder = '{{ __('locale.Write cancellation reason') }}';
    var rejectReasonRequiredLabel = '{{ __('locale.Cancellation reason is required') }}';
    var confirmAcceptLabel = '{{ __('locale.Accept') }}';
    var confirmCancelLabel = '{{ __('locale.Cancel') }}';
    var approvedSuccessLabel = '{{ __('locale.Order approved successfully') }}';
    var completedSuccessLabel = '{{ __('locale.Order completed successfully') }}';
    var rejectedSuccessLabel = '{{ __('locale.Order rejected successfully') }}';

    function refreshDynamicIcons() {
      if (window.feather) {
        feather.replace({ width: 14, height: 14 });
      }

      if ($.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip({
          container: 'body'
        });
      }
    }

    var table = $('.purchases-table').DataTable({
      responsive: true,
      language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
      data: [],
      columns: [
        { data: 'id' },
        { data: 'createdAt' },
        { data: 'clientName' },
        {
          data: 'tip',
          render: function (data) {
            return Number(data || 0).toLocaleString('es-ES', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            });
          }
        },
        {
          data: 'amount',
          render: function (data) {
            return Number(data || 0).toLocaleString('es-ES', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            });
          }
        },
        { data: 'paymentType' },
        { data: 'payName' },
        { data: 'deliveryType' },
        {
          data: 'statusType',
          render: function (data, type, row) {
            var statusValue = Number(row && row.status);
            var statusText = String(data || '').trim();
            var normalized = statusText.toLowerCase();
            var badgeClass = 'badge-light-secondary';

            if (statusValue === 0 || normalized === 'en espera' || normalized === 'pending') {
              badgeClass = 'badge-light-warning';
            } else if (statusValue === 1 || normalized === 'procesando' || normalized === 'processing') {
              badgeClass = 'badge-light-info';
            } else if (statusValue === 2 || normalized === 'cancelado' || normalized === 'rejected') {
              badgeClass = 'badge-light-danger';
            } else if (statusValue === 3 || normalized === 'completado' || normalized === 'completed') {
              badgeClass = 'badge-light-success';
            }

            return '<span class="badge ' + badgeClass + '">' + escapeHtml(statusText || '—') + '</span>';
          }
        },
        { data: null, orderable:false, searchable:false }
      ],
      columnDefs: [{
        targets: -1,
        render: function(data){
          var viewBtn = '<button type="button" class="btn btn-icon btn-flat-primary mr-1 view" data-id="'+data.id+'" data-toggle="tooltip" data-placement="top" title="'+"{{ __('locale.View Order') }}"+'"><i data-feather="eye"></i></button>';
          var viewCompanyBtn = '<button type="button" class="btn btn-icon btn-flat-primary mr-1 view-company" data-id="'+data.id+'" data-toggle="tooltip" data-placement="top" title="'+"{{ __('locale.View Company Details') }}"+'"><i data-feather="file-text"></i></button>';
          var statusValue = Number(data.status);
          var isPending = statusValue === 0 || String(data.statusType || '').toLowerCase() === 'en espera' || String(data.statusType || '').toLowerCase() === 'pending';
          var isProcessing = statusValue === 1 || String(data.statusType || '').toLowerCase() === 'procesando' || String(data.statusType || '').toLowerCase() === 'processing';
          var approveBtn = isPending
            ? '<button type="button" class="btn btn-icon btn-flat-success mr-1 approve-action" data-id="'+data.id+'" data-next-status="1" data-action-label="{{ __('locale.Approve') }}" data-toggle="tooltip" data-placement="top" title="'+"{{ __('locale.Approve') }}"+'"><i data-feather="check-square"></i></button>'
            : '';
          var completeBtn = isProcessing
            ? '<button type="button" class="btn btn-icon btn-flat-success mr-1 approve-action" data-id="'+data.id+'" data-next-status="3" data-action-label="{{ __('locale.Complete') }}" data-toggle="tooltip" data-placement="top" title="'+"{{ __('locale.Complete') }}"+'"><i data-feather="check-circle"></i></button>'
            : '';
          var printBtn = '<a class="btn btn-icon btn-flat-dark mr-1" href="'+window.location.origin+'/panel/pedidos/'+data.id+'/print" target="_blank" data-toggle="tooltip" data-placement="top" title="'+"{{ __('locale.Print') }}"+'"><i data-feather="printer"></i></a>';
          var printCompanyBtn = '<a class="btn btn-icon btn-flat-dark mr-1" href="'+window.location.origin+'/panel/pedidos/'+data.id+'/print?company=1" target="_blank" data-toggle="tooltip" data-placement="top" title="'+"{{ __('locale.Print Order') }}"+'"><i data-feather="printer"></i></a>';
          var rejectBtn = (isPending || isProcessing)
            ? '<button type="button" class="btn btn-icon btn-flat-danger reject" data-id="'+data.id+'" data-toggle="tooltip" data-placement="top" title="'+"{{ __('locale.Cancel') }}"+'"><i data-feather="x-circle"></i></button>'
            : '';
          return '<div class="d-flex align-items-center">' + viewBtn + viewCompanyBtn + approveBtn + completeBtn + printBtn + printCompanyBtn + rejectBtn + '</div>';
        }
      }],
      drawCallback: function () {
        refreshDynamicIcons();
      }
    });

    table.on('responsive-display.dt responsive-resize.dt', function () {
      refreshDynamicIcons();
    });

    function setConsultLoading(isLoading) {
      var $button = $('#btn-consult');
      var $tableOverlay = $('#purchases-table-loading');

      if (isLoading) {
        $button.prop('disabled', true);
        $button.html('<span class="spinner-border spinner-border-sm mr-50" role="status" aria-hidden="true"></span>' + loadingLabel);
        $tableOverlay.removeClass('d-none').addClass('d-flex');
        return;
      }

      $button.prop('disabled', false);
      $button.text(consultLabel);
      $tableOverlay.removeClass('d-flex').addClass('d-none');
    }

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

    function renderPurchaseDetailsModal(res, totalsMode) {
      var clientName = escapeHtml(res.user && res.user.name ? res.user.name : '—');
      var paymentMethod = escapeHtml(res.pay_name || res.text_payment_type || '—');
      var paymentAmount = formatMoney(res.total || 0);
      var turn = resolveTurn(res.delivery ? res.delivery.turn : null);
      var dateTime = formatDateTime(res.created_at);
      var deliveryType = escapeHtml(resolveDeliveryType(res.delivery ? res.delivery.type : null));
      // Formatear el teléfono: agregar espacios y un icono
      var rawPhone = (res.user && res.user.phone) || (res.delivery && res.delivery.phone) || '';
      var phoneFormatted = '—';
      if (rawPhone && /^\d{11,15}$/.test(rawPhone)) {
        // Formato internacional: +58 424 444 70584
        var match = rawPhone.match(/^(\d{2,3})(\d{3})(\d{3,5})(\d{3,5})$/);
        if (match) {
          phoneFormatted = '+' + match[1] + ' ' + match[2] + ' ' + match[3] + (match[4] ? ' ' + match[4] : '');
        } else {
          phoneFormatted = '+' + rawPhone;
        }
      } else if (rawPhone) {
        phoneFormatted = rawPhone;
      }
      // Icono de teléfono (feather)
      var phoneHtml = '<i data-feather="phone" class="mr-50"></i>' + escapeHtml(phoneFormatted);
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
      html += '      <p class="mb-0"><strong>{{ __('locale.Phone') }}:</strong> ' + phoneHtml + '</p>';
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

      if (totalsMode) {
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
        if (totalsMode === 'full') {
          html += '      <tr><td class="text-right pr-2"><strong>{{ __('locale.Tip') }}</strong></td><td class="text-right">' + formatMoney(tipValue) + '</td></tr>';
          html += '      <tr><td class="text-right pr-2"><strong>{{ __('locale.Shipping Cost') }}</strong></td><td class="text-right">' + formatMoney(shippingValue) + '</td></tr>';
        }
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
      var q = $('#search_q').length ? $('#search_q').val() : '';

      setConsultLoading(true);
      $.post("{{ url('panel/pedidos/date') }}", {_token:'{{ csrf_token() }}', date_from: date_from, date_to: date_to, type: type, q: q}, function(res){
        if(res && res.data){
          table.clear();
          table.rows.add(res.data);
          table.draw();
          refreshDynamicIcons();
        }
      }, 'json')
      .fail(function(){
        if (window.toastr) {
          toastr.error("{{ __('locale.Network error. Please try again.') }}");
        }
      })
      .always(function(){
        setConsultLoading(false);
      });
    }

    loadData();

    // view button
    $(document).on('click', '.purchases-table .view', function(){
      var id = $(this).data('id');
      $.post("{{ route('purchases.getDetails') }}", {_token:'{{ csrf_token() }}', id: id}, function(res){
        // Si la respuesta viene como {data: ...} (por fallback), usar res.data; si no, usar res directo
        var data = (res && typeof res === 'object' && 'data' in res) ? res.data : res;
        if(data){
          $('#purchaseDetailsModal .modal-body').html('{{ __('locale.Loading') }}...');
          renderPurchaseDetailsModal(data, 'full');
        }
      }, 'json');
    });

    // view company button
    $(document).on('click', '.purchases-table .view-company', function(){
      var id = $(this).data('id');
      $.post("{{ route('purchases.getDetailsCompany') }}", {_token:'{{ csrf_token() }}', id: id}, function(res){
        var data = (res && typeof res === 'object' && 'data' in res) ? res.data : res;
        if(data){
          $('#purchaseDetailsModal .modal-body').html('{{ __('locale.Loading') }}...');
          renderPurchaseDetailsModal(data, 'company');
        }
      }, 'json');
    });

    // approve/complete button
    $(document).on('click', '.purchases-table .approve-action', function(){
      var $actionButton = $(this);
      var id = $actionButton.data('id');
      var nextStatus = Number($actionButton.data('next-status'));
      var actionLabel = String($actionButton.data('action-label') || '');
      var isApprove = nextStatus === 1;
      var confirmText = (isApprove ? confirmApproveLabel : confirmCompleteLabel) + ' ' + id + '?';

      var completeRequest = function () {
        $.post('/panel/pedidos/' + id + '/approve', {_token:'{{ csrf_token() }}', status: nextStatus}, function(){
          var $rowElement = $actionButton.closest('tr');
          if ($rowElement.hasClass('child')) {
            $rowElement = $rowElement.prev();
          }

          var row = table.row($rowElement);
          if (row && row.length) {
            var rowData = row.data() || {};
            rowData.status = nextStatus;
            rowData.statusType = nextStatus === 1 ? 'Procesando' : 'Completado';
            row.data(rowData).invalidate();
            table.draw(false);
            refreshDynamicIcons();
          } else {
            loadData();
          }

          if (window.toastr) {
            toastr.success(nextStatus === 1 ? approvedSuccessLabel : completedSuccessLabel);
          } else if (window.Swal) {
            Swal.fire({
              icon: 'success',
              text: nextStatus === 1 ? approvedSuccessLabel : completedSuccessLabel,
              timer: 1400,
              showConfirmButton: false
            });
          }
        });
      };

      if (window.Swal) {
        Swal.fire({
          title: confirmTitle,
          text: confirmText,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: confirmAcceptLabel,
          cancelButtonText: confirmCancelLabel,
          reverseButtons: true,
          customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-outline-secondary mr-1'
          },
          buttonsStyling: false
        }).then(function(result){
          if (result.isConfirmed) {
            completeRequest();
          }
        });
        return;
      }

      if (confirm((actionLabel || '{{ __('locale.Complete') }}') + ': ' + id + '?')) {
        completeRequest();
      }
    });

    // reject button
    $(document).on('click', '.purchases-table .reject', function(){
      var $rejectButton = $(this);
      var id = $rejectButton.data('id');
      var rejectRequest = function (rejectReason) {
        $.post('/panel/pedidos/' + id + '/reject', {_token:'{{ csrf_token() }}', status: 2, rejectReason: rejectReason}, function(){
          var $rowElement = $rejectButton.closest('tr');
          if ($rowElement.hasClass('child')) {
            $rowElement = $rowElement.prev();
          }

          var row = table.row($rowElement);
          if (row && row.length) {
            var rowData = row.data() || {};
            rowData.status = 2;
            rowData.statusType = 'Cancelado';
            row.data(rowData).invalidate();
            table.draw(false);
            refreshDynamicIcons();
          } else {
            loadData();
          }

          if (window.toastr) {
            toastr.success(rejectedSuccessLabel);
          } else if (window.Swal) {
            Swal.fire({
              icon: 'success',
              text: rejectedSuccessLabel,
              timer: 1400,
              showConfirmButton: false
            });
          }
        });
      };

      if (window.Swal) {
        Swal.fire({
          title: confirmRejectLabel + ' ' + id + ' ?',
          icon: 'warning',
          input: 'textarea',
          inputLabel: rejectReasonLabel,
          inputPlaceholder: rejectReasonPlaceholder,
          inputAttributes: {
            rows: 5
          },
          inputValidator: function (value) {
            if (!value || !String(value).trim()) {
              return rejectReasonRequiredLabel;
            }
          },
          showCancelButton: true,
          confirmButtonText: confirmAcceptLabel,
          cancelButtonText: confirmCancelLabel,
          reverseButtons: true,
          customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-outline-secondary mr-1'
          },
          buttonsStyling: false
        }).then(function(result){
          if (result.isConfirmed) {
            rejectRequest(String(result.value || '').trim());
          }
        });
        return;
      }

      if (confirm(confirmRejectLabel + ' ' + id + '?')) {
        var reason = prompt(rejectReasonLabel, '');
        if (reason && String(reason).trim()) {
          rejectRequest(String(reason).trim());
        }
      }
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
