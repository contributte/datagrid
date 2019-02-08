import $ from 'jquery';

export const NETTE_AJAX = 'NETTE_AJAX';

export const NAJA = 'NAJA';

let use = '';

try {
    require('naja');
    use = NAJA;
} catch(e) {
    if($.nette !== undefined) 
        use = NETTE_AJAX;
    else 
        throw 'Ublaboo Datagrid requires naja.js or natte-ajax!';
}

export default use;

