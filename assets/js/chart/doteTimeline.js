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




    // console.log(data);
    // const height = 500;
    // const width = 500;
    // const margin = {left: 10, right: 10};
    //
    // const timeScaleBefore = d3.scaleTime()
    //     .domain(d3.extent(data, d => d.date))
    //     .range([margin.left, width/2 - margin.right/2])
    // ;
    //
    // const svg = d3
    //     .create("svg")
    //     .attr("viewBox", `0 0 ${width} ${height}`)
    //     .attr("width", '100%')
    //     .attr("height", height)
    // ;
    //
    // const g = svg.append("g");
    //
    // g.selectAll('circle')
    //     .data(data)
    //     .join("circle")
    //     .attr("r", 4)
    //     .attr("cx", 0)
    //     .attr("cy", 0)
    //     .style("fill", "black");
    //
    // svg.append("g")
    //     .call(xAxisBefore);
    //
    // return svg.node()


    // const width = 1000;
    // const height = 800;
    // const dateRange = [new Date(2019, 0).getTime(), new Date(2025, 0).getTime()];
    // const xScale = d3.scaleTime().range([10, width]).domain(dateRange);
    //
    // //Math.min(...data.map(e => e.date.getFullYear()));
    // const xAxis = svg => svg.append('g')
    //     .attr('color', '#3a3a3a')
    //     .attr('transform', `translate(0, ${height - 10})`)
    //     .call(d3.axisBottom(xScale).tickSize(30).tickSizeOuter(0))
    //     .call(el => el.selectAll('.tick text')
    //         .attr('text-anchor', 'start')
    //         .attr('transform', 'translate(-12, -30)'))
    // ;
    //
    // let categories = ['0', '1'];
    // data = d3.group(
    //     data.filter(e => categories.includes(`${e.category}`)),
    //     d => new Date(d.date.getFullYear(), d.date.getMonth()).toDateString(),
    //     d => new Date(d.date.getFullYear(), d.date.getMonth()).toDateString());
    //
    // const svg = d3.create('svg')
    //     .attr('width', width)
    //     .attr('height', height)
    //     .attr('font-family', 'sans-serif')
    //     .call(xAxis);
    //
    // svg.selectAll('.date-bin')
    //     .data(data, d => d[0])
    //     .join('g')
    //     .attr('class', 'date-bin')
    //     .attr('transform', d => `translate(${xScale(new Date(d[0]))}, ${height - 10})`)
    //     .selectAll('.date-bin-group')
    //     .data(d => d[1])
    //     .join('g')
    //     .attr('class', 'date-bin-group')
    //     .attr('transform', d => `translate(0, ${15 * -1})`)
    //     .selectAll('.event')
    //     .data(d => d[1])
    //     .join(
    //         enter => enter.append('circle')
    //             .attr('fill', d => d3.schemeTableau10[d.category])
    //             .attr('r', 3)
    //             .attr('cx', 0)
    //             .attr('cy', 0)
    //             .attr('class', 'event')
    //             .attr('transform', (d, i, nodes) => `translate(0, ${i * 7 * -1})`)
    //             .call(el => el.attr('opacity', 0)
    //                 .transition().duration(200)
    //                 .attr('opacity', 1)),
    //     );
    //
    // return svg.node();



    // return Object.assign(svg.node(), {
    //     update(data) {
    //         console.log(data);
    //         svg.selectAll('.date-bin')
    //             .data(data)
    //             .join('g')
    //             .attr('class', 'date-bin')
    //             .attr('transform', d => `translate(${xScale(new Date(d[0]))}, ${height / 2})`) // ici on se retrouve avec chaque entrée de data
    //             .selectAll('.date-bin-group')
    //             .data(d => d[1])
    //             .join('g')
    //             .attr('class', 'date-bin-group')
    //             .attr('transform', d => `translate(0, ${d})`) //ici on se retrouve avec la value value de chaque sous data
    //             .selectAll('.event')
    //             .data(d => d[1])
    //             .join(
    //                 enter => enter.append('circle')
    //                     .attr('fill', d => d3.schemeTableau10[d.category])
    //                     .attr('r', 4)
    //                     .attr('cx', 0)
    //                     .attr('cy', 0)
    //                     .attr('class', 'event')
    //                     .attr('transform', (d, i, nodes) => `translate(0, ${i * 10 * (d.position ? 1 : -1)})`)
    //                     .call(el => el.attr('opacity', 0)
    //                         .transition().duration(200)
    //                         .attr('opacity', 1)),
    //                 update => update
    //                     .call(els => els.transition().duration(200)
    //                         .attr('transform', (d, i, nodes) => `translate(0, ${i * 10 * (d.position ? 1 : -1)})`)),
    //                 exit => exit.remove());
    //     }
    // });
}
