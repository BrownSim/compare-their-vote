import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import 'bootstrap/dist/css/bootstrap.min.css';
import 'tom-select/dist/css/tom-select.bootstrap5.min.css';

import TomSelect from 'tom-select';
import 'bootstrap';

import { registerables, Chart } from 'chart.js';

import './js/collection.js';
import './styles/app.css';

window.addEventListener('load', function () {
    'use strict';

    document.querySelectorAll('.collection-list').forEach(el => {
        el.addEventListener('collection-append-new-item', (event) => {
            let select = event.detail.querySelector('select');
            if (null !== select) {
                new TomSelect(select, {});
            }
        });
    });

    Chart.register(...registerables);
    document.querySelectorAll('[data-chart-type]').forEach(el => {
        let data = JSON.parse(el.dataset.value);
        new Chart(el, {
            type: el.dataset.chartType,
            data: data
        });
    });

    document.querySelectorAll('[data-select]').forEach(el  => {
        new TomSelect(el, {});
    });
});




