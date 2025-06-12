function switchToRegister() {
  document.querySelector(".auth-container").classList.add("register-mode");
  document.querySelector(".login-form").style.display = "none";
  document.querySelector(".register-form").style.display = "block";
  document.querySelector(".login-side").style.display = "none";
  document.querySelector(".register-side").style.display = "block";
}

function switchToLogin() {
  document.querySelector(".auth-container").classList.remove("register-mode");
  document.querySelector(".login-form").style.display = "block";
  document.querySelector(".register-form").style.display = "none";
  document.querySelector(".login-side").style.display = "block";
  document.querySelector(".register-side").style.display = "none";
}

async function submitLogin(event) {
  event.preventDefault();
  const form = event.target;
  const formData = new FormData(form);

  try {
    const response = await fetch("auth.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      window.location.href = data.redirect;
    } else {
      const errorDiv = document.getElementById("login-error");
      errorDiv.textContent = data.message;
      errorDiv.style.display = "block";
    }
  } catch (error) {
    console.error("Error:", error);
  }
}

async function submitRegister(event) {
  event.preventDefault();
  const form = event.target;
  const formData = new FormData(form);

  try {
    const response = await fetch("auth.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      const successDiv = document.getElementById("register-success");
      successDiv.textContent = data.message;
      successDiv.style.display = "block";
      document.getElementById("register-error").style.display = "none";
      form.reset();
      setTimeout(() => {
        switchToLogin();
      }, 2000);
    } else {
      const errorDiv = document.getElementById("register-error");
      errorDiv.textContent = data.message;
      errorDiv.style.display = "block";
      document.getElementById("register-success").style.display = "none";
    }
  } catch (error) {
    console.error("Error:", error);
  }
}

document.getElementById("password")?.addEventListener("input", function () {
  var confirmPassword = document.getElementById("confirm_password");
  if (confirmPassword && this.value !== confirmPassword.value) {
    confirmPassword.setCustomValidity("Les mots de passe ne correspondent pas");
  } else if (confirmPassword) {
    confirmPassword.setCustomValidity("");
  }
});

document
  .getElementById("confirm_password")
  ?.addEventListener("input", function () {
    if (this.value !== document.getElementById("password").value) {
      this.setCustomValidity("Les mots de passe ne correspondent pas");
    } else {
      this.setCustomValidity("");
    }
  });