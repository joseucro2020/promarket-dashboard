@extends('layouts/contentLayoutMaster')

@section('title', __('Payment Gateway'))

@section('content')
@php $gateway = $gateway ?? null; @endphp

<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ $gateway ? __('Edit Payment Gateway') : __('New Payment Gateway') }}</h4>
        </div>
        <div class="card-body">
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form method="POST" action="{{ $gateway ? route('payment-gateway.update', $gateway->id) : route('payment-gateway.store') }}" enctype="multipart/form-data">
            @csrf
            @if($gateway)
              @method('PUT')
            @endif

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ __('Name') }}</label>
                  <input type="text" class="form-control" name="name" value="{{ old('name', $gateway->name ?? '') }}" required>
                  @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ __('Provider') }}</label>
                  <input type="text" class="form-control" name="provider" value="{{ old('provider', $gateway->provider ?? '') }}" required>
                  @error('provider')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('Currency') }}</label>
                  <input type="text" class="form-control" name="currency" value="{{ old('currency', $gateway->currency ?? '') }}" placeholder="{{ __('USD / VES') }}">
                  @error('currency')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('Order') }}</label>
                  <input type="number" class="form-control" name="order" min="1" value="{{ old('order', $gateway->order ?? '') }}">
                  @error('order')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('Status') }}</label>
                  <select class="form-control" name="status" required>
                    <option value="1" {{ old('status', isset($gateway) ? (int)$gateway->status : 1) == 1 ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="0" {{ old('status', isset($gateway) ? (int)$gateway->status : 1) == 0 ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                  </select>
                  @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
              </div>

              <div class="col-md-12">
                <div class="form-group">
                  <label>{{ __('Description') }}</label>
                  <input type="text" class="form-control" name="description" value="{{ old('description', $gateway->description ?? '') }}" maxlength="255">
                  @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ __('Payment Method Code') }}</label>
                  <input type="text" class="form-control" name="payment_method_code" value="{{ old('payment_method_code', $gateway->payment_method_code ?? '') }}" required>
                  @error('payment_method_code')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ __('Type') }}</label>
                  <select class="form-control" name="type">
                    <option value="" {{ old('type', $gateway->type ?? '') === '' ? 'selected' : '' }}>{{ __('Auto') }}</option>
                    <option value="unique" {{ old('type', $gateway->type ?? '') === 'unique' ? 'selected' : '' }}>{{ __('Unique') }}</option>
                    <option value="multi" {{ old('type', $gateway->type ?? '') === 'multi' ? 'selected' : '' }}>{{ __('Multi') }}</option>
                  </select>
                  @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
              </div>

              <div class="col-md-12">
                <div class="form-group">
                  <label class="d-block">{{ __('Available Types') }}</label>
                  @php
                    $selected = old('available_types', $gateway->available_types ?? []);
                    $selected = is_array($selected) ? $selected : [];
                  @endphp
                  <div class="d-flex flex-wrap" style="gap: 1rem;">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" id="avail_unique" name="available_types[]" value="unique" {{ in_array('unique', $selected, true) ? 'checked' : '' }}>
                      <label class="custom-control-label" for="avail_unique">{{ __('Unique') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" id="avail_multi" name="available_types[]" value="multi" {{ in_array('multi', $selected, true) ? 'checked' : '' }}>
                      <label class="custom-control-label" for="avail_multi">{{ __('Multi') }}</label>
                    </div>
                  </div>
                  @error('available_types')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
              </div>

              <div class="col-md-12">
                <div class="form-group">
                  <label>{{ __('Config (JSON)') }}</label>
                  <textarea class="form-control" name="config" rows="4" placeholder='{"email":"..."}'>{{ old('config', isset($gateway) && is_array($gateway->config) ? json_encode($gateway->config, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) : ($gateway->config ?? '')) }}</textarea>
                  @error('config')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
              </div>

              <div class="col-md-12">
                <div class="form-group">
                  <label>{{ __('Icon') }} ({{ __('JPG/PNG') }})</label>
                  <input type="file" class="form-control" name="icon_file" accept="image/png,image/jpeg">
                  @error('icon_file')<small class="text-danger">{{ $message }}</small>@enderror
                  <small class="text-muted">{{ __('JPG/PNG up to 5MB') }}</small>
                  @if(!empty($gateway) && !empty($gateway->icon))
                    <div class="mt-2">
                      <img src="{{ asset($gateway->icon) }}" alt="{{ $gateway->name }}" class="img-fluid" style="max-height: 80px;">
                    </div>
                  @endif
                </div>
              </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
              <a href="{{ route('payment-gateway.index') }}" class="btn btn-outline-secondary mr-2">{{ __('Back') }}</a>
              <button type="submit" class="btn btn-primary">{{ $gateway ? __('Update') : __('Save') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
