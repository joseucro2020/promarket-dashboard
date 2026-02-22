@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Edit Purchase Order'))

@section('content')
<section>
  <div class="card">
    <div class="card-header">
      <h4 class="card-title">{{ __('locale.Edit Purchase Order') }}</h4>
      <div style="position:absolute;right:16px;top:18px;">
        <span id="order-status" class="badge {{ $order->status==3 ? 'badge-success' : ($order->status==2 ? 'badge-danger' : 'badge-secondary') }}">{{ $order->status==3 ? __('locale.Approved') : ($order->status==2 ? __('locale.Canceled') : __('locale.Pending')) }}</span>
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
          <button id="btn-approve" data-url="{{ route('buyorders.aprobar', [$order->id, 0]) }}" class="btn btn-warning" type="button">{{ __('locale.Approve Purchase Order') }}</button>
          <button id="btn-cancel" data-url="{{ route('buyorders.anular', $order->id) }}" class="btn btn-danger" type="button">{{ __('locale.Cancel Purchase Order') }}</button>
        </div>
      @endif
    
    </div>
  </div>
</section>
@endsection

@section('page-script')
@parent
<script>
console.log('buyorder edit: page-script loaded');
document.addEventListener('DOMContentLoaded', function() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
  const statusEl = document.getElementById('order-status');
  const approveBtn = document.getElementById('btn-approve');
  const cancelBtn = document.getElementById('btn-cancel');

  const messages = {!! json_encode([
    'confirmApprove' => __('locale.Are you sure you want to approve this order?'),
    'confirmCancel' => __('locale.Are you sure you want to cancel this order?'),
    'processing' => __('locale.Processing'),
    'errorApproving' => __('locale.Error approving order'),
    'errorCancelling' => __('locale.Error cancelling order'),
    'approved' => __('locale.Aprobada'),
    'canceled' => __('locale.Anulada')
  ]) !!};

  async function postAction(url) {
    // Use FormData/URLSearchParams to avoid forcing JSON content-type
    const params = new URLSearchParams();
    params.append('_token', csrf);

    const options = {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: params,
      credentials: 'same-origin'
    };
    console.log('POST', url, params.toString());
    return fetch(url, options);
  }

  function submitForm(url) {
    console.warn('Falling back to form submit for', url);
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.style.display = 'none';
    const tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = '_token';
    tokenInput.value = csrf;
    form.appendChild(tokenInput);
    document.body.appendChild(form);
    form.submit();
  }

  if (approveBtn) {
    approveBtn.addEventListener('click', async function() {
      console.log('btn-approve clicked', { url: this.dataset.url });
      if (!confirm(messages.confirmApprove)) return;
      const url = this.dataset.url;
      this.disabled = true;
      const orig = this.innerHTML;
      this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + messages.processing;
      try {
        const res = await postAction(url);
        if (res.ok) {
          if (statusEl) {
            statusEl.textContent = messages.approved;
            statusEl.className = 'badge badge-success';
          }
          this.style.display = 'none';
          if (cancelBtn) cancelBtn.style.display = 'none';
        } else {
          let msg = await res.text();
          console.error('Approve failed', res.status, msg);
          alert(messages.errorApproving + ': ' + msg);
        }
      } catch (err) {
        console.error('Fetch error approving', err);
        // Fallback to form submit for environments where fetch is blocked
        submitForm(url);
        return;
      } finally {
        this.disabled = false;
        this.innerHTML = orig;
      }
    });
  }

  if (cancelBtn) {
    cancelBtn.addEventListener('click', async function() {
      if (!confirm(messages.confirmCancel)) return;
      const url = this.dataset.url;
      this.disabled = true;
      const orig = this.innerHTML;
      this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + messages.processing;
      try {
        const res = await postAction(url);
        if (res.ok) {
          if (statusEl) {
            statusEl.textContent = messages.canceled;
            statusEl.className = 'badge badge-danger';
          }
          this.style.display = 'none';
          if (approveBtn) approveBtn.style.display = 'none';
        } else {
          let msg = await res.text();
          console.error('Cancel failed', res.status, msg);
          alert(messages.errorCancelling + ': ' + msg);
        }
      } catch (err) {
        console.error('Fetch error cancelling', err);
        // Fallback to form submit
        submitForm(url);
        return;
      } finally {
        this.disabled = false;
        this.innerHTML = orig;
      }
    });
  }

});
</script>
@endsection
