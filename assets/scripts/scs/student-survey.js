document.addEventListener("DOMContentLoaded", function () {
  console.log("Student survey JS loaded. Initializing Bootstrap tabs.");

  // Debug the tab elements
  console.log(
    "Tab nav items:",
    document.querySelectorAll("#surveyTab .nav-item").length
  );
  console.log("Tab panes:", document.querySelectorAll(".tab-pane").length);

  // Log each tab pane for debugging
  document.querySelectorAll(".tab-pane").forEach(function (pane) {
    console.log(
      "Tab pane ID:",
      pane.id,
      "Content empty:",
      pane.innerHTML.trim() === ""
    );
  });

  // Initialize Bootstrap tabs using jQuery (for BS4)
  // This tells Bootstrap to handle the tab switching based on the data-toggle attributes.
  jQuery('#surveyTab a[data-toggle="tab"]').on("click", function (e) {
    e.preventDefault();
    console.log("Tab clicked:", this.id);
    jQuery(this).tab("show");
  });

  // Initialize tabs on page load
  jQuery("#surveyTab a:first").tab("show");

  // Add a listener for tab changes
  jQuery('a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
    console.log("Tab changed to:", jQuery(e.target).attr("id"));
    console.log("Tab pane shown:", jQuery(e.target).attr("href"));
  });

  // Keep form submission handling
  const studentSurveyForm = document.getElementById("studentSurveyForm");
  if (studentSurveyForm) {
    studentSurveyForm.addEventListener("submit", function (e) {
      console.log("Form submit event triggered.");
      const submitButton = document.getElementById("submitButton");
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML =
          '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
      }
      // Allow natural form submission
    });
  }
});
