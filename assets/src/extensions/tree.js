import $ from 'jquery';

import registerExtension from '../utils/registerExtension';
import load from '../utils/load';
import sortableTree from '../actions/sortableTree';


registerExtension('datagrid.tree', {
    before(xhr, settings) {
        if (settings.nette && settings.nette.el.attr('data-toggle-tree')) {
            settings.nette.el.toggleClass('toggle-rotate');
            const children_block = settings.nette.el.closest('.datagrid-tree-item').find('.datagrid-tree-item-children').first();

            if (children_block.hasClass('loaded')) {
                children_block.slideToggle('fast');

                return false;
            }
        }

        return true;
    },

    success(payload) {
        if (payload._datagrid_tree) {
            const id = payload._datagrid_tree;
            const children_block = $(`.datagrid-tree-item[data-id="${id}"]`).find('.datagrid-tree-item-children').first();
            children_block.addClass('loaded');

            for (let name in payload.snippets) {
                const snippet = payload.snippets[name];
                const content = $(snippet);
                const template = $(`<div class="datagrid-tree-item" id="${name}">`);
                template.attr('data-id', content.attr('data-id'));
                template.append(content);

                if (content.data('has-children')) {
                    template.addClass('has-children');
                }


                children_block.append(template);
            }

            children_block.addClass('loaded');
            children_block.slideToggle('fast');

            load();
        }

        return sortableTree();
    }
});