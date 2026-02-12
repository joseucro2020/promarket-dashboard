@extends('layouts/contentLayoutMaster')

@section('title', __('Create Purchase Order'))

@section('content')
    <section>
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Create Purchase Order') }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('buyorders.store') }}" method="POST">
                    @csrf
                    @include('panel.buyorder.form')
                    <div class="text-right">
                        {{-- <a href="{{ route('buyorders.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
          <button type="submit" class="btn btn-primary">{{ __('Save') }}</button> --}}

                        <a href="{{ route('buyorders.index') }}"
                            class="btn btn-outline-secondary mr-2">{{ __('Back') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
