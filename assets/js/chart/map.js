import * as topojson from "topojson";
import ue from "./ue.json";
import * as d3 from "d3";

export function Map(data) {
    const states = topojson.feature(ue, ue.objects.nutsrg).features;

    states.forEach(state => {
        let datumState = data.find(datum => datum.id === state.properties.id);

        if (datumState) {
            state.color = datumState.color;
            state.tooltip = datumState.tooltip;
            state.ue = true;
        } else {
            state.color = '#bbb';
            state.ue = false;
        }
    });

    const svg = d3.create("svg")
        .attr("width", 700)
        .attr("height", 600)
    ;

    const path = d3
        .geoPath()
        .projection(
            d3
                .geoIdentity()
                .reflectY(true)
                .fitSize([700, 600], topojson.feature(ue, ue.objects.nutsrg))
        );

    let tooltip = (selectionGroup) => {
        selectionGroup.nodes().forEach((el, index) => {
            let stateData = selectionGroup.data()[index];
            if (stateData.ue) {
                el.setAttribute('data-bs-custom-class', 'popover-shadow');
                el.setAttribute('data-bs-toggle', 'popover');
                el.setAttribute('data-bs-content', stateData.tooltip);
                el.setAttribute('data-bs-html', true);
                el.setAttribute('data-bs-trigger', 'hover');
            }
        });
    }

    const groups = svg
        .selectAll(".state")
        .data(states)
        .enter()
        .append("g")
        .attr("class", "state")
    ;


    const paths = groups
        .append("path")
        .attr("d", path)
        .attr("fill", d => d.color)
        .attr("stroke", 'white')
        .attr("stroke-width", 1)
        .attr("class", d => d.ue ? 'ue-member' : '')
    ;

    tooltip(paths);

    return svg.node();
}
