import $ from 'jquery';

const getEventDomPath = function(e) {
    if (Array.from(e).includes(path)) {
        return e.path;
    }

    const path = [];
    let node = e.target;

    while (node !== document.body) {
        if (node === null) {
            break;
        }
        path.push(node);
        node = node.parentNode;
    }

    return path;
};

export default function() {
    let last_checkbox = null;

    return document.addEventListener('click', function(e) {
        let el;
        for (el of Array.from(getEventDomPath(e))) {
            if ($(el).is('.col-checkbox') && last_checkbox && e.shiftKey) {
                let rows;
                const current_checkbox_row = $(el).closest('tr');

                const last_checkbox_row = last_checkbox.closest('tr');
                const last_checkbox_tbody = last_checkbox_row.closest('tbody');

                const checkboxes_rows = last_checkbox_tbody.find('tr').toArray();

                if (current_checkbox_row.index() > last_checkbox_row.index()) {
                    rows = checkboxes_rows.slice(last_checkbox_row.index(), current_checkbox_row.index());

                } else if (current_checkbox_row.index() < last_checkbox_row.index()) {
                    rows = checkboxes_rows.slice(current_checkbox_row.index() + 1, last_checkbox_row.index());
                }

                if (!rows) {
                    return;
                }

                for (let row of Array.from(rows)) {
                    const input = $(row).find('.col-checkbox input[type=checkbox]')[0];

                    if (input) {
                        let event;
                        input.checked = true;

                        const ie = window.navigator.userAgent.indexOf('MSIE ');

                        if (ie) {
                            event = document.createEvent('Event');
                            event.initEvent('change', true, true);
                        } else {
                            event = new Event('change', {'bubbles': true});
                        }

                        input.dispatchEvent(event);
                    }
                }
            }
        }


        return (() => {
            const result = [];
            for (el of Array.from(getEventDomPath(e))) {
                if ($(el).is('.col-checkbox')) {
                    result.push(last_checkbox = $(el));
                } else {
                    result.push(undefined);
                }
            }
            return result;
        })();
    });
}