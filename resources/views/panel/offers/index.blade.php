@extends('layouts/contentLayoutMaster')

@section('title', __('Offers'))

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
            <h4 class="mb-0">{{ __('Offers') }}</h4>
          </div>
          <div class="dt-action-buttons text-right">
            <div class="dt-buttons d-inline-flex">
              <a href="{{ route('offers.create') }}" class="dt-button create-new btn btn-primary">
                <i data-feather="plus"></i> {{ __('Add New') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover w-100 module-list-table offers-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>{{ __('Start') }}</th>
                  <th>{{ __('End') }}</th>
                  <th>{{ __('Percentage') }}</th>
                  <th class="text-end">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                
                @foreach($offers as $offer)
                  <tr>
                    <td>{{ $offer->id }}</td>
                    <td>{{ optional($offer->start)->format('d-m-Y') }}</td>
                    <td>{{ optional($offer->end)->format('d-m-Y') }}</td>
                    <td>{{ number_format($offer->percentage, 2) }}%</td>
                    <td>
                      <div class="d-flex align-items-center">
                        <form class="m-0 mr-1" action="{{ route('offers.status', $offer) }}" method="POST">
                          @csrf
                          <div class="custom-control custom-switch custom-switch-success">
                            <input type="checkbox" class="custom-control-input" id="offer_status_{{ $offer->id }}" {{ $offer->status === \App\Models\Offer::ACTIVE ? 'checked' : '' }} onchange="this.form.submit()" />
                            <label class="custom-control-label" for="offer_status_{{ $offer->id }}"></label>
                          </div>
                        </form>
                        <a href="{{ route('offers.edit', $offer) }}" class="btn btn-icon btn-flat-success mr-1" data-toggle="tooltip" data-placement="top" title="{{ __('Edit') }}">
                          <i data-feather="edit"></i>
                        </a>
                        <form class="m-0" action="{{ route('offers.destroy', $offer) }}" method="POST" onsubmit="return confirm('{{ __('Delete this offer?') }}');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-icon btn-flat-danger" data-toggle="tooltip" data-placement="top" title="{{ __('Delete') }}">
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
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection

@section('page-script')
  <script src="{{ asset(mix('js/scripts/pages/app-module-list.js')) }}"></script>
@endsection
