import * as d3 from "d3";

export function DotAverageByCategory(data) {
    const height = 600;
    const width = 1200;
    const margin = {
        'top': 10,
        'left': 90,
        'right': 0,
        'bottom': 50
    }

    let categories = [];
    data.forEach((el) => {
       categories.push(el.category.name);
    });

    const svg = d3
        .create("svg")
        .attr("viewBox", `0 0 ${width} ${height}`)
        .attr("width", '100%')
        .attr("height", height);


    const x = d3
        .scaleSymlog()
        .domain([0, 100])
        .rangeRound([margin.left, width])
        .constant(8)
    ;

    const y = d3
        .scalePoint()
        .domain(categories)
        .range([margin.top, height - margin.bottom])
        .padding(1);

    const color = d3
        .scaleDiverging(["blue", "mediumvioletred", "red"])
        .domain([0, 10, 100])
    ;

    const xAxis = d3
        .axisBottom(x)
        .tickSizeOuter(0)
        .tickFormat(d => `${d}%`)
    ;

    svg
        .append("g")
        .attr("transform", `translate(0, ${height - margin.bottom})`)
        .call(xAxis)
    ;

    data.forEach((el, index) => {
        dayOverDayChange({
            svg,
            x,
            y,
            color,
            data: el.data,
            era: el.category.name,
            average: el.category.value,
            showLegend: 0 === index
        });
    });

    svg
        .append("text")
        .attr("transform", `translate(${width / 2 + margin.left / 2}, ${height - 5})`)
        .attr("text-anchor", "end")
        .attr("font-size", "1rem")
        .text("Abstention rate")
    ;

    return svg.node();
}


const dayOverDayChange = ({ svg, x, y, color, data, era, average, showLegend}) => {
    svg
        .append("g")
        .selectAll("circle")
        .data(data)
        .join("circle")
        .attr("cx", d => x(d.value))
        .attr("cy", y(era))
        .attr("r", 4)
        .attr("fill", d => color(d.value))
        .attr("fill-opacity", .5)
        .attr('data-bs-custom-class', 'popover-chart')
        .attr('data-bs-toggle', 'popover')
        .attr('data-bs-content', d => d.tooltip)
        .attr('data-bs-html', true)
        .attr('data-bs-trigger', 'hover')
    ;

    const averageValue = svg
        .append("g")
        .attr("transform", `translate(${x(average)}, ${y(era)})`)
    ;

    averageValue
        .append("line")
        .attr("x1", 0)
        .attr("y1", -10)
        .attr("x2", 0)
        .attr("y2", 10)
        .attr("stroke", '#4B5563')
    ;

    averageValue
        .append("text")
        .attr("font-size", "1rem")
        .attr("text-anchor", "middle")
        .attr("y", -15)
        .attr("fill", '#4B5563')
        .text(`${Math.round(average * 10) / 10}%`)
    ;

    svg
        .append("text")
        .attr("x", -20)
        .attr("y", y(era))
        .attr("dy", 4)
        .attr("font-size", "1rem")
        .text(era)
    ;

    if (showLegend) {
        averageLabel(svg, x, y, era, average);
    }
}

const averageLabel = (svg, x, y, era, average) => {
    svg
        .append("g")
        .append("line")
        .attr("stroke", '#4B5563')
        .attr("x1", x(average) + 15)
        .attr("y1", y(era) - 30)
        .attr("x2", x(average) + 23)
        .attr("y2", y(era) - 40)
    ;

    svg
        .append("g")
        .append("line")
        .attr("stroke", '#4B5563')
        .attr("x1", x(average) + 23)
        .attr("y1", y(era) - 40)
        .attr("x2", x(average) + 155)
        .attr("y2", y(era) - 40)
    ;

    svg
        .append("g")
        .attr("transform", `translate(${x(average) + 90}, ${y(era) - 15})`)
        .append("text")
        .attr("font-size", ".75rem")
        .attr("text-anchor", "middle")
        .attr("y", -30)
        .attr("fill", '#4B5563')
        .text('Average absence rate')
    ;
}
