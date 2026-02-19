@extends('layouts/contentLayoutMaster')

@section('title', __('locale.View Category'))

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header border-bottom p-1 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">{{ __('locale.View Category') }}</h4>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-primary btn-sm">
          <i data-feather="arrow-left" class="me-50"></i>{{ __('locale.Back') }}
        </a>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-1">
            <label class="form-label">{{ __('locale.Category (Spanish)') }}</label>
            <input class="form-control" value="{{ $category->name }}" disabled>
          </div>
          <div class="col-md-6 mb-1">
            <label class="form-label">{{ __('locale.Category (English)') }}</label>
            <input class="form-control" value="{{ $category->name_english }}" disabled>
          </div>
        </div>
        <div class="row">
          <div class="col-md-3 mb-1">
            <label class="form-label">{{ __('locale.Status') }}</label>
            <input class="form-control" value="{{ $category->status === '1' ? __('locale.Active') : __('locale.Inactive') }}" disabled>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
