window.addEventListener('load', function () {
    'use strict';

    const mpFilter = document.querySelector('[data-mp-filter]');
    const mpFilterTypes = ['data-mp-group', 'data-mp-country', 'data-mp-party'];

    if (mpFilter) {
        mpFilter.addEventListener('change', (event) => {
            document.querySelectorAll('.grid-list-mp a').forEach((el) => {
                el.style.display = '';
            });

            mpFilter.querySelectorAll('option:checked').forEach((option) => {
                mpFilterTypes.forEach((attr) => {
                    if (option.hasAttribute(attr)) {
                        document.querySelectorAll('.grid-list-mp a:not(['+attr+'="'+option.getAttribute(attr)+'"]').forEach((el) => {
                            el.style.display = 'none';
                        });
                    }
                });
            });
        });
    }
});
