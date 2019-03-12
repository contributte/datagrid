export const NETTE_AJAX = 'NETTE_AJAX';

export const NAJA = 'NAJA';

let use = '';

try {
    require('naja');
    use = NAJA;
} catch(e) {

    try {
        require('netteAjax');
        use = NETTE_AJAX;
    } catch(e) {
        throw 'Ublaboo Datagrid requires naja.js or natte-ajax!';
    }
}

export default use;

