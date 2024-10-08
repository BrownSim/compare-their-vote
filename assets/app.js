import * as bootstrap from 'bootstrap';
import AOS from 'aos';

import './js/collection.js';
import './js/select';
import './js/chart';
import './js/filter';
import './js/lazy-loading';
import './js/datatable';
import './js/mathajax';

import './scss/app.scss';

window.addEventListener('load', function () {
    'use strict';

    AOS.init();

    const mapTypeChoice = document.querySelector('[data-maptype]');
    if (mapTypeChoice) {
        mapTypeChoice.addEventListener('change', (event) => {
            let target = event.target;

            mapTypeChoice.querySelectorAll('input[type=radio]').forEach((el) => {
                document.querySelector('[' + el.getAttribute('data-target') + ']').style.display = 'none';
            })

            document.querySelectorAll('['+target.getAttribute('data-target')+']').forEach((el) => {
               el.style.display = '';
            });

            document.querySelector('[data-related-country]').style.display = '';
        })
    }

    document.querySelectorAll('[data-form-loading]').forEach((el) => {
        el.addEventListener('submit', (e) => {
            e.target.classList.add('loading');
        });
    });

    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    })
});



