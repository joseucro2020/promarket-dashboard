@extends('layouts/contentLayoutMaster')

@section('title', __('New Inventory Replenishment'))

@section('content')
<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ __('Add Replenishment') }}</h4>
        </div>
        <div class="card-body">
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form action="{{ route('inventory.store') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ __('Product') }}</label>
                  <select name="product_id" class="form-control">
                    <option value="">{{ __('Select') }}</option>
                    @foreach($products as $id => $name)
                      <option value="{{ $id }}" {{ old('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>{{ __('Type') }}</label>
                  <select name="type" class="form-control">
                    <option value="entrada" {{ old('type') == 'entrada' ? 'selected' : '' }}>{{ __('Entry') }}</option>
                    <option value="salida" {{ old('type') == 'salida' ? 'selected' : '' }}>{{ __('Exit') }}</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>{{ __('Quantity') }}</label>
                  <input type="number" name="quantity" class="form-control" step="1" value="{{ old('quantity') }}">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>{{ __('Reason') }}</label>
              <textarea name="reason" class="form-control" rows="3">{{ old('reason') }}</textarea>
            </div>
            <div class="mt-4 d-flex justify-content-end">
              <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary mr-2">{{ __('Back') }}</a>
              <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
