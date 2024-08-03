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

    document.querySelectorAll('[data-chart-type]').forEach(el => {
        let data = JSON.parse(el.dataset.value);
        let title = el.dataset.title;
        chartDonuts(el, data, title)
    });

    document.querySelectorAll('select').forEach(el  => {
        new TomSelect(el, {});
    });

    function chartDonuts(el, data, title) {
        am5.ready(function() {
            var root = am5.Root.new(el);

            root.setThemes([
                am5themes_Animated.new(root)
            ]);

            root.container.set('layout', root.verticalLayout);

            var chartContainer = root.container.children.push(am5.Container.new(root, {
                layout: root.horizontalLayout,
                width: am5.p100,
                height: am5.p100
            }));

            var chart = chartContainer.children.push(
                am5percent.PieChart.new(root, {
                    endAngle: 270,
                    innerRadius: am5.percent(60)
                })
            );

            var series = chart.series.push(
                am5percent.PieSeries.new(root, {
                    valueField: 'value',
                    categoryField: 'category',
                    endAngle: 270,
                    alignLabels: false
                })
            );

            series.get("colors").set("colors", [
                am5.color('#67B7DC'),
                am5.color('#DC6788'),
            ]);

            series.children.push(am5.Label.new(root, {
                centerX: am5.percent(50),
                centerY: am5.percent(50),
                text: title,
                populateText: true,
                fontSize: '1.5em'
            }));

            series.slices.template.setAll({
                cornerRadius: 8
            })

            series.states.create('hidden', {
                endAngle: -90
            });

            series.labels.template.setAll({
                textType: 'circular'
            });

            series.data.setAll(data.dataset);

            series.appear(1000, 100);
        });
    }
});



