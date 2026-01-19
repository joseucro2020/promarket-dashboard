@extends('layouts/contentLayoutMaster')

@section('title', __('Kromi Market'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/sweetalert2.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/toastr.min.css')) }}">
  <style>
    /* Kromi: aumentar legibilidad de la lista */
    #kromi-products-table thead th,
      #kromi-products-table tbody td {
      font-size: 0.95rem;
      vertical-align: middle;
    }
    /* SKU badge */
    .kromi-sku.badge {
      font-size: 0.85rem;
      font-weight: 600;
      padding: .35rem .55rem;
      border-radius: 999px;
    }
    /* Ajustes responsivos: reducir tamaño en pantallas muy pequeñas */
    @media (max-width: 575.98px) {
      #kromi-products-table thead th,
      #kromi-products-table tbody td { font-size: 0.86rem; }
      .kromi-sku.badge { font-size: 0.78rem; padding: .25rem .45rem; }
    }
    /* Name column emphasis */
    #kromi-products-table tbody td:nth-child(3) {
      font-size: 1rem;
      font-weight: 600;
      color: #343a40;
    }
    /* Quantity badge */
    .kromi-qty.badge {
      font-size: 0.82rem;
      font-weight: 600;
      padding: .28rem .5rem;
      border-radius: 6px;
    }
    @media (max-width: 575.98px) {
      .kromi-qty.badge { font-size: 0.74rem; padding: .2rem .4rem; }
    }
    /* Table overlay for loading during filters */
    .kromi-table-wrapper { position: relative; }
    .kromi-table-loading {
      position: absolute;
      inset: 0; /* top:0; right:0; bottom:0; left:0; */
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255,255,255,0.65);
      z-index: 11;
    }
    .kromi-table-loading .spinner-border { width: 2.2rem; height: 2.2rem; }
    /* Toastr: mejorar contraste y legibilidad según UI-UX del panel */
    .toast {
      background: #ffffff !important;
      color: #2b2b2b !important;
      font-weight: 600 !important;
      font-family: inherit !important;
      border-radius: 6px !important;
      box-shadow: 0 6px 20px rgba(16,24,40,0.08) !important;
      padding: .75rem 1rem !important;
    }
    .toast .toast-title { color: inherit !important; font-weight: 700 !important; }
    .toast .toast-message { color: inherit !important; font-size: .95rem !important; }
    .toast-success { border-left: 4px solid #28c76f !important; }
    .toast-info { border-left: 4px solid #00cfe8 !important; }
    .toast-warning { border-left: 4px solid #ff9f43 !important; }
    .toast-error { border-left: 4px solid #ea5455 !important; }
    .toast-bottom-right { right: 1rem; bottom: 1rem; }
    .toast { z-index: 200000 !important; }
    /* Icono profesional para toasts de advertencia (SVG data URI) */
    .toast.toast-warning {
      position: relative;
      padding-left: 2.6rem !important;
    }
    /* Row highlight when update succeeded */
    .row-highlight {
      animation: flash-highlight 1.6s ease;
    }
    @keyframes flash-highlight {
      0% { box-shadow: inset 0 0 0 9999px rgba(40,167,69,0.18); }
      100% { box-shadow: none; }
    }
    .kromi-updated { vertical-align: middle; margin-left: .35rem; display: inline-flex; align-items: center; }
    .toast.toast-warning::before {
      content: "";
      position: absolute;
      left: .9rem;
      top: 50%;
      transform: translateY(-50%);
      width: 1.15rem;
      height: 1.15rem;
      background-repeat: no-repeat;
      background-size: contain;
      background-image: url("data:image/svg+xml;utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ff9f43' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z'/%3E%3Cline x1='12' y1='9' x2='12' y2='13'/%3E%3Cline x1='12' y1='17' x2='12.01' y2='17'/%3E%3C/svg%3E");
    }
  </style>
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/extensions/toastr.min.js')) }}"></script>
@endsection

@section('page-script')
  <script>
    $(function() {
      // Helper: convert string to Title Case for display
      function toTitleCase(str) {
        if (!str) return '';
        try {
          return String(str).toLowerCase().replace(/\b\S/g, function(t){ return t.toUpperCase(); });
        } catch(e) { return str; }
      }
      // Helper: normalize SKU/text for reliable comparisons
      function normalizeSku(s) {
        if (!s) return '';
        try {
          var str = String(s).replace(/^\uFEFF/, '').replace(/[\u200B-\u200D\uFEFF]/g, '');
          // Normalize unicode and remove diacritics
          if (str.normalize) str = str.normalize('NFKD').replace(/[\u0300-\u036f]/g, '');
          // collapse whitespace, trim and lowercase
          str = str.replace(/\s+/g, ' ').trim().toLowerCase();
          // remove any remaining non-alphanumeric characters but keep dash and underscore
          str = str.replace(/[^a-z0-9\-_ ]+/g, '');
          // remove spaces to normalize formatting (e.g., "SKU 001" -> "sku001")
          str = str.replace(/\s+/g, '');
          return str;
        } catch(e) {
          return String(s).trim().toLowerCase().replace(/[^a-z0-9\-_]+/g, '');
        }
      }
      // Flag that indicates Promarket products have been loaded
      var promarketLoaded = false;
      // Global set of normalized SKUs from Promarket (populated on AJAX load)
      var promarketSkusGlobal = {};

      // Check Kromi table rows against loaded Promarket SKUs and mark matches
      function markExistingSkus() {
        try {
          var promarketSkus = {};
          // If we have a global promarket SKUs set from the AJAX load, prefer it
          if (promarketSkusGlobal && Object.keys(promarketSkusGlobal).length) {
            promarketSkus = promarketSkusGlobal;
          } else {
            // Fallback: try to build from rendered table rows/column data
            try {
              var promarketData = promarketTable.column(0).data() || [];
              for (var i = 0; i < promarketData.length; i++) {
                try {
                  var h = promarketData[i] || '';
                  var raw = '';
                  if (typeof h === 'string') raw = $('<div>').html(h).text();
                  else if (h && h.nodeType) raw = $(h).text();
                  var txt = normalizeSku(raw);
                  if (txt) promarketSkus[txt] = true;
                } catch(e) { }
              }
              var pNodes = promarketTable.rows().nodes() || [];
              $(pNodes).each(function(i, node) {
                try {
                  var $n = $(node);
                  var raw = $n.find('.kromi-sku').text().trim();
                  if (!raw) raw = $n.find('td').eq(0).text().trim();
                  if (!raw) raw = $n.text().trim();
                  var txt = normalizeSku(raw);
                  if (txt) promarketSkus[txt] = true;
                } catch(e) { }
              });
            } catch(e) { console.warn('[markExistingSkus] Error building promarket SKUs', e); }
            var keys = Object.keys(promarketSkus || {});
            console.info('[markExistingSkus] Promarket SKUs count (fallback):', keys.length, 'sample:', keys.slice(0,10));
          }

          var activeCount = 0;
          kromiTable.rows().nodes().each(function(idx, node) {
            var $node = $(node);
            var rawSku = $node.find('.kromi-sku').text().trim();
            if (!rawSku) rawSku = $node.find('td').eq(1).text().trim(); // checkbox at col 0
            if (!rawSku) rawSku = $node.text().trim();
            var skuText = normalizeSku(rawSku);
            // Debug log for target SKUs
            if (debugMap[skuText]) console.info('[markExistingSkus] Kromi row SKU:', debugMap[skuText], 'raw:', rawSku, 'normalized:', skuText, 'existsInPromarket:', !!promarketSkus[skuText]);
            if (skuText && promarketSkus[skuText]) {
              $node.find('input[type="checkbox"]').prop('checked', true);
              $node.addClass('table-success');
              activeCount++;
            }
          });
          $('#kromi-active-count').text(activeCount);
        } catch(e) {
          console.warn('Error checking existing SKUs', e);
        }
      }
      var kromiTable = $('#kromi-products-table').DataTable({
        responsive: true,
        columns: [
          { orderable: false },
          null,
          null,
          null,
          null,
          { visible: false }, // hidden column to store `father` for filtering
          { visible: false }, // hidden column to store `son` (subcategoria)
          { visible: false }  // hidden column to store `grandson` (sub-subcategoria)
        ],
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
        drawCallback: function() { if (feather) { feather.replace({width:14,height:14}); } }
      });

      var promarketTable = $('#promarket-products-table').DataTable({
        responsive: true,
        columns: [
          null,
          null,
          null,
          null,
          null,
          null,
          { visible: false }, // father_id
          { visible: false }, // son_id
          { visible: false }  // grandson_id
        ],
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
        drawCallback: function() { if (feather) { feather.replace({width:14,height:14}); } }
      });

      // Checkbox handlers: register once so work immediately after CSV load
      // Header: select/deselect all rows (all pages)
      $('#kromi-select-all').on('change', function() {
        var checked = $(this).is(':checked');
        var allNodes = kromiTable.rows().nodes();
        $(allNodes).each(function() {
          var $row = $(this);
          $row.find('input[type="checkbox"]').prop('checked', checked);
          if (checked) $row.addClass('table-success'); else $row.removeClass('table-success');
        });
        var totalAll = $(allNodes).find('input[type="checkbox"]').length;
        var checkedAll = $(allNodes).find('input[type="checkbox"]:checked').length;
        $('#kromi-active-count').text(checkedAll);
        var header = $('#kromi-select-all')[0];
        if (header) {
          header.checked = (totalAll > 0) && (checkedAll === totalAll);
          header.indeterminate = checkedAll > 0 && checkedAll < totalAll;
        }
      });

      // Delegated handler for individual checkboxes
      $('#kromi-products-table tbody').on('change', 'input[type="checkbox"]', function() {
        var $row = $(this).closest('tr');
        if ($(this).is(':checked')) $row.addClass('table-success'); else $row.removeClass('table-success');
        var allNodes = kromiTable.rows().nodes();
        var totalAll = $(allNodes).find('input[type="checkbox"]').length;
        var checkedAll = $(allNodes).find('input[type="checkbox"]:checked').length;
        $('#kromi-active-count').text(checkedAll);
        var header = $('#kromi-select-all')[0];
        if (header) {
          header.checked = (totalAll > 0) && (checkedAll === totalAll);
          header.indeterminate = checkedAll > 0 && checkedAll < totalAll;
        }
      });

      // Keep header state in sync after DataTable redraws
      kromiTable.on('draw', function() {
        var allNodes = kromiTable.rows().nodes();
        var totalAll = $(allNodes).find('input[type="checkbox"]').length;
        var checkedAll = $(allNodes).find('input[type="checkbox"]:checked').length;
        $('#kromi-active-count').text(checkedAll);
        var header = $('#kromi-select-all')[0];
        if (header) {
          header.checked = (totalAll > 0) && (checkedAll === totalAll);
          header.indeterminate = checkedAll > 0 && checkedAll < totalAll;
        }
      });

      // Helper to refresh kromi header state (active count + header checkbox)
      function refreshKromiHeaderState() {
        var allNodes = kromiTable.rows().nodes();
        var totalAll = $(allNodes).find('input[type="checkbox"]').length;
        var checkedAll = $(allNodes).find('input[type="checkbox"]:checked').length;
        $('#kromi-active-count').text(checkedAll);
        var header = $('#kromi-select-all')[0];
        if (header) {
          header.checked = (totalAll > 0) && (checkedAll === totalAll);
          header.indeterminate = checkedAll > 0 && checkedAll < totalAll;
        }
      }

      // Filter: show only rows with checked checkbox
      var kromiShowActiveOnly = false;
      $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData) {
        // apply only to kromi-products-table
        if (!settings || !settings.nTable) return true;
        if (settings.nTable.id !== 'kromi-products-table') return true;
        if (!kromiShowActiveOnly) return true;
        try {
          var node = kromiTable.row(dataIndex).node();
          if (!node) return false;
          return $(node).find('input[type="checkbox"]').is(':checked');
        } catch(e) { return false; }
      });

      // Toggle filter control
      $(document).on('change', '#kromi-filter-active', function() {
        kromiShowActiveOnly = $(this).is(':checked');
        kromiTable.draw(false);
        // refresh header state because draw may hide rows
        setTimeout(refreshKromiHeaderState, 50);
      });

      // Invert selection button: toggle checkboxes across ALL rows
      $(document).on('click', '#kromi-invert-selection', function() {
        var allNodes = kromiTable.rows().nodes() || [];
        var checkedCount = 0;
        $(allNodes).each(function(i, node) {
          try {
            var $r = $(node);
            var $cb = $r.find('input[type="checkbox"]');
            if (!$cb.length) return;
            var newState = !$cb.is(':checked');
            $cb.prop('checked', newState);
            if (newState) $r.addClass('table-success'); else $r.removeClass('table-success');
            if (newState) checkedCount++;
          } catch(e) { }
        });
        // update header state and count
        refreshKromiHeaderState();
        // If filter is active, re-draw to reflect visible rows
        if (kromiShowActiveOnly) kromiTable.draw(false);
      });

      // Table overlay controls
      function showTableLoading() { $('#kromi-table-loading').show(); }
      function hideTableLoading() { $('#kromi-table-loading').hide(); }

      // Load initial products via AJAX and populate the Promarket table
      function loadInitialProducts() {
        $.ajax({
          url: '{{ route("kromi.products") }}',
          method: 'GET',
          dataType: 'json',
          success: function(response) {
            if (response.products && Array.isArray(response.products)) {
                // Keep Kromi CSV maps untouched; we populate Promarket table
                // reset global SKU map and populate from response
                promarketSkusGlobal = {};
              promarketTable.clear();
              var rows = response.products.map(function(p) {
                var sku = p.sku || '';
                try { var nsku = normalizeSku(sku); if (nsku) promarketSkusGlobal[nsku] = true; } catch(e) {}
                var skuHtml = '<span class="badge badge-light-primary kromi-sku">' + $('<div>').text(sku).html() + '</span>';
                var nameHtml = $('<div>').text(toTitleCase(p.name || '')).html();
                var cost = p.cost !== undefined ? p.cost : '';
                var costFormatted = '';
                if (cost !== '') {
                  var cnum = Number(String(cost).replace(/[^0-9\-\.,]/g,'').replace(/,/g, '.'));
                  if (!isNaN(cnum)) {
                    try { costFormatted = '$ ' + cnum.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
                    catch(e) { costFormatted = '$ ' + cnum.toFixed(2); }
                  } else { costFormatted = '$ ' + $('<div>').text(cost).html(); }
                }
                // Profit select: values 1..99, default from p.utilidad or 25
                var utilidadDefault = (p.utilidad !== undefined && p.utilidad !== null) ? parseInt(p.utilidad) : 25;
                if (isNaN(utilidadDefault) || utilidadDefault < 1 || utilidadDefault > 99) utilidadDefault = 25;
                var paId = p.product_amount_id || p.product_amount || p.id || '';
                var profitHtml = '<select class="form-control form-control-sm utilidad-select" data-sku="' + $('<div>').text(sku).html() + '" data-pa-id="' + $('<div>').text(paId).html() + '">';
                for (var u = 1; u <= 99; u++) {
                  profitHtml += '<option value="' + u + '"' + (u === utilidadDefault ? ' selected' : '') + '>' + u + '</option>';
                }
                profitHtml += '</select>';

                var price = p.price !== undefined ? p.price : '';
                var priceFormatted = '';
                if (price !== '') {
                  var pnum = Number(String(price).replace(/[^0-9\-\.,]/g,'').replace(/,/g, '.'));
                  if (!isNaN(pnum)) {
                    try { priceFormatted = '$ ' + pnum.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
                    catch(e) { priceFormatted = '$ ' + pnum.toFixed(2); }
                  } else { priceFormatted = '$ ' + $('<div>').text(price).html(); }
                }
                var qty = p.amount !== undefined ? p.amount : '';
                var qtyHtml = qty !== '' ? '<span class="badge badge-light-info kromi-qty">' + $('<div>').text(qty).html() + '</span>' : '';
                var fatherId = p.father_id !== undefined && p.father_id !== null ? String(p.father_id) : '';
                var sonId = p.son_id !== undefined && p.son_id !== null ? String(p.son_id) : '';
                var grandsonId = p.grandson_id !== undefined && p.grandson_id !== null ? String(p.grandson_id) : '';
                return [skuHtml, nameHtml, costFormatted, profitHtml, priceFormatted, qtyHtml, fatherId, sonId, grandsonId];
              });
              promarketTable.rows.add(rows).draw();
              promarketLoaded = true;
              // ensure Kromi rows are marked if CSV already uploaded
              // prefer using the in-memory promarketSkusGlobal for deterministic matching
              markExistingSkus();
              // Delegate change handler for utilidad selects (also works for future redraws)
              $(document).off('change', '.utilidad-select').on('change', '.utilidad-select', function() {
                var $sel = $(this);
                var utilidad = parseInt($sel.val(), 10) || 0;
                var $tr = $sel.closest('tr');
                // read cost from column 2 (index 2) and parse numeric
                var costText = $tr.find('td').eq(2).text().trim();
                var costNum = Number(String(costText).replace(/[^0-9\-\.,]/g,'').replace(/,/g, '.'));
                if (isNaN(costNum)) costNum = 0;
                var newPrice = costNum * (1 + utilidad / 100);
                // format
                var priceFormatted = '';
                try { priceFormatted = '$ ' + newPrice.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
                catch(e) { priceFormatted = '$ ' + newPrice.toFixed(2); }
                // update DataTable cell (price column index 4)
                try {
                  promarketTable.cell($tr, 4).data(priceFormatted).draw(false);
                } catch(e) {
                  $tr.find('td').eq(4).html(priceFormatted);
                }

                // If data-pa-id available, send AJAX to persist change
                var paId = $sel.data('pa-id');
                if (paId) {
                  var payload = { amount: { price: newPrice, utilidad: utilidad } };
                  // disable select while saving
                  $sel.prop('disabled', true);
                  $.ajax({
                    url: '{{ url('panel/product-amounts') }}/' + paId + '/utilidad',
                    method: 'POST',
                    data: payload,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val() },
                    success: function(resp){
                      if (resp && resp.result) {
                        // show toast with updated price if returned
                        var returnedPrice = (resp.ProductAmount && resp.ProductAmount.price) ? Number(resp.ProductAmount.price) : newPrice;
                        var priceStr = '';
                        try { priceStr = '$ ' + returnedPrice.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
                        catch(e) { priceStr = '$ ' + parseFloat(returnedPrice).toFixed(2); }
                        if (typeof toastr !== 'undefined') toastr.success('Utilidad actualizada — Precio: ' + priceStr);

                        // visually mark the row: add highlight and a temporary check icon next to SKU
                        try {
                          var $skuCell = $tr.find('td').eq(0);
                          var $icon = $('<span class="kromi-updated text-success"><i data-feather="check-circle"></i></span>');
                          $skuCell.append($icon);
                          $tr.addClass('row-highlight');
                          if (typeof feather !== 'undefined') { feather.replace({ width: 14, height: 14 }); }
                          setTimeout(function(){ $icon.remove(); $tr.removeClass('row-highlight'); }, 1600);
                        } catch(e) { console.warn('Highlight update error', e); }
                      } else {
                        // log detailed response for debugging
                        try { console.error('Utilidad update failed response:', resp); } catch(e) {}
                        if (resp && resp.errors) {
                          var msgs = [];
                          Object.keys(resp.errors).forEach(function(k){ msgs.push((resp.errors[k]||[]).join(', ')); });
                          if (typeof toastr !== 'undefined') toastr.error(msgs.join(' / '));
                        } else if (typeof toastr !== 'undefined') {
                          toastr.error('Error actualizando utilidad');
                        }
                      }
                    },
                    error: function(xhr) {
                      // detailed logging for debugging
                      try { console.error('AJAX error updating utilidad:', xhr.responseJSON || xhr); } catch(e) {}
                      var msg = 'Error actualizando utilidad';
                      if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                      if (typeof toastr !== 'undefined') toastr.error(msg);
                    },
                    complete: function() { $sel.prop('disabled', false); }
                  });
                }
              });
            } else if (response.error) {
              console.warn('Kromi products error:', response.error);
            }
          },
          error: function(xhr) { console.error('Error loading kromi products', xhr); }
        });
      }

      // Load Promarket products on page load (Kromi table stays for CSV uploads)
      loadInitialProducts();

      // Handle CSV upload via AJAX and populate Kromi table
      $('#upload-csv-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this)[0];
        var data = new FormData(form);

        $.ajax({
          url: $(this).attr('action'),
          method: 'POST',
          data: data,
          processData: false,
          contentType: false,
          headers: { 'X-CSRF-TOKEN': $('input[name="_token"]', this).val() },
          beforeSend: function() {
            $('#upload-csv-button').attr('disabled', true);
            $('#upload-csv-loading').show();
          },
          success: function(response) {
            if (response.products && Array.isArray(response.products)) {
              // Build unique categories list from CSV 'father' field
              var fathers = [];
              response.products.forEach(function(p) {
                if (p.father) {
                  var f = String(p.father).trim();
                  if (f && fathers.indexOf(f) === -1) fathers.push(f);
                }
              });

              // Replace category select with CSV-derived categories
              var $cat = $('#kromi-categories');
              $cat.empty().append($('<option>').val('').text('{{ __('All') }}'));
              fathers.forEach(function(f) { $cat.append($('<option>').val(f).text(toTitleCase(f))); });
              // update counts
              $('#kromi-categories-count').text(fathers.length);

              // build CSV maps for sons (subcategories) and grandsons (sub-subcategories)
              csvMapSubs = {};
              csvMapSubSubs = {};
              response.products.forEach(function(p) {
                var father = p.father ? String(p.father).trim() : '';
                var son = p.son ? String(p.son).trim() : '';
                var grandson = p.grandson ? String(p.grandson).trim() : '';
                if (father) {
                  csvMapSubs[father] = csvMapSubs[father] || [];
                  if (son && csvMapSubs[father].indexOf(son) === -1) csvMapSubs[father].push(son);
                }
                if (son) {
                  csvMapSubSubs[son] = csvMapSubSubs[son] || [];
                  if (grandson && csvMapSubSubs[son].indexOf(grandson) === -1) csvMapSubSubs[son].push(grandson);
                }
              });

              // clear sub and sub-sub selects and counts (CSV does not provide them)
              $('#kromi-subcategories').empty().append($('<option>').val('').text('{{ __('All') }}'));
              $('#kromi-sub-subcategories').empty().append($('<option>').val('').text('{{ __('All') }}'));
              $('#kromi-subcategories-count').text('0');
              $('#kromi-sub-subcategories-count').text('0');

              // populate table rows
              kromiTable.clear();
              var rows = response.products.map(function(p) {
                var checkbox = '<input type="checkbox" />';
                var sku = p.sku || '';
                // SKU rendered as a badge for better visibility
                var skuHtml = '<span class="badge badge-light-primary kromi-sku">' + $('<div>').text(sku).html() + '</span>';
                var name = p.name || '';
                var nameHtml = $('<div>').text(toTitleCase(name)).html();
                var cost = p.price !== undefined ? p.price : '';
                // format cost with $ and two decimals when numeric
                var costFormatted = '';
                if (cost !== '') {
                  var cnum = Number(String(cost).replace(/[^0-9\-\.\,]/g,'').replace(/,/g, '.'));
                  if (!isNaN(cnum)) {
                    try {
                      costFormatted = '$ ' + cnum.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    } catch(e) {
                      costFormatted = '$ ' + cnum.toFixed(2);
                    }
                  } else {
                    costFormatted = '$ ' + $('<div>').text(cost).html();
                  }
                }
                var qty = p.amount !== undefined ? p.amount : '';
                var qtyHtml = qty !== '' ? '<span class="badge badge-light-info kromi-qty">' + $('<div>').text(qty).html() + '</span>' : '';
                var father = p.father ? String(p.father).trim() : '';
                var son = p.son ? String(p.son).trim() : '';
                var grandson = p.grandson ? String(p.grandson).trim() : '';
                return [checkbox, skuHtml, nameHtml, costFormatted, qtyHtml, father, son, grandson];
              });
              kromiTable.rows.add(rows).draw();
              // If Promarket products already loaded, mark matches immediately using the in-memory set.
              if (promarketLoaded && promarketSkusGlobal && Object.keys(promarketSkusGlobal).length) {
                var activeLocal = 0;
                var kNodes = kromiTable.rows().nodes() || [];
                $(kNodes).each(function(i, node) {
                  try {
                    var $n = $(node);
                    var rawSku = $n.find('.kromi-sku').text().trim();
                    if (!rawSku) rawSku = $n.find('td').eq(1).text().trim();
                    if (!rawSku) rawSku = $n.text().trim();
                    var skuNorm = normalizeSku(rawSku);
                    if (skuNorm && promarketSkusGlobal[skuNorm]) {
                      $n.find('input[type="checkbox"]').prop('checked', true);
                      $n.addClass('table-success');
                      activeLocal++;
                    }
                  } catch(e) { }
                });
                $('#kromi-active-count').text(activeLocal);
              } else if (promarketLoaded) {
                // fallback: run existing matcher which will try to build from table if needed
                markExistingSkus();
              }
              // clear any previous category/sub/sub-sub filters
              kromiTable.column(5).search('').column(6).search('').column(7).search('').draw();
            } else if (response.error) {
              alert(response.error);
            } else {
              alert('No se recibieron productos desde el servidor');
            }
            // restore button state
            $('#upload-csv-button').attr('disabled', false);
            $('#upload-csv-loading').hide();
          },
          error: function(xhr) {
            var msg = 'Error procesando el archivo';
            if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
            alert(msg);
            // restore button state
            $('#upload-csv-button').attr('disabled', false);
            $('#upload-csv-loading').hide();
          }
        });
      });

      // Categories data from server (includes subcategories and sub_subcategories)
      var kromiCategories = @json($categories ?? []);
      // populate Promarket categories select on load
      (function populatePromarketCategories(){
        var $pcat = $('#promarket-categories');
        $pcat.empty().append($('<option>').val('').text('{{ __('All') }}'));
        kromiCategories.forEach(function(c){
          $pcat.append($('<option>').val(c.id).text(c.name));
        });
        $('#promarket-categories-count').text(kromiCategories.length);
      })();
      // CSV-derived maps (populated after CSV upload)
      var csvMapSubs = null; // { fatherName: [son1, son2, ...] }
      var csvMapSubSubs = null; // { sonName: [grandson1, ...] }

      // Populate subcategories when a category is selected
      $('#kromi-categories').on('change', function() {
        var catId = $(this).val();
        var $sub = $('#kromi-subcategories');
        var $subsub = $('#kromi-sub-subcategories');
        var $countSub = $('#kromi-subcategories-count');
        $sub.empty().append($('<option>').val('').text('{{ __('All') }}'));
        $subsub.empty().append($('<option>').val('').text('{{ __('All') }}'));

        // show loading indicator while filtering
        $('#kromi-subcategories-loading').show();
        $countSub.text('...');

        if (!catId) {
          // no category selected: clear count and hide loader
          $countSub.text('0');
          $('#kromi-subcategories-loading').hide();
          return;
        }

        // small timeout to let spinner display for very quick operations
        setTimeout(function() {
          // If CSV maps exist and the selected category matches a CSV father, use CSV data
          if (csvMapSubs && csvMapSubs[catId]) {
            var sons = csvMapSubs[catId];
            sons.forEach(function(s) { $sub.append($('<option>').val(s).text(toTitleCase(s))); });
            $countSub.text(sons.length);
            $('#kromi-subcategories-loading').hide();
            // apply filter to DataTable by father (exact match)
            showTableLoading();
            kromiTable.column(5).search('^' + catId.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$', true, false)
                      .column(6).search('').column(7).search('').draw();
            hideTableLoading();
            return;
          }

          var cat = kromiCategories.find(function(c) { return String(c.id) === String(catId); });
          if (!cat || !cat.subcategories) {
            $countSub.text('0');
            $('#kromi-subcategories-loading').hide();
            return;
          }

          cat.subcategories.forEach(function(sc) {
            $sub.append($('<option>').val(sc.id).text(toTitleCase(sc.name)));
          });

          $countSub.text(cat.subcategories.length);
          $('#kromi-subcategories-loading').hide();
          // clear filters for father/son/grandson if server-driven categories used
          showTableLoading();
          kromiTable.column(5).search('').column(6).search('').column(7).search('').draw();
          hideTableLoading();
        }, 150);
      });

      // Populate sub-subcategories when a subcategory is selected
      // Promarket: cascade for Promarket selects and filtering
      $('#promarket-categories').on('change', function(){
        var catId = $(this).val();
        var $sub = $('#promarket-subcategories');
        var $subsub = $('#promarket-sub-subcategories');
        $sub.empty().append($('<option>').val('').text('{{ __('All') }}'));
        $subsub.empty().append($('<option>').val('').text('{{ __('All') }}'));
        $('#promarket-subcategories-loading').show();
        $('#promarket-subcategories-count').text('...');

        if (!catId) {
          $('#promarket-subcategories-count').text('0');
          $('#promarket-subcategories-loading').hide();
          // clear filters
          promarketTable.column(6).search('').column(7).search('').column(8).search('').draw();
          return;
        }

        setTimeout(function(){
          var cat = kromiCategories.find(function(c){ return String(c.id) === String(catId); });
          if (!cat || !cat.subcategories) {
            $('#promarket-subcategories-count').text('0');
            $('#promarket-subcategories-loading').hide();
            return;
          }
          cat.subcategories.forEach(function(sc){ $sub.append($('<option>').val(sc.id).text(sc.name)); });
          $('#promarket-subcategories-count').text(cat.subcategories.length);
          $('#promarket-subcategories-loading').hide();
          // filter promarketTable by father id
          showTableLoading();
          promarketTable.column(6).search('^' + String(catId).replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$', true, false).column(7).search('').column(8).search('').draw();
          hideTableLoading();
        }, 100);
      });

      // Promarket: populate sub-sub when subcategory selected
      $('#promarket-subcategories').on('change', function(){
        var subId = $(this).val();
        var $subsub = $('#promarket-sub-subcategories');
        $subsub.empty().append($('<option>').val('').text('{{ __('All') }}'));
        if (!subId) {
          $('#promarket-sub-subcategories-count').text('0');
          // clear son/grandson filters
          promarketTable.column(7).search('').column(8).search('').draw();
          return;
        }
        $('#promarket-sub-subcategories-loading').show();
        setTimeout(function(){
          var found = null;
          for (var i=0;i<kromiCategories.length;i++){
            var sc = (kromiCategories[i].subcategories || []).find(function(s){ return String(s.id) === String(subId); });
            if (sc) { found = sc; break; }
          }
          if (!found || !found.sub_subcategories) {
            $('#promarket-sub-subcategories-loading').hide();
            promarketTable.column(7).search('').column(8).search('').draw();
            return;
          }
          found.sub_subcategories.forEach(function(ss){ $subsub.append($('<option>').val(ss.id).text(ss.name)); });
          $('#promarket-sub-subcategories-count').text(found.sub_subcategories.length);
          $('#promarket-sub-subcategories-loading').hide();
          // filter promarketTable by son id
          showTableLoading();
          promarketTable.column(7).search('^' + String(subId).replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$', true, false).column(8).search('').draw();
          hideTableLoading();
        }, 100);
      });

      // Promarket: filter by sub-subcategory
      $('#promarket-sub-subcategories').on('change', function(){
        var subSubId = $(this).val();
        $('#promarket-sub-subcategories-loading').show();
        setTimeout(function(){
          if (!subSubId) {
            promarketTable.column(8).search('').draw();
            $('#promarket-sub-subcategories-loading').hide();
            return;
          }
          showTableLoading();
          promarketTable.column(8).search('^' + String(subSubId).replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$', true, false).draw();
          hideTableLoading();
          $('#promarket-sub-subcategories-loading').hide();
        }, 100);
      });
      
      $(document).on('change', '#kromi-subcategories', function() {
        var subId = $(this).val();
        var $subsub = $('#kromi-sub-subcategories');
        var $countSub = $('#kromi-subcategories-count');
        $subsub.empty().append($('<option>').val('').text('{{ __('All') }}'));
        if (!subId) {
          // if no sub selected, reset sub-sub count to 0 and clear son/grandson filters
          $countSub.text($countSub.text() || '0');
          $('#kromi-sub-subcategories-count').text('0');
          // clear son/grandson filters
          kromiTable.column(6).search('').column(7).search('').draw();
          return;
        }

        // show loading near subcategories while finding sub-subcategories
        $('#kromi-subcategories-loading').show();

        setTimeout(function() {
          // If CSV maps exist and subId matches a CSV son, use CSV grandsons
          if (csvMapSubSubs && csvMapSubSubs[subId]) {
            var grands = csvMapSubSubs[subId];
            grands.forEach(function(g) { $subsub.append($('<option>').val(g).text(toTitleCase(g))); });
            $('#kromi-sub-subcategories-count').text(grands.length);
            $('#kromi-subcategories-loading').hide();
            // apply filter to DataTable by son (exact match) and clear grandson filter
            showTableLoading();
            kromiTable.column(6).search('^' + subId.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$', true, false).column(7).search('').draw();
            hideTableLoading();
            return;
          }

          // find across all categories
          var found = null;
          for (var i=0;i<kromiCategories.length;i++){
            var sc = (kromiCategories[i].subcategories || []).find(function(s){ return String(s.id) === String(subId); });
            if (sc) { found = sc; break; }
          }
          if (!found || !found.sub_subcategories) {
            $('#kromi-subcategories-loading').hide();
            // clear son/grandson filters
            showTableLoading();
            kromiTable.column(6).search('').column(7).search('').draw();
            hideTableLoading();
            return;
          }
          found.sub_subcategories.forEach(function(ss){
            $subsub.append($('<option>').val(ss.id).text(toTitleCase(ss.name)));
          });
          // update sub-subcategories count
          $('#kromi-sub-subcategories-count').text(found.sub_subcategories.length);
          $('#kromi-subcategories-loading').hide();
          // clear son/grandson filters for server-driven flow
          showTableLoading();
          kromiTable.column(6).search('').column(7).search('').draw();
          hideTableLoading();
        }, 150);
      });

      // Filter by sub-subcategory when selected (show loader for UX)
      $(document).on('change', '#kromi-sub-subcategories', function() {
        var subSubId = $(this).val();
        // show spinner
        $('#kromi-sub-subcategories-loading').show();

        setTimeout(function() {
          if (!subSubId) {
            // clear only grandson filter
            showTableLoading();
            kromiTable.column(7).search('').draw();
            hideTableLoading();
            $('#kromi-sub-subcategories-loading').hide();
            return;
          }

          // If CSV-derived grandsons exist, filter by exact grandson match
          if (csvMapSubSubs) {
            showTableLoading();
            kromiTable.column(7).search('^' + subSubId.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$', true, false).draw();
            hideTableLoading();
            $('#kromi-sub-subcategories-loading').hide();
            return;
          }

          // server-driven flow: no table filtering available (grandson ids differ from CSV names)
          showTableLoading();
          kromiTable.column(7).search('').draw();
          hideTableLoading();
          $('#kromi-sub-subcategories-loading').hide();
        }, 150);
      });

      // Validación: al registrar, exigir que haya una categoría Promarket seleccionada
      $('#promarket-mapping').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);

        // 1) recopilar items marcados en la tabla Kromi (checkboxes) de TODAS las páginas
        var selectedItems = [];
        var allNodesForCollect = kromiTable.rows().nodes();
        $(allNodesForCollect).find('input[type="checkbox"]:checked').each(function() {
          var $row = $(this).closest('tr');
          var sku = $row.find('.kromi-sku').text().trim();
          var name = $row.find('td').eq(2).text().trim() || sku;
          var priceText = $row.find('td').eq(3).text().trim();
          var amountText = $row.find('.kromi-qty').text().trim();
          // Parse price to number
          var price = 0;
          try {
            price = Number(String(priceText).replace(/[^0-9\-\.,]/g, '').replace(/,/g, '.'));
            if (isNaN(price)) price = 0;
          } catch(e) { price = 0; }
          var amount = 0;
          try { amount = parseInt(amountText.replace(/[^0-9]/g, '')) || 0; } catch(e) { amount = 0; }
          if (sku) {
            selectedItems.push({ sku: sku, price: price, amount: amount, name: name });
          }
        });

        if (!selectedItems.length) {
          var msgNoItems = {!! json_encode(__('Seleccione al menos un producto (checkbox) de la lista importada para registrar.')) !!};
          if (typeof toastr !== 'undefined') {
            toastr.options = toastr.options || {};
            toastr.options.closeButton = true;
            toastr.options.progressBar = true;
            toastr.options.positionClass = 'toast-bottom-right';
            toastr.warning(msgNoItems);
          } else if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({ icon: 'warning', title: msgNoItems, confirmButtonText: {!! json_encode(__('OK')) !!} });
          } else { alert(msgNoItems); }
          return false;
        }

        // quitar inputs previos si existen
        $form.find('input[name="checkActive"]').remove();
        // anexar JSON de items seleccionados como input oculto
        $('<input>').attr({ type: 'hidden', name: 'checkActive' }).val(JSON.stringify(selectedItems)).appendTo($form);

        // 2) validar que exista categoría destino seleccionada
        var cat = $('#promarket-categories').val();
        if (!cat || String(cat) === '0') {
          var msgCat = {!! json_encode(__('Por favor seleccione una categoría antes de registrar.')) !!};
          if (typeof toastr !== 'undefined') {
            toastr.options = toastr.options || {};
            toastr.options.closeButton = true;
            toastr.options.progressBar = true;
            toastr.options.positionClass = 'toast-bottom-right';
            toastr.warning(msgCat);
            $('#promarket-categories').focus();
          } else if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({ icon: 'warning', title: msgCat, confirmButtonText: {!! json_encode(__('OK')) !!} }).then(function(){ $('#promarket-categories').focus(); });
          } else { alert(msgCat); $('#promarket-categories').focus(); }
          return false;
        }

        // 3) Mostrar modal de confirmación con recuento y resumen antes de enviar
        var total = selectedItems.length;
        var previewList = '<div style="max-height:240px;overflow:auto;text-align:left;">';
        selectedItems.slice(0, 20).forEach(function(it){
          var skuEsc = $('<div>').text(it.sku).html();
          var nameEsc = $('<div>').text(it.name).html();
          previewList += '<div style="margin-bottom:6px;"><strong>' + skuEsc + '</strong> — ' + nameEsc + ' <span class="text-muted">(' + (it.amount||0) + ')</span></div>';
        });
        if (selectedItems.length > 20) previewList += '<div class="text-muted">... + ' + (selectedItems.length - 20) + ' más</div>';
        previewList += '</div>';

        if (typeof Swal !== 'undefined' && Swal.fire) {
          Swal.fire({
            title: {!! json_encode(__('Confirma registro')) !!} + ' — ' + total + ' {{ __('items') }}',
            html: previewList,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: {!! json_encode(__('Confirmar')) !!},
            cancelButtonText: {!! json_encode(__('Cancelar')) !!},
            width: 700,
            allowOutsideClick: false
          }).then(function(result){
            if (result.isConfirmed) {
              $form.off('submit');
              $form.trigger('submit');
            }
          });
        } else {
          // fallback: enviar directamente
          $form.off('submit');
          $form.trigger('submit');
        }
      });

    });
  </script>
@endsection

@section('content')
<!-- Upload CSV -->
<section class="mb-2">
  <div class="card">
    <div class="card-body">
      <form id="upload-csv-form" enctype="multipart/form-data" method="POST" action="{{ route('kromi.import_csv') }}">
        @csrf
        <div class="row align-items-center">
          <div class="col-10">
            <div class="form-group mb-0">
              <label class="d-block">{{ __('Select file to upload:') }}</label>
              <input type="file" name="file" class="form-control">
            </div>
          </div>
          <div class="col-2 text-right">
            <button id="upload-csv-button" class="btn btn-warning mt-2">{{ __('Load Data') }}</button>
            <span id="upload-csv-loading" class="spinner-border spinner-border-sm text-warning ml-2" role="status" aria-hidden="true" style="display:none;"></span>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>

<!-- Main two-column layout -->
<section>
  <div class="row">
    <!-- Left: Kromi Market -->
    <div class="col-lg-6 col-12">
      <div class="card mb-2">
        <div class="card-header">
          <h5 class="mb-0">{{ __('Kromi Market') }}</h5>
        </div>
        <div class="card-body">
          <div class="card mb-2">
            <div class="card-body">
              <h6>{{ __('Search filters') }}</h6>
              <form id="kromi-filters">
                <div class="form-group">
                  <label>
                    {{ __('Categories') }}
                    {{-- <small class="text-muted" id="kromi-categories-count">0</small> --}}
                    {{-- <div class="badge badge-light-primary" id="kromi-categories-count">0</div> --}}
                  </label>
                  <select id="kromi-categories" name="category_id" class="form-control">
                    <option value="">{{ __('All') }}</option>                   
                  </select>
                </div>
                <div class="form-group">
                  <label>
                    {{ __('Subcategories') }}
                    <div class="badge badge-light-primary" id="kromi-subcategories-count">0</div>
                    <span id="kromi-subcategories-loading" class="spinner-border spinner-border-sm text-warning ml-2" role="status" aria-hidden="true" style="display:none;"></span>
                  </label>
                  <select id="kromi-subcategories" name="subcategory_id" class="form-control">
                    <option value="">{{ __('All') }}</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>
                    {{ __('Sub-Subcategories') }}
                    <div class="badge badge-light-primary" id="kromi-sub-subcategories-count">0</div>
                    <span id="kromi-sub-subcategories-loading" class="spinner-border spinner-border-sm text-warning ml-2" role="status" aria-hidden="true" style="display:none;"></span>
                  </label>
                  <select id="kromi-sub-subcategories" name="sub_subcategory_id" class="form-control">
                    <option value="">{{ __('All') }}</option>
                  </select>
                </div>
                {{-- <div class="form-group mb-0">
                  <label>{{ __('Search products by name or SKU') }}</label>
                  <div class="input-group">
                    <input type="text" class="form-control" placeholder="{{ __('Search') }}...">
                    <div class="input-group-append">
                      <button class="btn btn-warning">{{ __('Search') }}</button>
                    </div>
                  </div>
                </div> --}}
              </form>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                  <h6 class="mb-0">{{ __('Product list') }} <span id="kromi-active-count" class="badge badge-light-success">0</span></h6>
                  <div class="d-flex align-items-center">
                    <div class="form-check form-check-inline mr-2">
                      <input class="form-check-input" type="checkbox" id="kromi-filter-active">
                      <label class="form-check-label small mb-0" for="kromi-filter-active">{{ __('Show only active') }}</label>
                    </div>
                    <button id="kromi-invert-selection" type="button" class="btn btn-sm btn-outline-secondary">{{ __('Invert selection') }}</button>
                  </div>
                </div>
              </div>
            <div class="card-body p-0">
              <div class="table-responsive kromi-table-wrapper">
                <div id="kromi-table-loading" class="kromi-table-loading" style="display:none;">
                  <div class="spinner-border text-warning" role="status"><span class="sr-only">{{ __('Loading') }}</span></div>
                </div>
                <table class="table table-striped" id="kromi-products-table">
                  <thead>
                    <tr>
                      <th style="width:30px;"><input id="kromi-select-all" type="checkbox"></th>
                      <th>{{ __('SKU') }}</th>
                      <th>{{ __('Name') }}</th>
                      <th>{{ __('Cost') }}</th>
                      <th>{{ __('Quantity') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Rows cargadas dinámicamente -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: PromarketLatino -->
    <div class="col-lg-6 col-12">
      <div class="card mb-2">
        <div class="card-header">
          <h5 class="mb-0">{{ __('PromarketLatino') }}</h5>
        </div>
        <div class="card-body">
          <div class="card mb-2">
            <div class="card-body">
              <h6>{{ __('Categories to register') }}</h6>
              <form id="promarket-mapping" method="POST" action="{{ route('kromi.register') }}">
                @csrf
                <div class="form-group">
                  <label>
                    {{ __('Categories') }}
                    <div class="badge badge-light-primary" id="promarket-categories-count">0</div>
                  </label>
                  <select id="promarket-categories" name="promarket-categories" class="form-control">
                    <option value="">{{ __('All') }}</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>
                    {{ __('Sub Category') }}
                    <div class="badge badge-light-primary" id="promarket-subcategories-count">0</div>
                    <span id="promarket-subcategories-loading" class="spinner-border spinner-border-sm text-warning ml-2" role="status" aria-hidden="true" style="display:none;"></span>
                  </label>
                  <select id="promarket-subcategories" name="promarket-subcategories" class="form-control">
                    <option value="">{{ __('All') }}</option>
                  </select>
                </div>
                <div class="form-group mb-0">
                  <label>
                    {{ __('Sub-Sub Category') }}
                    <div class="badge badge-light-primary" id="promarket-sub-subcategories-count">0</div>
                    <span id="promarket-sub-subcategories-loading" class="spinner-border spinner-border-sm text-warning ml-2" role="status" aria-hidden="true" style="display:none;"></span>
                  </label>
                  <select id="promarket-sub-subcategories" name="promarket-sub-subcategories" class="form-control">
                    <option value="">{{ __('All') }}</option>
                  </select>
                </div>
                <div class="text-center mt-3">
                  <button class="btn btn-warning">{{ __('Register') }}</button>
                </div>
              </form>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">{{ __('Product list') }}</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover" id="promarket-products-table">
                  <thead>
                    <tr>
                      <th>{{ __('SKU') }}</th>
                      <th>{{ __('Name') }}</th>
                      <th>{{ __('Cost') }}</th>
                      <th>{{ __('Profit') }}</th>
                      <th>{{ __('Price') }}</th>
                      <th>{{ __('Quantity') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Rows cargadas dinámicamente -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
