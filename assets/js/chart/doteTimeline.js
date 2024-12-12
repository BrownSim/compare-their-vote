import * as d3 from "d3";

export function DoteTimeline(data, options={})
{
    const svg = d3.create("svg");

    //group event by month
    const groupedEvents = d3
        .group(data, d => new Date(d.date.getFullYear(), d.date.getMonth()).toDateString())
    ;

    const monthDiff = (dateFrom, dateTo) => {
        return dateTo.getMonth() - dateFrom.getMonth() + (12 * (dateTo.getFullYear() - dateFrom.getFullYear()));
    }

    const domain = d3.extent(data, d => d.date);
    let firstDate = new Date(domain[0]);
    let lastDate = new Date(domain[1]);

    firstDate = new Date(firstDate.getFullYear(), firstDate.getMonth() - 1, 0);
    lastDate = new Date(lastDate.getFullYear(), lastDate.getMonth() + 1, 0);

    const calculateGraphWidth = () => {
        const width = monthDiff(firstDate, lastDate) * 17;
        return width < 500 ? 500 : width;
    }

    const calculateGraphHeight = () => {
        let maxEvents = 0;

        groupedEvents.forEach(el => {
            if (el.length > maxEvents) {
                maxEvents = el.length;
            }
        });

        return maxEvents * 4 + 50;
    }

    const height = calculateGraphHeight();
    const width = calculateGraphWidth();

    const xScale = d3
        .scaleUtc()
        .domain([firstDate, lastDate])
        .range([0, width])
    ;

    const xAxisGenerator = d3
        .axisBottom()
        .scale(xScale)
    ;

    /**
     * Function to split col un 2 cols
     */
    const yDotPosition = (i, d) => {
        const keydate = new Date(d.date.getFullYear(), d.date.getMonth()).toDateString();
        const roundUp = Math.ceil(groupedEvents.get(keydate).length / 2);

        if (roundUp > i) {
            return i * 7.5 * -1;
        }

        return (i - roundUp) * 7.5 * -1;
    }

    const xDotPosition = (i, d) => {
        const keydate = new Date(d.date.getFullYear(), d.date.getMonth()).toDateString();
        const roundUp = Math.ceil(groupedEvents.get(keydate).length / 2);

        return roundUp > i ? 0 : 7.5;
    }

    //generate box
    svg
        .attr("viewBox", `0 0 ${width} ${height}`)
        .attr("width", '100%')
        .attr("height", height)
    ;

    //generate x axis
    svg
        .append("g")
        .call(xAxisGenerator)
        .style("transform", `translateY(${height -30}px)`)
    ;


    const columns = svg.selectAll(".column")
        .data(groupedEvents)
        .join('g')
            .attr("class", "column")
            .attr("transform", d => `translate(${xScale(new Date(d[0]))}, ${height - 40})`)
    ;

    //generate dots
    const dots = columns
        .selectAll("circle")
        .data(d => d[1])
        .join('circle')
            .attr("cx", 0)
            .attr('cy', 0)
            .attr('transform', (d, i, nodes) => `translate(${xDotPosition(i, d)}, ${yDotPosition(i, d)})`)
            .attr("r", 3)
            .attr('fill', d => d3.schemeTableau10[d.value])
    ;

    return svg.node();
}
