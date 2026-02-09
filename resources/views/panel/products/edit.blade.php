@extends('layouts/contentLayoutMaster')

@section('title', __('Edit Product'))

@section('content')
<section id="multiple-column-form">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ __('Edit Product') }}</h4>
          {{-- <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a> --}}
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
                    <a class="nav-link" id="presentations-tab" data-toggle="tab" href="#presentations" aria-controls="presentations" role="tab" aria-selected="false">{{ __('Presentations') }}</a>
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
                            <label>{{ __('Product Type') }} {{$product->variable}}</label>
                            <div class="demo-inline-spacing justify-content-center">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="typeSimple" name="variable" class="custom-control-input" value="0" {{ $product->variable == 0 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeSimple">{{ __('Simple Product') }}</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="typeVariable" name="variable" class="custom-control-input" value="1" {{ $product->variable == 1 ? 'checked' : '' }}>
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
                                <input type="number" class="form-control" name="min_stock_deactivate" value="{{ $product->minexi }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Max Stock to Activate') }}</label>
                                <input type="number" class="form-control" name="max_stock_activate" value="{{ $product->maxexi }}">
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

                <!-- Presentations Tab -->
                <div class="tab-pane" id="presentations" aria-labelledby="presentations-tab" role="tabpanel">
                    <div class="row mt-2">
                        <div class="col-12 mb-2">
                            <button type="button" class="btn btn-primary" onclick="addPresentationField()">
                                <i data-feather="plus"></i> {{ __('Add New') }}
                            </button>
                        </div>
                        
                        <div id="presentations-container" class="col-12">
                            @php
                                $defaultColor = $product->colors->first();
                                $amounts = $defaultColor ? $defaultColor->amounts : collect([]);
                            @endphp

                            @forelse($amounts as $index => $amount)
                                <div class="card border presentation-item" id="presentation_row_{{ $index }}">
                                    <div class="card-body position-relative">
                                        <button type="button" class="close position-absolute" style="top: 10px; right: 10px;" aria-label="Close" onclick="removePresentationRow({{ $index }})">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <input type="hidden" name="presentations[{{ $index }}][id]" value="{{ $amount->id }}">
                                        
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('Unit') }}</label>
                                                    <select class="form-control" name="presentations[{{ $index }}][unit]">
                                                        <option value="1" {{ $amount->unit == 1 ? 'selected' : '' }}>1 - Gr</option>
                                                        <option value="2" {{ $amount->unit == 2 ? 'selected' : '' }}>2 - Kg</option>
                                                        <option value="3" {{ $amount->unit == 3 ? 'selected' : '' }}>3 - Ml</option>
                                                        <option value="4" {{ $amount->unit == 4 ? 'selected' : '' }}>4 - L</option>
                                                        <option value="5" {{ $amount->unit == 5 ? 'selected' : '' }}>5 - Cm</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('Presentation') }}</label>
                                                    <input type="number" class="form-control" name="presentations[{{ $index }}][presentation]" value="{{ $amount->presentation }}" placeholder="250">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('Quantity') }}</label>
                                                    <input type="number" class="form-control" name="presentations[{{ $index }}][amount]" value="{{ $amount->amount }}" placeholder="101">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('Price') }} $</label>
                                                    <input type="number" step="0.01" class="form-control" name="presentations[{{ $index }}][price]" value="{{ $amount->price }}" placeholder="4.99">
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('Cost') }}</label>
                                                    <input type="number" step="0.01" class="form-control" name="presentations[{{ $index }}][cost]" value="{{ $amount->cost }}" placeholder="3.99">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('Min. Sale') }}</label>
                                                    <input type="number" class="form-control" name="presentations[{{ $index }}][min]" value="{{ $amount->min }}" placeholder="1">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('Max. Sale') }}</label>
                                                    <input type="number" class="form-control" name="presentations[{{ $index }}][max]" value="{{ $amount->max }}" placeholder="1">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('Threshold') }}</label>
                                                    <input type="number" class="form-control" name="presentations[{{ $index }}][umbral]" value="{{ $amount->umbral }}" placeholder="1">
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('SKU') }}</label>
                                                    <input type="text" class="form-control" name="presentations[{{ $index }}][sku]" value="{{ $amount->sku }}" placeholder="4176">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Images Tab -->
                <div class="tab-pane" id="images" aria-labelledby="images-tab" role="tabpanel">
                    <div class="row mt-2">
                        <div class="col-12 text-center mb-2">
                            <h4>{{ __('Main Image') }}</h4>
                        </div>
                        <div class="col-12 d-flex justify-content-center mb-3">
                            <div class="image-upload-container text-center border rounded p-3 bg-light" style="width: 100%; max-width: 600px; cursor: pointer; min-height: 200px; display: flex; align-items: center; justify-content: center;" onclick="document.getElementById('main_image_input').click();">
                                <div id="main_image_preview_container">
                                    @php
                                        $mainImage = $product->images->where('main', '1')->first();
                                    @endphp
                                    @if($mainImage)
                                        <img src="{{ $mainImage->image_url }}" alt="Main Image" class="img-fluid" style="max-height: 300px;">
                                    @else
                                        <i data-feather="image" style="width: 64px; height: 64px; color: #5e5873;"></i>
                                    @endif
                                </div>
                                <input type="file" id="main_image_input" name="image" class="d-none" accept="image/*" onchange="previewImage(this, 'main_image_preview_container')">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div id="secondary-images-container" class="row">
                                @foreach($product->images as $img)
                                    @if($img->main != '1')
                                        <div class="col-md-4 col-6 mb-2" id="existing_img_{{ $img->id }}">
                                            <div class="card border">
                                                <div class="card-body p-2 text-center position-relative">
                                                    <button type="button" class="close position-absolute" style="top: 5px; right: 5px;" onclick="deleteExistingImage({{ $img->id }})">
                                                        <span>&times;</span>
                                                    </button>
                                                    <div class="image-preview mb-2" style="height: 150px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                                        <img src="{{ $img->image_url }}" class="img-fluid" style="max-height: 100%;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            
                            <div class="text-center mt-2">
                                <button type="button" class="btn btn-flat-primary d-flex align-items-center justify-content-center mx-auto" onclick="addSecondaryImageField()">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-1" style="width: 30px; height: 30px;">
                                        <i data-feather="plus" style="width: 20px; height: 20px;"></i>
                                    </div>
                                    <span style="font-size: 1.1rem; font-weight: 600; color: #000;">{{ __('Add new secondary image') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-12">
                    {{-- <button type="submit" class="btn btn-primary mr-1">{{ __('Save Changes') }}</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a> --}}

                    <div class="mt-4 d-flex justify-content-end">
                    <a href="{{ route('products.index') }}"
                    class="btn btn-outline-secondary mr-2">{{ __('Back') }}</a>
                    <button type="submit" id="products-submit" class="btn btn-primary"
                    disabled>{{ isset($products) ? __('Update') : __('Save') }}</button>
                    </div>
                
                </div>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@section('page-script')
<script>
    function previewImage(input, containerId) {
        const container = document.getElementById(containerId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                container.innerHTML = '<img src="' + e.target.result + '" class="img-fluid" style="max-height: 300px;">';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function addSecondaryImageField() {
        const container = document.getElementById('secondary-images-container');
        const index = new Date().getTime(); // Use timestamp for unique ID
        const html = `
            <div class="col-md-4 col-6 mb-2" id="sec_img_${index}">
                <div class="card border">
                    <div class="card-body p-2 text-center position-relative">
                        <button type="button" class="close position-absolute" style="top: 5px; right: 5px;" onclick="document.getElementById('sec_img_${index}').remove()">
                            <span>&times;</span>
                        </button>
                        <div class="image-preview mb-2" id="sec_preview_${index}" style="height: 150px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; cursor: pointer;" onclick="document.getElementById('sec_input_${index}').click()">
                            <i data-feather="image" style="width: 32px; height: 32px; color: #ccc;"></i>
                        </div>
                        <input type="file" name="secondary_images[]" id="sec_input_${index}" class="d-none" accept="image/*" onchange="previewImage(this, 'sec_preview_${index}')">
                        <label class="btn btn-sm btn-outline-primary mb-0" for="sec_input_${index}">{{ __('Select Image') }}</label>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        
        if (feather) {
            feather.replace();
        }
    }

    let presentationIndex = {{ $amounts->count() > 0 ? $amounts->count() : 0 }};

    function addPresentationField() {
        const container = document.getElementById('presentations-container');
        const index = presentationIndex++;
        const html = `
            <div class="card border presentation-item" id="presentation_row_${index}">
                <div class="card-body position-relative">
                    <button type="button" class="close position-absolute" style="top: 10px; right: 10px;" aria-label="Close" onclick="removePresentationRow(${index})">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Unit') }}</label>
                                <select class="form-control" name="presentations[${index}][unit]">
                                    <option value="1">1 - Gr</option>
                                    <option value="2">2 - Kg</option>
                                    <option value="3">3 - Ml</option>
                                    <option value="4">4 - L</option>
                                    <option value="5">5 - Cm</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Presentation') }}</label>
                                <input type="number" class="form-control" name="presentations[${index}][presentation]" placeholder="250">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Quantity') }}</label>
                                <input type="number" class="form-control" name="presentations[${index}][amount]" placeholder="101">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Price') }} $</label>
                                <input type="number" step="0.01" class="form-control" name="presentations[${index}][price]" placeholder="4.99">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Cost') }}</label>
                                <input type="number" step="0.01" class="form-control" name="presentations[${index}][cost]" placeholder="3.99">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Min. Sale') }}</label>
                                <input type="number" class="form-control" name="presentations[${index}][min]" placeholder="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Max. Sale') }}</label>
                                <input type="number" class="form-control" name="presentations[${index}][max]" placeholder="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Threshold') }}</label>
                                <input type="number" class="form-control" name="presentations[${index}][umbral]" placeholder="1">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('SKU') }}</label>
                                <input type="text" class="form-control" name="presentations[${index}][sku]" placeholder="4176">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        
        if (feather) {
            feather.replace();
        }
    }

    function removePresentationRow(index) {
        document.getElementById(`presentation_row_${index}`).remove();
    }

    function deleteExistingImage(id) {
        document.getElementById(`existing_img_${id}`).remove();
        const container = document.getElementById('secondary-images-container');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_images[]';
        input.value = id;
        container.appendChild(input);
    }
</script>
@endsection
@endsection
