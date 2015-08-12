var root = {};
var texts;
var Bubbles = function() {
    var chart, collide, collisionPadding, colors, data, force, gravity, height, idValue, imgValue, jitter, margin, maxRadius, minCollisionRadius, node, rScale, rValue, textValue, tick, transformData, update, updateLabels,
        updateNodes, width, minRadius, minFontSize;
    width = window.innerWidth;
    height = 480;
    data = [];
    node = null;
    margin = {
        top: 5,
        right: 0,
        bottom: 0,
        left: 0
    };
    minRadius = 14;
    maxRadius = 60;
    minFontSize = 8;
    colors = ["67AB50", "D7772F", "5587C6", "43727D", "CF5251", "E9B34C"];
    rScale = d3.scale.pow().exponent(0.3).range([minRadius, maxRadius]);
    rValue = function(d) {
        return parseInt(d.contributions);
    };
    idValue = function(d) {
        return d.login;
    };
    textValue = function(d) {
        return d.login;
    };
    imgValue = function(d) {
        return d.avatarUrl;
    };
    collisionPadding = 4;
    minCollisionRadius = 12;
    jitter = 0.5;
    transformData = function(rawData) {
        for (var i = 0; i < rawData.length; i++) {
            rawData[i].contributions = parseInt(rawData[i].contributions);
            rawData.sort(function() {
                return 0.5 - Math.random();
            });
        }
        return rawData;
    };
    tick = function(e) {
        var dampenedAlpha;
        dampenedAlpha = e.alpha * 0.1;
        node.each(gravity(dampenedAlpha)).each(collide(jitter)).attr("transform", function(d) {
            return "translate(" + d.x + "," + d.y + ")";
        });
    };
    force = d3.layout.force().gravity(0).charge(0).size([width, height]).on("tick", tick);
    chart = function(selection) {
        return selection.each(function(rawData) {
            var maxDomainValue, svg, svgEnter;
            data = transformData(rawData);
            maxRadius = ((width * height) / data.length) / 160;
            rScale = d3.scale.pow().exponent(0.3).range([minRadius, maxRadius]);
            maxDomainValue = d3.max(data, function(d) {
                return rValue(d);
            });
            rScale.domain([0, maxDomainValue]);
            svg = d3.select(this).selectAll("svg").data([data]);
            svgEnter = svg.enter().append("svg");
            document.getElementById("vis").classList.add("visible");
            svg.attr("width", width + margin.left + margin.right);
            svg.attr("height", height + margin.top + margin.bottom);
            node = svgEnter.append("g").attr("id", "bubble-nodes").attr("transform", "translate(" + margin.left + "," + margin.top + ")");
            node.append("rect").attr("id", "bubble-background").attr("width", width).attr("height", height);
            return update();
        });
    };
    update = function() {
        data.forEach(function(d, i) {
            d.forceR = Math.max(minCollisionRadius, rScale(rValue(d)));
        });
        force.nodes(data).start();
        updateNodes();
    };
    updateNodes = function() {
        node = node.selectAll(".bubble-node").data(data, function(d) {
            return idValue(d);
        });
        node.exit().remove();
        node.enter()
            .append("a")
            .attr("class", "bubble-node")
            .attr("xlink:href", function(d) {
            return "https://github.com/amplab/tachyon/commits?author=" + (encodeURIComponent(idValue(d)));})
            .call(force.drag);
        node.append("clipPath")
            .attr('id', function(d) {
                return "clip" + idValue(d);
            })
            .append("circle")
            .attr("r", function(d) {
                return rScale(rValue(d));
            });
        node.append("svg:image")
            .attr("clip-path", function(d) {
                return "url(#clip" + idValue(d) + ")";
            })
            .attr('x', function(d) {
                return 0 - rScale(rValue(d));
            })
            .attr('y', function(d) {
                return 0 - rScale(rValue(d));
            })
            .attr('width', function(d) {
                return rScale(rValue(d)) * 2;
            })
            .attr('height', function(d) {
                return rScale(rValue(d)) * 2;
            })
            .attr("xlink:href", function(d) {
                return d.avatarUrl;
            });
        node.append("circle")
            .attr("r", function(d) {
                return rScale(rValue(d));
            })
            .style("fill", function(d) {
                return "#" + colors[Math.floor(Math.random() * 6)];
            })
            .style("opacity", 0.75);
        node.append("text")
            .attr("clip-path", function(d) {
                return "url(#clip" + idValue(d) + ")";
            })
            .attr("class", "bubble-label-name")
            .attr("dy",function (d) {
                return (0.75 * Math.max(minFontSize, rScale(rValue(d)) / Math.sqrt((textValue(d)).length)) / 2) + "px";
            })
            .style("text-anchor", "middle")
            .style("fill", "white")
            .style("font-weight", function(d) {
                if(rScale(rValue(d)) < 27) {
                    return "300";
                }
                return "100";
            })
            .style("font-family", "Helvetica Neue")
            .text(function(d) {
                if(rScale(rValue(d)) / Math.sqrt((textValue(d)).length) < minFontSize)
                    return textValue(d).substring(0, Math.max(5, Math.floor(Math.pow(rScale(rValue(d)), 2) / Math.pow(minFontSize, 2)) - 1 ))+'\u2026';
                else
                    return textValue(d);
            })
            .style("font-size", function(d) {
                return Math.max(minFontSize, rScale(rValue(d)) / Math.sqrt((textValue(d)).length)) + "px";
            }).style("width", function(d) {
                return 0.5 * rScale(rValue(d)) + "px";
            });
    };
    gravity = function(alpha) {
        var ax, ay, cx, cy, vStrength;
        cx = width / 2;
        cy = height / 2;
        vStrength = 1.2;
        ax = alpha * Math.pow((height / vStrength) / width, 8 / 9);
        ay = alpha * Math.pow(width / (height / vStrength), 8 / 9);
        return function(d) {
            d.x += (cx - d.x) * ax;
            return d.y += (cy - d.y) * ay;
        };
    };
    collide = function(jitter) {
        return function(d) {
            return data.forEach(function(d2) {
                var distance, minDistance, moveX, moveY, x, y;
                if (d !== d2) {
                    x = d.x - d2.x;
                    y = d.y - d2.y;
                    distance = Math.sqrt(x * x + y * y);
                    minDistance = d.forceR + d2.forceR + collisionPadding;
                    if (distance < minDistance) {
                        distance = (distance - minDistance) / distance * jitter;
                        moveX = x * distance;
                        moveY = y * distance;
                        d.x -= moveX;
                        d.y -= moveY;
                        d2.x += moveX;
                        return d2.y += moveY;
                    }
                }
            });
        };
    };
    chart.jitter = function(_) {
        if (!arguments.length) {
            return jitter;
        }
        jitter = _;
        force.start();
        return chart;
    };
    chart.height = function(_) {
        if (!arguments.length) {
            return height;
        }
        height = _;
        return chart;
    };
    chart.width = function(_) {
        if (!arguments.length) {
            return width;
        }
        width = _;
        return chart;
    };
    chart.r = function(_) {
        if (!arguments.length) {
            return rValue;
        }
        rValue = _;
        return chart;
    };
    return chart;
};
root.plotData = function(selector, data, plot) {
    return d3.select(selector).datum(data).call(plot);
};
