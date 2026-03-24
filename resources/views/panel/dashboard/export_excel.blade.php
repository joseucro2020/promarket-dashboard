<table>
    <thead>
        <tr>
            <th>{{ __('locale.Transactions') }}</th>
            <th>{{ __('locale.Payment Method Share') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ __('locale.From') }}</td>
            <td>{{ data_get($data, 'date_from') }}</td>
        </tr>
        <tr>
            <td>{{ __('locale.To') }}</td>
            <td>{{ data_get($data, 'date_to') }}</td>
        </tr>
        @forelse (data_get($data, 'transactions', []) as $transaction)
            <tr>
                <td>{{ data_get($transaction, 'label') }}</td>
                <td>{{ data_get($transaction, 'percent', 0) }}%</td>
            </tr>
        @empty
            <tr>
                <td>{{ __('locale.No records found.') }}</td>
                <td>0%</td>
            </tr>
        @endforelse
    </tbody>
</table>
