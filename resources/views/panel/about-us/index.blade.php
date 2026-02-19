@extends('layouts/contentLayoutMaster')

@section('title', __('locale.About Us'))

@section('page-style')
  <style>
    .aboutus-title { font-style: italic; }
    .aboutus-box {
      border: 2px solid rgba(0,0,0,.18);
      border-radius: .75rem;
      padding: 1rem;
      background: #fff;
      min-height: 140px;
      resize: vertical;
    }
    .aboutus-heading {
      font-weight: 700;
      text-align: center;
      margin-bottom: .5rem;
    }
    .aboutus-section { margin-bottom: 1.5rem; }
  </style>
@endsection

@section('content')
  <section>
    <h2 class="text-center aboutus-title mb-3">{{ __('locale.About Us') }}</h2>

    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-8">
        <div class="card">
          <div class="card-body">
            @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger">{{ __('locale.Please review the form fields.') }}</div>
            @endif

            <form method="POST" action="{{ $us ? route('about-us.update', $us->id) : route('about-us.store') }}">
              @csrf
              @if($us)
                @method('PUT')
              @endif

              <div class="aboutus-section">
                <div class="row">
                  <div class="col-md-6">
                    <div class="aboutus-heading">{{ __('locale.Who are we? (Spanish)') }}</div>
                    <textarea class="form-control aboutus-box" name="texto" rows="5" required>{{ old('texto', $us->texto ?? '') }}</textarea>
                    @error('texto')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                  <div class="col-md-6">
                    <div class="aboutus-heading">{{ __('locale.Who are we? (English)') }}</div>
                    <textarea class="form-control aboutus-box" name="english" rows="5" required>{{ old('english', $us->english ?? '') }}</textarea>
                    @error('english')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
              </div>

              <div class="aboutus-section">
                <div class="row">
                  <div class="col-md-6">
                    <div class="aboutus-heading">{{ __('locale.Mission (Spanish)') }}</div>
                    <textarea class="form-control aboutus-box" name="mision" rows="5" required>{{ old('mision', $us->mision ?? '') }}</textarea>
                    @error('mision')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                  <div class="col-md-6">
                    <div class="aboutus-heading">{{ __('locale.Mission (English)') }}</div>
                    <textarea class="form-control aboutus-box" name="mision_english" rows="5" required>{{ old('mision_english', $us->mision_english ?? '') }}</textarea>
                    @error('mision_english')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
              </div>

              <div class="aboutus-section">
                <div class="row">
                  <div class="col-md-6">
                    <div class="aboutus-heading">{{ __('locale.Vision (Spanish)') }}</div>
                    <textarea class="form-control aboutus-box" name="vision" rows="5" required>{{ old('vision', $us->vision ?? '') }}</textarea>
                    @error('vision')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                  <div class="col-md-6">
                    <div class="aboutus-heading">{{ __('locale.Vision (English)') }}</div>
                    <textarea class="form-control aboutus-box" name="vision_english" rows="5" required>{{ old('vision_english', $us->vision_english ?? '') }}</textarea>
                    @error('vision_english')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
              </div>

              <div class="text-center mt-2">
                <button type="submit" class="btn btn-primary px-5">{{ __('locale.Update') }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
