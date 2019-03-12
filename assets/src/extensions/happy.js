import $ from 'jquery';

import registerExtension from '../utils/registerExtension';


registerExtension('datagrid.happy', {
    success() {
        if (window.happy) {
            window.happy.reset();
        }

        const grids = $('.datagrid');

        return (() => {
            const result = [];
            for (let grid of Array.from(grids)) {
                const classes = grid.classList;
                let class_selector = '';

                for (let c of Array.from(classes)) {
                    class_selector = class_selector + '.' + c;
                }


                const checked_rows = document.querySelectorAll(class_selector + ' ' + 'input[data-check]:checked');

                if ((checked_rows.length === 1) && (checked_rows[0].getAttribute('name') === 'toggle-all')) {
                    const input = document.querySelector(class_selector + ' input[name=toggle-all]');

                    if (input) {
                        let event;
                        input.checked = false;
                        const ie = window.navigator.userAgent.indexOf('MSIE ');

                        if (ie) {
                            event = document.createEvent('Event');
                            event.initEvent('change', true, true);
                        } else {
                            event = new Event('change', {'bubbles': true});
                        }

                        result.push(input.dispatchEvent(event));
                    } else {
                        result.push(undefined);
                    }
                } else {
                    result.push(undefined);
                }
            }
            return result;
        })();
    }
});
