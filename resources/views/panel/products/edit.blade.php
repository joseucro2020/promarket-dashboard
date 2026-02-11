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
                        @php
                            $defaultColor = $product->colors->first();
                            $amounts = $defaultColor ? $defaultColor->amounts : collect([]);
                        @endphp
                        <form action="{{ route('products.update', $product->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="variable" value="{{ $product->variable }}">

                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info"
                                        aria-controls="info" role="tab" aria-selected="true">{{ __('Information') }}</a>
                                </li>
                                @if ($product->variable == 1)
                                    <li class="nav-item">
                                        <a class="nav-link" id="presentations-tab" data-toggle="tab" href="#presentations"
                                            aria-controls="presentations" role="tab"
                                            aria-selected="false">{{ __('Presentations') }}</a>
                                    </li>
                                @endif
                                <li class="nav-item">
                                    <a class="nav-link" id="images-tab" data-toggle="tab" href="#images"
                                        aria-controls="images" role="tab" aria-selected="false">{{ __('Images') }}</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Information Tab -->
                                <div class="tab-pane active" id="info" aria-labelledby="info-tab" role="tabpanel">

                                    <div class="row mt-2">
                                        <div class="col-md-12 text-center mb-2">
                                            <p class="text-muted mb-1">{{ __('Product Type') }}</p>
                                            <span class="badge badge-light-primary px-2 py-1">
                                                {{ $product->variable == 1 ? __('Variable Product') : __('Simple Product') }}
                                            </span>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('Mark as PRO Product?') }}</label>
                                                <div class="custom-control custom-switch custom-switch-success">
                                                    <input type="checkbox" class="custom-control-input" id="is_pro"
                                                        name="is_pro" {{ $product->is_pro ? 'checked' : '' }}>
                                                    <label class="custom-control-label"
                                                        for="is_pro">{{ __('Yes') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('Auto Update?') }}</label>
                                                <div class="custom-control custom-switch custom-switch-success">
                                                    <input type="checkbox" class="custom-control-input" id="auto_update"
                                                        name="auto_update" {{ $product->auto_update ? 'checked' : '' }}>
                                                    <label class="custom-control-label"
                                                        for="auto_update">{{ __('Yes') }}</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('Min Stock to Deactivate') }}</label>
                                                <input type="number" class="form-control" name="min_stock_deactivate"
                                                    value="{{ $product->minexi }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('Max Stock to Activate') }}</label>
                                                <input type="number" class="form-control" name="max_stock_activate"
                                                    value="{{ $product->maxexi }}">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('Name (Spanish)') }}</label>
                                                <input type="text" class="form-control" name="name"
                                                    value="{{ $product->name }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('Name (English)') }}</label>
                                                <input type="text" class="form-control" name="name_english"
                                                    value="{{ $product->name_english }}">
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>{{ __('Slug') }}</label>
                                                <input type="text" class="form-control" name="slug"
                                                    value="{{ $product->slug }}" required>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="border rounded p-2 mb-2 bg-light">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group mb-1">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="is_active_hours" name="is_active_hours"
                                                                    {{ $product->is_active_hours ? 'checked' : '' }}>
                                                                <label class="custom-control-label"
                                                                    for="is_active_hours">{{ __('Active Hours') }}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>{{ __('Time From') }}</label>
                                                            <input type="time" class="form-control"
                                                                name="active_hours_start"
                                                                value="{{ $product->active_hours_start }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>{{ __('Time To') }}</label>
                                                            <input type="time" class="form-control"
                                                                name="active_hours_end"
                                                                value="{{ $product->active_hours_end }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Categories -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('Main Category') }}</label>
                                                <select class="form-control" id="category_id" name="category_id">
                                                    <option value="">{{ __('Select') }}</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}"
                                                            {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('Sub Category') }}</label>
                                                <select class="form-control" id="subcategory_id" name="subcategory_id">
                                                    <option value="">{{ __('Select') }}</option>
                                                    <!-- Subcategories should be loaded via AJAX based on Category -->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('Sub-Sub Category') }}</label>
                                                <select class="form-control" id="subsubcategory_id"
                                                    name="subsubcategory_id">
                                                    <option value="">{{ __('Select') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex align-items-center mb-1">
                                                <button type="button"
                                                    class="btn btn-flat-primary d-flex align-items-center p-0"
                                                    id="toggle-secondary-category-form">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-1"
                                                        style="width: 28px; height: 28px;">
                                                        <i data-feather="plus" style="width: 16px; height: 16px;"></i>
                                                    </div>
                                                    <span
                                                        style="font-size: 1rem; font-weight: 600; color: #000;">{{ __('Add secondary category') }}</span>
                                                </button>
                                            </div>
                                            <div id="secondary-category-form" class="border rounded p-2 mb-1 d-none">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>{{ __('Secondary Category') }}</label>
                                                            <div class="d-flex align-items-center">
                                                                <select class="form-control" id="secondary_category_id">
                                                                    <option value="">{{ __('Select') }}</option>
                                                                    @foreach ($categories as $category)
                                                                        <option value="{{ $category->id }}">
                                                                            {{ $category->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button"
                                                                    class="btn btn-flat-primary d-flex align-items-center justify-content-center ml-1"
                                                                    id="add-secondary-category-item-btn"
                                                                    aria-label="{{ __('Add secondary category') }}">
                                                                    <i data-feather="plus"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 d-none" id="secondary_subcategory_wrap">
                                                        <div class="form-group">
                                                            <label>{{ __('Secondary Subcategory') }}</label>
                                                            <div class="d-flex align-items-center">
                                                                <select class="form-control"
                                                                    id="secondary_subcategory_id">
                                                                    <option value="">{{ __('Select') }}</option>
                                                                </select>
                                                                <button type="button"
                                                                    class="btn btn-flat-primary d-flex align-items-center justify-content-center ml-1"
                                                                    id="add-secondary-subcategory-btn"
                                                                    aria-label="{{ __('Add secondary subcategory') }}">
                                                                    <i data-feather="plus"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="border rounded p-2 p-md-3 bg-white shadow-sm mt-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span
                                                        class="text-uppercase small text-muted">{{ __('Secondary Subcategory') }}</span>
                                                </div>
                                                <div id="secondary-subcategory-list" class="d-flex flex-wrap">
                                                    @if ($product->secondary_subcategories->isEmpty())
                                                        <p class="text-muted mb-0" data-empty>
                                                            {{ __('No secondary subcategories selected') }}</p>
                                                    @else
                                                        <p class="text-muted mb-0" data-empty style="display: none;">
                                                            {{ __('No secondary subcategories selected') }}</p>
                                                        @foreach ($product->secondary_subcategories as $secondarySubcategory)
                                                            <span
                                                                class="badge badge-light-primary d-flex align-items-center mr-1 mb-1"
                                                                data-id="{{ $secondarySubcategory->id }}">
                                                                <span
                                                                    class="mr-1">{{ $secondarySubcategory->name }}</span>
                                                                <input type="hidden" name="secondary_subcategories[]"
                                                                    value="{{ $secondarySubcategory->id }}">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-flat-danger p-0 ml-1"
                                                                    data-remove="secondary_subcategories"
                                                                    aria-label="{{ __('Remove') }}">
                                                                    <i data-feather="x"></i>
                                                                </button>
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                </div>
                                                <div class="mt-2">
                                                    <span
                                                        class="text-uppercase small text-muted">{{ __('Secondary Categories') }}</span>
                                                </div>
                                                <div id="secondary-category-list" class="d-flex flex-wrap mt-1">
                                                    @if ($product->secondary_categories->isEmpty())
                                                        <p class="text-muted mb-0" data-empty>
                                                            {{ __('No secondary categories selected') }}</p>
                                                    @else
                                                        <p class="text-muted mb-0" data-empty style="display: none;">
                                                            {{ __('No secondary categories selected') }}</p>
                                                        @foreach ($product->secondary_categories as $secondaryCategory)
                                                            <span
                                                                class="badge badge-light-primary d-flex align-items-center mr-1 mb-1"
                                                                data-id="{{ $secondaryCategory->id }}">
                                                                <span class="mr-1">{{ $secondaryCategory->name }}</span>
                                                                <input type="hidden" name="secondary_categories[]"
                                                                    value="{{ $secondaryCategory->id }}">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-flat-danger p-0 ml-1"
                                                                    data-remove="secondary_categories"
                                                                    aria-label="{{ __('Remove') }}">
                                                                    <i data-feather="x"></i>
                                                                </button>
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mt-2">
                                            <div class="form-group">
                                                <label>{{ __('Description (Spanish)') }}</label>
                                                <textarea class="form-control" name="description" rows="3">{{ $product->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <div class="form-group">
                                                <label>{{ __('Description (English)') }}</label>
                                                <textarea class="form-control" name="description_english" rows="3">{{ $product->description_english }}</textarea>
                                            </div>
                                        </div>

                                        @php
                                            $simpleAmount = $amounts->first();
                                        @endphp
                                        @if ($product->variable == 0)
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('Price') }} $</label>
                                                    <input type="number" step="0.01" class="form-control"
                                                        name="price_1" value="{{ $product->price_1 }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('Min Sale') }}</label>
                                                    <input type="number" class="form-control" name="retail"
                                                        value="{{ $product->retail }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('Max Sale') }}</label>
                                                    <input type="number" class="form-control" name="wholesale"
                                                        value="{{ $product->wholesale }}">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('Cost') }}</label>
                                                    <input type="number" step="0.01" class="form-control"
                                                        name="price_2" value="{{ $product->price_2 }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('Threshold') }}</label>
                                                    <input type="number" class="form-control" name="umbral"
                                                        value="{{ $simpleAmount ? $simpleAmount->umbral : '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{ __('SKU') }}</label>
                                                    <input type="text" class="form-control" name="sku"
                                                        value="{{ $simpleAmount ? $simpleAmount->sku : '' }}">
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ __('Tax') }}</label>
                                                <select class="form-control" name="taxe_id">
                                                    <option value="">{{ __('Select') }}</option>
                                                    @foreach ($taxes as $tax)
                                                        <option value="{{ $tax->id }}"
                                                            {{ $product->taxe_id == $tax->id ? 'selected' : '' }}>
                                                            {{ $tax->name }} ({{ $tax->percentage }}%)</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="font-weight-bold">{{ __('Tags') }}</label>
                                            <div class="d-flex align-items-center mb-1">
                                                <button type="button"
                                                    class="btn btn-flat-primary d-flex align-items-center p-0"
                                                    id="add-tag-btn">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-1"
                                                        style="width: 28px; height: 28px;">
                                                        <i data-feather="plus" style="width: 16px; height: 16px;"></i>
                                                    </div>
                                                    <span
                                                        style="font-size: 1rem; font-weight: 600; color: #000;">{{ __('Add Tag') }}</span>
                                                </button>
                                            </div>
                                            <div id="tag-form" class="row d-none">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <div class="d-flex align-items-center">
                                                            <select class="form-control" id="tag_select">
                                                                <option value="">{{ __('Select') }}</option>
                                                                @foreach ($tags as $tag)
                                                                    <option value="{{ $tag->id }}">{{ $tag->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button"
                                                                class="btn btn-flat-primary d-flex align-items-center justify-content-center ml-1"
                                                                id="add-tag-item-btn" aria-label="{{ __('Add Tag') }}">
                                                                <i data-feather="plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="tag-list" class="d-flex flex-wrap">
                                                @if ($product->tags->isEmpty())
                                                    <p class="text-muted mb-0" data-empty>{{ __('No tags selected') }}</p>
                                                @else
                                                    <p class="text-muted mb-0" data-empty style="display: none;">
                                                        {{ __('No tags selected') }}</p>
                                                    @foreach ($product->tags as $tag)
                                                        <span
                                                            class="badge badge-light-primary d-flex align-items-center mr-1 mb-1"
                                                            data-id="{{ $tag->id }}">
                                                            <span class="mr-1">{{ $tag->name }}</span>
                                                            <input type="hidden" name="tags[]"
                                                                value="{{ $tag->id }}">
                                                            <button type="button"
                                                                class="btn btn-sm btn-flat-danger p-0 ml-1"
                                                                data-remove="tags" aria-label="{{ __('Remove') }}">
                                                                <i data-feather="x"></i>
                                                            </button>
                                                        </span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- Presentations Tab -->
                                @if ($product->variable == 1)
                                    <div class="tab-pane" id="presentations" aria-labelledby="presentations-tab"
                                        role="tabpanel">
                                        <div class="row mt-2">
                                            <div class="col-12 mb-2">
                                                <button type="button" class="btn btn-primary"
                                                    onclick="addPresentationField()">
                                                    <i data-feather="plus"></i> {{ __('Add New') }}
                                                </button>
                                            </div>

                                            <div id="presentations-container" class="col-12">
                                                @forelse($amounts as $index => $amount)
                                                    <div class="card border presentation-item"
                                                        id="presentation_row_{{ $index }}">
                                                        <div class="card-body position-relative">
                                                            <button type="button" class="close position-absolute"
                                                                style="top: 10px; right: 10px;" aria-label="Close"
                                                                onclick="removePresentationRow({{ $index }})">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                            <input type="hidden"
                                                                name="presentations[{{ $index }}][id]"
                                                                value="{{ $amount->id }}">

                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>{{ __('Unit') }}</label>
                                                                        <select class="form-control"
                                                                            name="presentations[{{ $index }}][unit]">
                                                                            <option value="1"
                                                                                {{ $amount->unit == 1 ? 'selected' : '' }}>
                                                                                1 - Gr</option>
                                                                            <option value="2"
                                                                                {{ $amount->unit == 2 ? 'selected' : '' }}>
                                                                                2 - Kg</option>
                                                                            <option value="3"
                                                                                {{ $amount->unit == 3 ? 'selected' : '' }}>
                                                                                3 - Ml</option>
                                                                            <option value="4"
                                                                                {{ $amount->unit == 4 ? 'selected' : '' }}>
                                                                                4 - L</option>
                                                                            <option value="5"
                                                                                {{ $amount->unit == 5 ? 'selected' : '' }}>
                                                                                5 - Cm</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>{{ __('Presentation') }}</label>
                                                                        <input type="number" class="form-control"
                                                                            name="presentations[{{ $index }}][presentation]"
                                                                            value="{{ $amount->presentation }}"
                                                                            placeholder="250">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>{{ __('Quantity') }}</label>
                                                                        <input type="number" class="form-control"
                                                                            name="presentations[{{ $index }}][amount]"
                                                                            value="{{ $amount->amount }}"
                                                                            placeholder="101">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>{{ __('Price') }} $</label>
                                                                        <input type="number" step="0.01"
                                                                            class="form-control"
                                                                            name="presentations[{{ $index }}][price]"
                                                                            value="{{ $amount->price }}"
                                                                            placeholder="4.99">
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>{{ __('Cost') }}</label>
                                                                        <input type="number" step="0.01"
                                                                            class="form-control"
                                                                            name="presentations[{{ $index }}][cost]"
                                                                            value="{{ $amount->cost }}"
                                                                            placeholder="3.99">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>{{ __('Min. Sale') }}</label>
                                                                        <input type="number" class="form-control"
                                                                            name="presentations[{{ $index }}][min]"
                                                                            value="{{ $amount->min }}" placeholder="1">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>{{ __('Max. Sale') }}</label>
                                                                        <input type="number" class="form-control"
                                                                            name="presentations[{{ $index }}][max]"
                                                                            value="{{ $amount->max }}" placeholder="1">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>{{ __('Threshold') }}</label>
                                                                        <input type="number" class="form-control"
                                                                            name="presentations[{{ $index }}][umbral]"
                                                                            value="{{ $amount->umbral }}"
                                                                            placeholder="1">
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>{{ __('SKU') }}</label>
                                                                        <input type="text" class="form-control"
                                                                            name="presentations[{{ $index }}][sku]"
                                                                            value="{{ $amount->sku }}"
                                                                            placeholder="4176">
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
                                @endif

                                <!-- Images Tab -->
                                <div class="tab-pane" id="images" aria-labelledby="images-tab" role="tabpanel">
                                    <div class="row mt-2">
                                        <div class="col-12 text-center mb-2">
                                            <h4>{{ __('Main Image') }}</h4>
                                        </div>
                                        <div class="col-12 d-flex justify-content-center mb-3">
                                            <div class="image-upload-container text-center border rounded p-3 bg-light"
                                                style="width: 100%; max-width: 600px; cursor: pointer; min-height: 200px; display: flex; align-items: center; justify-content: center;"
                                                onclick="document.getElementById('main_image_input').click();">
                                                <div id="main_image_preview_container">
                                                    @php
                                                        $mainImage = $product->images->where('main', '1')->first();
                                                    @endphp
                                                    @if ($mainImage)
                                                        <img src="{{ $mainImage->image_url }}" alt="Main Image"
                                                            class="img-fluid" style="max-height: 300px;">
                                                    @else
                                                        <i data-feather="image"
                                                            style="width: 64px; height: 64px; color: #5e5873;"></i>
                                                    @endif
                                                </div>
                                                <input type="file" id="main_image_input" name="image"
                                                    class="d-none" accept="image/*"
                                                    onchange="previewImage(this, 'main_image_preview_container')">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div id="secondary-images-container" class="row">
                                                @foreach ($product->images as $img)
                                                    @if ($img->main != '1')
                                                        <div class="col-md-4 col-6 mb-2"
                                                            id="existing_img_{{ $img->id }}">
                                                            <div class="card border">
                                                                <div class="card-body p-2 text-center position-relative">
                                                                    <button type="button" class="close position-absolute"
                                                                        style="top: 5px; right: 5px;"
                                                                        onclick="deleteExistingImage({{ $img->id }})">
                                                                        <span>&times;</span>
                                                                    </button>
                                                                    <div class="image-preview mb-2"
                                                                        style="height: 150px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                                                        <img src="{{ $img->image_url }}"
                                                                            class="img-fluid" style="max-height: 100%;">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>

                                            <div class="text-center mt-2">
                                                <button type="button"
                                                    class="btn btn-flat-primary d-flex align-items-center justify-content-center mx-auto"
                                                    onclick="addSecondaryImageField()">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-1"
                                                        style="width: 30px; height: 30px;">
                                                        <i data-feather="plus" style="width: 20px; height: 20px;"></i>
                                                    </div>
                                                    <span
                                                        style="font-size: 1.1rem; font-weight: 600; color: #000;">{{ __('Add new secondary image') }}</span>
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
                                        <button type="submit" id="products-submit"
                                            class="btn btn-primary">{{ isset($products) ? __('Update') : __('Save') }}</button>
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function showToast(icon, message) {
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: icon,
                        title: message,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    return;
                }

                alert(message);
            }

            function loadSubcategories(categoryId, selectedSubcategoryId, selectedSubsubcategoryId) {
                const $subcat = $('#subcategory_id');
                const $subsub = $('#subsubcategory_id');

                $subcat.empty().append('<option value="">{{ __('Select') }}</option>');
                $subsub.empty().append('<option value="">{{ __('Select') }}</option>');

                if (!categoryId) {
                    return;
                }

                $.getJSON('{{ url('panel/productos') }}/' + categoryId + '/subcategorias', function(response) {
                    const items = response.subcategory || [];
                    items.forEach(function(s) {
                        const selected = selectedSubcategoryId && String(selectedSubcategoryId) === String(s
                            .id) ? 'selected' : '';
                        $subcat.append('<option value="' + s.id + '" ' + selected + '>' + s.name + '</option>');
                    });

                    if (selectedSubcategoryId) {
                        const match = items.find(function(s) {
                            return String(s.id) === String(selectedSubcategoryId);
                        });
                        if (match && match.sub_subcategories) {
                            match.sub_subcategories.forEach(function(ss) {
                                const selected = selectedSubsubcategoryId && String(
                                    selectedSubsubcategoryId) === String(ss.id) ? 'selected' : '';
                                $subsub.append('<option value="' + ss.id + '" ' + selected + '>' + ss.name +
                                    '</option>');
                            });
                        }
                    }
                });
            }

            function loadSecondarySubcategories(categoryId) {
                const $subcat = $('#secondary_subcategory_id');
                const $wrap = $('#secondary_subcategory_wrap');
                $subcat.empty().append('<option value="">{{ __('Select') }}</option>');

                if (!categoryId) {
                    $wrap.addClass('d-none');
                    return;
                }

                $.getJSON('{{ url('panel/productos') }}/' + categoryId + '/subcategorias', function(response) {
                    const items = response.subcategory || [];
                    if (!items.length) {
                        $wrap.addClass('d-none');
                        return;
                    }
                    $wrap.removeClass('d-none');
                    items.forEach(function(s) {
                        $subcat.append('<option value="' + s.id + '">' + s.name + '</option>');
                    });
                });
            }

            function updateEmptyState(container) {
                const empty = container.querySelector('[data-empty]');
                const hasItems = container.querySelector('[data-id]');
                if (empty) {
                    empty.style.display = hasItems ? 'none' : 'block';
                }
            }

            function addSelectableItem(selectId, listId, inputName) {
                const select = document.getElementById(selectId);
                const list = document.getElementById(listId);
                const id = select.value;
                const label = select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : '';

                if (!id || !list || list.querySelector('[data-id="' + id + '"]')) {
                    return;
                }

                const badge = document.createElement('span');
                badge.className = 'badge badge-light-primary d-flex align-items-center mr-1 mb-1';
                badge.setAttribute('data-id', id);
                badge.innerHTML =
                    '<span class="mr-1">' + label + '</span>' +
                    '<input type="hidden" name="' + inputName + '[]" value="' + id + '">' +
                    '<button type="button" class="btn btn-sm btn-flat-danger p-0 ml-1" data-remove="' + inputName + '" aria-label="{{ __('Remove') }}">' +
                    '<i data-feather="x"></i>' +
                    '</button>';

                list.appendChild(badge);
                updateEmptyState(list);

                if (feather) {
                    feather.replace();
                }
            }

            function addSecondaryCategoryItem() {
                const select = document.getElementById('secondary_category_id');
                const list = document.getElementById('secondary-category-list');
                const id = select ? select.value : '';
                const label = select && select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : '';

                if (!id) {
                    showToast('warning', '{{ __('Select a secondary category') }}');
                    return;
                }

                if (!list || list.querySelector('[data-id="' + id + '"]')) {
                    showToast('warning', '{{ __('Secondary category already added') }}');
                    return;
                }

                const badge = document.createElement('span');
                badge.className = 'badge badge-light-primary d-flex align-items-center mr-1 mb-1';
                badge.setAttribute('data-id', id);
                badge.innerHTML =
                    '<span class="mr-1">' + label + '</span>' +
                    '<input type="hidden" name="secondary_categories[]" value="' + id + '">' +
                    '<button type="button" class="btn btn-sm btn-flat-danger p-0 ml-1" data-remove="secondary_categories" aria-label="{{ __('Remove') }}">' +
                    '<i data-feather="x"></i>' +
                    '</button>';

                list.appendChild(badge);
                updateEmptyState(list);

                if (feather) {
                    feather.replace();
                }

                $('#secondary-category-form').addClass('d-none');
            }

            function addSecondarySubcategoryItem() {
                const categorySelect = document.getElementById('secondary_category_id');
                const subcategorySelect = document.getElementById('secondary_subcategory_id');
                const list = document.getElementById('secondary-subcategory-list');
                const id = subcategorySelect ? subcategorySelect.value : '';
                const label = subcategorySelect && subcategorySelect.options[subcategorySelect.selectedIndex] ?
                    subcategorySelect.options[subcategorySelect.selectedIndex].text :
                    '';
                const categoryLabel = categorySelect && categorySelect.options[categorySelect.selectedIndex] ?
                    categorySelect.options[categorySelect.selectedIndex].text :
                    '';

                if (!categorySelect || !categorySelect.value) {
                    showToast('warning', '{{ __('Select a secondary category') }}');
                    return;
                }

                if (!id) {
                    showToast('warning', '{{ __('Select a secondary subcategory') }}');
                    return;
                }

                if (!list || list.querySelector('[data-id="' + id + '"]')) {
                    showToast('warning', '{{ __('Secondary subcategory already added') }}');
                    return;
                }

                addSecondaryCategoryItem();

                const badge = document.createElement('span');
                badge.className = 'badge badge-light-primary d-flex align-items-center mr-1 mb-1';
                badge.setAttribute('data-id', id);
                badge.innerHTML =
                    '<span class="mr-1">' + categoryLabel + ' / ' + label + '</span>' +
                    '<input type="hidden" name="secondary_subcategories[]" value="' + id + '">' +
                    '<button type="button" class="btn btn-sm btn-flat-danger p-0 ml-1" data-remove="secondary_subcategories" aria-label="{{ __('Remove') }}">' +
                    '<i data-feather="x"></i>' +
                    '</button>';

                list.appendChild(badge);
                updateEmptyState(list);

                if (feather) {
                    feather.replace();
                }

                $('#secondary-category-form').addClass('d-none');
            }

            function wireRemove(listId) {
                const list = document.getElementById(listId);
                if (!list) {
                    return;
                }

                list.addEventListener('click', function(event) {
                    const removeButton = event.target.closest('button[data-remove]');
                    if (!removeButton) {
                        return;
                    }
                    const badge = removeButton.closest('[data-id]');
                    if (badge) {
                        badge.remove();
                        updateEmptyState(list);
                    }
                });
            }

            $(function() {
                const currentCategory = '{{ $product->category_id }}';
                const currentSubcategory = '{{ $product->subcategory_id }}';
                const currentSubsubcategory = '{{ $product->subsubcategory_id }}';

                loadSubcategories(currentCategory, currentSubcategory, currentSubsubcategory);

                $('#category_id').on('change', function() {
                    loadSubcategories($(this).val(), null, null);
                });

                $('#subcategory_id').on('change', function() {
                    const categoryId = $('#category_id').val();
                    const selectedSubcategoryId = $(this).val();
                    loadSubcategories(categoryId, selectedSubcategoryId, null);
                });

                $('#toggle-secondary-category-form').on('click', function() {
                    $('#secondary-category-form').toggleClass('d-none');
                });

                $('#secondary_category_id').on('change', function() {
                    loadSecondarySubcategories($(this).val());
                });

                $('#add-secondary-category-item-btn').on('click', function() {
                    addSecondaryCategoryItem();
                });

                $('#add-secondary-subcategory-btn').on('click', function() {
                    addSecondarySubcategoryItem();
                });

                $('#add-tag-btn').on('click', function() {
                    $('#tag-form').toggleClass('d-none');
                });

                $('#add-tag-item-btn').on('click', function() {
                    const selectedId = $('#tag_select').val();
                    const list = document.getElementById('tag-list');

                    if (!selectedId) {
                        showToast('warning', '{{ __('Select a tag') }}');
                        return;
                    }

                    if (list && list.querySelector('[data-id="' + selectedId + '"]')) {
                        showToast('warning', '{{ __('Tag already added') }}');
                        return;
                    }

                    addSelectableItem('tag_select', 'tag-list', 'tags');
                    $('#tag-form').addClass('d-none');
                });

                wireRemove('secondary-category-list');
                wireRemove('secondary-subcategory-list');
                wireRemove('tag-list');
            });

            function previewImage(input, containerId) {
                const container = document.getElementById(containerId);
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        container.innerHTML = '<img src="' + e.target.result +
                            '" class="img-fluid" style="max-height: 300px;">';
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
