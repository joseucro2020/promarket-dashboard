@extends('layouts/contentLayoutMaster')

@section('title', __('Contact'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('page-style')
  <style>
    .contact-title { font-style: italic; }
    .contact-wrap {
      border: 2px solid rgba(0,0,0,.18);
      border-radius: .5rem;
      padding: 1.5rem;
      background: #fff;
    }
    .contact-search {
      max-width: 420px;
      border: 0;
      border-bottom: 1px solid #ddd;
      border-radius: 0;
      padding-left: 2.25rem;
    }
    .contact-search-icon {
      position: absolute;
      left: .75rem;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
    }
    .contact-table th { font-weight: 700; }
    .contact-actions a { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; }
  </style>
@endsection

@section('content')
  <section>
    <h2 class="text-center contact-title mb-3">{{ __('Contact Information') }}</h2>

    <div class="contact-wrap">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      <div class="position-relative mb-2">
        <i data-feather="search" class="contact-search-icon"></i>
        <input id="contactSearch" type="text" class="form-control contact-search" placeholder="{{ __('Search') }}" />
      </div>

      <div class="table-responsive">
        <table id="contactTable" class="table table-borderless contact-table w-100">
          <thead>
            <tr>
              <th>{{ __('Address') }}</th>
              <th>{{ __('Phone') }}</th>
              <th>{{ __('Email') }}</th>
              <th class="text-center">{{ __('Action') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($social as $s)
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
                <td class="text-center contact-actions">
                  <a href="#" class="mr-1" onclick="window.__openContactView(this); return false;" title="{{ __('View') }}">
                    <i data-feather="eye"></i>
                  </a>
                  <a href="{{ route('contact.edit', $s->id) }}" title="{{ __('Edit') }}">
                    <i data-feather="edit-2"></i>
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <div class="modal fade" id="contactViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('Contact Information') }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <dl class="row mb-0">
            <dt class="col-4">{{ __('Address') }}</dt>
            <dd class="col-8" id="cv_address"></dd>
            <dt class="col-4">{{ __('Phone') }}</dt>
            <dd class="col-8" id="cv_phone"></dd>
            <dt class="col-4">{{ __('Email') }}</dt>
            <dd class="col-8" id="cv_email"></dd>
          </dl>

          <hr>

          <dl class="row mb-0">
            <dt class="col-4">{{ __('Facebook') }}</dt>
            <dd class="col-8" id="cv_facebook"></dd>
            <dt class="col-4">{{ __('Instagram') }}</dt>
            <dd class="col-8" id="cv_instagram"></dd>
            <dt class="col-4">{{ __('YouTube') }}</dt>
            <dd class="col-8" id="cv_youtube"></dd>
          </dl>

          <hr>

          <dl class="row mb-0">
            <dt class="col-4">{{ __('Slogan (Spanish)') }}</dt>
            <dd class="col-8" id="cv_slogan"></dd>
            <dt class="col-4">{{ __('Slogan (English)') }}</dt>
            <dd class="col-8" id="cv_slogan_en"></dd>
          </dl>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Close') }}</button>
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
    const table = $('#contactTable').DataTable({
      responsive: true,
      dom: 't',
      paging: false,
      info: false,
      ordering: false,
      language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
      drawCallback: function() { if (feather) { feather.replace({ width: 16, height: 16 }); } }
    });

    $('#contactSearch').on('keyup', function () {
      table.search(this.value).draw();
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
