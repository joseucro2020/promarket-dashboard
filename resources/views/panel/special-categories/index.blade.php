@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Special Categories'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('page-style')
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('css/base/pages/app-user.css')) }}">
@endsection

@section('content')
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom p-1">
          <div class="head-label">
            <h4 class="mb-0">{{ __('locale.Special Categories') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <button type="button" class="dt-button create-new btn btn-primary" data-toggle="modal" data-target="#specialCategoryModal" onclick="window.__openSpecialCategoryCreate()">
                <i data-feather="plus"></i> {{ __('locale.Add New') }}
              </button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover w-100 module-list-table special-categories-table" id="specialCategoriesTable">
          <thead>
            <tr>
              <th>{{ __('locale.Name') }}</th>
              <th>{{ __('locale.Order') }}</th>
              <th>{{ __('locale.Products to Show') }}</th>
              <th>{{ __('locale.Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($categories as $cat)
              <tr data-id="{{ $cat->id }}"
                  data-name="{{ $cat->name }}"
                  data-order="{{ $cat->order }}"
                  data-slider_quantity="{{ $cat->slider_quantity }}"
                  data-tipo_order="{{ $cat->tipo_order }}"
                  data-tipo_special="{{ $cat->tipo_special }}"
                  data-status="{{ $cat->status }}">
                <td>{{ $cat->id }} - {{ $cat->name }}</td>
                <td>{{ $cat->order }}</td>
                <td>{{ $cat->slider_quantity }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="custom-control custom-switch custom-switch-success mr-1">
                      <input type="checkbox" class="custom-control-input" id="sc_status_{{ $cat->id }}" {{ (int)$cat->status === 1 ? 'checked' : '' }} onchange="window.__toggleSpecialCategoryStatus({{ $cat->id }})" />
                      <label class="custom-control-label" for="sc_status_{{ $cat->id }}"></label>
                    </div>

                    <button type="button" class="btn btn-icon btn-flat-success" data-toggle="modal" data-target="#specialCategoryModal" onclick="window.__openSpecialCategoryEdit({{ $cat->id }})" title="{{ __('locale.Edit') }}">
                      <i data-feather="edit"></i>
                    </button>

                    <form action="{{ url('panel/categorias-especiales/'.$cat->id) }}" method="POST" class="ml-25" onsubmit="return confirm('{{ __('locale.Are you sure?') }}')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-icon btn-flat-danger" title="{{ __('locale.Delete') }}">
                        <i data-feather="trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Modal create/edit --}}
<div class="modal fade" id="specialCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="specialCategoryModalTitle">{{ __('locale.Add New') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="specialCategoryForm">
          @csrf
          <input type="hidden" id="sc_id" value="">

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="sc_name">{{ __('locale.Name') }}</label>
              <input type="text" class="form-control" id="sc_name" required>
            </div>
            <div class="form-group col-md-3">
              <label for="sc_order">{{ __('locale.Order') }}</label>
              <input type="number" class="form-control" id="sc_order" min="1">
            </div>
            <div class="form-group col-md-3">
              <label for="sc_slider_quantity">{{ __('locale.Products to Show') }}</label>
              <input type="number" class="form-control" id="sc_slider_quantity" min="1">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="sc_status">{{ __('locale.Status') }}</label>
              <select class="form-control" id="sc_status">
                <option value="1">{{ __('locale.Active') }}</option>
                <option value="0">{{ __('locale.Inactive') }}</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="sc_products">{{ __('locale.Products') }}</label>
            <select multiple class="form-control" id="sc_products" style="min-height: 180px;">
              @foreach($products as $p)
                <option value="{{ $p->id }}">{{ $p->id }} - {{ $p->name }}</option>
              @endforeach
            </select>
            <small class="form-text text-muted">{{ __('locale.Select Products') }}</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('locale.Cancel') }}</button>
        <button type="button" class="btn btn-primary" onclick="window.__saveSpecialCategory()">{{ __('locale.Save') }}</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
@endsection

@section('page-script')
<script>
  const csrfToken = '{{ csrf_token() }}';

  $(function() {
    $('.special-categories-table').DataTable({
      responsive: true,
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
      order: [[1, 'asc']],
      columnDefs: [{ orderable: false, targets: -1 }],
      drawCallback: function() { if (feather) { feather.replace({ width: 14, height: 14 }); } }
    });
  });

  function jsonSelectedValues(selectEl) {
    const values = [];
    for (const opt of selectEl.options) {
      if (opt.selected) values.push(parseInt(opt.value, 10));
    }
    return JSON.stringify(values);
  }

  window.__openSpecialCategoryCreate = function() {
    document.getElementById('specialCategoryModalTitle').innerText = '{{ __('Add New') }}';
    document.getElementById('sc_id').value = '';
    document.getElementById('sc_name').value = '';
    document.getElementById('sc_order').value = '';
    document.getElementById('sc_slider_quantity').value = '';
    document.getElementById('sc_status').value = '1';
    const productsSelect = document.getElementById('sc_products');
    for (const opt of productsSelect.options) opt.selected = false;
  }

  window.__openSpecialCategoryEdit = async function(id) {
    document.getElementById('specialCategoryModalTitle').innerText = '{{ __('Edit') }}';

    const row = document.querySelector('tr[data-id="' + id + '"]');
    document.getElementById('sc_id').value = id;
    document.getElementById('sc_name').value = row?.dataset?.name || '';
    document.getElementById('sc_order').value = row?.dataset?.order || '';
    document.getElementById('sc_slider_quantity').value = row?.dataset?.slider_quantity || '';
    document.getElementById('sc_status').value = (row?.dataset?.status ?? '1');

    const productsSelect = document.getElementById('sc_products');
    for (const opt of productsSelect.options) opt.selected = false;

    try {
      const res = await fetch(`{{ url('panel/categorias-especiales') }}/${id}/detail`, {
        headers: { 'X-CSRF-TOKEN': csrfToken }
      });
      const data = await res.json();
      const selected = (data.categories?.[0]?.products || []).map(p => String(p.id));
      for (const opt of productsSelect.options) {
        if (selected.includes(opt.value)) opt.selected = true;
      }
    } catch (e) {
      console.error(e);
    }
  }

  window.__saveSpecialCategory = async function() {
    const id = document.getElementById('sc_id').value;
    const payload = {
      name: document.getElementById('sc_name').value,
      order: document.getElementById('sc_order').value || null,
      slider_quantity: document.getElementById('sc_slider_quantity').value || null,
      status: document.getElementById('sc_status').value,
      products: jsonSelectedValues(document.getElementById('sc_products')),
    };

    const urlBase = '{{ url('panel/categorias-especiales') }}';
    const url = id ? `${urlBase}/${id}` : urlBase;
    const method = id ? 'POST' : 'POST';

    const formData = new FormData();
    formData.append('_token', csrfToken);
    if (id) formData.append('_method', 'PUT');
    for (const [k,v] of Object.entries(payload)) {
      if (v === null || typeof v === 'undefined') continue;
      formData.append(k, v);
    }

    const res = await fetch(url, { method, body: formData });
    if (!res.ok) {
      alert('{{ __('locale.Error') }}');
      return;
    }

    location.reload();
  }

  window.__toggleSpecialCategoryStatus = async function(id) {
    const url = `{{ url('panel/categorias-especiales') }}/${id}/status`;
    const formData = new FormData();
    formData.append('_token', csrfToken);

    const res = await fetch(url, { method: 'POST', body: formData });
    if (!res.ok) {
      alert('{{ __('locale.Error') }}');
      return;
    }
  }
</script>
@endsection
