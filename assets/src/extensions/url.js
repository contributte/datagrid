import registerExtension from '../utils/registerExtension';

<<<<<<< HEAD
const serializeUrl = function(obj, prefix) {
    const str = [];
    for(let p in obj) {
        if (obj.hasOwnProperty(p)) {
            const k = prefix ? prefix + '[' + p + ']' : p, v = obj[p];
            if (v !== null && v !== '') {
                if (typeof v == 'object') {
                    const r = serializeUrl(v, k);
=======
const datagridSerializeUrl = (obj, prefix) => {
    var str = [];
    for (var p in obj) {
        if (obj.hasOwnProperty(p)) {
            var k = prefix ? prefix + '[' + p + ']' : p, v = obj[p];
            if (v !== null && v !== '') {
                if (typeof v == 'object') {
                    var r = datagridSerializeUrl(v, k);
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
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

<<<<<<< HEAD


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
=======
registerExtension('datagrid.url', {
    success: function (payload) {
        var host, path, query, url;
        if (payload._datagrid_url) {
            if (window.history.pushState) {
                host = window.location.protocol + '//' + window.location.host;
                path = window.location.pathname;
                query = datagridSerializeUrl(payload.state).replace(/&+$/gm, '');
                if (query) {
                    url = host + path + '?' + query.replace(/\&*$/, '');
                } else {
                    url = host + path;
                }
                url += window.location.hash;
                if (window.location.href !== url) {
                    return window.history.pushState({
                        path: url
                    }, '', url);
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
                }
            }
        }
    }
});