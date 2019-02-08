import $ from 'jquery';

import use, {NETTE_AJAX, NAJA} from './resolveDependency';

let ajaxCall = () => { };

if (use === NAJA) {

    ajaxCall = ({type, data, url, error, success}) => {
        const method = type || 'GET';
        data = data || null;

        require('naja').makeRequest(method, url, data, {}).then(success).catch(error);
    };
        
}

if(use === NETTE_AJAX) {
    ajaxCall = (settings) => $.nette.ajax(settings);
}

export default ajaxCall;
