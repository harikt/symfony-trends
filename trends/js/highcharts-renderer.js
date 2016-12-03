function HighchartsRenderer () {

    Highcharts.setOptions({
        title: {
            text: null
        },
        credits: {
            enabled: false
        },
        legend: {
            enabled: true,
            verticalAlign: "top"
        },
        xAxis: {
            tickLength: 0
        },
        yAxis: {
            endOnTick: false,
            title: {
                text: null
            }
        },
        plotOptions: {
            area: {
                marker: {
                    enabled: false
                }
            },
            pie: {
                dataLabels: {
                    format: "{point.name}<br\/>{point.y}"
                }
            }
        }
    });
}

HighchartsRenderer.prototype.columnChart = function(container, data) {

    var categories = [];

    data.series.forEach(function(series) {
        series.data.forEach(function(item) {
            categories.push(item['name']);
            item['y'] = item['value'];
            delete item['name'];
            delete item['value'];
        })
    });

    Highcharts.chart(container, {
        chart: {
            type: 'column'
        },
        xAxis: {
            categories: categories
        },
        series: data.series
    });
}

HighchartsRenderer.prototype.stairAreaChart = function(container, data) {

    var categories = [];

    data.series.forEach(function(series) {

        series.data.forEach(function(item) {
            categories.push(item['name']);
            item['y'] = item['value'];
            delete item['value'];
        })

        categories.unshift('');
        series.data.unshift({name: '', y: series.data[0].y});
    });

    Highcharts.chart(container, {
        chart: {
            type: 'area'
        },
        xAxis: {
            categories: categories
        },
        plotOptions: {
            area: {
                step: 'right'
            }
        },
        series: data.series
    });
}

HighchartsRenderer.prototype.pieChart = function(container, data) {

    // replace element with y, name, color
    data.series.forEach(function(series) {
        series.data.forEach(function(item) {
            item['y'] = item['value'];
            delete item['value'];
        })
    });

    Highcharts.chart(container, {
        chart: {
            type: 'pie'
        },
        series: data.series
    });
}


var Renderer = new HighchartsRenderer();
