<h1 style="text-align: center;">LISTADO DE PRODUCTOS {{ $today }}</h1>

<table border="1" cellspacing="0" cellpadding="3">
    <thead>
        <tr>
            <th style="background-color: #f2f2f2;">Código del Producto</th>
            <th style="background-color: #f2f2f2;">Nombre</th>
            <th style="background-color: #f2f2f2;">Presentación</th>
            <th style="background-color: #f2f2f2;">Tipo</th>
            <th style="background-color: #f2f2f2;">Existencia</th>
            <th style="background-color: #f2f2f2;">Costo Unitario</th>
            <th style="background-color: #f2f2f2;">Umbral</th>
            <th style="background-color: #f2f2f2;">Mín. Venta</th>
            <th style="background-color: #f2f2f2;">Máx. Venta</th>
            <th style="background-color: #f2f2f2;">Precio ($)</th>
            <th style="background-color: #f2f2f2;">Ganancia ($)</th>
            <th style="background-color: #f2f2f2;">% Utilidad</th>
            <th style="background-color: #f2f2f2;">ID Categoría</th>
            <th style="background-color: #f2f2f2;">Categoría</th>
            <th style="background-color: #f2f2f2;">ID Subcategoría</th>
            <th style="background-color: #f2f2f2;">Subcategoría</th>
            <th style="background-color: #f2f2f2;">Referencia</th>
            <th style="background-color: #f2f2f2;">SKU</th>
            <th style="background-color: #f2f2f2;">Tags</th>
            <th style="background-color: #f2f2f2;">Proveedor</th>
            <th style="background-color: #f2f2f2;">Fecha ingreso</th>
            <th style="background-color: #f2f2f2;">Fecha modificación</th>
            <th style="background-color: #f2f2f2;">Proveedor</th>
            <th style="background-color: #f2f2f2;">Padre</th>
            <th style="background-color: #f2f2f2;">Compañía</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalCost = 0;
            $totalPrice = 0;
            $totalGanancia = 0;
        @endphp

        @foreach ($products as $item)
            @php
                $presentations = $item->colors->flatMap(function ($color) {
                    return $color->amounts;
                });
                $tags = $item->tags->pluck('name')->implode(', ');
                $supplier = $item->supplier->pluck('nombre_prove')->filter()->implode(', ');
                $supplier = $supplier !== '' ? $supplier : '-';
            @endphp

            @foreach ($presentations as $product)
                @php
                    $price = (int) $item->variable === 0 ? (float) $item->price_1 : (float) $product->price;
                    $cost = (float) $product->cost;
                    $ganancia = $price - $cost;
                    $porcentaje = $cost > 0 ? number_format(($ganancia / $cost) * 100, 2, '.', ',') : 0;
                    $totalCost += $cost;
                    $totalPrice += $price;
                    $totalGanancia += $ganancia;
                @endphp
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ (int) $item->variable === 1 ? ($product->presentation ?? '') : '' }}</td>
                    <td>{{ $item->type_variable }}</td>
                    <td>{{ $product->amount }}</td>
                    <td style="mso-number-format:'0.00';">{{ number_format($cost, 2, '.', ',') }}</td>
                    <td>{{ $product->umbral }}</td>
                    <td>{{ $product->min }}</td>
                    <td>{{ $product->max }}</td>
                    <td style="mso-number-format:'0.00';">{{ number_format($price, 2, '.', ',') }}</td>
                    <td style="mso-number-format:'0.00';">{{ number_format($ganancia, 2, '.', ',') }}</td>
                    <td>{{ $porcentaje }}</td>
                    <td>{{ optional($item->categories)->id ?? '' }}</td>
                    <td>{{ optional($item->categories)->name ?? '' }}</td>
                    <td>{{ optional($item->subcategories)->id ?? '' }}</td>
                    <td>{{ optional($item->subcategories)->name ?? '' }}</td>
                    <td>{{ $product->id }}</td>
                    <td style="mso-number-format:'@';">{{ $product->sku }}</td>
                    <td>{{ $tags }}</td>
                    <td>{{ $supplier }}</td>
                    <td>{{ $item->es_date }}</td>
                    <td>{{ $item->es_update }}</td>
                    <td>{{ $supplier }}</td>
                    <td>{{ data_get($item, 'categories.id_father', '-') }}</td>
                    <td>{{ $item->company_id ?? '-' }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5"></td>
            <th style="background-color: #e0e0e0;">{{ number_format($totalCost, 2, '.', ',') }}</th>
            <td colspan="3"></td>
            <th style="background-color: #e0e0e0;">{{ number_format($totalPrice, 2, '.', ',') }}</th>
            <th style="background-color: #e0e0e0;">{{ number_format($totalGanancia, 2, '.', ',') }}</th>
            <td colspan="14"></td>
        </tr>
    </tfoot>
</table>
