function initializeCharts(questionId, chartData) {
  // Bar Chart
  Highcharts.chart("barChart_" + questionId, {
    chart: {
      type: "column",
    },
    title: {
      text: "Response Distribution",
    },
    xAxis: {
      categories: chartData.labels,
      title: {
        text: "Options",
      },
    },
    yAxis: {
      title: {
        text: "Number of Responses",
      },
    },
    series: [
      {
        name: "Responses",
        data: chartData.data,
      },
    ],
    exporting: {
      enabled: true,
    },
  });

  // Pie Chart
  Highcharts.chart("pieChart_" + questionId, {
    chart: {
      type: "pie",
    },
    title: {
      text: "Response Distribution",
    },
    plotOptions: {
      pie: {
        allowPointSelect: true,
        cursor: "pointer",
        dataLabels: {
          enabled: true,
          format: "<b>{point.name}</b>: {point.percentage:.1f}%",
        },
      },
    },
    series: [
      {
        name: "Responses",
        data: chartData.labels.map((label, index) => ({
          name: label,
          y: chartData.data[index],
        })),
      },
    ],
    exporting: {
      enabled: true,
    },
  });

  // Add event listeners for export buttons
  document.getElementById("pdfExport").addEventListener("click", function () {
    const charts = Highcharts.charts.filter((chart) => chart !== undefined);
    exportCharts(charts, "pdf");
  });

  document.getElementById("excelExport").addEventListener("click", function () {
    const charts = Highcharts.charts.filter((chart) => chart !== undefined);
    exportCharts(charts, "xlsx");
  });
}

function exportCharts(charts, type) {
  if (type === "pdf") {
    charts.forEach((chart) => {
      chart.exportChart({
        type: "application/pdf",
      });
    });
  } else if (type === "xlsx") {
    charts.forEach((chart) => {
      chart.downloadXLS();
    });
  }
}
