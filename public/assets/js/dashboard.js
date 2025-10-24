document.addEventListener("DOMContentLoaded", async () => {
  const welcomeMsg = document.getElementById("welcomeMsg");
  const roleInfo = document.getElementById("roleInfo");
  const logoutBtn = document.getElementById("logoutBtn");

  try {
    // Verificar sesión desde el backend protegido
    const res = await fetch("/api/test/protected.php", {
      method: "GET",
      credentials: "include"
    });

    const data = await res.json();

    // Si no hay sesión, redirigir al login
    if (!res.ok || data.error) {
            console.log("Respuesta del backend:", data);
            window.location.href = "/views/login.html";
        return;
        }


    // Mostrar nombre y rol
    const nombre = data.fullName ?? "Usuario";
    const rol = (data.roles && data.roles.length > 0) ? data.roles[0] : "Sin rol";

    welcomeMsg.textContent = `Bienvenido, ${nombre}`;
    roleInfo.textContent = `Usted es un ${rol}.`;

  } catch (err) {
    console.error("Error verificando sesión:", err);
    window.location.href = "/views/login.html";
  }

  // Cerrar sesión
      logoutBtn.addEventListener("click", async () => {
      try {
        const res = await fetch("/api/auth/logout.php", {
          method: "POST",
          credentials: "include"
        });

        const data = await res.json();
        console.log("Logout:", data);

        // Limpia las cookies localmente (por si acaso)
        document.cookie = "accessToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie = "refreshToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        
        window.location.href = "/views/login.html";
      } catch (err) {
        console.error("Error cerrando sesión:", err);
      }
      d

    });

});
