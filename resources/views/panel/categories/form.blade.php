@extends('layouts/contentLayoutMaster')

@section('title', isset($category) ? __('Edit Category') : __('New Category'))

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header border-bottom p-1 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">{{ isset($category) ? __('Edit Category') : __('New Category') }}</h4>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-primary btn-sm">
          <i data-feather="arrow-left" class="me-50"></i>{{ __('Back') }}
        </a>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ isset($category) ? route('categories.update', $category->id) : route('categories.store') }}">
          @csrf
          @if(isset($category))
            @method('PUT')
          @endif

          <div class="row">
            <div class="col-md-5 mb-1">
              <label class="form-label" for="name">{{ __('Category (Spanish)') }}</label>
              <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
              @error('name')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>

            <div class="col-md-5 mb-1">
              <label class="form-label" for="name_english">{{ __('Category (English)') }}</label>
              <input type="text" id="name_english" name="name_english" class="form-control" value="{{ old('name_english', $category->name_english ?? '') }}">
              @error('name_english')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>

            <div class="col-md-2 mb-1">
              <label class="form-label" for="status">{{ __('Status') }}</label>
              <select id="status" name="status" class="form-control" required>
                <option value="1" {{ old('status', $category->status ?? '1') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="0" {{ old('status', $category->status ?? '1') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
              </select>
              @error('status')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-12 d-flex justify-content-end mt-2">
              <button type="submit" class="btn btn-primary">
                <i data-feather="save" class="me-50"></i>{{ isset($category) ? __('Update category') : __('Save category') }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
