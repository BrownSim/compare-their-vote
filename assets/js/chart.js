import * as am5 from '@amcharts/amcharts5/index';
import * as am5percent from '@amcharts/amcharts5/percent';

import {BeeswarmChart} from './chart/beeswarmChart';
import {DotTrends} from './chart/dotTrends';
import {DotAverageByCategory} from './chart/dotAverageByCategory';
import {Map} from './chart/map';

window.addEventListener('load', function () {
    'use strict';

    document.querySelectorAll('[data-chart-type]').forEach(el => {
        let data = JSON.parse(el.dataset.value);
        let title = el.dataset.title;
        chartDonuts(el, data, title)
    });

    document.querySelectorAll('[data-scatter-plot]').forEach(el => {
        let data = JSON.parse(el.dataset.value);
        // scatterPlot(el, data);
    });

    function chartDonuts(el, data, title) {
        am5.ready(function() {
            var root = am5.Root.new(el);
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
                am5.color('#6794DC'),
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

    if (document.querySelector('#beeswarm') !== null) {
        let data = JSON.parse(document.querySelector('#beeswarm').dataset.json);
        let label = document.querySelector('#beeswarm').dataset.chartX
        let chart = BeeswarmChart(data, {
            x: d => d.ratio,
            xDomain: [0, 100],
            label: label,
            width: 1200,
            radius: 4,
            tooltipBody: d => d.tooltip,
            tooltipTitle: d => d.member,
            fill: d => d.color,
            marginBottom: 40
        })

        document.querySelector('#beeswarm').append(chart);
    }

    if (document.querySelector('#dottrends') !== null) {
        const dottrends = document.querySelector('#dottrends');
        let data = JSON.parse(dottrends.dataset.json);
        let chart = DotTrends(data.data, {
            tooltipBody: d => d.tooltip,
            tooltipTitle: d => d.member,
            xTitle: data.label.x,
            yTitle: data.label.y,
            xData: d => d.total,
            yData: d => d.prediction.gap,
        });

        dottrends.append(chart);
    }

    if (document.querySelector('#dot-average-category') !== null) {
        const dom = document.querySelector('#dot-average-category');
        dom.append(DotAverageByCategory(JSON.parse(dom.dataset.json)));
    }

    if (document.querySelector('#absenteeism-map') !== null) {
        const data = JSON.parse(document.querySelector('#absenteeism-map').dataset.json);
        let chart = Map(data);

        document.querySelector('#absenteeism-map').append(chart);
    }
});
