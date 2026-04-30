document.addEventListener("DOMContentLoaded", function () {
  const formPerfil = document.getElementById("formPerfil");
  const formPassword = document.getElementById("formPassword");

  const btnRecargarPerfil = document.getElementById("btnRecargarPerfil");
  const btnLimpiarPassword = document.getElementById("btnLimpiarPassword");

  const btnGuardarPerfil = document.getElementById("btnGuardarPerfil");
  const btnGuardarPassword = document.getElementById("btnGuardarPassword");

  const inputIdUsuario = document.getElementById("id_usuario");
  const inputNombre = document.getElementById("nombre");
  const inputApellido = document.getElementById("apellido");
  const inputUsuario = document.getElementById("usuario");
  const inputCorreo = document.getElementById("correo");
  const inputRol = document.getElementById("rol");
  const inputEstado = document.getElementById("estado");

  const inputPasswordActual = document.getElementById("password_actual");
  const inputPasswordNueva = document.getElementById("password_nueva");
  const inputPasswordConfirmar = document.getElementById("password_confirmar");

  cargarPerfil();

  if (formPerfil) {
    formPerfil.addEventListener("submit", function (e) {
      e.preventDefault();
      actualizarPerfil();
    });
  }

  if (formPassword) {
    formPassword.addEventListener("submit", function (e) {
      e.preventDefault();
      cambiarPassword();
    });
  }

  if (btnRecargarPerfil) {
    btnRecargarPerfil.addEventListener("click", function () {
      cargarPerfil();
    });
  }

  if (btnLimpiarPassword) {
    btnLimpiarPassword.addEventListener("click", function () {
      limpiarPassword();
    });
  }

  document.querySelectorAll(".btn-toggle-password").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const targetId = this.dataset.target;
      togglePassword(targetId, this);
    });
  });

  /* ============================================================
     CARGAR PERFIL
  ============================================================ */
  function cargarPerfil() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "perfil/obtener", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No fue posible cargar tu perfil.",
        });

        console.error("Error HTTP perfil:", xhr.status, xhr.responseText);
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          Swal.fire({
            icon: "warning",
            title: "No fue posible cargar el perfil",
            text: res.msg || "Intenta nuevamente.",
          });

          return;
        }

        llenarPerfil(res.perfil || {});
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Respuesta inválida",
          text: "El servidor no regresó un JSON válido.",
        });

        console.error("JSON inválido perfil:", error, xhr.responseText);
      }
    };

    xhr.send();
  }

  function llenarPerfil(perfil) {
    if (inputIdUsuario) inputIdUsuario.value = perfil.id_usuario || "";
    if (inputNombre) inputNombre.value = perfil.nombre || "";
    if (inputApellido) inputApellido.value = perfil.apellido || "";
    if (inputUsuario) inputUsuario.value = perfil.usuario || "";
    if (inputCorreo) inputCorreo.value = perfil.correo || "";
    if (inputRol) inputRol.value = perfil.rol || "";
    if (inputEstado)
      inputEstado.value =
        Number(perfil.estado || 0) === 1 ? "Activo" : "Inactivo";

    setTextRaw("kpiUsuarioPerfil", perfil.usuario || "--");
    setTextRaw("kpiRolPerfil", perfil.rol || "--");
    setHtml("kpiEstadoPerfil", badgeEstado(perfil.estado));
    setTextRaw(
      "kpiActualizadoPerfil",
      formatoFechaHora(perfil.actualizado_en || perfil.creado_en),
    );

    if (window.feather) {
      feather.replace();
    }
  }

  /* ============================================================
     ACTUALIZAR PERFIL
  ============================================================ */
  function actualizarPerfil() {
    if (!formPerfil) return;

    const nombre = inputNombre ? inputNombre.value.trim() : "";
    const usuario = inputUsuario ? inputUsuario.value.trim() : "";
    const correo = inputCorreo ? inputCorreo.value.trim() : "";

    if (nombre === "") {
      Swal.fire({
        icon: "warning",
        title: "Nombre requerido",
        text: "Ingresa tu nombre.",
      });

      if (inputNombre) inputNombre.focus();
      return;
    }

    if (usuario === "") {
      Swal.fire({
        icon: "warning",
        title: "Usuario requerido",
        text: "Ingresa tu nombre de usuario.",
      });

      if (inputUsuario) inputUsuario.focus();
      return;
    }

    if (!/^[A-Za-z0-9._-]+$/.test(usuario)) {
      Swal.fire({
        icon: "warning",
        title: "Usuario no válido",
        text: "El usuario solo puede contener letras, números, punto, guion y guion bajo.",
      });

      if (inputUsuario) inputUsuario.focus();
      return;
    }

    if (correo !== "" && !validarCorreo(correo)) {
      Swal.fire({
        icon: "warning",
        title: "Correo no válido",
        text: "Ingresa un correo con formato válido.",
      });

      if (inputCorreo) inputCorreo.focus();
      return;
    }

    const formData = new FormData(formPerfil);

    setLoading(btnGuardarPerfil, true);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", base_url + "perfil/actualizar", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      setLoading(btnGuardarPerfil, false);

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No fue posible actualizar tu perfil.",
        });

        console.error(
          "Error HTTP actualizar perfil:",
          xhr.status,
          xhr.responseText,
        );
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          Swal.fire({
            icon: "warning",
            title: "No fue posible actualizar",
            text: res.msg || "Revisa la información e intenta nuevamente.",
          });

          return;
        }

        Swal.fire({
          icon: "success",
          title: "Perfil actualizado",
          text: res.msg || "Tu información fue actualizada correctamente.",
          timer: 1400,
          showConfirmButton: false,
        });

        if (res.perfil) {
          llenarPerfil(res.perfil);
        } else {
          cargarPerfil();
        }
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Respuesta inválida",
          text: "El servidor no regresó un JSON válido.",
        });

        console.error(
          "JSON inválido actualizar perfil:",
          error,
          xhr.responseText,
        );
      }
    };

    xhr.send(formData);
  }

  /* ============================================================
     CAMBIAR CONTRASEÑA
  ============================================================ */
  function cambiarPassword() {
    if (!formPassword) return;

    const actual = inputPasswordActual ? inputPasswordActual.value.trim() : "";
    const nueva = inputPasswordNueva ? inputPasswordNueva.value.trim() : "";
    const confirmar = inputPasswordConfirmar
      ? inputPasswordConfirmar.value.trim()
      : "";

    if (actual === "" || nueva === "" || confirmar === "") {
      Swal.fire({
        icon: "warning",
        title: "Campos requeridos",
        text: "Completa todos los campos de contraseña.",
      });
      return;
    }

    if (nueva.length < 8) {
      Swal.fire({
        icon: "warning",
        title: "Contraseña muy corta",
        text: "La nueva contraseña debe tener al menos 8 caracteres.",
      });

      if (inputPasswordNueva) inputPasswordNueva.focus();
      return;
    }

    if (nueva.length > 72) {
      Swal.fire({
        icon: "warning",
        title: "Contraseña demasiado larga",
        text: "La nueva contraseña no puede exceder 72 caracteres.",
      });

      if (inputPasswordNueva) inputPasswordNueva.focus();
      return;
    }

    if (actual === nueva) {
      Swal.fire({
        icon: "warning",
        title: "Contraseña repetida",
        text: "La nueva contraseña debe ser diferente a la contraseña actual.",
      });

      if (inputPasswordNueva) inputPasswordNueva.focus();
      return;
    }

    if (nueva !== confirmar) {
      Swal.fire({
        icon: "warning",
        title: "No coinciden",
        text: "La confirmación no coincide con la nueva contraseña.",
      });

      if (inputPasswordConfirmar) inputPasswordConfirmar.focus();
      return;
    }

    const formData = new FormData(formPassword);

    setLoading(btnGuardarPassword, true);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", base_url + "perfil/cambiarPassword", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      setLoading(btnGuardarPassword, false);

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No fue posible cambiar la contraseña.",
        });

        console.error(
          "Error HTTP cambiar password:",
          xhr.status,
          xhr.responseText,
        );
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          Swal.fire({
            icon: "warning",
            title: "No fue posible cambiar la contraseña",
            text: res.msg || "Revisa la información e intenta nuevamente.",
          });

          return;
        }

        Swal.fire({
          icon: "success",
          title: "Contraseña actualizada",
          text: res.msg || "Tu contraseña fue actualizada correctamente.",
          timer: 1600,
          showConfirmButton: false,
        });

        limpiarPassword();
        cargarPerfil();
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Respuesta inválida",
          text: "El servidor no regresó un JSON válido.",
        });

        console.error(
          "JSON inválido cambiar password:",
          error,
          xhr.responseText,
        );
      }
    };

    xhr.send(formData);
  }

  /* ============================================================
     LIMPIEZA / PASSWORD TOGGLE
  ============================================================ */
  function limpiarPassword() {
    if (formPassword) {
      formPassword.reset();
    }

    [inputPasswordActual, inputPasswordNueva, inputPasswordConfirmar].forEach(
      function (input) {
        if (input) {
          input.type = "password";
        }
      },
    );

    document.querySelectorAll(".btn-toggle-password").forEach(function (btn) {
      btn.innerHTML = `<i data-feather="eye"></i>`;
    });

    if (window.feather) {
      feather.replace();
    }
  }

  function togglePassword(targetId, btn) {
    const input = document.getElementById(targetId);

    if (!input || !btn) return;

    const visible = input.type === "text";
    input.type = visible ? "password" : "text";

    btn.innerHTML = visible
      ? `<i data-feather="eye"></i>`
      : `<i data-feather="eye-off"></i>`;

    if (window.feather) {
      feather.replace();
    }
  }

  /* ============================================================
     HELPERS
  ============================================================ */
  function setLoading(button, state) {
    if (!button) return;

    const btnText = button.querySelector(".btn-text");
    const btnLoading = button.querySelector(".btn-loading");

    button.disabled = state;

    if (!btnText || !btnLoading) return;

    if (state) {
      btnText.classList.add("d-none");
      btnLoading.classList.remove("d-none");
    } else {
      btnText.classList.remove("d-none");
      btnLoading.classList.add("d-none");
    }
  }

  function setTextRaw(id, value) {
    const el = document.getElementById(id);

    if (el) {
      el.textContent =
        value === null || value === undefined || value === ""
          ? "--"
          : String(value);
    }
  }

  function setHtml(id, html) {
    const el = document.getElementById(id);

    if (el) {
      el.innerHTML = html;
    }
  }

  function badgeEstado(estado) {
    estado = Number(estado || 0);

    if (estado === 1) {
      return `
        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-6">
          Activo
        </span>
      `;
    }

    return `
      <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill fs-6">
        Inactivo
      </span>
    `;
  }

  function validarCorreo(correo) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo);
  }

  function formatoFechaHora(fecha) {
    if (!fecha) return "--";

    const partes = String(fecha).split(" ");

    if (partes.length < 1) {
      return fecha;
    }

    const fechaPartes = partes[0].split("-");

    if (fechaPartes.length !== 3) {
      return fecha;
    }

    const fechaFormateada = `${fechaPartes[2]}/${fechaPartes[1]}/${fechaPartes[0]}`;
    const hora = partes[1] ? partes[1].substring(0, 5) : "";

    return hora !== "" ? `${fechaFormateada} ${hora}` : fechaFormateada;
  }
});
