@extends('layouts/contentLayoutMaster')

@section('title', __('Exchange Rate'))

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">{{ __('Exchange Rate Registry') }}</h4>
        <a href="{{ route('exchange-rates.create') }}" class="btn btn-primary">{{ __('Register new rate') }}</a>
      </div>
      <div class="card-body">
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>{{ __('Date Recorded') }}</th>
                <th>{{ __('Currency From') }}</th>
                <th>{{ __('Currency To') }}</th>
                <th>{{ __('Rate') }}</th>
                <th>{{ __('Notes') }}</th>
                <th class="text-end">{{ __('Actions') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rates as $r)
                <tr>
                  <td>{{ $r->created_at ? $r->created_at->format('d-m-Y H:i') : '' }}</td>
                  <td>{{ $r->currency_from }}</td>
                  <td>{{ $r->currency_to }}</td>
                  <td>{{ $r->change }}</td>
                  <td>{{ \Illuminate\Support\Str::limit($r->notes, 60) }}</td>
                  <td class="d-flex gap-1 justify-content-end">
                    <a href="{{ route('exchange-rates.edit', $r->id) }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>
                    <form action="{{ route('exchange-rates.destroy', $r->id) }}" method="POST" onsubmit="return confirm('{{ __('Delete this rate?') }}')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center">{{ __('No exchange rates recorded yet.') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-end">
          {{ $rates->links() }}
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
