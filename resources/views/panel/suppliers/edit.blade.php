@extends('layouts/contentLayoutMaster')

@section('title', 'Editar Proveedor')

@section('content')
@section('content')
  @php
    $paises = $paises ?? null;
    $states = $states ?? null;
    $municipalities = $municipalities ?? null;
  @endphp
  @include('panel.suppliers.form')
@endsection
