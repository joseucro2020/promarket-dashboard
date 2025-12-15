@extends('layouts/contentLayoutMaster')

@section('title', __('Edit Product'))

@section('content')
<section id="multiple-column-form">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ __('Edit Product') }}</h4>
          <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
        </div>
        <div class="card-body">
          <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" aria-controls="info" role="tab" aria-selected="true">{{ __('Information') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="images-tab" data-toggle="tab" href="#images" aria-controls="images" role="tab" aria-selected="false">{{ __('Images') }}</a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Information Tab -->
                <div class="tab-pane active" id="info" aria-labelledby="info-tab" role="tabpanel">
                    
                    <div class="row mt-2">
                        <div class="col-md-12 text-center mb-2">
                            <label>{{ __('Product Type') }}</label>
                            <div class="demo-inline-spacing justify-content-center">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="typeSimple" name="type" class="custom-control-input" value="simple" {{ $product->type == 'simple' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeSimple">{{ __('Simple Product') }}</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="typeVariable" name="type" class="custom-control-input" value="variable" {{ $product->type == 'variable' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeVariable">{{ __('Variable Product') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Mark as PRO Product?') }}</label>
                                <div class="custom-control custom-switch custom-switch-success">
                                    <input type="checkbox" class="custom-control-input" id="is_pro" name="is_pro" {{ $product->is_pro ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_pro">{{ __('Yes') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Auto Update?') }}</label>
                                <div class="custom-control custom-switch custom-switch-success">
                                    <input type="checkbox" class="custom-control-input" id="auto_update" name="auto_update" {{ $product->auto_update ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="auto_update">{{ __('Yes') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Min Stock to Deactivate') }}</label>
                                <input type="number" class="form-control" name="min_stock_deactivate" value="{{ $product->min_stock_deactivate }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Max Stock to Activate') }}</label>
                                <input type="number" class="form-control" name="max_stock_activate" value="{{ $product->max_stock_activate }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Name (Spanish)') }}</label>
                                <input type="text" class="form-control" name="name" value="{{ $product->name }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Name (English)') }}</label>
                                <input type="text" class="form-control" name="name_english" value="{{ $product->name_english }}">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ __('Slug') }}</label>
                                <input type="text" class="form-control" name="slug" value="{{ $product->slug }}" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_active_hours" name="is_active_hours" {{ $product->is_active_hours ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active_hours">{{ __('Active Hours') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Time From') }}</label>
                                <input type="time" class="form-control" name="active_hours_start" value="{{ $product->active_hours_start }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Time To') }}</label>
                                <input type="time" class="form-control" name="active_hours_end" value="{{ $product->active_hours_end }}">
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('Main Category') }}</label>
                                <select class="form-control" name="category_id">
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('Sub Category') }}</label>
                                <select class="form-control" name="subcategory_id">
                                    <option value="">{{ __('Select') }}</option>
                                    <!-- Subcategories should be loaded via AJAX based on Category -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('Sub-Sub Category') }}</label>
                                <select class="form-control" name="subsubcategory_id">
                                    <option value="">{{ __('Select') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Description (Spanish)') }}</label>
                                <textarea class="form-control" name="description" rows="3">{{ $product->description }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Description (English)') }}</label>
                                <textarea class="form-control" name="description_english" rows="3">{{ $product->description_english }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Price') }} $</label>
                                <input type="number" step="0.01" class="form-control" name="price_1" value="{{ $product->price_1 }}" required>
                            </div>
                        </div>
                        <!-- Stock not in fillable
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Stock Quantity') }}</label>
                                <input type="number" class="form-control" name="stock" value="{{ $product->stock ?? '' }}" disabled placeholder="N/A in DB">
                            </div>
                        </div>
                        -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Min Sale') }}</label>
                                <input type="number" class="form-control" name="retail" value="{{ $product->retail }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Max Sale') }}</label>
                                <input type="number" class="form-control" name="wholesale" value="{{ $product->wholesale }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Cost') }}</label>
                                <input type="number" step="0.01" class="form-control" name="price_2" value="{{ $product->price_2 }}" required>
                            </div>
                        </div>
                        <!-- Threshold not in fillable
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Threshold') }}</label>
                                <input type="number" class="form-control" name="threshold" value="{{ $product->threshold ?? '' }}">
                            </div>
                        </div>
                        -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Tax') }}</label>
                                <select class="form-control" name="taxe_id">
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach($taxes as $tax)
                                        <option value="{{ $tax->id }}" {{ $product->taxe_id == $tax->id ? 'selected' : '' }}>{{ $tax->name }} ({{ $tax->percentage }}%)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- SKU not in fillable
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('SKU') }}</label>
                                <input type="text" class="form-control" name="sku" value="{{ $product->sku ?? '' }}">
                            </div>
                        </div>
                        -->

                    </div>
                </div>

                <!-- Images Tab -->
                <div class="tab-pane" id="images" aria-labelledby="images-tab" role="tabpanel">
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="form-group">
                                <label>{{ __('Main Image') }}</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="image" name="image">
                                    <label class="custom-file-label" for="image">{{ __('Choose file') }}</label>
                                </div>
                                @if($product->image)
                                    <div class="mt-2">
                                        <img src="{{ asset($product->image) }}" alt="Current Image" height="100">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary mr-1">{{ __('Save Changes') }}</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
