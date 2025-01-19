let isDomReady = false;

document.addEventListener('DOMContentLoaded', function() {
    isDomReady = true;

    if(document.querySelectorAll('input[id*="-search"]').length) {
        document.querySelectorAll('input[id*="-search"]').forEach(function(selector) {
            selector.addEventListener('keydown', function (e) {
                const value = this;
                if (window.search_field_intent_timeout)
                    clearTimeout(window.search_field_intent_timeout);
                if (e.keyCode === 13) {
                    searchFieldIntent(value);
                } else {
                    window.search_field_intent_timeout = setTimeout(function () {
                        searchFieldIntent(value)
                    }, 1000);
                }
            });
        })
    }

    document.addEventListener('click', function (event) {
        if (event.target.matches('div[class*="__search-dropdown-item"]')) {
            const item = event.target;
            const value = item.textContent.trim();
            const dropdown = item.parentNode;
            const input = dropdown.parentNode.querySelector('input');

            if (input) {
                input.value = value;
            }

            // Grade Search Fields
            if (input.classList.contains('grades-search')) {
                input.dataset.id = item.dataset.id;
            }

            searchFieldIntent(input);
        }
    });

    if (document.querySelectorAll('span[class*="__search-icon"]')) {
        document.querySelectorAll('span[class*="__search-icon"]').forEach(function(selector) {
            selector.addEventListener('click', function (e) {
                if (this.parentNode.querySelector('input').value !== '') {
                    searchFieldIntent(this.parentNode.querySelector('input'))
                }
            })
        });
    }
});

function createDataTable(selector, columns, additionalOptions = {}) {
    const defaultOptions = {
        data: [],
        retrieve: true,
        processing: true,
        serverSide: true,
        columns: columns,
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: {
                paging: {
                    numbers: 5,
                }
            },
            bottomEnd: {
                pageLength: {
                    menu: [10, 25, 50],
                }
            },
        },
        language: {
            lengthMenu: '_MENU_' + table_data.per_page_placeholder,
            emptyTable: table_data.not_available,
            zeroRecords: table_data.not_found,
        }
    };

    const options = Object.assign({}, defaultOptions, additionalOptions);

    return new DataTable(selector, options);
}

function updateDataTable(table, selector, loaders, currentRoute, pageData, dataSrcCallback, columnDefs = [], reloadTable = false, hidePagination = false, isLessonsTable = false, lessonsData = [], searchFieldValue = '', orderIndex = '') {
    if (!isDomReady) return;

    if (!table || reloadTable) {
        loaders.forEach(loader => {
            showLoaders(loader);
        });

        if (table) {
            table.clear().destroy();
            table = null;
            jQuery(selector).empty();
        }

        let additionalOptions = {
            order: [[0, 'desc']],
            ajax: {
                url: api.getRouteUrl(currentRoute),
                type: 'POST',
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', api.getRouteNonce());
                },
                data: function (d) {
                    d.date_from = getDateFrom();
                    d.date_to = getDateTo();
                    d.search.value = searchFieldValue;
                    if(selector === '#masterstudy-datatable-lessons') {
                        d.type = document.getElementById('masterstudy-analytics-course-page-types').value;
                    }
                },
                dataSrc: dataSrcCallback,
                complete: function() {
                    loaders.forEach(loader => {
                        hideLoaders(loader);
                    });
                }
            },
            columnDefs: columnDefs,
        };

        if ( orderIndex ) {
            additionalOptions.order = [[orderIndex, 'desc']];
        }

        if (isLessonsTable) {
            additionalOptions.ajax.data = function (d) {
                d.date_from = getDateFrom();
                d.date_to = getDateTo();
                d.search.value = searchFieldValue;
                if(document.getElementById('masterstudy-analytics-course-page-orders')) {
                    d.sort = document.getElementById('masterstudy-analytics-course-page-orders').value;
                }
            }
            lessonsData.forEach(item => {
                pageData.push({
                    title: '<img src="' + table_data.img_route + '/assets/icons/lessons/' + item.lesson_type + '.svg' + '" class="masterstudy-datatables-lesson-icon"></img>' + item.lesson_name,
                    data: item.lesson_id,
                    orderable: false,
                    tooltip: item.lesson_name,
                    render: function (data, type, row, meta) {
                        if (data === '-') {
                            return `<div class="masterstudy-datatables-lesson-type masterstudy-datatables-lesson-type_progress">
                                <div class="masterstudy-datatables-lesson-tooltip">` + table_data.progress_lesson + `</div></div>`;
                        }else if (data === '0') {
                            return `<div class="masterstudy-datatables-lesson-type">
                                <div class="masterstudy-datatables-lesson-tooltip">` + table_data.not_started_lesson + `</div></div>`;
                        } else if (data === '1') {
                            return `<div class="masterstudy-datatables-lesson-type masterstudy-datatables-lesson-type_complete">
                                <div class="masterstudy-datatables-lesson-tooltip">` + table_data.completed_lesson + `</div></div>`;
                        } else if (data === '-1') {
                            return `<div class="masterstudy-datatables-lesson-type masterstudy-datatables-lesson-type_failed">
                                <div class="masterstudy-datatables-lesson-tooltip">` + table_data.failed_lesson + `</div></div>`;
                        }
                    }
                });
            });

            pageData.push({
                title: '',
                data: 'last',
                orderable: false
            });

            additionalOptions = {
                ...additionalOptions,
                columnDefs: [
                    { targets: 0, width: '30px', orderable: false },
                ],
                headerCallback: function(nHead) {
                    if (!jQuery(nHead).find('.masterstudy-datatables-skew').length) {
                        jQuery(nHead).find('th.dt-orderable-none').not('[data-dt-column="0"]').wrapInner(
                            '<div class="masterstudy-datatables-skew"><div class="masterstudy-datatables-skew__wrapper"><div class="masterstudy-datatables-skew__container"></div></div><div class="masterstudy-datatables-skew__lines"></div></div>'
                        );
                    }
                },
                initComplete: function() {
                    loaders.forEach(loader => {
                        hideLoaders(loader);
                    });

                    this.api().columns().header().to$().each(function() {
                        jQuery(this).find('.masterstudy-datatables-skew')
                            .append('<div class="masterstudy-datatables-skew__tooltip"><span>' + jQuery(this).text() + '</span></div>')
                    });
                    jQuery('.masterstudy-datatables-skew__lines')
                        .mouseover(function(event) {
                            jQuery(this).parent().find('.masterstudy-datatables-skew__tooltip').addClass('masterstudy-datatables-skew__tooltip_active');
                        })
                        .mouseout(function() {
                            jQuery(this).parent().find('.masterstudy-datatables-skew__tooltip').removeClass('masterstudy-datatables-skew__tooltip_active');
                        });
                },
                columns: pageData,
            };
        }

        // Initialize the DataTable
        table = createDataTable(selector, pageData, additionalOptions);
        observeTableChanges(table, hidePagination);
    }

    return table;
}

function tablePaginationVisibility(table, hide = false) {
    const tableWrapper = table.table().container();
    const paginationStart = tableWrapper.querySelector('.dt-layout-cell.dt-start');
    const paginationEnd = tableWrapper.querySelector('.dt-layout-cell.dt-end');

    if (table.data().count() === 0 || hide) {
        if (paginationStart) paginationStart.style.display = 'none';
        if (paginationEnd) paginationEnd.style.display = 'none';
    } else {
        if (paginationStart) paginationStart.style.display = '';
        if (paginationEnd) paginationEnd.style.display = '';
    }
}

function observeTableChanges(table, hide = false) {
    const tableWrapper = table.table().container();
    const observer = new MutationObserver(function() {
        tablePaginationVisibility(table, hide);
    });

    observer.observe(tableWrapper, {
        childList: true,
        subtree: true,
    });

    const intersectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                table.columns.adjust();
            }
        });
    }, { threshold: 0.1 });

    intersectionObserver.observe(tableWrapper);
}

function searchFieldIntent(target) {
    // Dispatch a custom event with the search value
    const searchEvent = new CustomEvent('intentTableSearch', {
        detail: {
            searchValue: target.value.trim(),
            searchTarget: target,
        }
    });
    document.dispatchEvent(searchEvent);
}