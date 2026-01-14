document.addEventListener("DOMContentLoaded", function () {
  // Auto-hide Alerts
  document.querySelectorAll(".alert").forEach((alert) => {
    setTimeout(() => {
      alert.style.opacity = "0";
      setTimeout(() => alert.remove(), 300);
    }, 3000);
  });

  // Category Edit Button
  document.querySelectorAll(".edit-category-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const categorySection = this.closest(".category-section");
      if (categorySection) {
        const titleElement = categorySection.querySelector(".box-title");
        const editForm = categorySection.querySelector(".edit-category-form");

        if (titleElement && editForm) {
          titleElement.style.display = "none";
          editForm.style.display = "block";
        }
      }
    });
  });

  // Cancel Edit Category
  document.querySelectorAll(".cancel-edit-category").forEach((btn) => {
    btn.addEventListener("click", function () {
      const categorySection = this.closest(".category-section");
      if (categorySection) {
        const titleElement = categorySection.querySelector(".box-title");
        const editForm = categorySection.querySelector(".edit-category-form");

        if (titleElement && editForm) {
          titleElement.style.display = "block";
          editForm.style.display = "none";
        }
      }
    });
  });
  // Handle category form submission
  $(".category-form").on("submit", function (e) {
    e.preventDefault();
    const form = $(this);
    $.ajax({
      url: window.location.href,
      method: "POST",
      data: form.serialize(),
      success: function (response) {
        location.reload();
      },
    });
  });
  // Delete Category Button
  document.querySelectorAll(".delete-category-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      if (
        confirm(
          "Are you sure you want to delete this category and all its questions?"
        )
      ) {
        const categoryId = this.dataset.categoryId;
        const categorySection = this.closest(".category-section");

        $.ajax({
          url: window.location.href,
          method: "POST",
          data: {
            action: "delete_category",
            category_id: categoryId,
          },
          success: function (response) {
            categorySection.remove();
          },
          error: function (xhr, status, error) {
            console.error("Error deleting category:", error);
            alert("Failed to delete category. Please try again.");
          },
        });
      }
    });
  });

  // Sortable for question lists
  document.querySelectorAll(".question-list tbody").forEach((list) => {
    new Sortable(list, {
      handle: ".handle",
      animation: 150,
      onEnd: function (evt) {
        const questions = evt.to.querySelectorAll(".question-item");
        const orders = {};
        questions.forEach((q, index) => {
          orders[q.dataset.id] = index;
        });

        $.ajax({
          url: window.location.href,
          method: "POST",
          data: {
            action: "reorder_questions",
            orders: JSON.stringify(orders),
          },
          success: function (response) {
            console.log("Reorder successful");
          },
        });
      },
    });
  });

  // Toggle Question Form
  document.querySelectorAll(".toggle-question-form").forEach((btn) => {
    btn.addEventListener("click", function () {
      const container = this.closest(".category-section").querySelector(
        ".question-form-container"
      );
      container.style.display =
        container.style.display === "none" ? "block" : "none";
    });
  });

  // Question Type Selection
  document.querySelectorAll(".question-type-select").forEach((select) => {
    select.addEventListener("change", function () {
      const form = this.closest("form");
      const likertOptions = form.querySelector(".likert-options");
      const optionsGroup = form.querySelector(".options-group");

      if (this.value === "likert_scale") {
        if (likertOptions) likertOptions.style.display = "block";
        if (optionsGroup) optionsGroup.style.display = "none";
      } else if (["drop_down", "checkbox"].includes(this.value)) {
        if (likertOptions) likertOptions.style.display = "none";
        if (optionsGroup) optionsGroup.style.display = "block";
      } else {
        if (likertOptions) likertOptions.style.display = "none";
        if (optionsGroup) optionsGroup.style.display = "none";
      }
    });
  });
  // Delete Question Button
  document.querySelectorAll(".delete-question-btn").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      if (confirm("Are you sure you want to delete this question?")) {
        const questionItem = this.closest(".question-item");
        const questionId = questionItem.dataset.id;

        fetch("questions.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `action=delete_question&question_id=${questionId}`,
        })
          .then((response) => response.text())
          .then(() => {
            questionItem.remove();
          });
      }
    });
  });

  // Cancel Question Form
  document.querySelectorAll(".cancel-question").forEach((btn) => {
    btn.addEventListener("click", function () {
      const container = this.closest(".question-form-container");
      container.style.display = "none";
      container.querySelector("form").reset();
    });
  });

  // Edit question functionality
  document.querySelectorAll(".edit-question-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const questionItem = this.closest(".question-item");
      const categorySection = this.closest(".category-section");
      const formContainer = categorySection.querySelector(
        ".question-form-container"
      );
      const form = formContainer.querySelector(".question-form");

      // Get question data
      const questionId = questionItem.dataset.id;
      const questionText = questionItem
        .querySelector(".question-text")
        .textContent.trim();
      const badgePrimary = questionItem.querySelector(".badge-primary");
      const questionType = badgePrimary
        ? badgePrimary.textContent.trim().toLowerCase()
        : "text"; // default to text if type not found
      const optionsText = questionItem.querySelector(".text-muted")
        ? questionItem
            .querySelector(".text-muted")
            .textContent.replace("Options: ", "")
            .trim()
        : "";
      // Update form for editing
      form.querySelector('input[name="action"]').value = "edit_question";
      form.insertAdjacentHTML(
        "beforeend",
        `<input type="hidden" name="question_id" value="${questionId}">`
      );
      form.querySelector('textarea[name="question_text"]').value = questionText;

      // Set question type
      const typeSelect = form.querySelector('select[name="question_type"]');
      let selectedType;
      switch (questionType) {
        case "likert scale":
          selectedType = "likert_scale";
          break;
        case "drop down":
          selectedType = "drop_down";
          break;
        default:
          selectedType = questionType;
      }
      typeSelect.value = selectedType;

      // Show appropriate options based on type
      const likertOptions = form.querySelector(".likert-options");
      const optionsGroup = form.querySelector(".options-group");

      if (selectedType === "likert_scale") {
        likertOptions.style.display = "block";
        optionsGroup.style.display = "none";
      } else if (["drop_down", "checkbox"].includes(selectedType)) {
        likertOptions.style.display = "none";
        optionsGroup.style.display = "block";
        form.querySelector('input[name="options"]').value = optionsText;
      } else {
        likertOptions.style.display = "none";
        optionsGroup.style.display = "none";
      }

      // Show form
      formContainer.style.display = "block";

      // Scroll to form
      formContainer.scrollIntoView({ behavior: "smooth", block: "center" });

      // Update form submit handler
      form.onsubmit = function (e) {
        e.preventDefault();
        $.ajax({
          url: window.location.href,
          method: "POST",
          data: $(this).serialize(),
          success: function (response) {
            location.reload();
          },
        });
      };
    });
  });

  // Reset form when cancelled or after submission
  document.querySelectorAll(".cancel-question").forEach((btn) => {
    btn.addEventListener("click", function () {
      const form = this.closest("form");
      form.reset();
      form.querySelector('input[name="action"]').value = "add_question";
      form.querySelector('input[name="question_id"]')?.remove();
      form.closest(".question-form-container").style.display = "none";
    });
  });

  // Handle cancel edit
  document.addEventListener("click", function (e) {
    if (e.target.matches(".cancel-edit")) {
      const editRow = e.target.closest("tr");
      const questionRow = editRow.previousElementSibling;
      questionRow.style.display = "table-row";
      editRow.remove();
    }
  });

  // Handle question form submission
  $(".question-form").on("submit", function (e) {
    e.preventDefault();
    const form = $(this);
    $.ajax({
      url: window.location.href,
      method: "POST",
      data: form.serialize(),
      dataType: "json",
      success: function (response) {
        if (response.success) {
          location.reload();
        } else {
          alert(response.message || "Error saving question");
        }
      },
      error: function (xhr, status, error) {
        alert("Error saving question: " + error);
      },
    });
  });

  // Handle edit category form
  $(".edit-category-form").on("submit", function (e) {
    e.preventDefault();
    const form = $(this);
    $.ajax({
      url: window.location.href,
      method: "POST",
      data: form.serialize(),
      success: function (response) {
        location.reload();
      },
    });
  });

  // Handle delete actions
  $('form[id^="delete-"]').on("submit", function (e) {
    e.preventDefault();
    const form = $(this);
    $.ajax({
      url: window.location.href,
      method: "POST",
      data: form.serialize(),
      success: function (response) {
        location.reload();
      },
    });
  });
  // Handle Likert Scale Custom Options
  document
    .querySelectorAll('select[name="likert_preset"]')
    .forEach((select) => {
      select.addEventListener("change", function () {
        const customOptions = this.closest(".likert-options").querySelector(
          "#custom_likert_options"
        );
        if (this.value === "custom") {
          customOptions.style.display = "block";
        } else {
          customOptions.style.display = "none";
        }
      });
    });

  // Validate custom scale options before form submission
  document.querySelectorAll(".question-form").forEach((form) => {
    form.addEventListener("submit", function (e) {
      const likertPreset = this.querySelector('select[name="likert_preset"]');
      if (likertPreset && likertPreset.value === "custom") {
        const customInputs = Array.from(
          this.querySelectorAll('input[name="custom_scale[]"]')
        );
        if (customInputs.some((input) => !input.value.trim())) {
          e.preventDefault();
          alert("Please fill in all custom scale options");
        } else {
          const customOptions = customInputs.map((input) => input.value.trim());
          const hiddenInput = document.createElement("input");
          hiddenInput.type = "hidden";
          hiddenInput.name = "custom_likert_options";
          hiddenInput.value = JSON.stringify(customOptions);
          this.appendChild(hiddenInput);
        }
      }
    });
  });
});
// document.querySelectorAll(".edit-question-btn").forEach((btn) => {
//   btn.addEventListener("click", function () {
//     const item = this.closest(".question-item");
//     const questionText = item
//       .querySelector(".question-text")
//       .textContent.trim();
//     const questionType = item
//       .querySelector(".badge-primary")
//       .textContent.trim()
//       .toLowerCase();

//     // Get the edit form elements
//     const editForm = item.querySelector(".edit-form");
//     const textArea = editForm.querySelector("textarea[name='question_text']");
//     const typeSelect = editForm.querySelector("select[name='question_type']");
//     const likertOptions = editForm.querySelector(".likert-options");
//     const optionsGroup = editForm.querySelector(".options-group");

//     // Set the question text
//     textArea.value = questionText;

//     // Set the question type with proper mapping
//     let selectedType;
//     switch (questionType) {
//       case "likert scale":
//         selectedType = "likert_scale";
//         break;
//       case "drop down":
//         selectedType = "drop_down";
//         break;
//       default:
//         selectedType = questionType;
//     }
//     typeSelect.value = selectedType;

//     // Trigger change event to show/hide appropriate options
//     const changeEvent = new Event("change");
//     typeSelect.dispatchEvent(changeEvent);

//     // Show edit form
//     item.querySelector(".question-content").style.display = "none";
//     item.querySelector(".question-actions").style.display = "none";
//     editForm.style.display = "block";
//   });
// });
// Initialize Sortable for question lists
