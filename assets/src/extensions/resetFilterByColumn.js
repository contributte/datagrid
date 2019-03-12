import $ from 'jquery';

import registerExtension from '../utils/registerExtension';

registerExtension('datagrid.reset-filter-by-column', {
    success(payload) {
        if (!payload._datagrid_name) {
            return;
        }

        const grid = $(`.datagrid-${payload._datagrid_name}`);

        // Show/hide reset-fitler indecators
        //
        grid.find('[data-datagrid-reset-filter-by-column]').addClass('hidden');

        if (payload.non_empty_filters && payload.non_empty_filters.length) {
            let key;
            for (key of Array.from(payload.non_empty_filters)) {
                grid.find(`[data-datagrid-reset-filter-by-column=${key}]`).removeClass('hidden');
            }

            // Refresh their url (table header is not refreshed using snippets)
            //
            const href = grid.find('.reset-filter').attr('href');

            return grid.find('[data-datagrid-reset-filter-by-column]').each(function() {
                key = $(this).attr('data-datagrid-reset-filter-by-column');

                let new_href = href.replace(`do=${payload._datagrid_name}-resetFilter`, `do=${payload._datagrid_name}-resetColumnFilter`);
                new_href += `&${payload._datagrid_name}-key=${key}`;

                return $(this).attr('href', new_href);
            });
        }
    }
});