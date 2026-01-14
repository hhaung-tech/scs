$(document).ready(function () {
  // Add Category
  $("#addCategoryForm").submit(function (e) {
    e.preventDefault();
    var formData = $(this).serialize();

    $.ajax({
      url: window.location.href,
      type: "POST",
      data: formData,
      success: function (response) {
        try {
          var result = JSON.parse(response);
          if (result.success) {
            window.location.reload();
          } else {
            alert(result.message);
          }
        } catch (e) {
          console.error("Response:", response);
          alert("Error processing response");
        }
      },
      error: function () {
        alert("Error submitting form");
      },
    });
  });

  // Edit Category
  $("#editCategoryForm").submit(function (e) {
    e.preventDefault();
    var formData = $(this).serialize();

    $.ajax({
      url: window.location.href,
      type: "POST",
      data: formData,
      success: function (response) {
        try {
          var result = JSON.parse(response);
          if (result.success) {
            window.location.reload();
          } else {
            alert(result.message);
          }
        } catch (e) {
          console.error("Response:", response);
          alert("Error processing response");
        }
      },
    });
  });

  // Delete Category
  $("#deleteCategoryForm").submit(function (e) {
    e.preventDefault();
    if (
      !confirm("Are you sure? This will delete all questions in this category.")
    ) {
      return;
    }

    var formData = $(this).serialize();
    $.ajax({
      url: window.location.href,
      type: "POST",
      data: formData,
      success: function (response) {
        try {
          var result = JSON.parse(response);
          if (result.success) {
            window.location.reload();
          } else {
            alert(result.message);
          }
        } catch (e) {
          console.error("Response:", response);
          alert("Error processing response");
        }
      },
    });
  });

  // Question Type Change
  $(".question-type-select").change(function () {
    var selectedType = $(this).val();
    var form = $(this).closest("form");

    form.find(".likert-options, .choice-options").hide();

    if (selectedType === "likert_scale") {
      form.find(".likert-options").show();
    } else if (selectedType === "drop_down" || selectedType === "checkbox") {
      form.find(".choice-options").show();
    }
  });

  // Add Question
  $(".question-form").submit(function (e) {
    e.preventDefault();
    var formData = $(this).serialize();

    $.ajax({
      url: window.location.href,
      type: "POST",
      data: formData,
      success: function (response) {
        try {
          var result = JSON.parse(response);
          if (result.success) {
            window.location.reload();
          } else {
            alert(result.message);
          }
        } catch (e) {
          console.error("Response:", response);
          alert("Error processing response");
        }
      },
    });
  });

  // Load Edit Category Modal
  $(".edit-category-btn").click(function () {
    var categoryId = $(this).data("category-id");
    var categoryName = $(this).data("category-name");
    $("#edit_category_id").val(categoryId);
    $("#edit_category_name").val(categoryName);
  });

  // Load Delete Category Modal
  $(".delete-category-btn").click(function () {
    var categoryId = $(this).data("category-id");
    var categoryName = $(this).data("category-name");
    $("#delete_category_id").val(categoryId);
    $("#delete_category_name").text(categoryName);
  });
});
