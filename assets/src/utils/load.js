<<<<<<< HEAD
=======
import $ from 'jquery';

>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
import use, {NETTE_AJAX, NAJA} from './resolveDependency';

let load = () => { };

if (use === NAJA) {
<<<<<<< HEAD
=======

>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
    load = () => require('naja').load();
}

if(use === NETTE_AJAX) {
<<<<<<< HEAD
    load = () => require('netteAjax').load();
=======
    load = () => $.nette.load();
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
}

export default load;
