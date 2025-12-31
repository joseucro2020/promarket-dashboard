@extends('layouts/contentLayoutMaster')

@section('title', __('Kromi Market'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection

@section('page-script')
  <script>
    $(function() {
      $('#kromi-products-table').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
        drawCallback: function() { if (feather) { feather.replace({width:14,height:14}); } }
      });

      $('#promarket-products-table').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
        drawCallback: function() { if (feather) { feather.replace({width:14,height:14}); } }
      });
    });
  </script>
@endsection

@section('content')
<!-- Upload CSV -->
<section class="mb-2">
  <div class="card">
    <div class="card-body">
      <form id="upload-csv-form" enctype="multipart/form-data" method="POST" action="{{ route('kromi.import_csv') }}">
        @csrf
        <div class="row align-items-center">
          <div class="col-10">
            <div class="form-group mb-0">
              <label class="d-block">Selecciona el archivo a cargar:</label>
              <input type="file" name="file" class="form-control">
            </div>
          </div>
          <div class="col-2 text-right">
            <button class="btn btn-warning mt-2">Cargar Datos</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>

<!-- Main two-column layout -->
<section>
  <div class="row">
    <!-- Left: Kromi Market -->
    <div class="col-lg-6 col-12">
      <div class="card mb-2">
        <div class="card-header">
          <h5 class="mb-0">Kromit Market</h5>
        </div>
        <div class="card-body">
          <div class="card mb-2">
            <div class="card-body">
              <h6>Filtros de busquedas</h6>
              <form id="kromi-filters">
                <div class="form-group">
                  <label>Categorías</label>
                  <select class="form-control">
                    <option value="">Todos</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Sub-Categorías</label>
                  <select class="form-control">
                    <option value="">Todos</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Sub-SubCategorías</label>
                  <select class="form-control">
                    <option value="">Todos</option>
                  </select>
                </div>
                <div class="form-group mb-0">
                  <label>Buscar por Nombre Productos o SKU</label>
                  <div class="input-group">
                    <input type="text" class="form-control" placeholder="Buscar...">
                    <div class="input-group-append">
                      <button class="btn btn-warning">Buscar</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">Listado Productos</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-striped" id="kromi-products-table">
                  <thead>
                    <tr>
                      <th style="width:30px;"><input type="checkbox"></th>
                      <th>SKU</th>
                      <th>Nombre</th>
                      <th>Costo</th>
                      <th>Cantidad</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Rows cargadas dinámicamente -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: PromarketLatino -->
    <div class="col-lg-6 col-12">
      <div class="card mb-2">
        <div class="card-header">
          <h5 class="mb-0">PromarketLatino</h5>
        </div>
        <div class="card-body">
          <div class="card mb-2">
            <div class="card-body">
              <h6>Categorías para Registrar</h6>
              <form id="promarket-mapping">
                <div class="form-group">
                  <label>Categorías</label>
                  <select class="form-control">
                    <option value="">Seleccione</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Subcategoria</label>
                  <select class="form-control">
                    <option>Todos</option>
                  </select>
                </div>
                <div class="form-group mb-0">
                  <label>Sub-Subcategoria</label>
                  <select class="form-control">
                    <option>Todos</option>
                  </select>
                </div>
                <div class="text-center mt-3">
                  <button class="btn btn-warning">Registrar</button>
                </div>
              </form>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">Listado Productos</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover" id="promarket-products-table">
                  <thead>
                    <tr>
                      <th>SKU</th>
                      <th>Nombre</th>
                      <th>Costo</th>
                      <th>Utilidad</th>
                      <th>Precio</th>
                      <th>Cantidad</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Rows cargadas dinámicamente -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
