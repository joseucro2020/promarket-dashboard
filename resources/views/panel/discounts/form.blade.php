@extends('layouts/contentLayoutMaster')

@section('title', isset($discount) ? __('Edit Discount') : __('New Discount'))

@section('content')
@php
  $discount = $discount ?? null;
@endphp

<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ isset($discount) ? __('Edit Discount') : __('New Discount') }}</h4>
        </div>
        <div class="card-body">
          <form action="{{ isset($discount) ? route('discounts.update', $discount) : route('discounts.store') }}" method="POST">
            @csrf
            @if(isset($discount)) @method('PUT') @endif

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name">{{ __('Name') }}</label>
                  <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $discount->name ?? '') }}" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="start">{{ __('Start') }}</label>
                  <input type="date" id="start" name="start" class="form-control" value="{{ old('start', optional($discount)->start?->format('Y-m-d')) }}">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="end">{{ __('End') }}</label>
                  <input type="date" id="end" name="end" class="form-control" value="{{ old('end', optional($discount)->end?->format('Y-m-d')) }}">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="percentage">{{ __('Percentage') }}</label>
                  <input type="number" step="0.01" min="0" max="100" id="percentage" name="percentage" class="form-control" value="{{ old('percentage', $discount->percentage ?? 0) }}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="type">{{ __('Type') }}</label>
                  <select id="type" name="type" class="form-control">
                    @foreach(\App\Models\Discount::TYPES as $key => $label)
                      <option value="{{ $key }}" {{ (old('type', $discount->type ?? '') == $key) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="limit">{{ __('Uses per client') }}</label>
                  <input type="number" id="limit" name="limit" class="form-control" min="0" value="{{ old('limit', $discount->limit ?? 0) }}">
                </div>
              </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
              <a href="{{ route('discounts.index') }}" class="btn btn-outline-secondary mr-2">{{ __('Back') }}</a>
              <button type="submit" class="btn btn-primary">{{ isset($discount) ? __('Update') : __('Save') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
