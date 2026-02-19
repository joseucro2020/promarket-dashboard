@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Terms & Conditions'))

@section('content')
  <section>
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">{{ __('locale.Purchase Terms & Conditions') }}</h4>
          </div>
          <div class="card-body">
            @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form method="POST" action="{{ route('terms-conditions.store') }}">
              @csrf

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>{{ __('locale.Terms and conditions (Spanish)') }}</label>
                    <textarea class="form-control" name="terms_text" rows="10" required>{{ old('terms_text', $terms->texto ?? '') }}</textarea>
                    @error('terms_text')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>{{ __('locale.Terms and conditions (English)') }}</label>
                    <textarea class="form-control" name="terms_english" rows="10" required>{{ old('terms_english', $terms->english ?? '') }}</textarea>
                    @error('terms_english')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>{{ __('locale.Purchase conditions (Spanish)') }}</label>
                    <textarea class="form-control" name="conditions_text" rows="10" required>{{ old('conditions_text', $conditions->texto ?? '') }}</textarea>
                    @error('conditions_text')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>{{ __('locale.Purchase conditions (English)') }}</label>
                    <textarea class="form-control" name="conditions_english" rows="10" required>{{ old('conditions_english', $conditions->english ?? '') }}</textarea>
                    @error('conditions_english')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
              </div>

              <div class="mt-4 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">{{ __('locale.Save') }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
