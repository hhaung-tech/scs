// Make sure function is available in global scope
window.initializeYearlyCharts = function (categoryId, year1, year2, data) {
  // Bar Chart
  Highcharts.chart(`chart-${categoryId}`, {
    chart: {
      type: "column",
    },
    title: {
      text: "Year Comparison",
    },
    xAxis: {
      categories: data.questions,
      labels: {
        style: {
          fontSize: "11px",
        },
      },
    },
    yAxis: {
      min: 0,
      max: 5,
      title: {
        text: "Average Score",
      },
    },
    legend: {
      align: "center",
      verticalAlign: "bottom",
    },
    plotOptions: {
      column: {
        groupPadding: 0.2,
      },
    },
    series: [
      {
        name: year1,
        data: data.year1Data,
      },
      {
        name: year2,
        data: data.year2Data,
      },
    ],
  });
};
