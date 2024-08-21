import TomSelect from 'tom-select';

const tomSelectConfig = {
    maxOptions: 10000,
    labelField: 'name',
    searchField: 'name',
    render: {
        option: function(data, escape) {
            return '<div>' +
                '<span class="pe-1">' + escape(data.name) + '</span>' + ' - ' +
                '<small class="ps-1 text-muted">' + escape(data.group) + '</small>' +
                '</div>'
                ;
        },
        item: function(data, escape) {
            return '<div>' +
                '<span class="pe-1">' + escape(data.name) + '</span>' + ' - ' +
                '<small class="ps-1 text-muted">' + escape(data.group) + '</small>' +
                '</div>'
                ;
        }
    }
};

window.addEventListener('load', function () {
    'use strict';

    document.querySelectorAll('.collection-list').forEach(el => {
        el.addEventListener('collection-append-new-item', (event) => {
            let select = event.detail.querySelector('select');
            if (null !== select) {
                initTomSelect(select);
            }
        });
    });

    document.querySelectorAll('select').forEach(el  => {
        initTomSelect(el);
    });

    function initTomSelect(el) {
        let config = tomSelectConfig;
        let options = {};
        if (el.hasAttribute('data-custom-provider')) {
            options = {options: tomSelectDataProvider(el.getAttribute('data-custom-provider'))};
        } else {
            config = {};
        }

        new TomSelect(el, {...config, ...options});
    }

    function tomSelectDataProvider(attr) {
        let data = document.querySelector('[' + attr + ']').getAttribute(attr);

        return JSON.parse(data);
    }
});
