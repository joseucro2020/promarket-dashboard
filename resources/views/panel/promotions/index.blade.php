@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Promotions'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('content')
@php use App\Models\Promotion; @endphp
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom p-1">
          <div class="head-label">
            <h4 class="mb-0">{{ __('locale.Promotions List') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
                <a href="{{ route('promotions.create') }}" class="dt-button create-new btn btn-primary">
                <i data-feather="plus"></i> {{ __('locale.Add New') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 promotions-table">
              <thead>
                <tr>
                  <th>{{ __('locale.Title') }}</th>
                  <th>{{ __('locale.Start Date') }}</th>
                  <th>{{ __('locale.End Date') }}</th>
                  <th>{{ __('locale.Discount Percentage') }}</th>
                  <th>{{ __('locale.Uses per client') }}</th>
                  <th class="text-center">{{ __('locale.Promotion status') }}</th>
                  <th class="text-center">{{ __('locale.Order') }}</th>
                  <th class="text-end">{{ __('locale.Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($promotions as $promotion)
                  @php
                    $isActive = $promotion->status === \App\Models\Promotion::STATUS_ACTIVE
                      && !($promotion->start_date && $promotion->start_date->isFuture())
                      && !($promotion->end_date && $promotion->end_date->isPast());
                  @endphp
                  <tr>
                    <td>{{ $promotion->title }}</td>
                    <td data-order="{{ optional($promotion->start_date)->format('Y-m-d') }}">{{ optional($promotion->start_date)->format('d-m-Y') }}</td>
                    <td data-order="{{ optional($promotion->end_date)->format('Y-m-d') }}">{{ optional($promotion->end_date)->format('d-m-Y') }}</td>
                    <td>{{ $promotion->discount_percentage }}%</td>
                    <td>{{ $promotion->limit }}</td>
                    <td class="text-center">
                      @php
                        switch ($promotion->status) {
                          case Promotion::STATUS_ACTIVE:
                            $badge = 'badge badge-pill badge-light-success';
                            break;
                          case Promotion::STATUS_SOLD_OUT:
                            $badge = 'badge badge-pill badge-light-warning';
                            break;
                          default:
                            $badge = 'badge badge-pill badge-light-secondary';
                        }
                      @endphp
                      <span class="{{ $badge }} promotion-status-badge" data-status="{{ $promotion->status }}">{{ $promotion->status_name }}</span>
                    </td>
                    <td class="text-center" style="min-width:120px;">
                      <input type="number" class="form-control promotions-order-input" data-id="{{ $promotion->id }}" value="{{ $promotion->order }}" min="0">
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="custom-control custom-switch custom-switch-success mr-1">
                          <input type="checkbox" class="custom-control-input promotion-status-toggle" id="promotion_status_{{ $promotion->id }}" data-url="{{ route('promotions.status', $promotion) }}" {{ $isActive ? 'checked' : '' }} />
                          <label class="custom-control-label" for="promotion_status_{{ $promotion->id }}"></label>
                        </div>
                        <a href="{{ route('promotions.edit', $promotion) }}" class="btn btn-icon btn-flat-success mr-1" data-toggle="tooltip" data-placement="top" title="{{ __('locale.Edit') }}">
                          <i data-feather="edit"></i>
                        </a>
                        <form class="m-0" action="{{ route('promotions.destroy', $promotion) }}" method="POST" onsubmit="return confirm('{{ __('locale.Delete this promotion?') }}');">
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
                    <td colspan="8" class="text-center">{{ __('locale.No promotions yet.') }}</td>
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
@endsection

@section('page-script')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    $(function() {
      const csrfToken = $('meta[name="csrf-token"]').attr('content');
      const orderUrlTemplate = "{{ url('panel/promociones/__ID__/orden') }}";

      $('.promotions-table').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        order: [[1, 'desc']],
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

      $('body').on('change', '.promotions-order-input', function () {
        const promotionId = $(this).data('id');
        const payload = {
          order: $(this).val()
        };

        $.ajax({
          url: orderUrlTemplate.replace('__ID__', promotionId),
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken },
          data: payload
        });
      });

      function isPromotionActive(payload) {
        if (!payload || typeof payload.status === 'undefined') return false;
        const statusActive = parseInt(payload.status, 10) === {{ (int)\App\Models\Promotion::STATUS_ACTIVE }};
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        let startOk = true;
        let endOk = true;
        if (payload.start_date) {
          const startDate = new Date(payload.start_date);
          startOk = startDate <= today;
        }
        if (payload.end_date) {
          const endDate = new Date(payload.end_date);
          endOk = endDate >= today;
        }
        return statusActive && startOk && endOk;
      }

      $('body').on('change', '.promotion-status-toggle', function () {
        const checkbox = this;
        const url = checkbox.dataset.url;
        const previous = !checkbox.checked;

        if (!url) {
          checkbox.checked = previous;
          return;
        }

        fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
              .then(async (response) => {
            if (!response.ok) {
              const payload = await response.json().catch(() => ({}));
              throw new Error(payload.message || '{{ __('locale.An error occurred') }}');
            }
            return response.json();
          })
          .then((payload) => {
            if (payload && typeof payload.status !== 'undefined') {
              checkbox.checked = isPromotionActive(payload);
              const $row = $(checkbox).closest('tr');
              const $badge = $row.find('.promotion-status-badge');
              const status = parseInt(payload.status, 10);
              let badgeClass = 'badge badge-pill badge-light-secondary';

              if (status === {{ (int)\App\Models\Promotion::STATUS_ACTIVE }}) {
                badgeClass = 'badge badge-pill badge-light-success';
              } else if (status === {{ (int)\App\Models\Promotion::STATUS_SOLD_OUT }}) {
                badgeClass = 'badge badge-pill badge-light-warning';
              }

              $badge.attr('class', badgeClass + ' promotion-status-badge');
              $badge.text(payload.status_name || $badge.text());
              $badge.attr('data-status', status);
            }
            if (window.Swal) {
              Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ __('locale.Information updated successfully.') }}',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
              });
            }
          })
          .catch((error) => {
            checkbox.checked = previous;
              const message = error.message || '{{ __('locale.An error occurred') }}';
            if (window.Swal) {
              Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: message,
                showConfirmButton: false,
                timer: 4500,
                timerProgressBar: true
              });
            } else {
              alert(message);
            }
          });
      });
    });
  </script>
@endsection
