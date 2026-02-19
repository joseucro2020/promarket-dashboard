@extends('layouts/contentLayoutMaster')

@section('title', isset($taxe) ? __('locale.Edit Tax') : __('locale.New Tax'))

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4 class="mb-0">{{ isset($taxe) ? __('locale.Edit Tax') : __('locale.New Tax') }}</h4>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ isset($taxe) ? route('taxes.update', $taxe->id) : route('taxes.store') }}">
          @csrf
          @if(isset($taxe))
            @method('PUT')
          @endif
          <div class="row">
            <div class="col-md-4 mb-1">
              <label class="form-label" for="name">{{ __('locale.Name') }}</label>
              <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $taxe->name ?? '') }}" required>
              @error('name')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
            <div class="col-md-4 mb-1">
              <label class="form-label" for="description">{{ __('locale.Description') }}</label>
              <input type="text" id="description" name="description" class="form-control" value="{{ old('description', $taxe->description ?? '') }}">
              @error('description')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
            <div class="col-md-4 mb-1">
              <label class="form-label" for="percentage">{{ __('locale.Percentage') }}</label>
              <input type="number" step="0.01" min="0" id="percentage" name="percentage" class="form-control" value="{{ old('percentage', $taxe->percentage ?? '') }}" required>
              @error('percentage')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
          </div>
          <div class="row">
            <div class="col-12 d-flex justify-content-end mt-2">
              <a href="{{ route('taxes.index') }}" class="btn btn-outline-secondary mr-2">{{ __('locale.Back') }}</a>
              {{-- <button type="submit" class="btn btn-primary">
                <i data-feather="save" class="me-50"></i>{{ isset($taxe) ? __('Update') : __('Save') }}
              </button> --}}
              <button type="submit" class="btn btn-primary">{{ isset($taxe) ? __('locale.Update') : __('locale.Save') }}</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
