@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Edit Purchase Order'))

@section('content')
<section>
  <div class="card">
    <div class="card-header">
      <h4 class="card-title">{{ __('locale.Edit Purchase Order') }}</h4>
      <div style="position:absolute;right:16px;top:18px;">
        <span id="order-status" class="badge {{ $order->status==3 ? 'badge-success' : ($order->status==2 ? 'badge-danger' : 'badge-secondary') }}">{{ $order->status==3 ? __('locale.Aprobada') : ($order->status==2 ? __('locale.Anulada') : __('locale.Pendiente')) }}</span>
      </div>
    </div>
    <div class="card-body">
      @php($buyorder = $order)
      <form id="form-update-order" action="{{ route('buyorders.update', $order->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('panel.buyorder.form')
        <div class="text-right">
          <a href="{{ route('buyorders.index') }}" class="btn btn-secondary">{{ __('locale.Back') }}</a>
          <button type="submit" class="btn btn-primary">{{ __('locale.Save') }}</button>
        </div>
      </form>

      @if(isset($order) && $order->status == 1)
        <div class="text-right" style="margin-top:8px;">
          <button id="btn-approve" data-url="{{ route('buyorders.aprobar', [$order->id, 0]) }}" class="btn btn-warning" type="button">{{ __('locale.Aprobar Orden De Compra') }}</button>
          <button id="btn-cancel" data-url="{{ route('buyorders.anular', $order->id) }}" class="btn btn-danger" type="button">{{ __('locale.Anular Orden De Compra') }}</button>
        </div>
      @endif
    
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
  const statusEl = document.getElementById('order-status');
  const approveBtn = document.getElementById('btn-approve');
  const cancelBtn = document.getElementById('btn-cancel');

  async function postAction(url) {
    const options = {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({})
    };
    return fetch(url, options);
  }

  if(approveBtn){
    approveBtn.addEventListener('click', async function(){
      if(!confirm('{{ __('locale.Are you sure you want to approve this order?') }}')) return;
      const url = this.dataset.url;
      this.disabled = true;
      const orig = this.innerHTML;
      this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('locale.Processing') }}';
      try{
        const res = await postAction(url);
        if(res.ok){
          statusEl.textContent = '{{ __('locale.Aprobada') }}';
          statusEl.className = 'badge badge-success';
          this.style.display = 'none';
          if(cancelBtn) cancelBtn.style.display = 'none';
        } else {
          let msg = await res.text();
          alert('{{ __('locale.Error approving order') }}: ' + msg);
        }
      } catch(err){
        alert('{{ __('locale.Error approving order') }}: ' + err.message);
      } finally{
        this.disabled = false;
        this.innerHTML = orig;
      }
    });
  }

  if(cancelBtn){
    cancelBtn.addEventListener('click', async function(){
      if(!confirm('{{ __('locale.Are you sure you want to cancel this order?') }}')) return;
      const url = this.dataset.url;
      this.disabled = true;
      const orig = this.innerHTML;
      this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('locale.Processing') }}';
      try{
        const res = await postAction(url);
        if(res.ok){
          statusEl.textContent = '{{ __('locale.Anulada') }}';
          statusEl.className = 'badge badge-danger';
          this.style.display = 'none';
          if(approveBtn) approveBtn.style.display = 'none';
        } else {
          let msg = await res.text();
          alert('{{ __('locale.Error cancelling order') }}: ' + msg);
        }
      } catch(err){
        alert('{{ __('locale.Error cancelling order') }}: ' + err.message);
      } finally{
        this.disabled = false;
        this.innerHTML = orig;
      }
    });
  }

});
</script>
@endpush
