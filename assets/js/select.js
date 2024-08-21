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
                let options = {options: tomSelectMembersData()};
                new TomSelect(select, {...tomSelectConfig, ...options});
            }
        });
    });

    document.querySelectorAll('select').forEach(el  => {
        let options = {options: tomSelectMembersData()};
        new TomSelect(el, {...tomSelectConfig, ...options});
    });

    function tomSelectMembersData() {
        let data = document.querySelector('[data-members-detail]').getAttribute('data-members-detail');

        return JSON.parse(data);
    }
});
