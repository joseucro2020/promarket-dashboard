@extends('layouts/contentLayoutMaster')

@section('title', 'Reposición de Inventario')

@section('content')
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="mb-0">Reposición de Inventario</h4>
          <a href="{{ route('inventory.create') }}" class="btn btn-primary"><i data-feather="plus"></i> Agregar nuevo</a>
        </div>
        <div class="card-body">
          <p>Listado de reposiciones. Implementar datatable según necesidad.</p>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Usuario</th>
                  <th>Producto</th>
                  <th>Tipo</th>
                  <th>Cantidad</th>
                  <th>Fecha</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {{-- Datos a implementar --}}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
