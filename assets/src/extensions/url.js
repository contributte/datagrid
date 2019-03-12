import registerExtension from '../utils/registerExtension';

const serializeUrl = function(obj, prefix) {
    const str = [];
    for(let p in obj) {
        if (obj.hasOwnProperty(p)) {
            const k = prefix ? prefix + '[' + p + ']' : p, v = obj[p];
            if (v !== null && v !== '') {
                if (typeof v == 'object') {
                    const r = serializeUrl(v, k);
                    if (r) {
                        str.push(r);
                    }
                } else {
                    str.push(encodeURIComponent(k) + '=' + encodeURIComponent(v));
                }
            }
        }
    }
    return str.join('&');
};



registerExtension('datagrid.url', {
    success(payload) {
        if (payload._datagrid_url) {
            if (window.history.pushState) {
                let url;
                const host = window.location.protocol + '//' + window.location.host;
                const path = window.location.pathname;
                const query = serializeUrl(payload.state).replace(/&+$/gm,'');

                if (query) {
                    url = host + path + '?'+ query.replace(/\&*$/, '');
                } else {
                    url = host + path;
                }

                url += window.location.hash;

                if (window.location.href !== url) {
                    return window.history.pushState({path: url}, '', url);
                }
            }
        }
    }
});