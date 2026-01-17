@extends('layouts/contentLayoutMaster')

@section('title', __('Orders'))

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
              <h4 class="mb-0">{{ __('Orders') }}</h4>
            </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <a href="#" class="dt-button btn btn-outline-primary" id="btn-export">
                <i data-feather="download"></i> {{ __('Export') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="mb-2">
            <div class="row">
              <div class="col-md-3 mb-1">
                <input type="date" id="date_from" class="form-control" placeholder="From" value="{{ request('date_from', '') }}">
              </div>
              <div class="col-md-3 mb-1">
                <input type="date" id="date_to" class="form-control" placeholder="To" value="{{ request('date_to', '') }}">
              </div>
              <div class="col-md-2 mb-1">
                <select id="filter_type" class="form-control">
                  <option value="">{{ __('All') }}</option>
                  <option value="pending">{{ __('Pending') }}</option>
                  <option value="processing">{{ __('Processing') }}</option>
                  <option value="completed">{{ __('Completed') }}</option>
                  <option value="rejected">{{ __('Rejected') }}</option>
                </select>
              </div>
              <div class="col-md-2 mb-1">
                <input type="text" id="search_q" class="form-control" placeholder="{{ __('Search') }}" value="{{ request('q','') }}">
              </div>
              <div class="col-md-2 mb-1">
                <button id="btn-consult" class="btn btn-primary">{{ __('Consult') }}</button>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 purchases-table">
              <thead>
                  <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Date - Time') }}</th>
                    <th>{{ __('Client') }}</th>
                    <th>{{ __('Tip') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Payment Type') }}</th>
                    <th>{{ __('Payment Method') }}</th>
                    <th>{{ __('Delivery Type') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
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
          var viewBtn = '<button class="btn btn-sm btn-outline-primary view" data-id="'+data.id+'" title="Ver"><i data-feather="eye"></i></button>';
          var printBtn = '<a class="btn btn-sm btn-outline-secondary" href="'+window.location.origin+'/panel/pedidos/'+data.id+'/print" target="_blank" title="'+"{{ __('Print') }}"+'"><i data-feather="printer"></i></a>';
          return '<div class="btn-group" role="group">' + viewBtn + printBtn + '</div>';
        }
      }]
    });

    function loadData(){
      var date_from = $('#date_from').val();
      var date_to = $('#date_to').val();
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
          $('#purchaseDetailsModal .modal-body').html('{{ __('Loading') }}...');
          var html = '<p><strong>ID:</strong> '+res.id+'</p>';
          html += '<p><strong>Cliente:</strong> '+(res.user?res.user.name:'-')+'</p>';
          html += '<p><strong>Monto:</strong> '+res.total+'</p>';
          // Detalles de items
          if(res.details && res.details.length){
            html += '<hr><h6>Items</h6><ul>';
            res.details.forEach(function(d){ html += '<li>'+ (d.description||'') +' x '+ d.quantity +'</li>'; });
            html += '</ul>';
          }
          $('#purchaseDetailsModal .modal-body').html(html);
          $('#purchaseDetailsModal').modal('show');
        }
      }, 'json');
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
