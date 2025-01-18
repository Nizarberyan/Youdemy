// Toast Notification System
const toast = {
  show(message, type = "success", duration = 3000) {
    const toast = document.createElement("div");
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.remove();
    }, duration);
  },
};

// Form Validation
const validateForm = (formElement) => {
  const inputs = formElement.querySelectorAll(
    "input[required], select[required], textarea[required]"
  );
  let isValid = true;

  inputs.forEach((input) => {
    if (!input.value.trim()) {
      isValid = false;
      input.classList.add("border-red-500");

      const errorMessage = input.dataset.error || "This field is required";
      const errorDiv = document.createElement("div");
      errorDiv.className = "text-red-500 text-sm mt-1";
      errorDiv.textContent = errorMessage;

      const existingError = input.parentNode.querySelector(".text-red-500");
      if (!existingError) {
        input.parentNode.appendChild(errorDiv);
      }
    } else {
      input.classList.remove("border-red-500");
      const existingError = input.parentNode.querySelector(".text-red-500");
      if (existingError) {
        existingError.remove();
      }
    }
  });

  return isValid;
};

// Lazy Loading Images
document.addEventListener("DOMContentLoaded", () => {
  const lazyImages = document.querySelectorAll("img[data-src]");

  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.removeAttribute("data-src");
        observer.unobserve(img);
      }
    });
  });

  lazyImages.forEach((img) => imageObserver.observe(img));
});

// Dropdown Toggle
const toggleDropdown = (dropdownId) => {
  const dropdown = document.getElementById(dropdownId);
  dropdown.classList.toggle("hidden");
};

// Mobile Menu Toggle
const toggleMobileMenu = () => {
  const mobileMenu = document.getElementById("mobile-menu");
  mobileMenu.classList.toggle("hidden");
};

// Confirm Dialog
const confirm = (message, callback) => {
  if (window.confirm(message)) {
    callback();
  }
};

// AJAX Form Submit
const submitForm = async (formElement, callback) => {
  if (!validateForm(formElement)) return;

  const formData = new FormData(formElement);

  try {
    const response = await fetch(formElement.action, {
      method: formElement.method,
      body: formData,
    });

    const data = await response.json();
    callback(data);
  } catch (error) {
    console.error("Form submission error:", error);
    toast.show("An error occurred. Please try again.", "error");
  }
};

// Stats Counter Animation
const animateCounter = (element, target, duration = 2000) => {
  const start = 0;
  const increment = target / (duration / 16);
  let current = start;

  const updateCounter = () => {
    current += increment;
    element.textContent = Math.floor(current);

    if (current < target) {
      requestAnimationFrame(updateCounter);
    } else {
      element.textContent = target;
    }
  };

  updateCounter();
};

// Initialize all counters
document.querySelectorAll(".stats-number").forEach((counter) => {
  const target = parseInt(counter.textContent);
  animateCounter(counter, target);
});
