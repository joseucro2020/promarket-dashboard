@extends('layouts/contentLayoutMaster')

@section('title', isset($offer) ? __('Edit Offer') : __('New Offer'))

@section('content')
@php $offer = $offer ?? null; @endphp

<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ isset($offer) ? __('Edit Offer') : __('New Offer') }}</h4>
        </div>
        <div class="card-body">
          <form action="{{ isset($offer) ? route('offers.update', $offer) : route('offers.store') }}" method="POST">
            @csrf
            @if(isset($offer)) @method('PUT') @endif

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="start">{{ __('Start') }}</label>
                  <input type="date" id="start" name="start" class="form-control" value="{{ old('start', optional($offer)->start?->format('Y-m-d')) }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="end">{{ __('End') }}</label>
                  <input type="date" id="end" name="end" class="form-control" value="{{ old('end', optional($offer)->end?->format('Y-m-d')) }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="percentage">{{ __('Percentage') }}</label>
                  <input type="number" step="0.01" min="0" max="100" id="percentage" name="percentage" class="form-control" value="{{ old('percentage', $offer->percentage ?? 0) }}">
                </div>
              </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
              <a href="{{ route('offers.index') }}" class="btn btn-outline-secondary mr-2">{{ __('Back') }}</a>
              <button type="submit" class="btn btn-primary">{{ isset($offer) ? __('Update') : __('Save') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
