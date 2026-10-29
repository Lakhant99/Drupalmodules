(function ($, Drupal, drupalSettings) {

  'use strict';
  Drupal.behaviors.unfpa_global_administrative_agent_chart = {
    attach: function (context, settings) {
      var finance_type = $('.custom-graph-header.overtime .tabs-nav__options li.active', context).once('unfpa_global_administrative_agent_chart').text();
      if (finance_type) {
        switch (finance_type) {
          case 'Transfers':
            var finance_type_name = 'disbursement';
            break;
          default:
            var finance_type_name = 'deposit';
        }
        regenerate_overtime_chart(finance_type_name);
      }

      var donut_finance_type = $('.custom-graph-header.donut .tabs-nav__options li.active',context).once('unfpa_global_administrative_agent_chart').text();
            if(donut_finance_type){
              switch (donut_finance_type) {
          case 'Transfers':
            var donut_finance_name = 'disbursement';
            break;
          default:
            var donut_finance_name = 'deposit';
          }
          generate_donut_chart(donut_finance_name);
        }

            //Js for 'overtime_chart' top tabs selection.
      $('.custom-graph-header.tabs-nav .tabs-nav__options li', context).on('click', function(e){
        $(this).closest('li').addClass('active').siblings().removeClass('active');
        var str_to_replace = $(this).parent('ul').siblings('label');
        var selected_value = $(this).text();

        str_to_replace.text(selected_value);
        $(this).closest('.mobile-dropdown').removeClass('opened');
      });
      
      $('.past-fund-data, .past-fund-close', context).hide();
      $(".past-fund", context).once('unfpa_global_administrative_agent_chart').click(function () {
        $(".past-fund-data").toggle();
        $(".past-fund-close").show();
        $(".past-fund").hide();
      });
      $(".past-fund-close", context).click(function () {
        console.log('hode');
        $(".past-fund-close").hide();
        $(".past-fund").show();
        $(".past-fund-data").hide();
      });
    }
  };
})(jQuery, Drupal, drupalSettings);


function generate_donut_chart(donut_finance_name){
   (function ($) {
    el = $('#donut_chart');
    $(el).html('');
    var data = [];
     var data_array = drupalSettings.donut_chart_data;
      var year = $("#select2-donut-year-container").text();
        if (data_array[donut_finance_name]) {
            data = data_array[donut_finance_name][year];
            var margin = {top: 50, right: 0, bottom: 100, left: 0};
            var width, height;
            var graphContainer, x, y, color, svg, tooltip;
            var path;
            var isMobile = window.innerWidth <= 768;
            if (data == 'undefined' || data == null) {
                $(el).html('<span class="map-no-data-available">No data available</span>');
            } else {

                if (isMobile) {
                    margin.bottom = 20;
                    margin.top = 20;
                }

                width = $(el).width() - margin.left - margin.right;
                height = (isMobile ? 340 : $(el).width() * 0.5) - margin.top - margin.bottom;
                var thickness = 50;
                var duration = 750;

                var radius = Math.min(width, height) / 2;
                color = d3.scaleOrdinal()
                        .domain(['commitment', 'deposit', 'aa_fee', 'disbursement', 'expenditure'])
                        .range(['#0084C4', '#12745D', '#F7941E', '#F7941E', '#56368B']);

                var svg = d3.select("#donut_chart")
                        .append('svg')
                        .attr('class', 'pie')
                        .attr('width', width)
                        .attr('height', height);

                var g = svg.append('g')
                        .attr('transform', 'translate(' + (width / 2) + ',' + (height / 2) + ')');
                tooltip = d3.select('body').append('div')
                        .attr('class', 'tooltip')
                        .style('opacity', 0);

                var arc = d3.arc()
                        .innerRadius(radius - thickness)
                        .outerRadius(radius);

                var pie = d3.pie()
                        .value(function (d) {
                            return d.value;
                        })
                        .sort(null)
                        .padAngle(.01);

                var path = g.selectAll('path')
                        .data(pie(data))
                        .enter()
                        .append("g")
                        .append('path')
                        .attr('d', arc)
                        .attr('fill', color(donut_finance_name))

                        .on("mousemove", function (d) {
                            d3.select(this)
                                    .style("cursor", "pointer")
                                    .style("opacity", 0.8);
                            tooltip.style('z-index', 2)
                                    .transition()
                                    .duration(200)
                                    .style('opacity', 1);
                            tooltip.html(d.data.tooltip)
                                    .style('color', color(donut_finance_name))
                                    .style('left', (d3.event.pageX) + 'px')
                                    .style('top', (d3.event.pageY - 10) + 'px');
                        })
                        .on("mouseout", function (d) {
                            d3.select(this)
                                    .style("cursor", "none")
                                    .style("opacity", 1);
                            tooltip.transition()
                                    .duration(200)
                                    .style('opacity', 0)
                                    .style('z-index', -1);
                        })
            }
        } else {
            $(el).html('No data available');
        }
    })(jQuery);
}

function regenerate_overtime_chart(finance_type) {
  (function ($) {
    var margin = {top: 50, right: 0, bottom: 100, left: 0};
    var width, height;
    var graphContainer, x, y, color, svg, tooltip;
    var data, path, overElement;
    var isMobile = window.innerWidth <= 768;

    overElement = $('#overtime_chart');
    $(overElement).html('');
    var data_array = drupalSettings.overtime_chart_data;
    if (data_array == 'undefined' || data_array == null) {
      $(overElement).html('<span class="map-no-data-available">No data available</span>');
    } else {
      var data = data_array[finance_type];

      if (data) {
        var format = d3.format(",.2f");
        var tip = d3.tip().html(function (d) {
          return "US$ " + format(d.value);
        });
        tip.attr("class", "tooltip");
        if (isMobile) {
          margin.bottom = 20;
          margin.left = 20;
          margin.top = 0;
          var Height = data.length * 50;
        }
        width = $(overElement).width() - margin.left - margin.right;
        height = (isMobile ? Height : ($(overElement).width() * 0.5) - margin.top - margin.bottom);

        color = d3.scaleOrdinal()
                .domain(['commitment', 'deposit', 'aa_fee', 'disbursement', 'expenditure'])
                .range(['#0084C4', '#12745D', '#F7941E', '#F7941E', '#56368B']);

        svg = d3.select('#overtime_chart').append('svg')
                .attr('width', width + margin.left + margin.right)
                .attr('height', height + margin.top + margin.bottom)
                .attr('class', finance_type)
                .append('g')
                .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')')
                .attr('class', 'chart-inner-element');
        tooltip = d3.select("body").append("div")
                .attr('class', 'tooltip')
                .style('opacity', 0);
        graphContainer = svg.append('g').attr('class', 'chart-inner-element-two');
        x = d3.scaleBand().rangeRound([0, width]).padding(0.1);
        y = d3.scaleLinear().rangeRound([height, 0]);

        if (isMobile) {
          x = d3.scaleLinear().rangeRound([0, width]);
          y = d3.scaleBand().rangeRound([0, height]).padding(0.1);

          y.domain(data.map(function (d) {
            return d.date;
          }));
          x.domain([0, d3.max(data, function (d) {
              return Number(d.value);
            })]);
        } else {
          x.domain(data.map(function (d) {
            return d.date;
          }));
          y.domain([0, d3.max(data, function (d) {
              return Number(d.value);
            })]);
        }

        if (isMobile) {
          graphContainer.append('g')
                  .attr('class', 'axis axis--x')
                  .attr('transform', 'translate(0,0)')
                  .call(d3.axisBottom(x).tickFormat(function (d) { return d/ 1.0e+6 + "M"; }));
        } else {
          graphContainer.append('g')
                  .attr('class', 'axis axis--x')
                  .attr('transform', 'translate(0,' + height + ')')
                  .call(d3.axisBottom(x));
        }
        if(isMobile) {
        graphContainer.append('g')
                .attr('class', 'axis axis--y')
                .call(d3.axisLeft(y))
                .append('text')
                .attr('transform', 'rotate(-90)')
                .attr('y', 6)
                .attr('dy', '0.71em')
                .attr('text-anchor', 'end')
                .text('Frequency');
        } else {
        graphContainer.append('g')
            .attr('class', 'axis axis--y')
            .call(d3.axisLeft(y).tickFormat(function (d) { return d/ 1.0e+6 + "M"; }))
            .append('text')
            .attr('transform', 'rotate(-90)')
            .attr('y', 6)
            .attr('dy', '0.71em')
            .attr('text-anchor', 'end')
            .text('Frequency');
        }
        graphContainer.selectAll('.bar')
                .data(data)
                .enter().append('rect')
                .attr('class', 'bar')
                .attr('x', function (d) {
                  if (isMobile) {
                    return 0;
                  } else {
                    if (x.bandwidth() > 160) {
                      var margin = (x.bandwidth() - 160) / 2;
                      return x(d.date) + margin;
                    } else {
                      return x(d.date);
                    }
                  }
                })
                .attr('y', function (d) {
                  if (isMobile) {
                    var margin = 60 / 2;
                    return y(d.date) + 8;
                  } else {
                    return y(d.value);
                  }
                })
                .attr('width', function (d) {
                  if (isMobile) {
                    return  x(d.value)
                  } else {
                    if (x.bandwidth() > 160) {
                      return 160;
                    } else {
                      return x.bandwidth();
                    }
                  }
                })
                .attr('height', function (d) {
                  return isMobile ? (y.bandwidth() - 15) : (height - y(d.value));
                })
                .attr('fill', function (d) {
                  return color(finance_type);
                })
                .on('mousemove', function (d) {
                  tooltip.style('z-index', 2)
                          .transition()
                          .duration(200)
                          .style('opacity', 1);

                  var year = d.date;

                  tooltip.html(year + ' ' + (d.type) + '<br> <strong>$' + formatNumber(d.value) + '</strong>')
                          .style('color', color(finance_type))
                          .style('left', (d3.event.pageX) + 'px')
                          .style('top', (d3.event.pageY - 10) + 'px');
                })
                .on('mouseout', function (d) {
                  tooltip.transition()
                          .duration(200)
                          .style('opacity', 0)
                          .style('z-index', -1);
                });
        if (isMobile) {
          svg.selectAll('.axis--x .tick').call(linesY, (height), isMobile);
        } else {
          svg.selectAll('.axis--y .tick').call(linesY, width, isMobile);
        }

      } else {
        $(overElement).html('No data available');
      }
    }
  })(jQuery);
}

function formatNumber(n) {
 return ((n) / 1.0e+6).toFixed(3) + " million"
}

function linesY(lines, width, isMobile) {
  lines.each(function () {
    if (isMobile) {
      var line = d3.select(this).select('line')
              .attr('y2', width)
    } else {
      var line = d3.select(this).select('line')
              .attr('x2', width);
    }
  });
}
