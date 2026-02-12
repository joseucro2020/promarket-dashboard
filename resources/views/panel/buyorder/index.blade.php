@extends('layouts/contentLayoutMaster')

@section('title', __('Purchase Orders'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
@endsection

@section('content')
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom p-1">
          <div class="head-label">
            <h4 class="mb-0">{{ __('Ordenes de Compra') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <a href="{{ route('buyorders.create') }}" class="dt-button create-new btn btn-primary">
                <i data-feather="plus"></i> {{ __('Add New') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 buyorders-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>{{ __('Nro. Documento') }}</th>
                  <th>{{ __('Fecha') }}</th>
                  <th>{{ __('Fecha Vcto') }}</th>
                  <th>{{ __('Cond. Pago') }}</th>
                  <th>{{ __('Proveedor') }}</th>
                  <th>{{ __('Almacén') }}</th>
                  <th>{{ __('Estatus') }}</th>
                  <th>{{ __('Registro') }}</th>
                  <th class="text-end">{{ __('Acciones') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($orders ?? [] as $order)
                  <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->nro_doc }}</td>
                    <td data-order="{{ $order->fecha ? \Carbon\Carbon::parse($order->fecha)->format('Y-m-d') : '' }}">
                      {{ $order->fecha ? \Carbon\Carbon::parse($order->fecha)->format('d-m-Y') : '' }}
                    </td>
                    <td data-order="{{ $order->fecha_vto ? \Carbon\Carbon::parse($order->fecha_vto)->format('Y-m-d') : '' }}">
                      {{ $order->fecha_vto ? \Carbon\Carbon::parse($order->fecha_vto)->format('d-m-Y') : '' }}
                    </td>
                    <td>{{ $order->cond_pago ?? '—' }}</td>
                    <td>{{ $order->supplier->nombre_prove ?? $order->supplier->name ?? $order->supplier->nombre ?? '—' }}</td>
                    <td>{{ $order->almacen_id ?? '—' }}</td>
                    <td>{{ $order->status ?? '—' }}</td>
                    <td data-order="{{ $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '' }}">
                      {{ $order->created_at ? $order->created_at->format('d-m-Y H:i') : '' }}
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <a href="{{ route('buyorders.edit', $order->id) }}" class="btn btn-icon btn-flat-success mr-1" data-toggle="tooltip" data-placement="top" title="{{ __('Edit') }}">
                          <i data-feather="edit"></i>
                        </a>
                        <form class="m-0" action="{{ route('buyorders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('{{ __('Delete this order?') }}');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-icon btn-flat-danger" data-toggle="tooltip" data-placement="top" title="{{ __('Delete') }}">
                            <i data-feather="trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="10" class="text-center">{{ __('No se encontraron registros.') }}</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection

@section('page-script')
  <script>
    $(function(){
      $('.buyorders-table').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        order: [[2, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' }
      });
    });
  </script>
@endsection
