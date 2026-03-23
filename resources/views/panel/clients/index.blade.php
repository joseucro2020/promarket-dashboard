@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Customers'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('content')
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      {{-- <h1 class="clients-title mb-2">{{ __('locale.Customers') }}</h1> --}}
      <div class="card">
        <div class="card-header border-bottom p-1">
            <div class="head-label">
            <h4 class="mb-0">{{ __('locale.Customers') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
                <a href="#" id="btn-export" class="dt-button create-new btn btn-primary">
                <i data-feather="download"></i> {{ __('locale.Export') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          {{-- <div class="clients-toolbar">
            <div style="display:flex;align-items:center;gap:.75rem;">
              <div class="clients-length-placeholder"></div>
              <a href="#" class="btn btn-primary" id="btn-export">
                {{ __('locale.Export') }}
              </a>
            </div>

          </div> --}}
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 module-list-table  clients-table" id="clientsTable">
              <thead>
                <tr>
                  <th>{{ __('locale.Name') }}</th>
                  <th>{{ __('locale.Identification') }}</th>
                  <th>{{ __('locale.Type') }}</th>
                  <th>{{ __('locale.Registration') }}</th>
                  <th>{{ __('locale.Phone') }}</th>
                  <th>{{ __('locale.Client Status') }}</th>
                  <th class="text-end">{{ __('locale.Actions') }}</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="modal fade" id="clientViewModal" tabindex="-1" role="dialog" aria-labelledby="clientViewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h4 class="modal-title text-primary font-italic" id="clientViewModalLabel">{{ __('locale.Customer Details') }}</h4>
      </div>
      <div class="modal-body pt-1">
        <hr class="my-1">
        <div class="row mt-2">
          <div class="col-md-6 mb-2">
            <div><strong>{{ __('locale.Name') }}:</strong> <span id="client_modal_name"></span></div>
            <div><strong>{{ __('locale.Phone') }}:</strong> <span id="client_modal_phone"></span></div>
            <div><strong>{{ __('locale.Client Status') }}:</strong> <span id="client_modal_status"></span></div>
            <div><strong>{{ __('locale.Address') }}:</strong> <span id="client_modal_address"></span></div>
          </div>
          <div class="col-md-6 mb-2">
            <div><strong>{{ __('locale.Identification') }}:</strong> <span id="client_modal_identification"></span></div>
            <div><strong>{{ __('locale.Email') }}:</strong> <span id="client_modal_email"></span></div>
            <div><strong>{{ __('locale.Postal Code') }}:</strong> <span id="client_modal_postal_code"></span></div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-link text-dark" data-dismiss="modal">{{ __('locale.Accept') }}</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="clientOrdersModal" tabindex="-1" role="dialog" aria-labelledby="clientOrdersModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title text-primary" id="clientOrdersModalLabel">{{ __('locale.Pedidos') }} - <span id="orders_client_name"></span></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-hover table-striped" id="ordersTable">
            <thead>
              <tr>
                <th>{{ __('locale.Order Number') }}</th>
                <th>{{ __('locale.Date') }}</th>
                <th>{{ __('locale.Total') }}</th>
                <th>{{ __('locale.Payment Method') }}</th>
                <th>{{ __('locale.Status') }}</th>
                <th>{{ __('locale.Actions') }}</th>
              </tr>
            </thead>
            <tbody id="client_orders_tbody">
              <!-- Content loaded via AJAX -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('locale.Close') }}</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
<script>
  $(function(){
    function renderFeatherIcons() {
      if (window.feather) {
        feather.replace({ width: 14, height: 14 });
      }
    }

    function renderFeatherIconsDeferred() {
      window.requestAnimationFrame(function() {
        renderFeatherIcons();
      });
    }

    // Render static icons (search/export/header) on initial load
    renderFeatherIcons();

    // Initialize DataTable with AJAX source (server-side processing)
    var table = $('#clientsTable').DataTable({
      responsive: true,
      processing: true,
      serverSide: true,
      pagingType: 'simple_numbers',
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      ajax: {
        url: "{{ url('panel/clientes/all') }}",
        type: 'GET',
        dataSrc: 'data'
      },
      columns: [
        { data: 'name' },
        { data: 'identificacion' },
        { data: 'persona', render: function(d){
          if (d === null || typeof d === 'undefined') return '';
          var value = String(d).toLowerCase();
          if (value === '1' || value === 'natural') return '{{ __('locale.Natural') }}';
          if (value === '2' || value === 'juridico' || value === 'jurídico' || value === 'legal') return '{{ __('locale.Legal') }}';
          return d;
        } },
        { data: 'created_at', render: function(d){
          if (!d) return '';
          var date = new Date(d);
          if (isNaN(date.getTime())) return '';
          var day = String(date.getDate()).padStart(2, '0');
          var month = String(date.getMonth() + 1).padStart(2, '0');
          var year = date.getFullYear();
          return day + '-' + month + '-' + year;
        } },
        { data: 'telefono' },
        { data: 'status', render: function(d){ return d == 1 ? '{{ __('locale.Active') }}' : '{{ __('locale.Inactive') }}'; } },
        { data: null, orderable:false, searchable:false }
      ],
      columnDefs: [{
        targets: -1,
        render: function(data){
          var id = data.id;
          var checked = data.status == 1 ? 'checked' : '';
          var html = '';
          // status toggle
          html += '<form class="m-0 mr-1" style="display:inline-block;">'
            + '<div class="custom-control custom-switch custom-switch-success">'
            + '<input class="custom-control-input client-status-toggle" type="checkbox" id="client_status_'+id+'" data-id="'+id+'" title="{{ __('locale.Status') }}" aria-label="{{ __('locale.Status') }}" '+checked+'> '
            + '<label class="custom-control-label" for="client_status_'+id+'" title="{{ __('locale.Status') }}"></label>'
            + '</div>'
            + '</form>';
          // view/profile
          html += '<button type="button" class="btn btn-sm btn-icon btn-flat-primary mr-1 btn-view" data-id="'+id+'" title="{{ __('locale.View') }}" aria-label="{{ __('locale.View') }}">'
            + '<i data-feather="eye" title="{{ __('locale.View') }}"></i></button> ';
          // edit (link to profile/edit if exists)
          html += '<a class="btn btn-sm btn-icon btn-flat-success mr-1" href="{{ url('panel/clientes') }}/'+id+'/editar" title="{{ __('locale.Edit') }}" aria-label="{{ __('locale.Edit') }}">'
            + '<i data-feather="edit" title="{{ __('locale.Edit') }}"></i></a> ';
          // invoice/details
          html += '<button class="btn btn-sm btn-icon btn-flat-primary mr-1 btn-invoice" data-id="'+id+'" title="{{ __('locale.Pedidos') }}" aria-label="{{ __('locale.Pedidos') }}">'
            + '<i data-feather="file-text" title="{{ __('locale.Pedidos') }}"></i></button> ';
          // delete
          html += '<button class="btn btn-sm btn-icon btn-flat-danger mr-1 btn-delete" data-id="'+id+'" title="{{ __('locale.Delete') }}" aria-label="{{ __('locale.Delete') }}">'
            + '<i data-feather="trash" title="{{ __('locale.Delete') }}"></i></button> ';
          // convert to pro
          html += '<button class="btn btn-sm btn-icon btn-flat-primary btn-convert" data-id="'+id+'" title="{{ __('locale.Convert to Pro') }}" aria-label="{{ __('locale.Convert to Pro') }}">'
            + '<i data-feather="user" title="{{ __('locale.Convert to Pro') }}"></i></button>';
          return '<div class="d-flex align-items-center">'+html+'</div>';
        }
      }],
      drawCallback: function(){ renderFeatherIcons(); }
    });

    // Uses module-list layout: DataTables places length (left) and filter (right) in a toolbar row.

    // Re-render icons when responsive plugin moves content to child rows
    $('#clientsTable').on('responsive-display.dt responsive-resize.dt column-visibility.dt', function(){
      renderFeatherIconsDeferred();
    });

    table.on('draw.dt', function(){
      renderFeatherIconsDeferred();
    });

    // No custom search handler — DataTables filter input handles searches.

    // Export: submit current filters/order; backend rebuilds dataset from DB
    $('#btn-export').on('click', function(e){
      e.preventDefault();
      var $btn = $(this);
      $btn.prop('disabled', true);
      var order = table.order();
      var $filterInput = $('.dataTables_filter input');

      var form = $('<form method="POST" action="{{ route('clients.export') }}" style="display:none;"></form>');
      form.append('<input name="_token" value="{{ csrf_token() }}">');

      if (order && order.length) {
        form.append('<input name="order_column" value="' + order[0][0] + '">');
        form.append('<input name="order_dir" value="' + order[0][1] + '">');
      }

      if ($filterInput.length) {
        form.append('<input name="search_value" value="' + $('<div/>').text($filterInput.val() || '').html() + '">');
      }

      $('body').append(form);
      form.submit();
      $btn.prop('disabled', false);
    });

    // Handlers: delegated events on table body
    $('#clientsTable').on('change', '.client-status-toggle', function(){
      var id = $(this).data('id');
      var status = $(this).is(':checked') ? 1 : 0;
      $.post('{{ url('panel/clientes') }}/'+id+'/status', {_token:'{{ csrf_token() }}', status: status}, function(res){
        if(res && res.result){ table.ajax.reload(null, false); }
      }, 'json');
    });

    // convert to pro
    $('#clientsTable').on('click', '.btn-convert', function(){
      var id = $(this).data('id');
      var tr = $(this).closest('tr');
      var row = table.row(tr);
      if (tr.hasClass('child')) {
        row = table.row(tr.prev());
      }
      var data = row.data() || {};
      var clientName = data.name || '';

      if (typeof Swal === 'undefined') {
        if(!confirm('¿ Realmente deseas Convertir el Usuario ' + clientName + ' en Vendedor PRO ?')) return;
        $.post('{{ url('panel/clientes') }}/'+id+'/convert-to-pro', {_token:'{{ csrf_token() }}'}, function(res){
          if(res && res.result){
            table.ajax.reload(null, false);
          }
        }, 'json');
        return;
      }

      Swal.fire({
        title: '',
        html: '<div class="text-center mb-2"><i data-feather="alert-circle" style="width: 3rem; height: 3rem; color: #5e5873;"></i></div>' +
              '<h4 style="font-weight: 400;">¿ Realmente deseas <b>Convertir</b> el Usuario <b>' + clientName + '</b> en Vendedor PRO ?</h4>',
        showCancelButton: true,
        confirmButtonText: 'ACEPTAR',
        cancelButtonText: 'CANCELAR',
        customClass: {
          confirmButton: 'btn btn-link text-primary font-weight-bold p-0 mr-4',
          cancelButton: 'btn btn-link text-dark font-weight-bold p-0',
          actions: 'justify-content-end pr-2 pb-1',
          popup: 'rounded-0'
        },
        buttonsStyling: false,
        onRender: function() {
          if (window.feather) {
            feather.replace({ width: 48, height: 48 });
          }
        }
      }).then(function(result) {
        if (result.isConfirmed) {
          $.post('{{ url('panel/clientes') }}/'+id+'/convert-to-pro', {_token:'{{ csrf_token() }}'}, function(res){
            if(res && res.result){ 
              table.ajax.reload(null, false); 
            }
          }, 'json');
        }
      });
    });

    // delete
    $('#clientsTable').on('click', '.btn-delete', function(){
      var id = $(this).data('id');
      if(!confirm('{{ __('locale.Are you sure to delete?') }}')) return;
      $.post('{{ url('panel/clientes') }}/'+id+'/delete', {_token:'{{ csrf_token() }}', status: 2}, function(res){
        if(res && res.result){ table.ajax.reload(null, false); }
      }, 'json');
    });

    // details button (open orders modal)
    $('#clientsTable').on('click', '.btn-invoice', function(){
      var id = $(this).data('id');
      var tr = $(this).closest('tr');
      var row = table.row(tr);
      if (tr.hasClass('child')) {
        row = table.row(tr.prev());
      }
      var data = row.data() || {};
      $('#orders_client_name').text(data.name || '');
      $('#client_orders_tbody').html('<tr><td colspan="6" class="text-center"><div class="spinner-border text-primary" role="status"></div></td></tr>');
      $('#clientOrdersModal').modal('show');

      $.get('{{ url('panel/clientes') }}/'+id+'/pedidos', function(res){
        if(res && res.result){
          var html = '';
          if(res.data && res.data.length > 0){
            res.data.forEach(function(order){
              var date = new Date(order.created_at);
              var formattedDate = date.toLocaleDateString();
              var statusText = '';
              var statusClass = '';
              
              switch(parseInt(order.status)) {
                case 0: statusText = '{{ __('locale.Pending') }}'; statusClass = 'badge-light-warning'; break;
                case 1: statusText = '{{ __('locale.Processing') }}'; statusClass = 'badge-light-info'; break;
                case 2: statusText = '{{ __('locale.Rejected') }}'; statusClass = 'badge-light-danger'; break;
                case 3: statusText = '{{ __('locale.Completed') }}'; statusClass = 'badge-light-success'; break;
                default: statusText = order.status; statusClass = 'badge-light-secondary';
              }

              html += '<tr>'
                + '<td>' + (order.id || '') + '</td>'
                + '<td>' + formattedDate + '</td>'
                + '<td>' + (order.total || '0.00') + '</td>'
                + '<td>' + (order.text_payment_type || '') + '</td>'
                + '<td><span class="badge badge-pill ' + statusClass + '">' + statusText + '</span></td>'
                + '<td>'
                + '<a href="{{ url('panel/pedidos') }}/' + order.id + '" class="btn btn-sm btn-icon btn-flat-primary" title="{{ __('locale.View') }}"><i data-feather="eye"></i></a>'
                + '</td>'
                + '</tr>';
            });
          } else {
            html = '<tr><td colspan="6" class="text-center">{{ __('locale.No data found') }}</td></tr>';
          }
          $('#client_orders_tbody').html(html);
          renderFeatherIcons();
        }
      });
    });

    // view button - open modal with client details
    $('#clientsTable').on('click', '.btn-view', function(){
      var tr = $(this).closest('tr');
      var row = table.row(tr);
      if (tr.hasClass('child')) {
        row = table.row(tr.prev());
      }

      var data = row.data() || {};
      var statusText = (String(data.status) === '1') ? '{{ __('locale.Active') }}' : '{{ __('locale.Inactive') }}';

      $('#client_modal_name').text(data.name || '');
      $('#client_modal_phone').text(data.telefono || '');
      $('#client_modal_status').text(statusText);
      $('#client_modal_address').text(data.direccion || '');
      $('#client_modal_identification').text(data.identificacion || '');
      $('#client_modal_email').text(data.email || '');
      $('#client_modal_postal_code').text(data.codigo_postal || data.postal_code || '');

      $('#clientViewModal').modal('show');
    });
  });
</script>
@endsection
