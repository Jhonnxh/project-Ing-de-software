document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("loginForm");
  const alertContainer = document.getElementById("alertContainer");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    alertContainer.innerHTML = "";

    try {
      const res = await fetch("/api/auth/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password }),
      });

      const data = await res.json();

      if (!res.ok || !data.ok) {
        showAlert("Correo o contraseña incorrectos", "danger");
        return;
      }

      showAlert("Inicio de sesión exitoso. Redirigiendo...", "success");

      setTimeout(() => {
        // Aquí puedes redirigir al dashboard o a otra vista
        window.location.href = "/views/dashboard.html";
      }, 1500);

    } catch (error) {
      console.error("Login error:", error);
      showAlert("Error de conexión con el servidor.", "warning");
    }
  });

  function showAlert(message, type = "info") {
    alertContainer.innerHTML = `
      <div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;
  }
});
