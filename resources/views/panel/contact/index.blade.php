@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Contact'))

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
              <h4 class="mb-0">{{ __('locale.Contact Information') }}</h4>
            </div>
          </div>
          <div class="card-body">
            @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
              <table id="contactTable" class="table table-striped table-bordered table-hover w-100 contact-table">
                <thead>
                  <tr>
                    <th>{{ __('locale.Address') }}</th>
                    <th>{{ __('locale.Phone') }}</th>
                    <th>{{ __('locale.Email') }}</th>
                    <th class="text-end">{{ __('locale.Actions') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($social as $s)
                    <tr
                      data-address="{{ $s->address }}"
                      data-phone="{{ $s->phone }}"
                      data-email="{{ $s->email }}"
                      data-facebook="{{ $s->facebook }}"
                      data-instagram="{{ $s->instagram }}"
                      data-youtube="{{ $s->youtube }}"
                      data-slogan="{{ $s->slogan }}"
                      data-english_slogan="{{ $s->english_slogan }}"
                    >
                      <td>{{ $s->address }}</td>
                      <td>{{ $s->phone }}</td>
                      <td>{{ $s->email }}</td>
                      <td>
                        <div class="d-flex align-items-center">
                          <a href="#" class="btn btn-icon btn-flat-primary mr-1" data-toggle="tooltip" data-placement="top" title="{{ __('locale.View') }}" onclick="window.__openContactView(this); return false;">
                            <i data-feather="eye"></i>
                          </a>
                          <a href="{{ route('contact.edit', $s->id) }}" class="btn btn-icon btn-flat-success" data-toggle="tooltip" data-placement="top" title="{{ __('locale.Edit') }}">
                            <i data-feather="edit"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center">{{ __('locale.No contact information yet.') }}</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="modal fade" id="contactViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('locale.Contact Information') }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <dl class="row mb-0">
            <dt class="col-4">{{ __('locale.Address') }}</dt>
            <dd class="col-8" id="cv_address"></dd>
            <dt class="col-4">{{ __('locale.Phone') }}</dt>
            <dd class="col-8" id="cv_phone"></dd>
            <dt class="col-4">{{ __('locale.Email') }}</dt>
            <dd class="col-8" id="cv_email"></dd>
          </dl>

          <hr>

          <dl class="row mb-0">
            <dt class="col-4">{{ __('locale.Facebook') }}</dt>
            <dd class="col-8" id="cv_facebook"></dd>
            <dt class="col-4">{{ __('locale.Instagram') }}</dt>
            <dd class="col-8" id="cv_instagram"></dd>
            <dt class="col-4">{{ __('locale.YouTube') }}</dt>
            <dd class="col-8" id="cv_youtube"></dd>
          </dl>

          <hr>

          <dl class="row mb-0">
            <dt class="col-4">{{ __('locale.Slogan (Spanish)') }}</dt>
            <dd class="col-8" id="cv_slogan"></dd>
            <dt class="col-4">{{ __('locale.Slogan (English)') }}</dt>
            <dd class="col-8" id="cv_slogan_en"></dd>
          </dl>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('locale.Close') }}</button>
        </div>
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
<script>
  $(function () {
    $('#contactTable').DataTable({
      responsive: true,
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      order: [[0, 'asc']],
      columnDefs: [
        { orderable: false, targets: -1 }
      ],
      language: {
        url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
      },
      drawCallback: function() {
        if (feather) {
          feather.replace({
            width: 14,
            height: 14
          });
        }
      }
    });
  });

  window.__openContactView = function(el) {
    const tr = el.closest('tr');
    if (!tr) return;

    const safe = (v) => (v && String(v).trim().length ? v : '—');

    document.getElementById('cv_address').innerText = safe(tr.dataset.address);
    document.getElementById('cv_phone').innerText = safe(tr.dataset.phone);
    document.getElementById('cv_email').innerText = safe(tr.dataset.email);

    document.getElementById('cv_facebook').innerText = safe(tr.dataset.facebook);
    document.getElementById('cv_instagram').innerText = safe(tr.dataset.instagram);
    document.getElementById('cv_youtube').innerText = safe(tr.dataset.youtube);

    document.getElementById('cv_slogan').innerText = safe(tr.dataset.slogan);
    document.getElementById('cv_slogan_en').innerText = safe(tr.dataset.english_slogan);

    $('#contactViewModal').modal('show');
  }
</script>
@endsection
