function createDatepicker(selector, options = {}) {
    const localeObject = flatpickr.l10ns[stats_data.locale['current_locale']];

    const defaultOptions = {
        inline: true,
        mode: 'range',
        monthSelectorType: 'static',
        locale: {
            ...localeObject,
            firstDayOfWeek: stats_data.locale['firstDayOfWeek']
        }
    };

    const finalOptions = Object.assign({}, defaultOptions, options);

    return flatpickr(selector, finalOptions);
}

function closeDatepickerModal() {
    document.querySelector('.masterstudy-datepicker-modal').classList.remove('masterstudy-datepicker-modal_open');
    document.body.classList.remove('masterstudy-datepicker-body-hidden');
}

function updateDates(period, datepicker = null, firstTime = false, saveToLocale = true) {
    if (!period) {
        return;
    }

    const periodStart = resetTime(period[0]);
    const periodEnd = resetTime(period[1]);
    const selectedStart = resetTime(selectedPeriod[0]);
    const selectedEnd = resetTime(selectedPeriod[1]);

    if (!firstTime && periodStart.getTime() === selectedStart.getTime() && periodEnd.getTime() === selectedEnd.getTime()) {
        return;
    }

    selectedPeriod = period;
    let isDefaultPeriod = false;
    let defaultPeriodKey = null;

    document.querySelectorAll('.masterstudy-datepicker-modal__single-item').forEach(function(item) {
        const periodKey = item.id.replace('masterstudy-datepicker-modal-', '');

        if (defaultDateRanges[periodKey][0].toDateString() === selectedPeriod[0].toDateString() &&
            defaultDateRanges[periodKey][1].toDateString() === selectedPeriod[1].toDateString()) {
            isDefaultPeriod = true;
            defaultPeriodKey = periodKey;
            item.classList.add('masterstudy-datepicker-modal__single-item_fill');
            if ( document.querySelector('.masterstudy-date-field-label') ) {
                document.querySelector('.masterstudy-date-field-label').textContent = item.textContent.trim();
            }
        } else {
            item.classList.remove('masterstudy-datepicker-modal__single-item_fill');
        }
    });


    if (!firstTime) {
        const event = new CustomEvent('datesUpdated', { detail: { selectedPeriod } });
        document.dispatchEvent(event);
    }

    if (datepicker) {
        datepicker.setDate(selectedPeriod, true);
    }

    if ( document.querySelector('.masterstudy-date-field-value') ) {
        document.querySelector('.masterstudy-date-field-value').textContent = `${formatDate(selectedPeriod[0])} - ${formatDate(selectedPeriod[1])}`;
    }

    if (isDefaultPeriod && saveToLocale) {
        localStorage.setItem('AnalyticsSelectedPeriodKey', defaultPeriodKey);
    } else {
        document.querySelectorAll('.masterstudy-datepicker-modal__single-item').forEach(function(item) {
            item.classList.remove('masterstudy-datepicker-modal__single-item_fill');
        });
        if ( document.querySelector('.masterstudy-date-field-label') ) {
            document.querySelector('.masterstudy-date-field-label').textContent = stats_data.custom_period;
        }
        if ( saveToLocale ) {
            localStorage.setItem('AnalyticsSelectedPeriod', JSON.stringify(selectedPeriod));
            localStorage.removeItem('AnalyticsSelectedPeriodKey');
        }
    }
}

function initializeDatepicker(selector) {
    const datepickerElement = document.querySelector(selector);
    if (!datepickerElement) {
        console.error(`Element not found for selector: ${selector}`);

        return;
    }

    const datepicker = createDatepicker(selector, {
        dateFormat: 'M d, Y',
        defaultDate: selectedPeriod,
        maxDate: new Date(),
        onClose: function(selectedDates, dateStr, instance) {
            updateDates(selectedDates, datepicker);
            closeDatepickerModal();
        }
    });

    if (!(selectedPeriod[0] instanceof Date)) {
        selectedPeriod = selectedPeriod.map(dateStr => new Date(dateStr));
    }

    updateDates(selectedPeriod, datepicker, true);

    document.querySelector('.masterstudy-datepicker-modal__reset').addEventListener('click', function() {
        datepicker.setDate(defaultDateRanges.this_week, true);
        updateDates(defaultDateRanges.this_week, datepicker);
        document.querySelector('#masterstudy-datepicker-modal-this_week').classList.add('masterstudy-datepicker-modal__single-item_fill');
        Array.from(document.querySelector('#masterstudy-datepicker-modal-this_week').parentNode.children).forEach(function(sibling) {
            if (sibling !== document.querySelector('#masterstudy-datepicker-modal-this_week')) {
                sibling.classList.remove('masterstudy-datepicker-modal__single-item_fill');
            }
        });
    });

    document.querySelector('.masterstudy-datepicker-modal__close').addEventListener('click', function() {
        closeDatepickerModal();
    });

    document.querySelectorAll('.masterstudy-datepicker-modal__single-item').forEach(function(item) {
        item.addEventListener('click', function() {
            const period = this.id.replace('masterstudy-datepicker-modal-', '');
            if (defaultDateRanges[period]) {
                datepicker.setDate(defaultDateRanges[period], true);
                updateDates(defaultDateRanges[period], datepicker);
                if ( document.querySelector('.masterstudy-date-field-label') ) {
                    document.querySelector('.masterstudy-date-field-label').textContent = this.textContent.trim();
                }
                closeDatepickerModal();
            }
        });
    });

    if ( document.querySelector('.masterstudy-date-field') ) {
        document.querySelector('.masterstudy-date-field').addEventListener('click', function() {
            document.querySelector('.masterstudy-datepicker-modal').classList.add('masterstudy-datepicker-modal_open');
            document.body.classList.add('masterstudy-datepicker-body-hidden');
        });
    }

    document.querySelector('.masterstudy-datepicker-modal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeDatepickerModal();
        }
    });
}