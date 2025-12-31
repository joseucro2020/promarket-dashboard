@extends('layouts/contentLayoutMaster')

@section('title', 'Nuevo Proveedor')

@section('content')
@section('content')
  @php $paises = $paises ?? null; @endphp
  @include('panel.suppliers.form')
@endsection
