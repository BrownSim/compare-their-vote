import * as d3 from "d3";

export function DotTrends(data, {
    xData = null,
    yData = null,
    xTitle = null,
    yTitle = null,
} = {}) {
    const width = 1200;
    const height = _height(width);
    const x = _x(d3, data, width);
    const y = _y(d3, data, height);
    const xAxis = _xAxis(y, d3);
    const yAxis = _yAxis(x, d3, height, width);

    const svg = d3.create("svg")
        .attr("viewBox", [0, 0, width, height])
    ;

    // To prevent dots passing over the x and y legends
    svg
        .append("clipPath")
        .attr("id", "border")
        .append("rect")
        .attr("width", width - 30)
        .attr("height", height - 50 -50)
        .attr("x", 30)
        .attr("y", 40)
        .attr("fill", "white");

    const gx = svg.append("g");
    const gy = svg.append("g");

    const clip = svg.append("g").attr("clip-path", "url(#border)");
    const dots = clip
        .selectAll("circle")
        .data(data)
        .join("circle")
        .attr("fill", d => d.color)
        .attr('opacity', .75)
    ;

    // z holds a copy of the previous transform, so we can track its changes
    let z = d3.zoomIdentity;

    // set up the ancillary zooms and an accessor for their transforms
    const zoomX = d3.zoom()
            .scaleExtent([1, 100], 1)
            .translateExtent([[0, 0], [width + 50, height]])
    ;
    const zoomY = d3.zoom()
            .scaleExtent([1, 100], 1)
            .translateExtent([[0, 0], [width + 50, height]])
    ;

    const tx = () => d3.zoomTransform(gx.node());
    const ty = () => d3.zoomTransform(gy.node());

    gx.call(zoomX).attr("pointer-events", "none");
    gy.call(zoomY).attr("pointer-events", "none");

    gx.call(g => g.append("text")
        .attr("x", width)
        .attr("y", 45)
        .attr("fill", "currentColor")
        .attr("text-anchor", "end")
        .text(xTitle)
    );

    gy.call(g => g.append("text")
        .attr("x", 150)
        .attr("y", 20)
        .attr("fill", "currentColor")
        .attr("text-anchor", "end")
        .text(yTitle)
    );

    // active zooming
    const zoom = d3.zoom().on("zoom", function(e) {
        const t = e.transform;
        const k = t.k / z.k;
        const point = center(e, this);

        // is it on an axis? is the shift key pressed?
        const doX = point[0] > x.range()[0];
        const doY = point[1] < y.range()[0];
        const shift = e.sourceEvent && e.sourceEvent.shiftKey;

        if (k === 1) {
            // pure translation?
            doX && gx.call(zoomX.translateBy, (t.x - z.x) / tx().k, 0);
            doY && gy.call(zoomY.translateBy, 0, (t.y - z.y) / ty().k);
        } else {
            // if not, we're zooming on a fixed point
            doX && gx.call(zoomX.scaleBy, shift ? 1 / k : k, point);
            doY && gy.call(zoomY.scaleBy, k, point);
        }

        z = t;

        redraw();
    });

    return svg
        .call(zoom)
        .call(zoom.transform, d3.zoomIdentity.scale(0.8))
        .node()
    ;

    function redraw() {
        const xr = tx().rescaleX(x);
        const yr = ty().rescaleY(y);

        gx.call(xAxis, xr);
        gy.call(yAxis, yr)

        dots
            .attr("cx", d => xr(xData(d)))
            .attr("cy", d => yr(yData(d)))
            .attr("r", 4 * Math.max(1, Math.min(2, Math.sqrt(tx().k) / 2)))
            .attr('data-bs-custom-class', 'popover-chart')
            .attr('data-bs-toggle', 'popover')
            .attr('data-bs-content', d => d.tooltip)
            .attr('data-bs-html', true)
            .attr('data-bs-trigger', 'hover')
        ;
    }

    // center the action (handles multitouch)
    function center(event, target) {
        if (event.sourceEvent) {
            const p = d3.pointers(event, target);
            return [d3.mean(p, d => d[0]), d3.mean(p, d => d[1])];
        }
        return [width / 2, height / 2];
    }


    function _height(width){return(
        Math.min(500, width * 0.8)
    )}

    function _x(d3,data,width) {
        return(
            d3.scaleLinear()
            .domain([0, d3.max(data, xData)])
            .range([40, width])
            .nice()
        )
    }

    function _y(d3,data,height) {
        return(
            d3.scaleLinear()
            .domain(d3.extent(data, yData))
            .range([height - 50, 50])
            .nice()
        )
    }

    function _xAxis(y,d3) {
        return((g, scale) => g
            .attr("transform", `translate(0, ${height - 50})`)
            .call(d3.axisBottom(scale).ticks(12))
            .call(g => g.select(".domain").attr("display", "none"))
            .call((g) => {
                g.select(".domain").remove();
                g.selectAll(".tick line")
                    .attr("y1", -height + 80)
                    .attr("stroke", "black")
                    .attr("stroke-opacity", .1)
                ;
            })
        )}

    function _yAxis(x,d3,height,width) {
        return((g, scale) => g
            .attr("transform", `translate(${x(0)}, 0)`)
            .call(d3.axisLeft(scale).ticks(12 * (height / width)))
            .call(g => g.select(".domain").attr("display", "none"))
            .call((g) => {
                g.select(".domain").remove();
                g.selectAll(".tick line")
                    .attr("x1", width)
                    .attr("stroke", "black")
                    .attr("stroke-opacity", d => d === 0 ? 1 : 0.1)
                ;
            })
        )
    }
}

