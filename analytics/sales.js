(function ($) {
  let instructorsTable   = null;
  let currentSearchValue = null;

  document.addEventListener('intentTableSearch', function(event) {
      currentSearchValue = event.detail.searchValue;
      if($(event.detail.searchTarget).parents('.masterstudy-analytics-sales-page-table').data('chart-id') === 'instructor-orders-table') {
        updateTable(routes.instructorSalesTable, true);
      }
  });

  document.addEventListener('datesUpdated', function() {
    updateTable(routes.instructorSalesTable, true);
  });

  $(document).ready(function() {
    initializeDatepicker('#masterstudy-datepicker-instructor-orders');

    updateTable(routes.instructorSalesTable);
  });

  function updateTable(currentRoute, reloadTable = false) {
    const dataSrc = function (json) {
      const pageInfo = $(`#masterstudy-datatable-${currentRoute}`)
        .DataTable()
        .page.info()
      const start = pageInfo.start;

      json.data = json.data.map((item, index) => {
        item.number = start + index + 1
        return item
      });

      updateTotal(`#${currentRoute}-table-total`, json.recordsTotal);

      $('.masterstudy-analytics-sales-page__title').find('span').empty();
      $('.masterstudy-analytics-sales-page__title').append(`<span>${json.recordsTotal}</span>`);

      return json.data;
    }

    const columnDefs = [
      { targets: 0, width: '30px', orderable: false },
      { targets: 1, data: 'date', orderable: true },
      { targets: 2, data: 'user_info', orderable: true },
      { targets: 3, data: 'total_items', orderable: true },
      { targets: 4, data: 'payment_code', orderable: true },
      { targets: 5, data: 'status_name', orderable: true },
      { targets: 6, data: 'total_price', orderable: true },
      { targets: users_page_data[currentRoute].length - 1, orderable: false },
      {
        targets: users_page_data[currentRoute].length - 1,
        data: 'instructor_id',
        render: function (data, type, row) {
          const baseUrl = `${window.location.origin}${window.location.pathname.split('/').slice(0, 2).join('/')}`;
          const newUrl = `${baseUrl}/instructor-sales-details/${data}/`;

          return renderReportButton(newUrl)
        },
      },
    ]

    tableToUpdate = instructorsTable;

    tableToUpdate = updateDataTable(
      tableToUpdate,
      `#masterstudy-datatable-${currentRoute}`,
      [`[data-chart-id="${currentRoute}-table"]`],
      currentRoute,
      users_page_data[currentRoute],
      dataSrc,
      columnDefs,
      reloadTable,
      false,
      false,
      [],
      currentSearchValue,
    );

    if (routes.instructorSalesTable === currentRoute) {
      instructorsTable = tableToUpdate;
    }
  }
})(jQuery);
