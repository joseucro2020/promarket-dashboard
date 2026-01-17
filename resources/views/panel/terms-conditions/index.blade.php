@extends('layouts/contentLayoutMaster')

@section('title', __('Terms & Conditions'))

@section('page-style')
  <style>
    .tc-title { font-style: italic; }
    .tc-box {
      border: 2px solid rgba(0,0,0,.18);
      border-radius: .75rem;
      padding: 1rem;
      background: #fff;
      min-height: 220px;
      resize: vertical;
    }
    .tc-heading {
      font-weight: 700;
      text-align: center;
      margin-bottom: .5rem;
    }
    .tc-section { margin-bottom: 1.5rem; }
  </style>
@endsection

@section('content')
  <section>
    <h2 class="text-center tc-title mb-3">{{ __('Purchase Terms & Conditions') }}</h2>

    <div class="row justify-content-center">
      <div class="col-12 col-lg-11 col-xl-10">
        <div class="card">
          <div class="card-body">
            @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger">{{ __('Please review the form fields.') }}</div>
            @endif

            <form method="POST" action="{{ route('terms-conditions.store') }}">
              @csrf

              <div class="tc-section">
                <div class="row">
                  <div class="col-md-6">
                    <div class="tc-heading">{{ __('Terms and conditions (Spanish)') }}</div>
                    <textarea class="form-control tc-box" name="terms_text" rows="10" required>{{ old('terms_text', $terms->texto ?? '') }}</textarea>
                    @error('terms_text')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                  <div class="col-md-6">
                    <div class="tc-heading">{{ __('Terms and conditions (English)') }}</div>
                    <textarea class="form-control tc-box" name="terms_english" rows="10" required>{{ old('terms_english', $terms->english ?? '') }}</textarea>
                    @error('terms_english')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
              </div>

              <div class="tc-section">
                <div class="row">
                  <div class="col-md-6">
                    <div class="tc-heading">{{ __('Purchase conditions (Spanish)') }}</div>
                    <textarea class="form-control tc-box" name="conditions_text" rows="10" required>{{ old('conditions_text', $conditions->texto ?? '') }}</textarea>
                    @error('conditions_text')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                  <div class="col-md-6">
                    <div class="tc-heading">{{ __('Purchase conditions (English)') }}</div>
                    <textarea class="form-control tc-box" name="conditions_english" rows="10" required>{{ old('conditions_english', $conditions->english ?? '') }}</textarea>
                    @error('conditions_english')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
              </div>

              <div class="text-center mt-2">
                <button type="submit" class="btn btn-primary px-5">{{ __('Save') }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
