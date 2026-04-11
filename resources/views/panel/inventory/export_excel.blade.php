<table>
  <thead>
    <tr>
      <th colspan="6">{{ __('locale.Inventory Replenishment List') }} - {{ $today ?? now()->format('d-m-Y H:i') }}</th>
    </tr>
    <tr>
      <th>#</th>
      <th>{{ __('locale.User') }}</th>
      <th>{{ __('locale.Product') }}</th>
      <th>{{ __('locale.Type') }}</th>
      <th>{{ __('locale.Quantity') }}</th>
      <th>{{ __('locale.Date') }}</th>
    </tr>
  </thead>
  <tbody>
    @forelse($replenishments ?? [] as $replenishment)
      <tr>
        <td>{{ $replenishment->id }}</td>
        <td>{{ $replenishment->user_name ?? '-' }}</td>
        <td>{{ $replenishment->product_name ?? '-' }}</td>
        <td>{{ $replenishment->type_label ?? '-' }}</td>
        <td>{{ $replenishment->quantity ?? '-' }}</td>
        <td>{{ $replenishment->created_at ? \Carbon\Carbon::parse($replenishment->created_at)->format('d-m-Y H:i') : '-' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="6">{{ __('locale.No replenishments yet.') }}</td>
      </tr>
    @endforelse
  </tbody>
</table>
