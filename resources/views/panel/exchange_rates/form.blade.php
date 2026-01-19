@extends('layouts/contentLayoutMaster')

@section('title', isset($rate) ? 'Editar tasa' : 'Nueva tasa')

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">{{ isset($rate) ? __('Edit Exchange Rate') : __('New Exchange Rate') }}</h4>
      </div>
      <div class="card-body">
        <form id="exchange-rate-form" method="POST" action="{{ isset($rate) ? route('exchange-rates.update', $rate->id) : route('exchange-rates.store') }}">
          @csrf
          @if(isset($rate))
            @method('PUT')
          @endif

          <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="datetime-local" name="date" class="form-control" value="{{ old('date', isset($rate) ? $rate->created_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">{{ __('Currency From') }}</label>
            <input type="text" name="currency_from" class="form-control" value="{{ old('currency_from', $rate->currency_from ?? 'USD') }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">{{ __('Currency To') }}</label>
            <input type="text" name="currency_to" class="form-control" value="{{ old('currency_to', $rate->currency_to ?? 'PEN') }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">{{ __('Rate') }}</label>
            <input type="number" step="0.0001" name="rate" class="form-control" value="{{ old('rate', $rate->change ?? '') }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">{{ __('Notes') }}</label>
            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $rate->notes ?? '') }}</textarea>
          </div>

          <div class="d-flex justify-content-end">
            <button id="exchange-rate-submit" type="submit" class="btn btn-primary me-2">
              <span id="exchange-rate-submit-text">{{ isset($rate) ? __('Update') : __('Create') }}</span>
              <span id="exchange-rate-submit-spinner" class="spinner-border spinner-border-sm ms-2" role="status" style="display:none;" aria-hidden="true"></span>
            </button>
            <a href="{{ route('exchange-rates.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection

@section('page-script')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    (function(){
      const form = document.getElementById('exchange-rate-form');
      const submitBtn = document.getElementById('exchange-rate-submit');
      const submitText = document.getElementById('exchange-rate-submit-text');
      const spinner = document.getElementById('exchange-rate-submit-spinner');

      form.addEventListener('submit', function(e){
        e.preventDefault();
        // confirmation modal for updates
        const isUpdate = {{ isset($rate) ? 'true' : 'false' }};

        const proceed = () => {
          // disable button and show spinner to avoid double submits
          submitBtn.disabled = true;
          spinner.style.display = 'inline-block';
          submitText.textContent = isUpdate ? '{{ __('Updating...') }}' : '{{ __('Creating...') }}';
          form.submit();
        };

        if (isUpdate) {
          Swal.fire({
            title: '{{ __('Confirm update') }}',
            text: '{{ __('Are you sure you want to update this rate?') }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '{{ __('Yes, update') }}',
            cancelButtonText: '{{ __('Cancel') }}'
          }).then((result) => {
            if (result.isConfirmed) proceed();
          });
        } else {
          proceed();
        }
      });
    })();
  </script>
@endsection
