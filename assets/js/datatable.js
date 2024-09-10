import Datatable from './plugin/datatable'

window.addEventListener('load', function () {
    'use strict';

    initDatatable();
});

function initDatatable() {
    document.querySelectorAll('[data-datatable]').forEach(function(table) {
        new Datatable(table, {
            nbElement: table.dataset.nbElement
        });
    });
}
