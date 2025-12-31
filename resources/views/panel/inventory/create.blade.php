@extends('layouts/contentLayoutMaster')

@section('title', 'Nueva Reposición')

@section('content')
<section>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Agregar Reposición</h4>
        </div>
        <div class="card-body">
          <form action="{{ route('inventory.store') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-md-6 form-group">
                <label>Producto</label>
                <select name="product_id" class="form-control">
                  <option value="">Seleccione</option>
                  @foreach($products as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3 form-group">
                <label>Tipo</label>
                <select name="type" class="form-control">
                  <option value="entrada">Entrada</option>
                  <option value="salida">Salida</option>
                </select>
              </div>
              <div class="col-md-3 form-group">
                <label>Cantidad</label>
                <input type="number" name="quantity" class="form-control" step="1">
              </div>
            </div>
            <div class="form-group">
              <label>Razón</label>
              <textarea name="reason" class="form-control" rows="3"></textarea>
            </div>
            <button class="btn btn-primary">Guardar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
