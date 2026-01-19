@extends('layouts/contentLayoutMaster')

@section('title', isset($offer) ? __('Edit Offer') : __('New Offer'))

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section('content')
    @php $offer = $offer ?? null; @endphp

    <section>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ isset($offer) ? __('Edit Offer') : __('New Offer') }}</h4>
                    </div>
                    <div class="card-body">
                        <form id="offer-form" action="{{ isset($offer) ? route('offers.update', $offer) : route('offers.store') }}"
                            method="POST">
                            @csrf
                            @if (isset($offer))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="start">{{ __('Start') }}</label>
                                        <input type="date" id="start" name="start" class="form-control"
                                            value="{{ old('start', optional($offer)->start?->format('Y-m-d')) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="end">{{ __('End') }}</label>
                                        <input type="date" id="end" name="end" class="form-control"
                                            value="{{ old('end', optional($offer)->end?->format('Y-m-d')) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="percentage">{{ __('Percentage') }}</label>
                                        <input type="number" step="0.01" min="0" max="100" id="percentage"
                                            name="percentage" class="form-control"
                                            value="{{ old('percentage', $offer->percentage ?? 0) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">{{ __('Products') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-2">
                                                <div class="col-4">
                                                    <select id="filter-category" class="form-select">
                                                        <option value="">{{ __('All Categories') }}</option>
                                                        @foreach ($categories as $c)
                                                            <option value="{{ $c->id }}">{{ $c->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-4">
                                                    <select id="filter-subcategory" class="form-select">
                                                        <option value="">{{ __('All Subcategories') }}</option>
                                                        @foreach ($subcategories as $s)
                                                            <option value="{{ $s->id }}"
                                                                data-category="{{ $s->category_id }}">{{ $s->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-4">
                                                    <select id="filter-subsub" class="form-select">
                                                        <option value="">{{ __('All Sub-Subcategories') }}</option>
                                                        @foreach ($subsub as $ss)
                                                            <option value="{{ $ss->id }}"
                                                                data-subcategory="{{ $ss->subcategory_id }}">
                                                                {{ $ss->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered products-table w-100">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center">{{ __('Logo') }}</th>
                                                            <th>{{ __('Name') }}</th>
                                                            <th class="text-end">{{ __('Actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {{-- server-side processing will populate rows via AJAX --}}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">{{ __('Offer Products') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table
                                                    class="table table-striped table-bordered offer-products-table w-100">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('ID') }}</th>
                                                            <th>{{ __('Name') }}</th>
                                                            <th class="text-end">{{ __('Actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $selected = collect();
                                                            if (isset($offer) && method_exists($offer, 'products')) {
                                                                $selected = $offer->products()->get();
                                                            }
                                                        @endphp
                                                        @foreach ($selected as $sp)
                                                            <tr data-id="{{ $sp->id }}">
                                                                <td>{{ $sp->id }}</td>
                                                                <td>{{ $sp->name }}</td>
                                                                <td class="text-end">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger remove-from-offer"
                                                                        data-id="{{ $sp->id }}">{{ __('Remove') }}</button>
                                                                    <input type="hidden" name="products[]"
                                                                        value="{{ $sp->id }}">
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-end">
                                <a href="{{ route('offers.index') }}"
                                    class="btn btn-outline-secondary mr-2">{{ __('Back') }}</a>
                                <button type="submit"
                                    class="btn btn-primary">{{ isset($offer) ? __('Update') : __('Save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection

@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const productsTable = $('.products-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                order: [
                    [1, 'asc']
                ],
                ajax: {
                    url: '{{ route('offers.products.data') }}',
                    data: function(d) {
                        d.category = $('#filter-category').val();
                        d.subcategory = $('#filter-subcategory').val();
                        d.subsub = $('#filter-subsub').val();
                    }
                },
                columns: [{
                        data: 0,
                        name: 'image',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 1,
                        name: 'name'
                    },
                    {
                        data: 2,
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
                }
            });

            const offerTable = $('.offer-products-table').DataTable({
                responsive: true,
                order: [
                    [0, 'asc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
                }
            });

            // server-side processing: no client cache needed

            // Filters are applied server-side; reload AJAX when selects change
            $('#filter-category, #filter-subcategory, #filter-subsub').on('change', function() {
                productsTable.ajax.reload();
            });

            // Dynamic subcategory/subsub selects
            const $subcat = $('#filter-subcategory');
            const $subsub = $('#filter-subsub');
            const subcatOptions = $subcat.find('option').clone();
            const subsubOptions = $subsub.find('option').clone();

            $('#filter-category').on('change', function() {
                const cat = $(this).val();
                if (!cat) {
                    $subcat.empty().append(subcatOptions);
                    $subsub.empty().append(subsubOptions);
                    productsTable.ajax.reload();
                    return;
                }

                const url = '/panel/promociones/' + cat + '/subcategorias';
                $.getJSON(url, function(response) {
                    // populate subcategories
                    $subcat.empty().append(
                        '<option value="">{{ __('All Subcategories') }}</option>');
                    response.subcategory.forEach(function(s) {
                        $subcat.append('<option value="' + s.id + '">' + s.name +
                            '</option>');
                    });

                    // reset subsub select
                    $subsub.empty().append(
                        '<option value="">{{ __('All Sub-Subcategories') }}</option>');
                    productsTable.ajax.reload();
                });
            });

            $subcat.on('change', function() {
                const sub = $(this).val();
                if (!sub) {
                    $subsub.empty().append(subsubOptions);
                    productsTable.ajax.reload();
                    return;
                }
                // filter subsub options based on data-subcategory attr from initial options
                $subsub.empty().append(subsubOptions.filter(function() {
                    const val = $(this).attr('value');
                    if (!val) return true;
                    return $(this).data('subcategory').toString() === sub.toString();
                }));

                // optional: fetch products for subcategory
                // just reload server-side table with subcategory filter
                productsTable.ajax.reload();
            });

            // Initialize Select2 for nicer selects
            $('#filter-category').select2({
                placeholder: '{{ __('All Categories') }}',
                allowClear: true,
                width: '100%'
            });
            $('#filter-subcategory').select2({
                placeholder: '{{ __('All Subcategories') }}',
                allowClear: true,
                width: '100%'
            });
            $('#filter-subsub').select2({
                placeholder: '{{ __('All Sub-Subcategories') }}',
                allowClear: true,
                width: '100%'
            });

            // Add to offer
            $(document).on('click', '.add-to-offer', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                // check if already added
                if ($('input[name="products[]"][value="' + id + '"]').length) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: '{{ __('Product already added') }}',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    return;
                }
                // append hidden input to form
                const input = $('<input>').attr('type', 'hidden').attr('name', 'products[]').val(id);
                $('form').first().append(input);
                // add row to offer table
                const rowNode = offerTable.row.add([id, name,
                    '<button type="button" class="btn btn-sm btn-danger remove-from-offer" data-id="' +
                    id + '">{{ __('Remove') }}</button>'
                ]).draw().node();
                $(rowNode).attr('data-id', id);
            });

            // Remove from offer
            $(document).on('click', '.remove-from-offer', function() {
                const id = $(this).data('id');
                // remove hidden input
                $('input[name="products[]"][value="' + id + '"]').remove();
                // remove from offer table
                const row = $('.offer-products-table tbody tr[data-id="' + id + '"]');
                if (row.length) {
                    offerTable.row(row).remove().draw();
                }
            });

            // Mostrar errores de validación o mensajes de éxito usando SweetAlert2
            @if ($errors->any())
                var _errors = @json($errors->all());
                var errorsHtml = '<ul style="text-align:left;margin:0;padding-left:20px;">';
                _errors.forEach(function(err) {
                    errorsHtml += '<li>' + err + '</li>';
                });
                errorsHtml += '</ul>';
                Swal.fire({
                    icon: 'error',
                    title: @json(__('Please fix the following errors')),
                    html: errorsHtml,
                });
            @endif

            @if (session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: @json(session('success')),
                    showConfirmButton: false,
                    timer: 2500
                });
            @endif

            // Enviar formulario por AJAX para evitar recargar la página
            $('#offer-form').on('submit', function(e) {
                e.preventDefault();
                var $form = $(this);
                var url = $form.attr('action');
                var method = ($form.find('input[name="_method"]').val() || 'POST').toUpperCase();
                var submitBtn = $form.find('button[type="submit"]');
                submitBtn.prop('disabled', true);

                var formData = new FormData(this);

                $.ajax({
                    url: url,
                    type: method === 'GET' ? 'GET' : 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-HTTP-Method-Override': method,
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $form.find('input[name="_token"]').val()
                    },
                    success: function(response, textStatus, xhr) {
                        var msg = response.message || '{{ __('Saved successfully') }}';
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: msg,
                            showConfirmButton: false,
                            timer: 2000
                        }).then(function() {
                            // si el servidor devuelve redirect URL, úsala, si no, vuelve al índice
                            var redirect = response.redirect || '{{ route('offers.index') }}';
                            window.location.href = redirect;
                        });
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false);
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                            var errorsHtml = '<ul style="text-align:left;margin:0;padding-left:20px;">';
                            Object.keys(errors).forEach(function(k) {
                                errors[k].forEach(function(msg) {
                                    errorsHtml += '<li>' + msg + '</li>';
                                });
                            });
                            errorsHtml += '</ul>';
                            Swal.fire({
                                icon: 'error',
                                title: @json(__('Please fix the following errors')),
                                html: errorsHtml,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('An error occurred') }}',
                                text: xhr.responseText || '{{ __('Please try again') }}'
                            });
                        }
                    }
                });
            });
        });
    </script>
@endsection
