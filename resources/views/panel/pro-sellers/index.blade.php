@extends('layouts/contentLayoutMaster')

@section('title', __('Pro Sellers'))
@section('title', __('locale.Pro Sellers'))

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
              <h4 class="mb-0">{{ __('Pro Sellers') }}</h4>
                <h4 class="mb-0">{{ __('locale.Pro Sellers') }}</h4>
            </div>
          {{-- <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <button type="button" class="dt-button create-new btn btn-primary" data-toggle="modal" data-target="#newProClientModal">
                <i data-feather="plus"></i> {{ __('Register new PRO client') }}
              </button>
            </div>
          </div> --}}
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100" id="proSellersTable">
              <thead>
                <tr>
                  <th>{{ __('Name') }}</th>
                    <th>{{ __('locale.Name') }}</th>
                  <th>{{ __('Identification') }}</th>
                    <th>{{ __('locale.Identification') }}</th>
                  <th>{{ __('Type') }}</th>
                    <th>{{ __('locale.Type') }}</th>
                  <th>{{ __('Register Date') }}</th>
                    <th>{{ __('locale.Register Date') }}</th>
                  <th>{{ __('Phone') }}</th>
                    <th>{{ __('locale.Phone') }}</th>
                  <th>{{ __('Status') }}</th>
                    <th>{{ __('locale.Status') }}</th>
                  <th class="text-end">{{ __('Actions') }}</th>
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

{{-- Modal: Register new PRO client (convert existing customer) --}}
<div class="modal fade" id="newProClientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('Register new PRO client') }}</h5>
          <h5 class="modal-title">{{ __('locale.Register new PRO client') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
            {{ __('locale.This will convert an existing customer into a PRO seller.') }}
        </div>

        <div class="form-group">
          <label for="proClientId">{{ __('Customer') }}</label>
            <label for="proClientId">{{ __('locale.Customer') }}</label>
          <select id="proClientId" class="form-control">
            <option value="">-- {{ __('Select') }} --</option>
              <option value="">-- {{ __('locale.Select') }} --</option>
          </select>
        </div>

        <div class="alert alert-danger d-none" id="newProClientError"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('locale.Cancel') }}</button>
        <button type="button" class="btn btn-primary" id="btnRegisterProClient">{{ __('Save') }}</button>
        <button type="button" class="btn btn-primary" id="btnRegisterProClient">{{ __('locale.Save') }}</button>
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
    var table = $('#proSellersTable').DataTable({
      responsive: true,
      ajax: {
        url: "{{ url('panel/pro-sellers/all') }}",
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
        { data: 'status', render: function(d){ return d == 1 ? '{{ __('locale.Active') }}' : '{{ __('locale.Inactive') }}'; } },
        { data: null, orderable:false, searchable:false }
      ],
      columnDefs: [{
        targets: -1,
        render: function(data){
          var id = data.id;
          var checked = data.status == 1 ? 'checked' : '';
          var html = '';
          html += '<form class="m-0 mr-1" style="display:inline-block;">'
            + '<div class="custom-control custom-switch custom-switch-success">'
            + '<input class="custom-control-input pro-status-toggle" type="checkbox" id="pro_status_'+id+'" data-id="'+id+'" '+checked+'> '
            + '<label class="custom-control-label" for="pro_status_'+id+'"></label>'
            + '</div>'
            + '</form>';
          html += '<a class="btn btn-sm btn-icon btn-flat-success mr-1" href="{{ url('panel/pro-sellers') }}/'+id+'" title="{{ __('View') }}">'
           html += '<a class="btn btn-sm btn-icon btn-flat-success mr-1" href="{{ url('panel/pro-sellers') }}/'+id+'" title="{{ __('locale.View') }}">'
            + '<i data-feather="eye"></i></a> ';
          html += '<a class="btn btn-sm btn-icon btn-flat-success mr-1" href="{{ url('panel/pro-sellers') }}/'+id+'" title="{{ __('Edit') }}">'
           html += '<a class="btn btn-sm btn-icon btn-flat-success mr-1" href="{{ url('panel/pro-sellers') }}/'+id+'" title="{{ __('locale.Edit') }}">'
            + '<i data-feather="edit"></i></a> ';
          html += '<button class="btn btn-sm btn-icon btn-flat-danger mr-1 btn-delete" data-id="'+id+'" title="{{ __('Delete') }}">'
           html += '<button class="btn btn-sm btn-icon btn-flat-danger mr-1 btn-delete" data-id="'+id+'" title="{{ __('locale.Delete') }}">'
            + '<i data-feather="trash"></i></button> ';
          html += '<button class="btn btn-sm btn-icon btn-flat-primary mr-1 btn-balance" data-id="'+id+'" title="{{ __('Balance') }}">'
           html += '<button class="btn btn-sm btn-icon btn-flat-primary mr-1 btn-balance" data-id="'+id+'" title="{{ __('locale.Balance') }}">'
            + '<i data-feather="dollar-sign"></i></button> ';
          html += '<button class="btn btn-sm btn-icon btn-flat-info mr-1 btn-promote" data-id="'+id+'" title="{{ __('Promote') }}">'
           html += '<button class="btn btn-sm btn-icon btn-flat-info mr-1 btn-promote" data-id="'+id+'" title="{{ __('locale.Promote') }}">'
            + '<i data-feather="volume-2"></i></button> ';
          html += '<a class="btn btn-sm btn-icon btn-flat-secondary" href="{{ url('panel/pro-sellers') }}/'+id+'" title="{{ __('Profile') }}">'
           html += '<a class="btn btn-sm btn-icon btn-flat-secondary" href="{{ url('panel/pro-sellers') }}/'+id+'" title="{{ __('locale.Profile') }}">'
            + '<i data-feather="user"></i></a>';
          return '<div class="d-flex align-items-center">'+html+'</div>';
        }
      }],
      drawCallback: function(){ feather.replace(); }
    });

    function resetNewProClientModal() {
      $('#proClientId').html('<option value="">-- {{ __('Select') }} --</option>');
        $('#proClientId').html('<option value="">-- {{ __('locale.Select') }} --</option>');
      $('#newProClientError').addClass('d-none').text('');
    }

    $('#newProClientModal').on('show.bs.modal', function(){
      resetNewProClientModal();

      $.get("{{ url('panel/clientes/all') }}", function(res){
        var items = (res && res.data) ? res.data : res;
        if (!Array.isArray(items)) return;

        items.forEach(function (client) {
          var label = (client.name || '') + (client.identificacion ? (' - ' + client.identificacion) : '');
          $('#proClientId').append(
            $('<option/>', { value: client.id, text: label })
          );
        });
      });
    });

    $('#btnRegisterProClient').on('click', function(){
      var id = $('#proClientId').val();
      if (!id) {
        $('#newProClientError').removeClass('d-none').text('{{ __('Select') }}');
        $('#newProClientError').removeClass('d-none').text('{{ __('locale.Select') }}');
        return;
      }

      $('#newProClientError').addClass('d-none').text('');

      $.post("{{ url('panel/clientes') }}/" + id + "/convert-to-pro", {_token:'{{ csrf_token() }}'}, function(res){
        if(res && res.result){
          $('#newProClientModal').modal('hide');
          table.ajax.reload(null, false);
          return;
        }
          $('#newProClientError').removeClass('d-none').text('{{ __('locale.An error occurred') }}');
      }, 'json').fail(function(){
          $('#newProClientError').removeClass('d-none').text('{{ __('locale.An error occurred') }}');
      });
    });

    // Toggle status
    $('#proSellersTable').on('change', '.pro-status-toggle', function(){
      var id = $(this).data('id');
      var status = $(this).is(':checked') ? 1 : 0;
      $.post('{{ url('panel/pro-sellers') }}/'+id+'/status', {_token:'{{ csrf_token() }}', status: status}, function(res){
        if(res && res.result){ table.ajax.reload(null, false); }
      }, 'json');
    });

    // Delete pro seller (set as not pro)
    $('#proSellersTable').on('click', '.btn-delete', function(){
      var id = $(this).data('id');
      if(!confirm('Are you sure?')) return;
        if(!confirm('{{ __('locale.Are you sure?') }}')) return;
      $.post('{{ url('panel/pro-sellers') }}/'+id+'/delete', {_token:'{{ csrf_token() }}'}, function(res){
        if(res && res.result){ table.ajax.reload(null, false); }
      }, 'json');
    });

    // Open balance report
    $('#proSellersTable').on('click', '.btn-balance', function(){
      var id = $(this).data('id');
      window.location.href = '{{ url('panel/pro-sellers') }}/'+id+'/balance';
    });

    // Promote / advertise action - placeholder
    $('#proSellersTable').on('click', '.btn-promote', function(){
      alert('Promote action not implemented');
    });
  });
</script>
@endsection
