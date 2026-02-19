@extends('layouts/contentLayoutMaster')

@section('title', __('locale.Nuevo Proveedor'))

@section('content')
@section('content')
  @php $paises = $paises ?? null; @endphp
  @include('panel.suppliers.form')
@endsection
