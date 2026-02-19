@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Add Product'))

@section('content')
    <section id="multiple-column-form">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ __('locale.Add Product') }}</h4>
                        {{-- <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a> --}}
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <p class="mb-1">{{ __('Please check the form errors.') }}</p>
                                <ul class="mb-0 pl-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info"
                                        aria-controls="info" role="tab" aria-selected="true">{{ __('locale.Information') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="images-tab" data-toggle="tab" href="#images"
                                        aria-controls="images" role="tab" aria-selected="false">{{ __('locale.Images') }}</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Information Tab -->
                                <div class="tab-pane active" id="info" aria-labelledby="info-tab" role="tabpanel">

                                    <div class="row mt-2">
                                        <div class="col-md-12 text-center mb-2">
                                                <label class="custom-control-label"
                                                    for="is_active_hours">{{ __('locale.Active Hours') }}</label>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="typeSimple" name="type"
                                                        class="custom-control-input" value="simple" checked>
                                                    <label class="custom-control-label"
                                                <label>{{ __('locale.Time From') }}</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="typeVariable" name="type"
                                                        class="custom-control-input" value="variable">
                                                <label>{{ __('locale.Time To') }}</label>
                                                        for="typeVariable">{{ __('locale.Variable Product') }}</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('locale.Mark as PRO Product?') }}</label>
                                                <div class="custom-control custom-switch custom-switch-success">
                                                    <input type="checkbox" class="custom-control-input" id="is_pro"
                                                        name="is_pro">
                                                    <label class="custom-control-label"
                                                        for="is_pro">{{ __('locale.Yes') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('locale.Auto Update?') }}</label>
                                                <div class="custom-control custom-switch custom-switch-success">
                                                    <input type="checkbox" class="custom-control-input" id="auto_update"
                                                        name="auto_update">
                                                    <label class="custom-control-label"
                                                        for="auto_update">{{ __('locale.Yes') }}</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('locale.Min Stock to Deactivate') }}</label>
                                                <input type="number" class="form-control" name="min_stock_deactivate"
                                                    value="5">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('locale.Max Stock to Activate') }}</label>
                                                <input type="number" class="form-control" name="max_stock_activate"
                                                    value="10">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('locale.Name (Spanish)') }}</label>
                                                <input type="text" class="form-control" name="name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('locale.Name (English)') }}</label>
                                                <input type="text" class="form-control" name="name_english">
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>{{ __('locale.Slug') }}</label>
                                                <input type="text" class="form-control" name="slug" required>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="is_active_hours" name="is_active_hours">
                                                    <label class="custom-control-label"
                                                        for="is_active_hours">{{ __('locale.Active Hours') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('locale.Time From') }}</label>
                                                <input type="time" class="form-control" name="active_hours_start">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('locale.Time To') }}</label>
                                                <input type="time" class="form-control" name="active_hours_end">
                                            </div>
                                        </div>

                                        <!-- Categories -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('locale.Main Category') }}</label>
                                                <select class="form-control" id="category_id" name="category_id">
                                                    <option value="">{{ __('locale.Select') }}</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('locale.Sub Category') }}</label>
                                                <select class="form-control" id="subcategory_id" name="subcategory_id">
                                                    <option value="">{{ __('locale.Select') }}</option>
                                                    <!-- Subcategories should be loaded via AJAX based on Category -->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('locale.Sub-Sub Category') }}</label>
                                                <select class="form-control" id="subsubcategory_id" name="subsubcategory_id">
                                                    <option value="">{{ __('locale.Select') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex align-items-center mb-1">
                                                <button type="button" class="btn btn-flat-primary d-flex align-items-center p-0" id="toggle-secondary-category-form">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-1" style="width: 28px; height: 28px;">
                                                        <i data-feather="plus" style="width: 16px; height: 16px;"></i>
                                                    </div>
                                                    <span style="font-size: 1rem; font-weight: 600; color: #000;">{{ __('locale.Add secondary category') }}</span>
                                                </button>
                                            </div>
                                            <div id="secondary-category-form" class="border rounded p-2 mb-1 d-none">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>{{ __('locale.Secondary Category') }}</label>
                                                            <div class="d-flex align-items-center">
                                                                <select class="form-control" id="secondary_category_id">
                                                                    <option value="">{{ __('Select') }}</option>
                                                                    @foreach ($categories as $category)
                                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                    <button type="button" class="btn btn-flat-primary d-flex align-items-center justify-content-center ml-1" id="add-secondary-category-item-btn" aria-label="{{ __('locale.Add secondary category') }}">
                                                                    <i data-feather="plus"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 d-none" id="secondary_subcategory_wrap">
                                                        <div class="form-group">
                                                            <label>{{ __('locale.Secondary Subcategory') }}</label>
                                                            <div class="d-flex align-items-center">
                                                                <select class="form-control" id="secondary_subcategory_id">
                                                                    <option value="">{{ __('Select') }}</option>
                                                                </select>
                                                                    <button type="button" class="btn btn-flat-primary d-flex align-items-center justify-content-center ml-1" id="add-secondary-subcategory-btn" aria-label="{{ __('locale.Add secondary subcategory') }}">
                                                                    <i data-feather="plus"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="border rounded p-2 p-md-3 bg-white shadow-sm mt-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="text-uppercase small text-muted">{{ __('locale.Secondary Subcategory') }}</span>
                                                </div>
                                                <div id="secondary-subcategory-list" class="d-flex flex-wrap">
                                                    <p class="text-muted mb-0" data-empty>{{ __('locale.No secondary subcategories selected') }}</p>
                                                </div>
                                                <div class="mt-2">
                                                    <span class="text-uppercase small text-muted">{{ __('locale.Secondary Categories') }}</span>
                                                </div>
                                                <div id="secondary-category-list" class="d-flex flex-wrap mt-1">
                                                    <p class="text-muted mb-0" data-empty>{{ __('locale.No secondary categories selected') }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('locale.Description (Spanish)') }}</label>
                                                <textarea class="form-control" name="description" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('locale.Description (English)') }}</label>
                                                <textarea class="form-control" name="description_english" rows="3"></textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ __('locale.Price') }} $</label>
                                                <input type="number" step="0.01" class="form-control" name="price_1"
                                                    required>
                                            </div>
                                        </div>
                                        <!-- Stock not in fillable
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('Stock Quantity') }}</label>
                                    <input type="number" class="form-control" name="stock" disabled placeholder="N/A in DB">
                                </div>
                            </div>
                            -->
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ __('locale.Min Sale') }}</label>
                                                <input type="number" class="form-control" name="retail"
                                                    value="1">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ __('locale.Max Sale') }}</label>
                                                <input type="number" class="form-control" name="wholesale"
                                                    value="1">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ __('locale.Cost') }}</label>
                                                <input type="number" step="0.01" class="form-control" name="price_2"
                                                    required>
                                            </div>
                                        </div>
                                        <!-- Threshold not in fillable
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('Threshold') }}</label>
                                    <input type="number" class="form-control" name="threshold" value="1">
                                </div>
                            </div>
                            -->
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ __('locale.Tax') }}</label>
                                                <select class="form-control" name="taxe_id">
                                                    <option value="">{{ __('Select') }}</option>
                                                    @foreach ($taxes as $tax)
                                                        <option value="{{ $tax->id }}">{{ $tax->name }}
                                                            ({{ $tax->percentage }}%)</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="font-weight-bold">{{ __('locale.Tags') }}</label>
                                            <div class="d-flex align-items-center mb-1">
                                                <button type="button" class="btn btn-flat-primary d-flex align-items-center p-0" id="add-tag-btn">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-1" style="width: 28px; height: 28px;">
                                                        <i data-feather="plus" style="width: 16px; height: 16px;"></i>
                                                    </div>
                                                    <span style="font-size: 1rem; font-weight: 600; color: #000;">{{ __('locale.Add Tag') }}</span>
                                                </button>
                                            </div>
                                            <div id="tag-form" class="row d-none">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <div class="d-flex align-items-center">
                                                            <select class="form-control" id="tag_select">
                                                                <option value="">{{ __('locale.Select') }}</option>
                                                                @foreach ($tags as $tag)
                                                                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button"
                                                                class="btn btn-flat-primary d-flex align-items-center justify-content-center ml-1"
                                                                    id="add-tag-item-btn" aria-label="{{ __('locale.Add Tag') }}">
                                                                <i data-feather="plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="border rounded p-2 p-md-3 bg-white shadow-sm mt-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="text-uppercase small text-muted">{{ __('Tags') }}</span>
                                                </div>
                                                <div id="tag-list" class="d-flex flex-wrap">
                                                    <p class="text-muted mb-0" data-empty>{{ __('locale.No tags selected') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- SKU not in fillable
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('SKU') }}</label>
                                    <input type="text" class="form-control" name="sku">
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
                                                <label>{{ __('locale.Main Image') }}</label>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="image"
                                                        name="image">
                                                    <label class="custom-file-label"
                                                        for="image">{{ __('locale.Choose file') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-12">
                                    {{-- <button type="submit" class="btn btn-primary mr-1">{{ __('Save') }}</button>
                    <button type="reset" class="btn btn-outline-secondary">{{ __('Reset') }}</button> --}}


                                    <div class="mt-4 d-flex justify-content-end">
                                        <a href="{{ route('products.index') }}"
                                            class="btn btn-outline-secondary mr-2">{{ __('locale.Back') }}</a>
                                        <button type="submit" id="products-submit" class="btn btn-primary">{{ isset($products) ? __('locale.Update') : __('locale.Save') }}</button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

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
                const selected = selectedSubcategoryId && String(selectedSubcategoryId) === String(s.id) ? 'selected' : '';
                $subcat.append('<option value="' + s.id + '" ' + selected + '>' + s.name + '</option>');
            });

            if (selectedSubcategoryId) {
                const match = items.find(function(s) { return String(s.id) === String(selectedSubcategoryId); });
                if (match && match.sub_subcategories) {
                    match.sub_subcategories.forEach(function(ss) {
                        const selected = selectedSubsubcategoryId && String(selectedSubsubcategoryId) === String(ss.id) ? 'selected' : '';
                        $subsub.append('<option value="' + ss.id + '" ' + selected + '>' + ss.name + '</option>');
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
        const label = subcategorySelect && subcategorySelect.options[subcategorySelect.selectedIndex]
            ? subcategorySelect.options[subcategorySelect.selectedIndex].text
            : '';
        const categoryLabel = categorySelect && categorySelect.options[categorySelect.selectedIndex]
            ? categorySelect.options[categorySelect.selectedIndex].text
            : '';

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
</script>
@endsection
