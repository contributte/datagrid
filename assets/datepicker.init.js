// https://getdatepicker.com/

document.querySelectorAll('.date-filter').forEach((el) => {
    let picker = new tempusDominus.TempusDominus(el, {
        localization: {
            dateFormats: {
                L: 'dd.MM.yyyy',
            },
            format: 'L',
        },
        dateRange: false,
        keepInvalid: true,
        display: {
            theme: 'light',
            keepOpen: false,
            buttons: {
                today: true,
                clear: true,
                close: true
            },
            components: {
                calendar: true,
                date: true,
                month: true,
                year: true,
                decades: true,
                clock: false,
                hours: false,
                minutes: false,
                seconds: false
            },
            icons: {
                time: 'fa-solid fa-clock fa-fw',
                date: 'fa-solid fa-calendar fa-fw',
                up: 'fa-solid fa-arrow-up fa-fw',
                down: 'fa-solid fa-arrow-down fa-fw',
                previous: 'fa-solid fa-chevron-left fa-fw',
                next: 'fa-solid fa-chevron-right fa-fw',
                today: 'fa-solid fa-calendar-check fa-fw',
                clear: 'fa-solid fa-trash fa-fw',
                close: 'fa-solid fa-check fa-fw'
            },
        },
        useCurrent: false
    });

    picker.dates.formatInput = function (date) {
        {
            return moment(date).format('DD.MM.YYYY');
        }
    };

    picker.subscribe(tempusDominus.Namespace.events.change, (e) => {
        $(this.form).trigger("change");
    });
});

document.querySelectorAll('.date-range-filter').forEach((el) => {
    let picker = new tempusDominus.TempusDominus(el, {
        localization: {
            dateFormats: {
                L: 'dd.MM.yyyy',
            },
            format: 'L',
        },
        multipleDatesSeparator: ', ',
        dateRange: true,
        keepInvalid: true,
        display: {
            theme: 'light',
            keepOpen: false,
            buttons: {
                today: true,
                clear: true,
                close: true
            },
            components: {
                calendar: true,
                date: true,
                month: true,
                year: true,
                decades: true,
                clock: false,
                hours: false,
                minutes: false,
                seconds: false
            },
            icons: {
                time: 'fa-solid fa-clock fa-fw',
                date: 'fa-solid fa-calendar fa-fw',
                up: 'fa-solid fa-arrow-up fa-fw',
                down: 'fa-solid fa-arrow-down fa-fw',
                previous: 'fa-solid fa-chevron-left fa-fw',
                next: 'fa-solid fa-chevron-right fa-fw',
                today: 'fa-solid fa-calendar-check fa-fw',
                clear: 'fa-solid fa-trash fa-fw',
                close: 'fa-solid fa-check fa-fw'
            },
        },
        useCurrent: false
    });

    picker.dates.formatInput = function (date) {
        {
            return moment(date).format('DD.MM.YYYY');
        }
    };

    picker.subscribe(tempusDominus.Namespace.events.change, (e) => {
        $(this.form).trigger("change");
    });
});
