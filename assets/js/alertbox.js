class AdmoAlertBox {
  static createAlert({ title = "", text = "", type = "info", onConfirm = null } = {}) {
    // Check if an alert is already displayed
    if (document.querySelector(".simple-alert-overlay")) {
      console.warn("An alert is already open.");
      return;
    }

    // Overlay
    const overlay = document.createElement("div");
    overlay.className = "simple-alert-overlay";
    document.body.appendChild(overlay);

    // Alert box
    const alertBox = document.createElement("div");
    alertBox.className = `simple-alert-box simple-alert-${type}`;
    overlay.appendChild(alertBox);

    // Animated Icon Box
    const iconBox = document.createElement("div");
    iconBox.className = `simple-alert-icon simple-alert-icon-${type}`;
    alertBox.appendChild(iconBox);

    // Checkmark for success or equivalent icon for other types
    if (type === "success") {
      iconBox.innerHTML = `
            <svg viewBox="0 0 52 52" class="simple-alert-checkmark">
                <circle cx="26" cy="26" r="24" fill="none" class="simple-alert-circle" />
                <path fill="none" d="M14 27l10 10L39 16" class="simple-alert-check" />
            </svg>
        `;
    }

    // Title
    if (title) {
      const alertTitle = document.createElement("h2");
      alertTitle.className = "simple-alert-title";
      alertTitle.textContent = title;
      alertBox.appendChild(alertTitle);
    }

    // Text
    if (text) {
      const alertText = document.createElement("p");
      alertText.className = "simple-alert-text";
      alertText.textContent = text;
      alertBox.appendChild(alertText);
    }

    // Confirm Button
    const confirmButton = document.createElement("button");
    confirmButton.className = "simple-alert-confirm";
    confirmButton.textContent = "OK";
    confirmButton.addEventListener("click", () => {
      document.body.removeChild(overlay);
      if (typeof onConfirm === "function") onConfirm();
    });
    alertBox.appendChild(confirmButton);
  }
}
