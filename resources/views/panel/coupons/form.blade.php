@extends('layouts/contentLayoutMaster')

@section('title', isset($coupon) ? __('Edit Coupon') : __('New Coupon'))

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4 class="mb-0">{{ isset($coupon) ? __('Edit Coupon') : __('New Coupon') }}</h4>
        {{-- <a href="{{ route('coupons.index') }}" class="btn btn-outline-primary btn-sm">
          <i data-feather="arrow-left" class="me-50"></i>{{ __('Back') }}
        </a> --}}
      </div>
      <div class="card-body">
        <form method="POST" action="{{ isset($coupon) ? route('coupons.update', $coupon) : route('coupons.store') }}">
          @csrf
          @if(isset($coupon))
            @method('PUT')
          @endif
          <div class="row">
            <div class="col-md-12 mb-1">
              <label class="form-label" for="user_id">{{ __('Seller PRO') }}</label>
              <select id="user_id" name="user_id" class="form-control" required>
                <option value="">{{ __('Select seller') }}</option>
                @foreach($sellers as $seller)
                  <option value="{{ $seller->id }}" {{ old('user_id', $coupon->user_id ?? '') == $seller->id ? 'selected' : '' }}>
                    {{ $seller->name }}
                  </option>
                @endforeach
              </select>
              @error('user_id')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
            <div class="col-md-12 mb-1">
              <label class="form-label" for="code">{{ __('Coupon Code') }}</label>
              <input type="text" id="code" name="code" class="form-control" value="{{ old('code', $coupon->code ?? '') }}" required>
              @error('code')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-1">
              <label class="form-label" for="uses">{{ __('Uses per client') }}</label>
              <input type="number" min="1" id="uses" name="uses" class="form-control" value="{{ old('uses', $coupon->uses ?? 1) }}" required>
              @error('uses')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
            <div class="col-md-6 mb-1">
              <label class="form-label" for="discount_percentage">{{ __('Discount Percentage') }}</label>
              <input type="number" step="0.01" min="0" max="100" id="discount_percentage" name="discount_percentage" class="form-control" value="{{ old('discount_percentage', $coupon->discount_percentage ?? '') }}" required>
              @error('discount_percentage')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
            <div class="col-md-4 mb-1">
              <label class="form-label" for="first_purchase">{{ __('First Purchase ($ - Amount)') }}</label>
              <input type="number" step="0.01" min="0" id="first_purchase" name="first_purchase" class="form-control" value="{{ old('first_purchase', $coupon->first_purchase ?? '') }}">
              @error('first_purchase')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
            <div class="col-md-4 mb-1">
              <label class="form-label" for="common_purchase">{{ __('Common Purchase ($ - Amount)') }}</label>
              <input type="number" step="0.01" min="0" id="common_purchase" name="common_purchase" class="form-control" value="{{ old('common_purchase', $coupon->common_purchase ?? '') }}">
              @error('common_purchase')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
            <div class="col-md-4 mb-1">
              <label class="form-label" for="recurrent_purchase">{{ __('Recurrent Purchase (% - Percentage)') }}</label>
              <input type="number" step="0.01" min="0" id="recurrent_purchase" name="recurrent_purchase" class="form-control" value="{{ old('recurrent_purchase', $coupon->recurrent_purchase ?? '') }}">
              @error('recurrent_purchase')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
          </div>
          <div class="row">
            <div class="col-12 d-flex justify-content-end mt-2">
              <a href="{{ route('coupons.index') }}" class="btn btn-outline-secondary mr-2">{{ __('Back') }}</a>
              <button type="submit" class="btn btn-primary">{{ isset($coupon) ? __('Update') : __('Save') }}</button>
            </div>
            {{-- <div class="mt-4 d-flex justify-content-end">
              <a href="{{ route('coupons.index') }}" class="btn btn-outline-secondary mr-2">{{ __('Back') }}</a>
              <button type="submit" class="btn btn-primary">{{ isset($coupon) ? __('Update') : __('Save') }}</button>
            </div> --}}
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
