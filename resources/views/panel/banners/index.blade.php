@extends('layouts/contentLayoutMaster')

@section('title', __('Banners'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('page-style')
  <style>
    .banner-upload-btn {
      display: inline-flex;
      align-items: center;
      gap: .75rem;
      padding: 1rem 1.25rem;
      border: 1px dashed rgba(0,0,0,.2);
      border-radius: .5rem;
      background: rgba(0,0,0,.01);
    }
    .banner-upload-btn i { width: 38px; height: 38px; }

    /* Gallery view (DataTable-backed) */
    .banners-table thead { display: none; }
    .banners-table.dataTable { border-collapse: separate !important; border-spacing: 0; }
    .banners-table.dataTable tbody {
      display: flex;
      flex-wrap: wrap;
      gap: 1.25rem;
      padding: 1.25rem 0;
    }
    .banners-table.dataTable tbody tr {
      display: block;
      position: relative;
      width: 320px;
      border: 1px solid rgba(0,0,0,.12);
      border-radius: .5rem;
      padding: .75rem;
      background: #fff;
      box-shadow: 0 1px 2px rgba(0,0,0,.03);
    }
    .banners-table.dataTable tbody tr:hover {
      box-shadow: 0 6px 18px rgba(0,0,0,.08);
      transform: translateY(-1px);
      transition: .15s ease;
    }
    .banners-table.dataTable tbody td {
      display: block;
      padding: 0;
      border: 0 !important;
      background: transparent !important;
    }
    .banner-thumb {
      width: 100%;
      height: 96px;
      object-fit: cover;
      border-radius: .35rem;
      border: 1px solid rgba(0,0,0,.08);
      background: #f8f8f8;
      cursor: pointer;
    }
    .banner-actions {
      position: absolute;
      top: .55rem;
      right: .55rem;
      display: flex;
      gap: .35rem;
      z-index: 2;
    }
    .banner-actions .btn {
      width: 32px;
      height: 32px;
      border-radius: 999px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(255,255,255,.92);
      border: 1px solid rgba(0,0,0,.10);
    }
    .banner-actions .btn:hover { background: #fff; }
    .banner-meta {
      margin-top: .6rem;
      display: flex;
      justify-content: space-between;
      gap: .5rem;
      color: #6c757d;
      font-size: .8rem;
    }
    @media (max-width: 576px) {
      .banners-table.dataTable tbody tr { width: 100%; }
    }
  </style>
@endsection

@section('content')
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      <h2 class="text-center mb-2">{{ __('Banners') }}</h2>

      <div class="card">
        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <div class="d-flex justify-content-start mb-2">
            <button type="button" class="banner-upload-btn btn btn-link p-0" onclick="window.__selectBannerFile(0)">
              <i data-feather="plus-circle" class="text-primary"></i>
              <div class="text-left">
                <div class="font-weight-bold">{{ __('Add New') }}</div>
                <small class="text-muted">{{ __('JPG/PNG up to 5MB') }}</small>
              </div>
            </button>
            <input id="bannerFileInput" type="file" accept="image/png,image/jpeg" class="d-none" />
          </div>

          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 module-list-table banners-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>{{ __('Photo') }}</th>
                  <th>{{ __('File') }}</th>
                  <th>{{ __('Registration') }}</th>
                  <th class="text-end">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($banners as $banner)
                  <tr data-id="{{ $banner->id }}" data-file="{{ $banner->foto }}">
                    <td>{{ $banner->id }}</td>
                    <td>
                      <div class="banner-actions">
                        <button type="button" class="btn" onclick="window.__selectBannerFile({{ $banner->id }})" title="{{ __('Edit') }}">
                          <i data-feather="upload"></i>
                        </button>

                        <form class="m-0" action="{{ route('banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('{{ __('Delete this banner?') }}');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn" title="{{ __('Delete') }}">
                            <i data-feather="x"></i>
                          </button>
                        </form>
                      </div>

                      @if($banner->foto && file_exists(public_path('img/slider/'.$banner->foto)))
                        <img class="banner-thumb" src="{{ asset('img/slider/'.$banner->foto) }}" alt="Banner #{{ $banner->id }}" onclick="window.__selectBannerFile({{ $banner->id }})">
                      @else
                        <div class="text-muted">{{ __('No image') }}</div>
                      @endif
                    </td>
                    <td>{{ $banner->foto }}</td>
                    <td>{{ optional($banner->created_at)->format('Y-m-d H:i') }}</td>
                    <td>
                      <div class="banner-meta">
                        <span>#{{ $banner->id }}</span>
                        <span>{{ optional($banner->created_at)->format('Y-m-d H:i') }}</span>
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
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.bootstrap4.min.js')) }}"></script>
@endsection

@section('page-script')
<script>
  const csrfToken = '{{ csrf_token() }}';
  const uploadUrl = '{{ route('banners.upload') }}';

  let __bannerTargetId = 0;

  $(function() {
    $('.banners-table').DataTable({
      responsive: true,
      dom: 't',
      language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
      paging: false,
      info: false,
      searching: false,
      ordering: false,
      columnDefs: [
        { visible: false, targets: [0, 2, 3] }
      ],
      drawCallback: function() { if (feather) { feather.replace({ width: 14, height: 14 }); } }
    });

    const input = document.getElementById('bannerFileInput');
    input.addEventListener('change', async function() {
      if (!input.files || !input.files[0]) return;

      const formData = new FormData();
      formData.append('_token', csrfToken);
      formData.append('id', String(__bannerTargetId || 0));
      formData.append('file', input.files[0]);

      try {
        const res = await fetch(uploadUrl, { method: 'POST', body: formData });
        const json = await res.json().catch(() => ({}));
        if (!res.ok || !json.result) {
          alert(json.error || '{{ __('An error occurred') }}');
          return;
        }
        location.reload();
      } catch (e) {
        alert('{{ __('An error occurred') }}');
      } finally {
        input.value = '';
        __bannerTargetId = 0;
      }
    });
  });

  window.__selectBannerFile = function(id) {
    __bannerTargetId = id || 0;
    document.getElementById('bannerFileInput').click();
  }
</script>
@endsection
