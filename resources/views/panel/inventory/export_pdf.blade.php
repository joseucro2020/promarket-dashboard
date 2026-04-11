<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>{{ __('locale.Inventory Replenishment List') }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
    h2 { margin: 0 0 8px; font-size: 18px; }
    .meta { margin-bottom: 14px; color: #4b5563; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
    th { background: #f3f4f6; }
    .empty { text-align: center; }
  </style>
</head>
<body>
  <h2>{{ __('locale.Inventory Replenishment List') }}</h2>
  <div class="meta">{{ __('locale.Date') }}: {{ $generatedAt ?? now()->format('d-m-Y H:i') }}</div>

  <table>
    <thead>
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
          <td colspan="6" class="empty">{{ __('locale.No replenishments yet.') }}</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
