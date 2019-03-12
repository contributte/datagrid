import use, {NETTE_AJAX, NAJA} from './resolveDependency';

export default function (name, extension) {
    

    if(use === NAJA) {
        
        const { init, success, before, complete } = extension;

        const NewExtension = class {
            constructor(naja, name) {
                this.name = name;

                if(init) {
                    naja.addEventListener('init', ({ defaultOptions }) => {
                        init(defaultOptions);
                    });
                }
                
                if(success) {
                    naja.addEventListener('success', ({ response, options }) => {
                        success(response, options);
                    });
                }

                if(before) {
                    naja.addEventListener('before', ({ xhr, options }) => {
                        before(xhr, options);
                    });
                }

                if(complete) {
                    naja.addEventListener('complete', ({ xhr, options }) => {
                        complete(xhr, options);
                    });
                }
            }
        };
        
        require('naja').registerExtension(NewExtension, name);
    }

    if(use === NETTE_AJAX) {
        require('netteAjax').ext(name, extension);
    }

}