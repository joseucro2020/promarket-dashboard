@extends('layouts/contentLayoutMaster')

@section('title', __('Customers'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
@endsection

@section('content')
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom p-1">
            <div class="head-label">
              <h4 class="mb-0">{{ __('Customers') }}</h4>
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
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 clients-table" id="clientsTable">
              <thead>
                <tr>
                  <th>{{ __('Name') }}</th>
                  <th>{{ __('Identification') }}</th>
                  <th>{{ __('Type') }}</th>
                  <th>{{ __('Register Date') }}</th>
                  <th>{{ __('Phone') }}</th>
                  <th>{{ __('Status') }}</th>
                  <th class="text-end">{{ __('Actions') }}</th>
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
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
@endsection

@section('page-script')
<script>
  $(function(){
    // Initialize DataTable with AJAX source
    var table = $('#clientsTable').DataTable({
      responsive: true,
      processing: false,
      serverSide: false,
      ajax: {
        url: "{{ url('panel/clientes/all') }}",
        type: 'GET',
        dataSrc: ''
      },
      columns: [
        { data: 'name' },
        { data: 'identificacion' },
        { data: 'persona' },
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
        { data: 'status', render: function(d){ return d == 1 ? '{{ __('Active') }}' : '{{ __('Inactive') }}'; } },
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
            + '<input class="custom-control-input client-status-toggle" type="checkbox" id="client_status_'+id+'" data-id="'+id+'" '+checked+'> '
            + '<label class="custom-control-label" for="client_status_'+id+'"></label>'
            + '</div>'
            + '</form>';
          // view/profile
          html += '<a class="btn btn-sm btn-icon btn-flat-success mr-1" href="{{ url('panel/clientes') }}/'+id+'" title="{{ __('View') }}">'
            + '<i data-feather="eye"></i></a> ';
          // edit (link to profile/edit if exists)
          html += '<a class="btn btn-sm btn-icon btn-flat-success mr-1" href="{{ url('panel/clientes') }}/'+id+'" title="{{ __('Edit') }}">'
            + '<i data-feather="edit"></i></a> ';
          // invoice/details
          html += '<button class="btn btn-sm btn-icon btn-flat-primary mr-1 btn-invoice" data-id="'+id+'" title="{{ __('Details') }}">'
            + '<i data-feather="file-text"></i></button> ';
          // convert to pro
          html += '<button class="btn btn-sm btn-icon btn-flat-info mr-1 btn-convert" data-id="'+id+'" title="{{ __('Convert to Pro') }}">'
            + '<i data-feather="user"></i></button> ';
          // delete
          html += '<button class="btn btn-sm btn-icon btn-flat-danger btn-delete" data-id="'+id+'" title="{{ __('Delete') }}">'
            + '<i data-feather="trash"></i></button>';
          return '<div class="d-flex align-items-center">'+html+'</div>';
        }
      }],
      drawCallback: function(){ feather.replace(); }
    });

    // Export form
    $('#btn-export').on('click', function(e){
      e.preventDefault();
      var form = $('<form method="POST" action="{{ route('clients.export') }}" style="display:none;"></form>');
      form.append('<input name="_token" value="{{ csrf_token() }}">');
      $('body').append(form);
      form.submit();
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
      if(!confirm('Convertir a Pro?')) return;
      $.post('{{ url('panel/clientes') }}/'+id+'/convert-to-pro', {_token:'{{ csrf_token() }}'}, function(res){
        if(res && res.result){ table.ajax.reload(null, false); }
      }, 'json');
    });

    // delete
    $('#clientsTable').on('click', '.btn-delete', function(){
      var id = $(this).data('id');
      if(!confirm('Are you sure to delete?')) return;
      $.post('{{ url('panel/clientes') }}/'+id+'/delete', {_token:'{{ csrf_token() }}', status: 2}, function(res){
        if(res && res.result){ table.ajax.reload(null, false); }
      }, 'json');
    });

    // details button (no backend route yet) - open profile
    $('#clientsTable').on('click', '.btn-invoice', function(){
      var id = $(this).data('id');
      window.location.href = '{{ url('panel/clientes') }}/'+id;
    });
  });
</script>
@endsection
