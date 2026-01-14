let currentSection = 0;
const sections = document.querySelectorAll(".category-section");
const totalSections = sections.length;

function updateButtons() {
  const prevButton = document.getElementById("prevButton");
  const nextButton = document.getElementById("nextButton");
  const submitButton = document.getElementById("submitButton");

  prevButton.style.display = currentSection > 0 ? "inline-block" : "none";
  nextButton.style.display =
    currentSection < totalSections - 1 ? "inline-block" : "none";
  submitButton.style.display =
    currentSection === totalSections - 1 ? "inline-block" : "none";
}

function showSection(index) {
  sections.forEach((section, idx) => {
    section.style.display = idx === index ? "block" : "none";
  });
  currentSection = index;
  updateButtons();
}

function nextSection() {
  if (validateCurrentSection()) {
    if (currentSection < totalSections - 1) {
      showSection(currentSection + 1);
      window.scrollTo(0, 0);
    }
  }
}

function prevSection() {
  if (currentSection > 0) {
    showSection(currentSection - 1);
    window.scrollTo(0, 0);
  }
}

function validateCurrentSection() {
  const currentSectionElement = sections[currentSection];
  const requiredInputs = currentSectionElement.querySelectorAll(
    "input[required], select[required], textarea[required]"
  );
  let isValid = true;

  requiredInputs.forEach((input) => {
    if (input.type === "radio") {
      const radioGroup = currentSectionElement.querySelectorAll(
        `input[name="${input.name}"]`
      );
      const checked = Array.from(radioGroup).some((radio) => radio.checked);
      if (!checked) isValid = false;
    } else if (!input.value.trim()) {
      isValid = false;
    }
  });

  if (!isValid) {
    alert("Please answer all questions in this section before proceeding.");
  }
  return isValid;
}

function handleSubmit(event) {
  event.preventDefault();
  const submitButton = document.getElementById("submitButton");

  if (!validateCurrentSection()) {
    return;
  }

  // Submit the form traditionally
  const form = event.target;
  submitButton.disabled = true;
  submitButton.innerHTML = "Submitting...";
  form.submit();
}

// function showSuccessMessage() {
//   const modal = document.createElement("div");
//   modal.className = "success-modal";
//   modal.innerHTML = `
//       <div class="success-content">
//           <i class="fa fa-check-circle"></i>
//           <h2>Thank You!</h2>
//           <p>Your survey response has been successfully recorded.</p>
//           <p>You will be redirected to the home page shortly...</p>
//       </div>
//   `;

//   // Add styles
//   const style = document.createElement("style");
//   style.textContent = `
//       .success-modal {
//           position: fixed;
//           top: 0;
//           left: 0;
//           right: 0;
//           bottom: 0;
//           background: rgba(0, 0, 0, 0.8);
//           display: flex;
//           align-items: center;
//           justify-content: center;
//           z-index: 9999;
//       }
//       .success-content {
//           background: white;
//           padding: 40px;
//           border-radius: 10px;
//           text-align: center;
//           max-width: 400px;
//           animation: slideIn 0.3s ease;
//           box-shadow: 0 5px 15px rgba(0,0,0,0.3);
//       }
//       .success-content i {
//           font-size: 48px;
//           color: #2ecc71;
//           margin-bottom: 20px;
//       }
//       .success-content h2 {
//           color: #2c3e50;
//           margin-bottom: 15px;
//       }
//       .success-content p {
//           color: #666;
//           margin-bottom: 10px;
//       }
//       @keyframes slideIn {
//           from { transform: translateY(-20px); opacity: 0; }
//           to { transform: translateY(0); opacity: 1; }
//       }
//   `;
//   document.head.appendChild(style);
//   document.body.appendChild(modal);

//   // Redirect after 10 seconds
//   setTimeout(() => {
//     window.location.href = "/isy_scs";
//   }, 10000);
// }
// Initialize first section
showSection(0);
