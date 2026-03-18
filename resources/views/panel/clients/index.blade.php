@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Customers'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
@endsection

@section('page-style')
<style>
  .clients-title {
    text-align: center;
    font-style: italic;
    font-weight: 700;
  }
  .clients-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }
  .clients-search {
    display: flex;
    align-items: center;
    min-width: 320px;
    gap: .75rem;
  }
  .clients-search .form-control {
    border-top: 0;
    border-left: 0;
    border-right: 0;
    border-radius: 0;
    padding-left: .5rem;
  }
  .clients-search .form-control:focus {
    box-shadow: none;
  }
  .clients-table thead th {
    border-top: 0;
  }
  .clients-table tbody td {
    vertical-align: middle;
  }
</style>
@endsection

@section('content')
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      {{-- <h1 class="clients-title mb-2">{{ __('locale.Customers') }}</h1> --}}
      <div class="card">
        <div class="card-body">
          <div class="clients-toolbar">
            <a href="#" class="btn btn-primary" id="btn-export">
              {{ __('locale.Export') }}
            </a>
            <div class="clients-search">
              <i data-feather="search" title="{{ __('locale.Search') }}" aria-label="{{ __('locale.Search') }}"></i>
              <input type="text" id="clients-custom-search" class="form-control" placeholder="{{ __('locale.Search') }}">
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover w-100 clients-table" id="clientsTable">
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
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
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

    // Initialize DataTable with AJAX source
    var table = $('#clientsTable').DataTable({
      responsive: true,
      processing: false,
      serverSide: false,
      dom: 't<"d-flex justify-content-between align-items-center mt-1"<"small"i><"small"p>>',
      ajax: {
        url: "{{ url('panel/clientes/all') }}",
        type: 'GET',
        dataSrc: ''
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
          html += '<button class="btn btn-sm btn-icon btn-flat-primary mr-1 btn-invoice" data-id="'+id+'" title="{{ __('locale.Details') }}" aria-label="{{ __('locale.Details') }}">'
            + '<i data-feather="file-text" title="{{ __('locale.Details') }}"></i></button> ';
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

    // Re-render icons when responsive plugin moves content to child rows
    $('#clientsTable').on('responsive-display.dt responsive-resize.dt column-visibility.dt', function(){
      renderFeatherIconsDeferred();
    });

    table.on('draw.dt', function(){
      renderFeatherIconsDeferred();
    });

    $('#clients-custom-search').on('keyup', function(){
      table.search(this.value).draw();
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
      if(!confirm('{{ __('locale.Convert to Pro?') }}')) return;
      $.post('{{ url('panel/clientes') }}/'+id+'/convert-to-pro', {_token:'{{ csrf_token() }}'}, function(res){
        if(res && res.result){ table.ajax.reload(null, false); }
      }, 'json');
    });

    // delete
    $('#clientsTable').on('click', '.btn-delete', function(){
      var id = $(this).data('id');
      if(!confirm('{{ __('locale.Are you sure to delete?') }}')) return;
      $.post('{{ url('panel/clientes') }}/'+id+'/delete', {_token:'{{ csrf_token() }}', status: 2}, function(res){
        if(res && res.result){ table.ajax.reload(null, false); }
      }, 'json');
    });

    // details button (no backend route yet) - open profile
    $('#clientsTable').on('click', '.btn-invoice', function(){
      var id = $(this).data('id');
      window.location.href = '{{ url('panel/clientes') }}/'+id;
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
