@extends('layouts/contentLayoutMaster')

@section('title', __('Edit Purchase Order'))

@section('content')
<section>
  <div class="card">
    <div class="card-header">
      <h4 class="card-title">{{ __('Edit Purchase Order') }}</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('buyorders.update', $buyorder->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('panel.buyorder.form')
        <div class="text-right">
          <a href="{{ route('buyorders.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
          <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </div>
      </form>
    </div>
  </div>
</section>
@endsection
