@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Bank Accounts'))

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
            <h4 class="mb-0">{{ __('locale.Bank Accounts') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <button type="button" id="btn-add-account" class="dt-button create-new btn btn-primary" data-toggle="modal" data-target="#bankAccountModal">
                <i data-feather="plus"></i> {{ __('locale.Add New') }}
              </button>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
              <table id="bank-accounts-table" class="table table-striped table-bordered table-hover w-100 module-list-table bank-accounts-table">
                <thead>
                  <tr>
                    <th>{{ __('locale.ID') }}</th>
                    <th>{{ __('locale.Account') }}</th>
                    <th>{{ __('locale.Bank') }}</th>
                    <th>{{ __('locale.Account number') }}</th>
                    <th>{{ __('locale.Identification') }}</th>
                    <th class="text-end">{{ __('locale.Actions') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($accounts as $account)
                    <tr
                      data-id="{{ $account->id }}"
                      data-name="{{ $account->name }}"
                      data-bank_id="{{ $account->bank_id }}"
                      data-number="{{ $account->number }}"
                      data-identification="{{ $account->identification }}"
                      data-type="{{ $account->type }}"
                      data-email="{{ $account->email }}"
                      data-phone="{{ $account->phone }}"
                    >
                      <td>{{ $account->id }}</td>
                      <td>{{ $account->name }}</td>
                      <td>{{ optional($account->bank)->name }}</td>
                      <td>{{ $account->number }}</td>
                      <td>{{ $account->identification }}</td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="custom-control custom-switch custom-switch-success mr-1">
                            <input type="checkbox" class="custom-control-input btn-toggle-status" id="bank_status_{{ $account->id }}" data-id="{{ $account->id }}" {{ (int)$account->status === 1 ? 'checked' : '' }} />
                            <label class="custom-control-label" for="bank_status_{{ $account->id }}"></label>
                          </div>
                          <button type="button" class="btn btn-icon btn-flat-success mr-1 btn-edit-account" data-toggle="tooltip" data-placement="top" title="{{ __('locale.Edit') }}">
                            <i data-feather="edit"></i>
                          </button>
                          <button type="button" class="btn btn-icon btn-flat-danger btn-delete-account" data-toggle="tooltip" data-placement="top" title="{{ __('locale.Delete') }}">
                            <i data-feather="trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Modal --}}
<div class="modal fade" id="bankAccountModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bankAccountModalTitle">{{ __('locale.New Bank Account') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="bankAccountForm">
        <div class="modal-body">
          @csrf
          <input type="hidden" id="account_id" value="" />

          <div class="form-group">
            <label for="name">{{ __('locale.Account') }}</label>
            <input type="text" class="form-control" id="name" name="name" required />
          </div>

          <div class="form-group">
            <label for="bank_id">{{ __('locale.Bank') }}</label>
            <select class="form-control" id="bank_id" name="bank_id" required>
              <option value="">--</option>
              @foreach($banks as $bank)
                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label for="number">{{ __('locale.Account number') }}</label>
            <input type="text" class="form-control" id="number" name="number" />
          </div>

          <div class="form-group">
            <label for="identification">{{ __('locale.Identification') }}</label>
            <input type="text" class="form-control" id="identification" name="identification" required />
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="email">{{ __('locale.Email') }}</label>
              <input type="email" class="form-control" id="email" name="email" />
            </div>
            <div class="form-group col-md-6">
              <label for="phone">{{ __('locale.Phone') }}</label>
              <input type="text" class="form-control" id="phone" name="phone" />
            </div>
          </div>

          <div class="form-group">
            <label for="type">{{ __('locale.Type') }}</label>
            <input type="text" class="form-control" id="type" name="type" />
          </div>

          <div class="alert alert-danger d-none" id="bankAccountFormError"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('locale.Cancel') }}</button>
          <button type="submit" class="btn btn-primary">{{ __('locale.Save') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection

@section('page-script')
  <script src="{{ asset(mix('js/scripts/pages/app-module-list.js')) }}"></script>
<script>
  $(function () {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    function resetModal() {
      $('#bankAccountModalTitle').text('{{ __('locale.New Bank Account') }}');
      $('#account_id').val('');
      $('#bankAccountForm')[0].reset();
      $('#bankAccountFormError').addClass('d-none').text('');
    }

    $('#bankAccountModal').on('hidden.bs.modal', resetModal);

    $(document).on('click', '#btn-add-account', function () {
      resetModal();
    });

    $(document).on('click', '.btn-edit-account', function (e) {
      e.preventDefault();
      var $tr = $(this).closest('tr');

      $('#bankAccountModalTitle').text('{{ __('locale.Edit Bank Account') }}');
      $('#account_id').val($tr.data('id'));
      $('#name').val($tr.data('name'));
      $('#bank_id').val($tr.data('bank_id'));
      $('#number').val($tr.data('number'));
      $('#identification').val($tr.data('identification'));
      $('#type').val($tr.data('type'));
      $('#email').val($tr.data('email'));
      $('#phone').val($tr.data('phone'));

      $('#bankAccountModal').modal('show');
    });

    $('#bankAccountForm').on('submit', function (e) {
      e.preventDefault();

      var id = $('#account_id').val();
      var isEdit = !!id;
      var url = isEdit ? '{{ url('panel/cuentas-bancarias') }}/' + id : '{{ route('bank-accounts.store') }}';
      var method = isEdit ? 'PUT' : 'POST';

      $('#bankAccountFormError').addClass('d-none').text('');

      $.ajax({
        url: url,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: $(this).serialize() + (isEdit ? '&_method=' + method : ''),
        success: function () {
          window.location.reload();
        },
        error: function (xhr) {
            var message = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error))
            ? (xhr.responseJSON.message || xhr.responseJSON.error)
            : '{{ __('locale.An error occurred') }}';

          $('#bankAccountFormError').removeClass('d-none').text(message);
        }
      });
    });

    $(document).on('change', '.btn-toggle-status', function () {
      var id = $(this).data('id');

      $.ajax({
        url: '{{ url('panel/cuentas-bancarias') }}/' + id + '/status',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function () {
          window.location.reload();
        },
        error: function () {
          window.location.reload();
        }
      });
    });

    $(document).on('click', '.btn-delete-account', function (e) {
      e.preventDefault();
      var $tr = $(this).closest('tr');
      var id = $tr.data('id');

      if (!confirm('{{ __('locale.Delete this bank account?') }}')) return;

      $.ajax({
        url: '{{ url('panel/cuentas-bancarias') }}/' + id,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: { _method: 'DELETE' },
        success: function () {
          window.location.reload();
        },
        error: function () {
          window.location.reload();
        }
      });
    });

    if (feather) feather.replace({ width: 14, height: 14 });
  });
</script>
@endsection
