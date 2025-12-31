/* Generic module list initializer
   Initializes DataTables for tables with class `module-list-table` and
   wires action buttons container `.dt-action-buttons` for Add New.
*/
$(function () {
  'use strict';

  var table = $('.module-list-table');

  if (table.length) {
    table.DataTable({
      responsive: true,
      order: [[0, 'desc']],
      columnDefs: [
        { orderable: false, targets: -1 }
      ],
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      language: {
        url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
      },
      drawCallback: function () {
        if (window.feather) {
          feather.replace({ width: 14, height: 14 });
        }
      }
    });
  }

  // Initialize tooltips
  $('body').tooltip({
    selector: '[data-toggle="tooltip"]',
    container: 'body'
  });
});