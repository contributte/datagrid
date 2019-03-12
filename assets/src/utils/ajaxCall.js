<<<<<<< HEAD
=======
import $ from 'jquery';

>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
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
<<<<<<< HEAD
    ajaxCall = (settings) => require('netteAjax').ajax(settings);
=======
    ajaxCall = (settings) => $.nette.ajax(settings);
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
}

export default ajaxCall;
