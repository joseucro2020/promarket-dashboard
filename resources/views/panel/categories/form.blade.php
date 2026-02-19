@extends('layouts/contentLayoutMaster')

@section('title', isset($category) ? __('locale.Edit Category') : __('locale.New Category'))

@section('content')
<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
          <div class="card-header">
          <h4 class="card-title">{{ isset($category) ? __('locale.Edit Category') : __('locale.New Category') }}</h4>
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

          @php
            $initialSubcategories = old('subcategories');
            if ($initialSubcategories === null) {
              $initialSubcategories = isset($category)
                ? $category->subcategories->map(function ($sub) {
                  return [
                    'id' => $sub->id,
                    'name' => $sub->name,
                    'name_english' => $sub->name_english,
                    'slug' => $sub->slug,
                    'icon' => $sub->icon,
                    'sub_subcategories' => $sub->sub_subcategories->map(function ($subSub) {
                      return [
                        'id' => $subSub->id,
                        'name' => $subSub->name,
                        'name_english' => $subSub->name_english,
                        'slug' => $subSub->slug,
                      ];
                    })->toArray(),
                  ];
                })->toArray()
                : [];
            }
          @endphp

          <form method="POST" action="{{ isset($category) ? route('categories.update', $category->id) : route('categories.store') }}" enctype="multipart/form-data">
            @csrf
            @if(isset($category))
              @method('PUT')
            @endif

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name">{{ __('locale.Category (Spanish)') }}</label>
                  <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="name_english">{{ __('locale.Category (English)') }}</label>
                  <input type="text" id="name_english" name="name_english" class="form-control" value="{{ old('name_english', $category->name_english ?? '') }}">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-10">
                <div class="form-group">
                  <label for="slug">{{ __('locale.Slug') }}</label>
                  <input type="text" id="slug" name="slug" class="form-control" value="{{ old('slug', $category->slug ?? '') }}">
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label for="order">{{ __('locale.Order') }}</label>
                  <input type="number" id="order" name="order" class="form-control" min="0" value="{{ old('order', $category->order ?? 0) }}">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('locale.Can be paid with PayPal') }}</label>
                  <div class="d-flex flex-wrap align-items-center">
                    <div class="custom-control custom-radio mr-2">
                      <input type="radio" id="paypal_yes" name="paypal" class="custom-control-input" value="1" {{ old('paypal', $category->paypal ?? '1') == '1' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="paypal_yes">{{ __('locale.Yes') }}</label>
                    </div>
                    <div class="custom-control custom-radio">
                      <input type="radio" id="paypal_no" name="paypal" class="custom-control-input" value="0" {{ old('paypal', $category->paypal ?? '1') == '0' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="paypal_no">{{ __('locale.No') }}</label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('locale.Can be paid with Stripe') }}</label>
                  <div class="d-flex flex-wrap align-items-center">
                    <div class="custom-control custom-radio mr-2">
                      <input type="radio" id="stripe_yes" name="stripe" class="custom-control-input" value="1" {{ old('stripe', $category->stripe ?? '1') == '1' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="stripe_yes">{{ __('locale.Yes') }}</label>
                    </div>
                    <div class="custom-control custom-radio">
                      <input type="radio" id="stripe_no" name="stripe" class="custom-control-input" value="0" {{ old('stripe', $category->stripe ?? '1') == '0' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="stripe_no">{{ __('locale.No') }}</label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('locale.Status') }}</label>
                  <div class="d-flex flex-wrap align-items-center">
                    <div class="custom-control custom-radio mr-2">
                      <input type="radio" id="status_active" name="status" class="custom-control-input" value="1" {{ old('status', $category->status ?? '1') == '1' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="status_active">{{ __('locale.Active') }}</label>
                    </div>
                    <div class="custom-control custom-radio">
                      <input type="radio" id="status_inactive" name="status" class="custom-control-input" value="0" {{ old('status', $category->status ?? '1') == '0' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="status_inactive">{{ __('locale.Inactive') }}</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('locale.Image') }}</label>
                  <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="height:160px;">
                    @if(isset($category) && !empty($category->image))
                      <img src="{{ asset($category->image) }}" alt="{{ $category->name ?? __('locale.Image') }}" class="img-fluid" style="max-height:140px;">
                    @else
                      <i data-feather="image"></i>
                    @endif
                  </div>
                  <input type="file" name="image" class="form-control mt-1" accept="image/png,image/jpeg">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('locale.Icon') }}</label>
                  <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="height:160px;">
                    @if(isset($category) && !empty($category->icon))
                      <img src="{{ asset($category->icon) }}" alt="{{ $category->name ?? __('locale.Icon') }}" class="img-fluid" style="max-height:140px;">
                    @else
                      <i data-feather="image"></i>
                    @endif
                  </div>
                  <input type="file" name="icon" class="form-control mt-1" accept="image/png,image/jpeg">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('locale.Slider') }}</label>
                  <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="height:160px;">
                    @if(isset($category) && !empty($category->icon2))
                      <img src="{{ asset($category->icon2) }}" alt="{{ $category->name ?? __('locale.Slider') }}" class="img-fluid" style="max-height:140px;">
                    @else
                      <i data-feather="image"></i>
                    @endif
                  </div>
                  <input type="file" name="icon2" class="form-control mt-1" accept="image/png,image/jpeg">
                </div>
              </div>
            </div>

            <hr>
            <div class="d-flex align-items-center mb-1">
              <button type="button" id="add-subcategory-btn" class="btn btn-primary btn-icon rounded-circle mr-1">
                <i data-feather="plus"></i>
              </button>
              <span class="font-weight-bold">{{ __('locale.Add Subcategory') }}</span>
            </div>

            <div id="subcategory-list">
              @forelse($initialSubcategories as $index => $sub)
                <div class="subcategory-item border rounded p-2 mb-2" data-index="{{ $index }}" data-subsub-index="{{ isset($sub['sub_subcategories']) ? count($sub['sub_subcategories']) : 0 }}">
                  <input type="hidden" name="subcategories[{{ $index }}][id]" value="{{ $sub['id'] ?? '' }}">
                  <input type="hidden" class="subcategory-delete" name="subcategories[{{ $index }}][delete]" value="0">

                  <div class="row align-items-start">
                    <div class="col-md-5">
                      <div class="form-group">
                        <label>{{ __('Subcategory (Spanish)') }}</label>
                        <input type="text" name="subcategories[{{ $index }}][name]" class="form-control" value="{{ $sub['name'] ?? '' }}">
                      </div>
                    </div>
                    <div class="col-md-5">
                      <div class="form-group">
                        <label>{{ __('Subcategory (English)') }}</label>
                        <input type="text" name="subcategories[{{ $index }}][name_english]" class="form-control" value="{{ $sub['name_english'] ?? '' }}">
                      </div>
                    </div>
                    <div class="col-md-2 text-right">
                      <button type="button" class="btn btn-icon btn-flat-danger subcategory-remove" title="{{ __('Delete') }}">
                        <i data-feather="trash-2"></i>
                      </button>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-8">
                      <div class="form-group">
                        <label>{{ __('Slug') }}</label>
                        <input type="text" name="subcategories[{{ $index }}][slug]" class="form-control" value="{{ $sub['slug'] ?? '' }}">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>{{ __('Subcategory Slider') }}</label>
                        <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="height:120px;">
                          @if(!empty($sub['icon']))
                            <img src="{{ asset($sub['icon']) }}" alt="{{ $sub['name'] ?? __('Subcategory Slider') }}" class="img-fluid" style="max-height:100px;">
                          @else
                            <i data-feather="image"></i>
                          @endif
                        </div>
                        <input type="file" name="subcategories[{{ $index }}][icon]" class="form-control mt-1" accept="image/png,image/jpeg">
                      </div>
                    </div>
                  </div>

                  <div class="border-top pt-2 mt-2">
                    <div class="d-flex align-items-center mb-1">
                      <button type="button" class="btn btn-outline-primary btn-sm subsubcategory-add">
                        <i data-feather="plus" class="mr-50"></i>{{ __('locale.Add Sub-Subcategory') }}
                      </button>
                    </div>
                    <div class="subsubcategory-list">
                      @if(!empty($sub['sub_subcategories']))
                        @foreach($sub['sub_subcategories'] as $subIndex => $subSub)
                          <div class="subsubcategory-item border rounded p-1 mb-1">
                            <input type="hidden" name="subcategories[{{ $index }}][sub_subcategories][{{ $subIndex }}][id]" value="{{ $subSub['id'] ?? '' }}">
                            <input type="hidden" class="subsubcategory-delete" name="subcategories[{{ $index }}][sub_subcategories][{{ $subIndex }}][delete]" value="0">
                            <div class="row align-items-start">
                              <div class="col-md-5">
                                <div class="form-group">
                                  <label>{{ __('locale.Sub-Subcategory (Spanish)') }}</label>
                                  <input type="text" name="subcategories[{{ $index }}][sub_subcategories][{{ $subIndex }}][name]" class="form-control" value="{{ $subSub['name'] ?? '' }}">
                                </div>
                              </div>
                              <div class="col-md-5">
                                <div class="form-group">
                                  <label>{{ __('locale.Sub-Subcategory (English)') }}</label>
                                  <input type="text" name="subcategories[{{ $index }}][sub_subcategories][{{ $subIndex }}][name_english]" class="form-control" value="{{ $subSub['name_english'] ?? '' }}">
                                </div>
                              </div>
                              <div class="col-md-2 text-right">
                                <button type="button" class="btn btn-icon btn-flat-danger subsubcategory-remove" title="{{ __('locale.Delete') }}">
                                  <i data-feather="trash-2"></i>
                                </button>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col-md-8">
                                <div class="form-group">
                                  <label>{{ __('locale.Slug') }}</label>
                                  <input type="text" name="subcategories[{{ $index }}][sub_subcategories][{{ $subIndex }}][slug]" class="form-control" value="{{ $subSub['slug'] ?? '' }}">
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-group">
                                  <label>{{ __('locale.Sub-Subcategory Slider') }}</label>
                                  <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="height:120px;">
                                    @if(!empty($subSub['icon']))
                                      <img src="{{ asset($subSub['icon']) }}" alt="{{ $subSub['name'] ?? __('Sub-Subcategory Slider') }}" class="img-fluid" style="max-height:100px;">
                                    @else
                                      <i data-feather="image"></i>
                                    @endif
                                  </div>
                                  <input type="file" name="subcategories[{{ $index }}][sub_subcategories][{{ $subIndex }}][icon]" class="form-control mt-1" accept="image/png,image/jpeg">
                                </div>
                              </div>
                            </div>
                          </div>
                        @endforeach
                      @endif
                    </div>
                  </div>
                </div>
                @empty
                <div class="text-muted">{{ __('locale.No subcategories added yet.') }}</div>
              @endforelse
            </div>

            <small class="text-muted">{{ __('locale.Sub-subcategories with products cannot be deleted.') }}</small>

            <div class="mt-4 d-flex justify-content-end">
              <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary mr-2">{{ __('locale.Back') }}</a>
              <button type="submit" class="btn btn-primary">{{ isset($category) ? __('locale.Update') : __('locale.Save') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@section('page-script')
  <script>
    (function () {
      const list = document.getElementById('subcategory-list');
      const addBtn = document.getElementById('add-subcategory-btn');
      const strings = {
        subcategoryEs: @json(__('locale.Subcategory (Spanish)')),
        subcategoryEn: @json(__('locale.Subcategory (English)')),
        subSubcategoryEs: @json(__('locale.Sub-Subcategory (Spanish)')),
        subSubcategoryEn: @json(__('locale.Sub-Subcategory (English)')),
        addSubSubcategory: @json(__('locale.Add Sub-Subcategory')),
        slug: @json(__('locale.Slug')),
        slider: @json(__('locale.Subcategory Slider')),
        subSubSlider: @json(__('locale.Sub-Subcategory Slider')),
        deleteLabel: @json(__('locale.Delete'))
      };

      let subcategoryIndex = {{ count($initialSubcategories) }};

      function subcategoryTemplate(index) {
        return `
          <div class="subcategory-item border rounded p-2 mb-2" data-index="${index}" data-subsub-index="0">
            <input type="hidden" name="subcategories[${index}][delete]" class="subcategory-delete" value="0">
            <div class="row align-items-start">
              <div class="col-md-5">
                <div class="form-group">
                  <label>${strings.subcategoryEs}</label>
                  <input type="text" name="subcategories[${index}][name]" class="form-control">
                </div>
              </div>
              <div class="col-md-5">
                <div class="form-group">
                  <label>${strings.subcategoryEn}</label>
                  <input type="text" name="subcategories[${index}][name_english]" class="form-control">
                </div>
              </div>
              <div class="col-md-2 text-right">
                <button type="button" class="btn btn-icon btn-flat-danger subcategory-remove" title="${strings.deleteLabel}">
                  <i data-feather="trash-2"></i>
                </button>
              </div>
            </div>
            <div class="row">
              <div class="col-md-8">
                <div class="form-group">
                  <label>${strings.slug}</label>
                  <input type="text" name="subcategories[${index}][slug]" class="form-control">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>${strings.slider}</label>
                  <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="height:120px;">
                    <i data-feather="image"></i>
                  </div>
                  <input type="file" name="subcategories[${index}][icon]" class="form-control mt-1" accept="image/png,image/jpeg">
                </div>
              </div>
            </div>
            <div class="border-top pt-2 mt-2">
              <div class="d-flex align-items-center mb-1">
                <button type="button" class="btn btn-outline-primary btn-sm subsubcategory-add">
                  <i data-feather="plus" class="mr-50"></i>${strings.addSubSubcategory}
                </button>
              </div>
              <div class="subsubcategory-list"></div>
            </div>
          </div>
        `;
      }

      function subSubcategoryTemplate(subIndex, subSubIndex) {
        return `
          <div class="subsubcategory-item border rounded p-1 mb-1">
            <input type="hidden" name="subcategories[${subIndex}][sub_subcategories][${subSubIndex}][delete]" class="subsubcategory-delete" value="0">
            <div class="row align-items-start">
              <div class="col-md-5">
                <div class="form-group">
                  <label>${strings.subSubcategoryEs}</label>
                  <input type="text" name="subcategories[${subIndex}][sub_subcategories][${subSubIndex}][name]" class="form-control">
                </div>
              </div>
              <div class="col-md-5">
                <div class="form-group">
                  <label>${strings.subSubcategoryEn}</label>
                  <input type="text" name="subcategories[${subIndex}][sub_subcategories][${subSubIndex}][name_english]" class="form-control">
                </div>
              </div>
              <div class="col-md-2 text-right">
                <button type="button" class="btn btn-icon btn-flat-danger subsubcategory-remove" title="${strings.deleteLabel}">
                  <i data-feather="trash-2"></i>
                </button>
              </div>
            </div>
            <div class="row">
              <div class="col-md-8">
                <div class="form-group">
                  <label>${strings.slug}</label>
                  <input type="text" name="subcategories[${subIndex}][sub_subcategories][${subSubIndex}][slug]" class="form-control">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>${strings.subSubSlider}</label>
                  <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="height:120px;">
                    <i data-feather="image"></i>
                  </div>
                  <input type="file" name="subcategories[${subIndex}][sub_subcategories][${subSubIndex}][icon]" class="form-control mt-1" accept="image/png,image/jpeg">
                </div>
              </div>
            </div>
          </div>
        `;
      }

      if (addBtn && list) {
        addBtn.addEventListener('click', function () {
          list.insertAdjacentHTML('beforeend', subcategoryTemplate(subcategoryIndex));
          subcategoryIndex += 1;
          if (window.feather) {
            feather.replace({ width: 14, height: 14 });
          }
        });
      }

      if (list) {
        list.addEventListener('click', function (event) {
          const removeBtn = event.target.closest('.subcategory-remove');
          if (removeBtn) {
            const item = removeBtn.closest('.subcategory-item');
            const deleteInput = item.querySelector('.subcategory-delete');
            if (deleteInput) {
              deleteInput.value = '1';
              item.classList.add('d-none');
            } else {
              item.remove();
            }
            return;
          }

          const addSubBtn = event.target.closest('.subsubcategory-add');
          if (addSubBtn) {
            const parent = addSubBtn.closest('.subcategory-item');
            const subIndex = parent.dataset.index;
            const subList = parent.querySelector('.subsubcategory-list');
            const nextIndex = parseInt(parent.dataset.subsubIndex || '0', 10);
            subList.insertAdjacentHTML('beforeend', subSubcategoryTemplate(subIndex, nextIndex));
            parent.dataset.subsubIndex = nextIndex + 1;
            if (window.feather) {
              feather.replace({ width: 14, height: 14 });
            }
            return;
          }

          const removeSubBtn = event.target.closest('.subsubcategory-remove');
          if (removeSubBtn) {
            const item = removeSubBtn.closest('.subsubcategory-item');
            const deleteInput = item.querySelector('.subsubcategory-delete');
            if (deleteInput) {
              deleteInput.value = '1';
              item.classList.add('d-none');
            } else {
              item.remove();
            }
          }
        });
      }
    })();
  </script>
@endsection
