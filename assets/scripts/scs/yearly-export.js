function exportToPDF() {
  try {
    // Check if data exists
    if (!document.querySelector(".box-content table")) {
      alert("Please select years and survey type first");
      return;
    }

    console.log("Starting PDF export...");
    const content = [];

    // Get form data
    const year1 = document.querySelector('select[name="year1"]').value;
    const year2 = document.querySelector('select[name="year2"]').value;
    const type = document.querySelector('select[name="type"]').value;

    // Add title
    content.push({
      text: `${
        type.charAt(0).toUpperCase() + type.slice(1)
      } Survey Analysis (${year1} vs ${year2})`,
      style: "header",
      margin: [0, 0, 0, 20],
    });

    // Process each category
    document.querySelectorAll(".box-content").forEach((box) => {
      const title = box.querySelector("h4.box-title");
      if (!title || title.textContent.includes("Year by Survey Analysis"))
        return;

      console.log("Processing category:", title.textContent);

      // Add category title
      content.push({
        text: title.textContent.trim(),
        style: "categoryHeader",
        margin: [0, 15, 0, 5],
      });

      // Add response info
      const responseInfo = Array.from(box.querySelectorAll(".badge-info"))
        .map((badge) => badge.textContent.trim())
        .join(" | ");

      if (responseInfo) {
        content.push({
          text: responseInfo,
          style: "responseInfo",
          margin: [0, 5, 0, 10],
        });
      }

      // Process table
      const table = box.querySelector("table.table");
      if (table) {
        const headers = Array.from(table.querySelectorAll("thead th")).map(
          (th) => th.textContent.trim()
        );

        const rows = Array.from(table.querySelectorAll("tbody tr")).map((row) =>
          Array.from(row.querySelectorAll("td")).map((td) => {
            const badge = td.querySelector(".badge");
            return badge ? badge.textContent.trim() : td.textContent.trim();
          })
        );

        if (headers.length && rows.length) {
          content.push({
            table: {
              headerRows: 1,
              widths: Array(headers.length).fill("*"),
              body: [headers, ...rows],
            },
            margin: [0, 0, 0, 15],
          });
        }
      }
    });

    if (content.length <= 1) {
      throw new Error("No data available to export");
    }

    console.log("Preparing PDF document...");
    const docDefinition = {
      pageSize: "A4",
      pageOrientation: "landscape",
      content: content,
      styles: {
        header: { fontSize: 18, bold: true },
        categoryHeader: { fontSize: 14, bold: true },
        responseInfo: { fontSize: 12, color: "#666666" },
      },
    };

    console.log("Generating PDF...");
    pdfMake
      .createPdf(docDefinition)
      .download(`${type}_survey_analysis_${year1}_vs_${year2}.pdf`);
  } catch (error) {
    console.error("PDF Export Error:", error);
    alert("Failed to generate PDF: " + error.message);
  }
}

function exportToExcel() {
  try {
    // Check if data exists
    if (!document.querySelector(".box-content table")) {
      alert("Please select years and survey type first");
      return;
    }

    console.log("Starting Excel export...");
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet("Yearly Analysis");

    // Get form data
    const year1 = document.querySelector('select[name="year1"]').value;
    const year2 = document.querySelector('select[name="year2"]').value;
    const type = document.querySelector('select[name="type"]').value;

    // Add title
    worksheet.addRow([
      `${
        type.charAt(0).toUpperCase() + type.slice(1)
      } Survey Analysis (${year1} vs ${year2})`,
    ]).font = { bold: true, size: 14 };
    worksheet.addRow([]);

    // Process each category
    document.querySelectorAll(".box-content").forEach((box) => {
      const title = box.querySelector("h4.box-title");
      if (!title || title.textContent.includes("Year by Survey Analysis"))
        return;

      // Add category title
      worksheet.addRow([title.textContent.trim()]).font = { bold: true };

      // Add response info
      const responseInfo = Array.from(box.querySelectorAll(".badge-info"))
        .map((badge) => badge.textContent.trim())
        .join(" | ");
      if (responseInfo) {
        worksheet.addRow([responseInfo]);
      }

      // Process table
      const table = box.querySelector("table.table");
      if (table) {
        // Add headers
        const headers = Array.from(table.querySelectorAll("thead th")).map(
          (th) => th.textContent.trim()
        );
        worksheet.addRow(headers).font = { bold: true };

        // Add data rows
        Array.from(table.querySelectorAll("tbody tr")).forEach((row) => {
          const rowData = Array.from(row.querySelectorAll("td")).map((td) => {
            const badge = td.querySelector(".badge");
            return badge ? badge.textContent.trim() : td.textContent.trim();
          });
          worksheet.addRow(rowData);
        });

        worksheet.addRow([]); // Add spacing
      }
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

    console.log("Generating Excel file...");
    workbook.xlsx.writeBuffer().then((buffer) => {
      const blob = new Blob([buffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = `${type}_survey_analysis_${year1}_vs_${year2}.xlsx`;
      a.click();
      window.URL.revokeObjectURL(url);
    });
  } catch (error) {
    console.error("Excel Export Error:", error);
    alert("Failed to generate Excel file: " + error.message);
  }
}
