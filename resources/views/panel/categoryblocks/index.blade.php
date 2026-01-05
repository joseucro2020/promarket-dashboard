@extends('layouts/contentLayoutMaster')

@section('title', __('Product Batch Changes'))

@section('content')
<div class="container">
  <h2 class="text-center mb-3">{{ __('Product Batch Changes') }}</h2>

  <div class="row">
    <div class="col-md-6">
      <fieldset class="border p-2 h-100">
        <legend class="w-auto px-2 mb-0" style="font-size: 0.9rem;">{{ __('Product Search by Categories') }}</legend>

        <div class="row mt-1">
          <div class="col-md-4">
            <label for="sCategory">{{ __('Category') }}</label>
            <select id="sCategory" class="form-control"></select>
          </div>
          <div class="col-md-4">
            <label for="sSubcategory">{{ __('Subcategory') }}</label>
            <select id="sSubcategory" class="form-control"></select>
          </div>
          <div class="col-md-4">
            <label for="sSubsubcategory">{{ __('Sub Subcategory') }}</label>
            <select id="sSubsubcategory" class="form-control"></select>
          </div>
        </div>

        <div class="mt-2">
          <button type="button" id="btnSearch" class="btn btn-danger">{{ __('Search') }}</button>
        </div>

        <div class="table-responsive mt-2">
          <table class="table" id="productsTable">
            <thead>
              <tr>
                <th>{{ __('ID') }}</th>
                <th>{{ __('Name') }}</th>
              </tr>
            </thead>
            <tbody id="productsBody">
              <tr><td colspan="2">{{ __('No records found.') }}</td></tr>
            </tbody>
          </table>
        </div>
      </fieldset>
    </div>

    <div class="col-md-6">
      <fieldset class="border p-2 h-100">
        <legend class="w-auto px-2 mb-0" style="font-size: 0.9rem;">{{ __('Category Changes to Products') }}</legend>

        <div class="row mt-1">
          <div class="col-md-4">
            <label for="cCategory">{{ __('Category') }}</label>
            <select id="cCategory" class="form-control"></select>
          </div>
          <div class="col-md-4">
            <label for="cSubcategory">{{ __('Subcategory') }}</label>
            <select id="cSubcategory" class="form-control"></select>
          </div>
          <div class="col-md-4">
            <label for="cSubsubcategory">{{ __('Sub Subcategory') }}</label>
            <select id="cSubsubcategory" class="form-control"></select>
          </div>
        </div>

        <div class="mt-2">
          <button type="button" id="btnProcess" class="btn btn-danger">{{ __('Process Change') }}</button>
        </div>
      </fieldset>
    </div>
  </div>
</div>

@push('scripts')
<script>
const categories = @json($categories);
const csrfToken = '{{ csrf_token() }}';

function buildSelect(selectEl, items, placeholderText){
  selectEl.innerHTML = '';
  const opt0 = document.createElement('option');
  opt0.value = '';
  opt0.textContent = placeholderText;
  selectEl.appendChild(opt0);

  (items || []).forEach(item => {
    const opt = document.createElement('option');
    opt.value = String(item.id);
    opt.textContent = item.name ?? '';
    selectEl.appendChild(opt);
  });
}

function getSelectedCategory(categoryId){
  return categories.find(c => String(c.id) === String(categoryId));
}

function getSelectedSubcategory(category, subcategoryId){
  if(!category || !Array.isArray(category.subcategories)) return null;
  return category.subcategories.find(s => String(s.id) === String(subcategoryId));
}

function wireCascades(prefix){
  const selCategory = document.getElementById(prefix + 'Category');
  const selSubcategory = document.getElementById(prefix + 'Subcategory');
  const selSubsubcategory = document.getElementById(prefix + 'Subsubcategory');

  buildSelect(selCategory, categories, '{{ __('Select') }}');
  buildSelect(selSubcategory, [], '{{ __('Select') }}');
  buildSelect(selSubsubcategory, [], '{{ __('Select') }}');

  selCategory.addEventListener('change', () => {
    const cat = getSelectedCategory(selCategory.value);
    buildSelect(selSubcategory, cat?.subcategories ?? [], '{{ __('Select') }}');
    buildSelect(selSubsubcategory, [], '{{ __('Select') }}');
  });

  selSubcategory.addEventListener('change', () => {
    const cat = getSelectedCategory(selCategory.value);
    const sub = getSelectedSubcategory(cat, selSubcategory.value);
    buildSelect(selSubsubcategory, sub?.sub_subcategories ?? [], '{{ __('Select') }}');
  });
}

function renderProducts(rows){
  const tbody = document.getElementById('productsBody');
  tbody.innerHTML = '';

  if(!Array.isArray(rows) || rows.length === 0){
    const tr = document.createElement('tr');
    tr.innerHTML = `<td colspan="2">{{ __('No records found.') }}</td>`;
    tbody.appendChild(tr);
    return;
  }

  rows.forEach(p => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${p.id ?? ''}</td>
      <td>${p.name ?? ''}</td>
    `;
    tbody.appendChild(tr);
  });
}

async function postJson(url, payload){
  const res = await fetch(url, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify(payload),
  });

  if(!res.ok){
    const txt = await res.text();
    throw new Error(txt || `HTTP ${res.status}`);
  }

  return await res.json();
}

async function doSearch(){
  const category = document.getElementById('sCategory').value || null;
  const subcategory = document.getElementById('sSubcategory').value || null;
  const subsubcategory = document.getElementById('sSubsubcategory').value || null;

  try {
    const url = `{{ url('panel/cambios-productos-lotes/search') }}`;
    const products = await postJson(url, { category, subcategory, subsubcategory });
    renderProducts(products);
  } catch (e) {
    console.error(e);
    alert('Error cargando productos. Revisa consola / logs.');
  }
}

async function doProcess(){
  const category = document.getElementById('sCategory').value || null;
  const subcategory = document.getElementById('sSubcategory').value || null;
  const subsubcategory = document.getElementById('sSubsubcategory').value || null;

  const ccategory = document.getElementById('cCategory').value || null;
  const csubcategory = document.getElementById('cSubcategory').value || null;
  const csubsubcategory = document.getElementById('cSubsubcategory').value || null;

  if(!ccategory){
    alert('{{ __('Select destination category') }}');
    return;
  }

  try {
    const url = `{{ url('panel/cambios-productos-lotes/update') }}`;
    await postJson(url, { category, subcategory, subsubcategory, ccategory, csubcategory, csubsubcategory });
    await doSearch();
  } catch (e) {
    console.error(e);
    alert('Error procesando cambio. Revisa consola / logs.');
  }
}

document.addEventListener('DOMContentLoaded', function(){
  wireCascades('s');
  wireCascades('c');

  document.getElementById('btnSearch').addEventListener('click', doSearch);
  document.getElementById('btnProcess').addEventListener('click', doProcess);
});
</script>
@endpush

@endsection
