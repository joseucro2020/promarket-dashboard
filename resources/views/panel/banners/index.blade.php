@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Banners'))

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
            <h4 class="mb-0">{{ __('locale.Banners List') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <button type="button" class="dt-button create-new btn btn-primary" onclick="window.__selectBannerFile(0)">
                <i data-feather="plus"></i> {{ __('locale.Add New') }}
              </button>
            </div>
          </div>
        </div>
        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <input id="bannerFileInput" type="file" accept="image/png,image/jpeg" class="d-none" />

          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 banners-table">
              <thead>
                <tr>
                  <th>{{ __('locale.ID') }}</th>
                  <th>{{ __('locale.Photo') }}</th>
                  <th>{{ __('locale.File') }}</th>
                  <th>{{ __('locale.Registration') }}</th>
                  <th class="text-end">{{ __('locale.Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($banners as $banner)
                  <tr data-id="{{ $banner->id }}" data-file="{{ $banner->foto }}">
                    <td>{{ $banner->id }}</td>
                    <td>
                      @if($banner->foto && file_exists(public_path('img/slider/'.$banner->foto)))
                        <img class="img-fluid rounded" style="max-height: 64px;" src="{{ asset('img/slider/'.$banner->foto) }}" alt="Banner #{{ $banner->id }}" onclick="window.__selectBannerFile({{ $banner->id }})">
                      @else
                        <span class="text-muted">{{ __('No image') }}</span>
                      @endif
                    </td>
                    <td>{{ $banner->foto }}</td>
                    <td>{{ optional($banner->created_at)->format('Y-m-d H:i') }}</td>
                    <td>
                      <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-icon btn-flat-success mr-1" data-toggle="tooltip" data-placement="top" title="{{ __('locale.Edit') }}" onclick="window.__selectBannerFile({{ $banner->id }})">
                          <i data-feather="edit"></i>
                        </button>
                        <form class="m-0" action="{{ route('banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('{{ __('locale.Delete this banner?') }}');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-icon btn-flat-danger" data-toggle="tooltip" data-placement="top" title="{{ __('locale.Delete') }}">
                            <i data-feather="trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center">{{ __('locale.No banners yet.') }}</td>
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
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      order: [[0, 'desc']],
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
          alert(json.error || '{{ __('locale.An error occurred') }}');
          return;
        }
        location.reload();
      } catch (e) {
        alert('{{ __('locale.An error occurred') }}');
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
