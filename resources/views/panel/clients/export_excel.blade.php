<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Clientes</title>
    <style>
      body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
      h1 { font-size: 16px; }
      table { border-collapse: collapse; width: 100%; }
      th, td { border: 1px solid #ddd; padding: 6px; }
      th { background: #f4f4f4; font-weight: 700; }
    </style>
  </head>
  <body>
    @php $data = $clients ?? []; @endphp
    <h1 style="text-align: center;">LISTADO DE CLIENTES</h1>

    <table>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Identificación</th>
          <th>Fecha de Registro</th>
          <th>Teléfono</th>
          <th>Estatus</th>
          <th>Correo</th>
          <th>Estado</th>
          <th>Municipio</th>
          <th>Parroquia</th>
          <th>Dirección</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $client)
          @php
            // normalize: client may be object or array
            $row = is_array($client) ? (object)$client : $client;
          @endphp
          <tr>
            <td>{{ $row->name ?? ($row->nombre ?? '—') }}</td>
            <td>{{ $row->full_document ?? $row->identificacion ?? '—' }}</td>
            <td>{{ $row->es_date ?? (isset($row->created_at) ? (string)$row->created_at : '—') }}</td>
            <td>{{ $row->telefono ?? '—' }}</td>
            <td>{{ $row->status_name ?? (isset($row->status) && $row->status == 1 ? 'Activo' : 'Inactivo') }}</td>
            <td>{{ $row->email ?? '—' }}</td>
            <td>{{ data_get($row, 'estado.nombre', '—') }}</td>
            <td>{{ data_get($row, 'municipality.name', '—') }}</td>
            <td>{{ data_get($row, 'parish.name', '—') }}</td>
            <td>{{ $row->direccion ?? '—' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </body>
</html>
