import registerExtension from '../utils/registerExtension';

const datagridSerializeUrl = (obj, prefix) => {
    var str = [];
    for (var p in obj) {
        if (obj.hasOwnProperty(p)) {
            var k = prefix ? prefix + '[' + p + ']' : p, v = obj[p];
            if (v !== null && v !== '') {
                if (typeof v == 'object') {
                    var r = datagridSerializeUrl(v, k);
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
                }
            }
        }
    }
});