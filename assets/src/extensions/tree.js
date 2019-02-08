import $ from 'jquery';

import registerExtension from '../utils/registerExtension';
import load from '../utils/load';
import sortableTree from '../actions/sortableTree';

registerExtension('datagrid.tree', {
    before: function (xhr, settings) {
        var children_block;
        if (settings.nette && settings.nette.el.attr('data-toggle-tree')) {
            settings.nette.el.toggleClass('toggle-rotate');
            children_block = settings.nette.el.closest('.datagrid-tree-item').find('.datagrid-tree-item-children').first();
            if (children_block.hasClass('loaded')) {
                children_block.slideToggle('fast');
                return false;
            }
        }
        return true;
    },
    success: function (payload) {
        var children_block, content, id, name, ref, snippet, template;
        if (payload._datagrid_tree) {
            id = payload._datagrid_tree;
            children_block = $('.datagrid-tree-item[data-id="' + id + '"]').find('.datagrid-tree-item-children').first();
            children_block.addClass('loaded');
            ref = payload.snippets;
            for (name in ref) {
                snippet = ref[name];
                content = $(snippet);
                template = $('<div class="datagrid-tree-item" id="' + name + '">');
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