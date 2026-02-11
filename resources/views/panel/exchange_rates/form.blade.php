@extends('layouts/contentLayoutMaster')

@section('title', isset($rate) ? __('Edit Exchange Rate') : __('New Exchange Rate'))

@section('content')
<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ isset($rate) ? __('Edit Exchange Rate') : __('New Exchange Rate') }}</h4>
        </div>
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          <form id="exchange-rate-form" method="POST" action="{{ isset($rate) ? route('exchange-rates.update', $rate->id) : route('exchange-rates.store') }}">
            @csrf
            @if(isset($rate))
              @method('PUT')
            @endif

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="rate_date">{{ __('Date Recorded') }}</label>
                  <input id="rate_date" type="datetime-local" name="date" class="form-control" value="{{ old('date', isset($rate) ? $rate->created_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="currency_from">{{ __('Currency From') }}</label>
                  <input id="currency_from" type="text" name="currency_from" class="form-control" value="{{ old('currency_from', $rate->currency_from ?? 'USD') }}" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="currency_to">{{ __('Currency To') }}</label>
                  <input id="currency_to" type="text" name="currency_to" class="form-control" value="{{ old('currency_to', $rate->currency_to ?? 'PEN') }}" required>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="rate_value">{{ __('Rate') }}</label>
                  <input id="rate_value" type="number" step="0.0001" name="rate" class="form-control" value="{{ old('rate', $rate->change ?? '') }}" required>
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group">
                  <label for="rate_notes">{{ __('Notes') }}</label>
                  <textarea id="rate_notes" name="notes" class="form-control" rows="3">{{ old('notes', $rate->notes ?? '') }}</textarea>
                </div>
              </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
              <a href="{{ route('exchange-rates.index') }}" class="btn btn-outline-secondary mr-2">{{ __('Back') }}</a>
              <button id="exchange-rate-submit" type="submit" class="btn btn-primary">
                <span id="exchange-rate-submit-text">{{ isset($rate) ? __('Update') : __('Save') }}</span>
                <span id="exchange-rate-submit-spinner" class="spinner-border spinner-border-sm ml-1" role="status" style="display:none;" aria-hidden="true"></span>
              </button>
            </div>
          </form>
        </div>
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
          submitText.textContent = isUpdate ? '{{ __('Updating...') }}' : '{{ __('Saving...') }}';
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
