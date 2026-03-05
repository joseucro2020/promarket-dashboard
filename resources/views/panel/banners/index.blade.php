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
                      @php
                        $file = $banner->foto;
                        $src = null;
                        $fallbackSrc = null;
                        if ($file) {
                          $fallbackSrc = route('banners.image', ['file' => $file]);
                          if (substr($file, 0, 4) === 'http') {
                            $src = $file;
                          } else {
                            $base = config('custom.banner_image_url');
                            if ($base) {
                              $src = rtrim($base, '/') . '/' . ltrim($file, '/');
                            } else {
                              $src = $fallbackSrc;
                            }
                          }
                        }
                      @endphp

                      @if($src)
                        <img
                          class="img-fluid rounded"
                          style="max-height: 64px;"
                          src="{{ $src }}"
                          @if($fallbackSrc && $src !== $fallbackSrc)
                            onerror="this.onerror=null;this.src='{{ $fallbackSrc }}';"
                            referrerpolicy="no-referrer"
                          @endif
                          alt="Banner #{{ $banner->id }}"
                          onclick="window.__selectBannerFile({{ $banner->id }})">
                      @else
                        <span class="text-muted">{{ __('No image') }}</span>
                      @endif
                    </td>
                    <td>{{ $banner->foto }}</td>
                    <td>{{ optional($banner->created_at)->format('Y-m-d H:i') }}</td>
                    <td>
                      <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-icon btn-flat-success mr-1" data-toggle="tooltip" data-placement="top" title="{{ __('locale.Upload Image') }}" onclick="window.__selectBannerFile({{ $banner->id }})">
                          <i data-feather="upload"></i>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const csrfToken = '{{ csrf_token() }}';
  const uploadUrl = '{{ route('banners.upload') }}';
  const deleteUrlTemplate = @json(route('banners.destroy', ['id' => '__ID__']));
  const uploadTooltip = @json(__('locale.Upload Image'));
  const deleteTooltip = @json(__('locale.Delete'));
  const deleteConfirmText = @json(__('locale.Delete this banner?'));
  const noImageText = @json(__('No image'));
  const genericErrorText = @json(__('locale.An error occurred'));
  const bannerImageTemplate = @json(route('banners.image', ['file' => '__FILE__']));
  const bannerCreatedText = @json(__('locale.Banner created successfully.'));
  const bannerUpdatedText = @json(__('locale.Banner updated successfully.'));

  let __bannerTargetId = 0;

  function escapeHtml(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function nowYmdHi() {
    const d = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }

  function localBannerImageUrl(fileName) {
    return bannerImageTemplate.replace('__FILE__', encodeURIComponent(fileName || ''));
  }

  function buildBannerImageHtml(bannerId, imageUrl, fileName) {
    if (!imageUrl) {
      return `<span class="text-muted">${escapeHtml(noImageText)}</span>`;
    }

    const fallbackUrl = localBannerImageUrl(fileName);
    const onErrorAttr = imageUrl !== fallbackUrl
      ? ` onerror="this.onerror=null;this.src='${escapeHtml(fallbackUrl)}';" referrerpolicy="no-referrer"`
      : '';

    return `<img class="img-fluid rounded" style="max-height: 64px;" src="${escapeHtml(imageUrl)}"${onErrorAttr} alt="Banner #${bannerId}" onclick="window.__selectBannerFile(${bannerId})">`;
  }

  function refreshDynamicIcons() {
    if (feather) {
      feather.replace({
        width: 14,
        height: 14
      });
    }

    if ($.fn.tooltip) {
      $('[data-toggle="tooltip"]').tooltip({ container: 'body' });
    }
  }

  function showToast(icon, title) {
    if (window.Swal) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });
    }
  }

  $(function() {
    const table = $('.banners-table').DataTable({
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
        refreshDynamicIcons();
      }
    });

    table.on('responsive-display.dt responsive-resize.dt', function() {
      refreshDynamicIcons();
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
          showToast('error', json.error || genericErrorText);
          return;
        }

        const tableApi = table;
        const bannerId = Number(json.id || __bannerTargetId || 0);
        const fileName = String(json.file || '');
        const imageUrl = String(json.url || '');

        const existingRow = bannerId
          ? document.querySelector(`.banners-table tbody tr[data-id="${bannerId}"]`)
          : null;

        if (existingRow) {
          existingRow.setAttribute('data-file', fileName);

          const photoCell = existingRow.children[1];
          const fileCell = existingRow.children[2];

          if (photoCell) {
            if (imageUrl) {
              photoCell.innerHTML = buildBannerImageHtml(bannerId, imageUrl, fileName);
            } else {
              photoCell.innerHTML = `<span class="text-muted">${escapeHtml(noImageText)}</span>`;
            }
          }

          if (fileCell) {
            fileCell.textContent = fileName;
          }
        } else if (bannerId) {
          $('.banners-table tbody tr td[colspan="5"]').closest('tr').remove();

          const deleteUrl = deleteUrlTemplate.replace('__ID__', String(bannerId));
          const actionsHtml = `
            <div class="d-flex align-items-center">
              <button type="button" class="btn btn-icon btn-flat-success mr-1" data-toggle="tooltip" data-placement="top" title="${escapeHtml(uploadTooltip)}" onclick="window.__selectBannerFile(${bannerId})">
                <i data-feather="upload"></i>
              </button>
              <form class="m-0" action="${escapeHtml(deleteUrl)}" method="POST" onsubmit="return confirm('${escapeHtml(deleteConfirmText)}');">
                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-icon btn-flat-danger" data-toggle="tooltip" data-placement="top" title="${escapeHtml(deleteTooltip)}">
                  <i data-feather="trash"></i>
                </button>
              </form>
            </div>
          `;

          const photoHtml = buildBannerImageHtml(bannerId, imageUrl, fileName);

          const rowNode = tableApi
            .row
            .add([
              String(bannerId),
              photoHtml,
              escapeHtml(fileName),
              nowYmdHi(),
              actionsHtml
            ])
            .draw(false)
            .node();

          rowNode.setAttribute('data-id', String(bannerId));
          rowNode.setAttribute('data-file', fileName);
        }

        refreshDynamicIcons();
        showToast('success', __bannerTargetId ? bannerUpdatedText : bannerCreatedText);
      } catch (e) {
        showToast('error', genericErrorText);
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
