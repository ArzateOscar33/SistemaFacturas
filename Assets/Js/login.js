alert("conectado al login.js");
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formLogin");
  const btn = document.getElementById("btnLogin");
  const btnText = btn.querySelector(".login-text");
  const btnLoading = btn.querySelector(".login-loading");

  const inputUsuario = document.getElementById("usuario");
  const inputPassword = document.getElementById("password");

  const togglePassword = document.getElementById("btnTogglePassword");

  // =========================
  // MOSTRAR / OCULTAR PASSWORD
  // =========================
  togglePassword.addEventListener("click", function () {
    const tipo =
      inputPassword.getAttribute("type") === "password" ? "text" : "password";
    inputPassword.setAttribute("type", tipo);

    this.innerHTML =
      tipo === "password"
        ? '<i data-feather="eye"></i>'
        : '<i data-feather="eye-off"></i>';

    feather.replace();
  });

  // =========================
  // SUBMIT LOGIN
  // =========================
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const usuario = inputUsuario.value.trim();
    const password = inputPassword.value.trim();

    if (usuario === "" || password === "") {
      Swal.fire({
        icon: "warning",
        title: "Campos requeridos",
        text: "Ingresa usuario y contraseña.",
      });
      return;
    }

    setLoading(true);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", base_url + "login/validar", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        setLoading(false);

        if (xhr.status === 200) {
          try {
            const res = JSON.parse(xhr.responseText);

            if (res.ok) {
              Swal.fire({
                icon: "success",
                title: "Bienvenido",
                text: res.msg,
                timer: 1500,
                showConfirmButton: false,
              }).then(() => {
                window.location.href = res.redirect;
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: res.msg,
              });
            }
          } catch (error) {
            Swal.fire({
              icon: "error",
              title: "Error inesperado",
              text: "Respuesta inválida del servidor.",
            });
            console.error(error, xhr.responseText);
          }
        } else {
          Swal.fire({
            icon: "error",
            title: "Error de conexión",
            text: "No se pudo conectar con el servidor.",
          });
        }
      }
    };

    const params = `usuario=${encodeURIComponent(usuario)}&password=${encodeURIComponent(password)}`;
    xhr.send(params);
  });

  // =========================
  // LOADING BUTTON
  // =========================
  function setLoading(state) {
    if (state) {
      btn.disabled = true;
      btnText.classList.add("d-none");
      btnLoading.classList.remove("d-none");
    } else {
      btn.disabled = false;
      btnText.classList.remove("d-none");
      btnLoading.classList.add("d-none");
    }
  }
});
