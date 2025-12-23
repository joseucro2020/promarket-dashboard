@extends('layouts/contentLayoutMaster')

@section('title', isset($taxe) ? __('Edit Tax') : __('New Tax'))

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header border-bottom p-1 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">{{ isset($taxe) ? __('Edit Tax') : __('New Tax') }}</h4>
        <a href="{{ route('taxes.index') }}" class="btn btn-outline-primary btn-sm">
          <i data-feather="arrow-left" class="me-50"></i>{{ __('Back') }}
        </a>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ isset($taxe) ? route('taxes.update', $taxe->id) : route('taxes.store') }}">
          @csrf
          @if(isset($taxe))
            @method('PUT')
          @endif
          <div class="row">
            <div class="col-md-4 mb-1">
              <label class="form-label" for="name">{{ __('Name') }}</label>
              <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $taxe->name ?? '') }}" required>
              @error('name')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
            <div class="col-md-4 mb-1">
              <label class="form-label" for="description">{{ __('Description') }}</label>
              <input type="text" id="description" name="description" class="form-control" value="{{ old('description', $taxe->description ?? '') }}">
              @error('description')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
            <div class="col-md-4 mb-1">
              <label class="form-label" for="percentage">{{ __('Percentage') }}</label>
              <input type="number" step="0.01" min="0" id="percentage" name="percentage" class="form-control" value="{{ old('percentage', $taxe->percentage ?? '') }}" required>
              @error('percentage')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
          </div>
          <div class="row">
            <div class="col-12 d-flex justify-content-end mt-2">
              <button type="submit" class="btn btn-primary">
                <i data-feather="save" class="me-50"></i>{{ isset($taxe) ? __('Update tax') : __('Save tax') }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
