import $ from 'jquery';

import registerExtension from '../utils/registerExtension';

registerExtension('datagrid.happy', {
    success: function () {
        var c, checked_rows, class_selector, classes, event, grid, grids, i, ie, input, j, len, len1, results;
        if (window.happy) {
            window.happy.reset();
        }
        grids = $('.datagrid');
        results = [];
        for (i = 0, len = grids.length; i < len; i++) {
            grid = grids[i];
            classes = grid.classList;
            class_selector = '';
            for (j = 0, len1 = classes.length; j < len1; j++) {
                c = classes[j];
                class_selector = class_selector + '.' + c;
            }
            checked_rows = document.querySelectorAll(class_selector + ' ' + 'input[data-check]:checked');
            if (checked_rows.length === 1 && checked_rows[0].getAttribute('name') === 'toggle-all') {
                input = document.querySelector(class_selector + ' input[name=toggle-all]');
                if (input) {
                    input.checked = false;
                    ie = window.navigator.userAgent.indexOf('MSIE ');
                    if (ie) {
                        event = document.createEvent('Event');
                        event.initEvent('change', true, true);
                    } else {
                        event = new Event('change', {
                            'bubbles': true
                        });
                    }
                    results.push(input.dispatchEvent(event));
                } else {
                    results.push(undefined);
                }
            } else {
                results.push(undefined);
            }
        }
        return results;
    }
});
