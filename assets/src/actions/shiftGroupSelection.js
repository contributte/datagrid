import $ from 'jquery';

const getEventDomPath = (e) => {
    if (e && e.hasOwnProperty('path')) {
        return e.path;
    }

    let path = [];
    let node = e.target;
    while (node !== document.body && node !== null) {
        path.push(node);
        node = node.parentNode;
    }
    return path;
};

export default function () {
    var last_checkbox;
    last_checkbox = null;
    return document.addEventListener('click', function (e) {
        var checkboxes_rows, current_checkbox_row, el, event, i, ie, input, j, k, last_checkbox_row, last_checkbox_tbody, len, len1, len2, ref, ref1, results, row, rows;
        ref = getEventDomPath(e);
        for (i = 0, len = ref.length; i < len; i++) {
            el = ref[i];
            if ($(el).is('.col-checkbox') && last_checkbox && e.shiftKey) {
                current_checkbox_row = $(el).closest('tr');
                last_checkbox_row = last_checkbox.closest('tr');
                last_checkbox_tbody = last_checkbox_row.closest('tbody');
                checkboxes_rows = last_checkbox_tbody.find('tr').toArray();
                if (current_checkbox_row.index() > last_checkbox_row.index()) {
                    rows = checkboxes_rows.slice(last_checkbox_row.index(), current_checkbox_row.index());
                } else if (current_checkbox_row.index() < last_checkbox_row.index()) {
                    rows = checkboxes_rows.slice(current_checkbox_row.index() + 1, last_checkbox_row.index());
                }
                if (!rows) {
                    return;
                }
                for (j = 0, len1 = rows.length; j < len1; j++) {
                    row = rows[j];
                    input = $(row).find('.col-checkbox input[type=checkbox]')[0];
                    if (input) {
                        input.checked = true;
                        ie = window.navigator.userAgent.indexOf('MSIE ');
                        if (ie) {
                            event = document.createEvent('Event');
                            event.initEvent('change', true, true);
                        } else {
                            event = new Event('change', {
                                'bubbles': true
                            });
                        }
                        input.dispatchEvent(event);
                    }
                }
            }
        }
        ref1 = getEventDomPath(e);
        results = [];
        for (k = 0, len2 = ref1.length; k < len2; k++) {
            el = ref1[k];
            if ($(el).is('.col-checkbox')) {
                results.push(last_checkbox = $(el));
            } else {
                results.push(undefined);
            }
        }
        return results;
    });
}