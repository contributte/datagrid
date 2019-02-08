import $ from 'jquery';

import use, {NETTE_AJAX, NAJA} from './resolveDependency';

let load = () => { };

if (use === NAJA) {

    load = () => require('naja').load();
}

if(use === NETTE_AJAX) {
    load = () => $.nette.load();
}

export default load;
