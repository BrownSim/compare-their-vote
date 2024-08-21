import './js/collection.js';
import './js/select';
import './js/chart';

import './styles/app.css';

window.addEventListener('load', function () {
    'use strict';

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
        })
    }
});



