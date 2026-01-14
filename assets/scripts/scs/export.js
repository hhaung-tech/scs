function exportToPDF() {
  try {
    const content = [];
    const mainBox = document.querySelector(".col-xs-12 .box-content");

    if (!mainBox) {
      throw new Error("No content found to export");
    }

    // Get title and response count
    const title = mainBox.querySelector(".page-title").textContent.trim();
    const responses = mainBox
      .querySelector(".total-responses")
      .textContent.trim();

    content.push(
      { text: title, style: "header", margin: [0, 0, 0, 20] },
      { text: responses, style: "subheader", margin: [0, 0, 0, 20] }
    );

    // Process categories
    document
      .querySelectorAll(".col-lg-12 > .box-content")
      .forEach((category) => {
        const categoryTitle = category.querySelector(".box-title");
        if (!categoryTitle) return;

        content.push({
          text: categoryTitle.textContent.trim(),
          style: "categoryHeader",
          margin: [0, 15, 0, 5],
        });

        // Process text responses
        category
          .querySelectorAll(".table-responsive")
          .forEach((tableWrapper) => {
            const table = tableWrapper.querySelector("table");
            if (!table) return;

            const headers = Array.from(table.querySelectorAll("thead th")).map(
              (th) => th.textContent.trim()
            );

            const rows = Array.from(table.querySelectorAll("tbody tr")).map(
              (row) =>
                Array.from(row.querySelectorAll("td")).map((td) =>
                  td.textContent.trim()
                )
            );

            if (headers.length && rows.length) {
              content.push({
                table: {
                  headerRows: 1,
                  widths: headers.map(() => "*"),
                  body: [headers, ...rows],
                },
                margin: [0, 5, 0, 15],
              });
            }
          });

        // Process chart questions and their data
        category.querySelectorAll(".row").forEach((questionRow) => {
          const chartBoxes = questionRow.querySelectorAll(".box-content");
          chartBoxes.forEach((chartBox, index) => {
            const questionTitle = chartBox.querySelector(".box-title");
            if (
              !questionTitle ||
              questionTitle.textContent === categoryTitle.textContent
            )
              return;

            // Add question title
            content.push({
              text: questionTitle.textContent.trim(),
              style: "questionHeader",
              margin: [0, 10, 0, 5],
            });

            // Get chart data
            const chartContainer = chartBox.querySelector(".chart-container");
            if (chartContainer) {
              const chartId = chartContainer.id;
              const chart = Highcharts.charts.find(
                (c) => c && c.renderTo.id === chartId
              );

              if (chart) {
                const chartData = chart.series[0].data.map((point) => ({
                  name: point.name || point.category,
                  y: point.y,
                }));

                // Add data table
                const dataRows = chartData.map((point) => [
                  point.name,
                  point.y.toString(),
                ]);

                content.push({
                  table: {
                    headerRows: 1,
                    widths: ["*", "auto"],
                    body: [["Option", "Responses"], ...dataRows],
                  },
                  margin: [0, 5, 0, 15],
                });
              }
            }
          });
        });
      });

    // Rest of the code remains the same...

    const docDefinition = {
      pageSize: "A4",
      pageOrientation: "portrait",
      content: content,
      styles: {
        header: { fontSize: 18, bold: true },
        subheader: { fontSize: 14, bold: true },
        categoryHeader: { fontSize: 16, bold: true },
        questionHeader: { fontSize: 12, bold: true },
      },
    };

    pdfMake.createPdf(docDefinition).download(title + ".pdf");
  } catch (error) {
    console.error("PDF Export Error:", error);
    alert("Failed to generate PDF: " + error.message);
  }
}

function exportToExcel() {
  try {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet("Survey Analytics");

    const mainBox = document.querySelector(".col-xs-12 .box-content");
    if (!mainBox) {
      throw new Error("No content found to export");
    }

    // Add title and responses
    const title = mainBox.querySelector(".page-title").textContent.trim();
    const responses = mainBox
      .querySelector(".total-responses")
      .textContent.trim();

    worksheet.addRow([title]).font = { bold: true, size: 14 };
    worksheet.addRow([responses]).font = { bold: true, size: 12 };
    worksheet.addRow([]);

    // Process categories
    document
      .querySelectorAll(".col-lg-12 > .box-content")
      .forEach((category) => {
        const categoryTitle = category.querySelector(".box-title");
        if (!categoryTitle) return;

        worksheet.addRow([categoryTitle.textContent.trim()]).font = {
          bold: true,
        };
        worksheet.addRow([]);

        // Process text responses
        category
          .querySelectorAll(".table-responsive")
          .forEach((tableWrapper) => {
            const table = tableWrapper.querySelector("table");
            if (!table) return;

            const headers = Array.from(table.querySelectorAll("thead th")).map(
              (th) => th.textContent.trim()
            );

            if (headers.length) {
              worksheet.addRow(headers).font = { bold: true };

              Array.from(table.querySelectorAll("tbody tr")).forEach((row) => {
                worksheet.addRow(
                  Array.from(row.querySelectorAll("td")).map((td) =>
                    td.textContent.trim()
                  )
                );
              });

              worksheet.addRow([]);
            }
          });

        // Process chart questions and their data
        category.querySelectorAll(".row").forEach((questionRow) => {
          const chartBoxes = questionRow.querySelectorAll(".box-content");
          chartBoxes.forEach((chartBox) => {
            const questionTitle = chartBox.querySelector(".box-title");
            if (
              !questionTitle ||
              questionTitle.textContent === categoryTitle.textContent
            )
              return;

            worksheet.addRow([questionTitle.textContent.trim()]).font = {
              bold: true,
            };

            // Get chart data
            const chartContainer = chartBox.querySelector(".chart-container");
            if (chartContainer) {
              const chartId = chartContainer.id;
              const chart = Highcharts.charts.find(
                (c) => c && c.renderTo.id === chartId
              );

              if (chart) {
                const chartData = chart.series[0].data.map((point) => ({
                  name: point.name || point.category,
                  y: point.y,
                }));

                // Add headers
                worksheet.addRow(["Option", "Responses"]).font = { bold: true };

                // Add data rows
                chartData.forEach((point) => {
                  worksheet.addRow([point.name, point.y]);
                });

                worksheet.addRow([]);
              }
            }
          });
        });
      });

    // Auto-fit columns
    worksheet.columns.forEach((column) => {
      column.width = Math.max(
        15,
        ...worksheet
          .getColumn(column.letter)
          .values.map((v) => (v ? v.toString().length : 0))
      );
    });

    // Generate Excel file
    workbook.xlsx.writeBuffer().then((buffer) => {
      const blob = new Blob([buffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = title + ".xlsx";
      a.click();
      window.URL.revokeObjectURL(url);
    });
  } catch (error) {
    console.error("Excel Export Error:", error);
    alert("Failed to generate Excel file: " + error.message);
  }
}
