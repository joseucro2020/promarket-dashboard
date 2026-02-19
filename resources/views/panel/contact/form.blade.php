@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Contact'))

@section('content')
  <section>
    <h2 class="text-center font-italic mb-3">{{ __('locale.Contact Information') }}</h2>

    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-8">
        <div class="card">
          <div class="card-body">
            @if ($errors->any())
              <div class="alert alert-danger">{{ __('locale.Please review the form fields.') }}</div>
            @endif

            <form method="POST" action="{{ route('contact.update', $social->id) }}">
              @csrf
              @method('PUT')

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>{{ __('locale.Address') }}</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $social->address) }}" required>
                    @error('address')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>{{ __('locale.Phone') }}</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $social->phone) }}" required>
                    @error('phone')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>{{ __('locale.Email') }}</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $social->email) }}" required>
                    @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
              </div>

              <hr>

              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>{{ __('locale.Facebook') }}</label>
                    <input type="url" name="facebook" class="form-control" value="{{ old('facebook', $social->facebook) }}" placeholder="https://...">
                    @error('facebook')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>{{ __('locale.Instagram') }}</label>
                    <input type="url" name="instagram" class="form-control" value="{{ old('instagram', $social->instagram) }}" placeholder="https://...">
                    @error('instagram')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>{{ __('locale.YouTube') }}</label>
                    <input type="url" name="youtube" class="form-control" value="{{ old('youtube', $social->youtube) }}" placeholder="https://...">
                    @error('youtube')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>{{ __('locale.Slogan (Spanish)') }}</label>
                    <input type="text" name="slogan" class="form-control" value="{{ old('slogan', $social->slogan) }}">
                    @error('slogan')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>{{ __('locale.Slogan (English)') }}</label>
                    <input type="text" name="slogan_english" class="form-control" value="{{ old('slogan_english', $social->english_slogan) }}">
                    @error('slogan_english')<small class="text-danger">{{ $message }}</small>@enderror
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-between mt-2">
                <a href="{{ route('contact.index') }}" class="btn btn-outline-secondary">{{ __('locale.Back') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('locale.Update') }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
