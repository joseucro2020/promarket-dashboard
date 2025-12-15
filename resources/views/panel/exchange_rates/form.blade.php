@extends('layouts/contentLayoutMaster')

@section('title', isset($rate) ? 'Editar tasa' : 'Nueva tasa')

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">{{ isset($rate) ? 'Editar tasa' : 'Nueva tasa' }}</h4>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ isset($rate) ? route('exchange-rates.update', $rate->id) : route('exchange-rates.store') }}">
          @csrf
          @if(isset($rate))
            @method('PUT')
          @endif

          <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="datetime-local" name="date" class="form-control" value="{{ old('date', isset($rate) ? $rate->created_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Moneda origen</label>
            <input type="text" name="currency_from" class="form-control" value="{{ old('currency_from', $rate->currency_from ?? 'USD') }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Moneda destino</label>
            <input type="text" name="currency_to" class="form-control" value="{{ old('currency_to', $rate->currency_to ?? 'PEN') }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Tasa</label>
            <input type="number" step="0.0001" name="rate" class="form-control" value="{{ old('rate', $rate->change ?? '') }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Notas</label>
            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $rate->notes ?? '') }}</textarea>
          </div>

          <div class="d-flex justify-content-end">
            <button class="btn btn-primary me-2">{{ isset($rate) ? 'Actualizar' : 'Crear' }}</button>
            <a href="{{ route('exchange-rates.index') }}" class="btn btn-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
