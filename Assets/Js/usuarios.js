document.addEventListener("DOMContentLoaded", function () {
  const btnNuevoUsuario = document.getElementById("btnNuevoUsuario");
  const modalUsuarioEl = document.getElementById("modalUsuario");
  const formUsuario = document.getElementById("formUsuario");
  const modalTitle = document.getElementById("modalUsuarioLabel");

  const btnGuardarUsuario = document.getElementById("btnGuardarUsuario");
  const guardarText = btnGuardarUsuario
    ? btnGuardarUsuario.querySelector(".guardar-text")
    : null;
  const guardarLoading = btnGuardarUsuario
    ? btnGuardarUsuario.querySelector(".guardar-loading")
    : null;

  const tbodyUsuarios = document.getElementById("tbodyUsuarios");
  const buscarUsuario = document.getElementById("buscarUsuario");
  const filtroEstado = document.getElementById("filtroEstado");

  const inputIdUsuario = document.getElementById("id_usuario");
  const inputNombre = document.getElementById("nombre");
  const inputApellido = document.getElementById("apellido");
  const selectRol = document.getElementById("id_rol");
  const inputUsuario = document.getElementById("usuario");
  const inputCorreo = document.getElementById("correo");
  const inputPassword = document.getElementById("password");
  const inputConfirmarPassword = document.getElementById("confirmar_password");
  const selectEstado = document.getElementById("estado");
  const btnTogglePassword = document.getElementById("btnTogglePassword");
  const passwordHelp = document.getElementById("passwordHelp");

  let modalUsuario = null;
  let timerBusqueda = null;

  if (modalUsuarioEl) {
    modalUsuario = new bootstrap.Modal(modalUsuarioEl, {
      backdrop: "static",
      keyboard: false,
    });
  }

  cargarRoles();
  cargarUsuarios();

  if (btnNuevoUsuario) {
    btnNuevoUsuario.addEventListener("click", function () {
      abrirModalNuevoUsuario();
    });
  }

  if (buscarUsuario) {
    buscarUsuario.addEventListener("input", function () {
      clearTimeout(timerBusqueda);

      timerBusqueda = setTimeout(function () {
        cargarUsuarios();
      }, 350);
    });
  }

  if (filtroEstado) {
    filtroEstado.addEventListener("change", function () {
      cargarUsuarios();
    });
  }

  if (btnTogglePassword) {
    btnTogglePassword.addEventListener("click", function () {
      togglePassword();
    });
  }

  if (tbodyUsuarios) {
    tbodyUsuarios.addEventListener("click", function (e) {
      const btnEditar = e.target.closest(".btnEditarUsuario");
      const btnEstado = e.target.closest(".btnCambiarEstadoUsuario");

      if (btnEditar) {
        const idUsuario = btnEditar.getAttribute("data-id");
        abrirModalEditarUsuario(idUsuario);
        return;
      }

      if (btnEstado) {
        const idUsuario = btnEstado.getAttribute("data-id");
        const estadoActual = btnEstado.getAttribute("data-estado");
        confirmarCambioEstado(idUsuario, estadoActual);
      }
    });
  }

  if (formUsuario) {
    formUsuario.addEventListener("submit", function (e) {
      e.preventDefault();

      if (!validarFormularioUsuario()) {
        return;
      }

      guardarUsuario();
    });
  }

  function cargarUsuarios() {
    if (!tbodyUsuarios) return;

    const buscar = buscarUsuario ? buscarUsuario.value.trim() : "";
    const estado = filtroEstado ? filtroEstado.value.trim() : "";

    tbodyUsuarios.innerHTML = `
      <tr>
        <td colspan="6">
          <div class="empty-state">
            <div class="empty-state-icon">
              <i data-feather="loader"></i>
            </div>
            <h6 class="fw-bold mb-1">Cargando usuarios...</h6>
            <div>Espera un momento.</div>
          </div>
        </td>
      </tr>
    `;

    refrescarIconos();

    const params =
      "buscar=" +
      encodeURIComponent(buscar) +
      "&estado=" +
      encodeURIComponent(estado);

    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "usuarios/listar?" + params, true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        pintarErrorTabla("No se pudo conectar con el servidor.");
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          pintarErrorTabla(res.msg || "No fue posible listar los usuarios.");
          return;
        }

        pintarUsuarios(res.data || []);
      } catch (error) {
        console.error(error, xhr.responseText);
        pintarErrorTabla("La respuesta del servidor no es válida.");
      }
    };

    xhr.send();
  }

  function cargarRoles() {
    if (!selectRol) return;

    selectRol.innerHTML = `<option value="">Cargando roles...</option>`;

    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "usuarios/roles", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        selectRol.innerHTML = `<option value="">Error al cargar roles</option>`;
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          selectRol.innerHTML = `<option value="">Error al cargar roles</option>`;
          return;
        }

        let html = `<option value="">Seleccione un rol</option>`;

        (res.data || []).forEach(function (rol) {
          html += `
            <option value="${rol.id_rol}">
              ${escapeHtml(rol.nombre)}
            </option>
          `;
        });

        selectRol.innerHTML = html;
      } catch (error) {
        console.error(error, xhr.responseText);
        selectRol.innerHTML = `<option value="">Respuesta inválida</option>`;
      }
    };

    xhr.send();
  }

  function pintarUsuarios(usuarios) {
    if (!tbodyUsuarios) return;

    if (!usuarios.length) {
      tbodyUsuarios.innerHTML = `
        <tr>
          <td colspan="6">
            <div class="empty-state">
              <div class="empty-state-icon">
                <i data-feather="users"></i>
              </div>
              <h6 class="fw-bold mb-1">No hay usuarios registrados</h6>
              <div>Presiona “Nuevo usuario” para registrar el primero.</div>
            </div>
          </td>
        </tr>
      `;
      refrescarIconos();
      return;
    }

    let html = "";

    usuarios.forEach(function (usuario) {
      const estado = Number(usuario.estado || 0);
      const activo = estado === 1;

      const nombreCompleto = [usuario.nombre || "", usuario.apellido || ""]
        .join(" ")
        .trim();

      const badgeEstado = activo
        ? `<span class="badge-activo">Activo</span>`
        : `<span class="badge-inactivo">Inactivo</span>`;

      const iconoEstado = activo ? "user-x" : "user-check";
      const tituloEstado = activo ? "Dar de baja" : "Activar";
      const claseEstado = activo ? "text-danger" : "text-success";

      html += `
        <tr>
          <td>
            <strong>${escapeHtml(nombreCompleto || "Sin nombre")}</strong>
          </td>
          <td>${escapeHtml(usuario.usuario || "")}</td>
          <td>${escapeHtml(usuario.correo || "No aplica")}</td>
          <td>${escapeHtml(usuario.rol || "Sin rol")}</td>
          <td>${badgeEstado}</td>
          <td class="text-center">
            <button type="button"
                    class="btn-icon btnEditarUsuario me-1"
                    data-id="${usuario.id_usuario}"
                    title="Editar usuario">
              <i data-feather="edit-3"></i>
            </button>

            <button type="button"
                    class="btn-icon btnCambiarEstadoUsuario ${claseEstado}"
                    data-id="${usuario.id_usuario}"
                    data-estado="${estado}"
                    title="${tituloEstado}">
              <i data-feather="${iconoEstado}"></i>
            </button>
          </td>
        </tr>
      `;
    });

    tbodyUsuarios.innerHTML = html;
    refrescarIconos();
  }

  function abrirModalNuevoUsuario() {
    limpiarFormularioUsuario();

    if (modalTitle) {
      modalTitle.textContent = "Registrar usuario";
    }

    setPasswordModoRegistro();

    if (selectEstado) {
      selectEstado.value = "1";
    }

    if (modalUsuario) {
      modalUsuario.show();
    }

    setTimeout(function () {
      if (inputNombre) {
        inputNombre.focus();
      }
    }, 350);
  }

  function abrirModalEditarUsuario(idUsuario) {
    if (!idUsuario) return;

    const xhr = new XMLHttpRequest();
    xhr.open(
      "GET",
      base_url + "usuarios/obtener/" + encodeURIComponent(idUsuario),
      true,
    );

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No se pudo conectar con el servidor.",
        });
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: res.msg || "No fue posible obtener el usuario.",
          });
          return;
        }

        llenarFormularioUsuario(res.data);

        if (modalTitle) {
          modalTitle.textContent = "Editar usuario";
        }

        setPasswordModoEdicion();

        if (modalUsuario) {
          modalUsuario.show();
        }
      } catch (error) {
        console.error(error, xhr.responseText);
        Swal.fire({
          icon: "error",
          title: "Error inesperado",
          text: "Respuesta inválida del servidor.",
        });
      }
    };

    xhr.send();
  }

  function llenarFormularioUsuario(usuario) {
    limpiarFormularioUsuario();

    if (inputIdUsuario) inputIdUsuario.value = usuario.id_usuario || "";
    if (inputNombre) inputNombre.value = usuario.nombre || "";
    if (inputApellido) inputApellido.value = usuario.apellido || "";
    if (selectRol) selectRol.value = String(usuario.id_rol || "");
    if (inputUsuario) inputUsuario.value = usuario.usuario || "";
    if (inputCorreo) inputCorreo.value = usuario.correo || "";
    if (inputPassword) inputPassword.value = "";
    if (inputConfirmarPassword) inputConfirmarPassword.value = "";
    if (selectEstado) selectEstado.value = String(usuario.estado ?? "1");
  }

  function guardarUsuario() {
    const idUsuario = inputIdUsuario ? inputIdUsuario.value.trim() : "";

    const url = idUsuario
      ? base_url + "usuarios/actualizar"
      : base_url + "usuarios/registrar";

    const formData = new FormData(formUsuario);

    setLoading(true);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      setLoading(false);

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No se pudo conectar con el servidor.",
        });
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          Swal.fire({
            icon: "warning",
            title: "Aviso",
            text: res.msg || "No fue posible guardar el usuario.",
          });
          return;
        }

        Swal.fire({
          icon: "success",
          title: "Correcto",
          text: res.msg,
          timer: 1400,
          showConfirmButton: false,
        });

        if (modalUsuario) {
          modalUsuario.hide();
        }

        limpiarFormularioUsuario();
        cargarUsuarios();
      } catch (error) {
        console.error(error, xhr.responseText);
        Swal.fire({
          icon: "error",
          title: "Error inesperado",
          text: "Respuesta inválida del servidor.",
        });
      }
    };

    xhr.send(formData);
  }

  function confirmarCambioEstado(idUsuario, estadoActual) {
    const estaActivo = Number(estadoActual) === 1;
    const nuevoEstado = estaActivo ? 0 : 1;

    Swal.fire({
      icon: estaActivo ? "warning" : "question",
      title: estaActivo ? "Dar de baja usuario" : "Activar usuario",
      text: estaActivo
        ? "El usuario quedará inactivo y no podrá iniciar sesión."
        : "El usuario volverá a estar activo.",
      showCancelButton: true,
      confirmButtonText: estaActivo ? "Sí, dar de baja" : "Sí, activar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: estaActivo ? "#dc2626" : "#0d47a1",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      cambiarEstadoUsuario(idUsuario, nuevoEstado);
    });
  }

  function cambiarEstadoUsuario(idUsuario, nuevoEstado) {
    const formData = new FormData();
    formData.append("id_usuario", idUsuario);
    formData.append("estado", nuevoEstado);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", base_url + "usuarios/cambiarEstado", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No se pudo conectar con el servidor.",
        });
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          Swal.fire({
            icon: "warning",
            title: "Aviso",
            text: res.msg || "No fue posible cambiar el estado.",
          });
          return;
        }

        Swal.fire({
          icon: "success",
          title: "Correcto",
          text: res.msg,
          timer: 1300,
          showConfirmButton: false,
        });

        cargarUsuarios();
      } catch (error) {
        console.error(error, xhr.responseText);
        Swal.fire({
          icon: "error",
          title: "Error inesperado",
          text: "Respuesta inválida del servidor.",
        });
      }
    };

    xhr.send(formData);
  }

  function validarFormularioUsuario() {
    const idUsuario = inputIdUsuario ? inputIdUsuario.value.trim() : "";
    const nombre = inputNombre ? inputNombre.value.trim() : "";
    const apellido = inputApellido ? inputApellido.value.trim() : "";
    const idRol = selectRol ? selectRol.value.trim() : "";
    const usuario = inputUsuario ? inputUsuario.value.trim() : "";
    const correo = inputCorreo ? inputCorreo.value.trim() : "";
    const password = inputPassword ? inputPassword.value.trim() : "";
    const confirmarPassword = inputConfirmarPassword
      ? inputConfirmarPassword.value.trim()
      : "";

    limpiarValidaciones();

    if (nombre === "") {
      marcarInvalido(inputNombre);
      alertaValidacion("Nombre requerido", "Ingresa el nombre del usuario.");
      return false;
    }

    if (nombre.length > 100) {
      marcarInvalido(inputNombre);
      alertaValidacion(
        "Nombre inválido",
        "El nombre no puede superar 100 caracteres.",
      );
      return false;
    }

    if (apellido.length > 100) {
      marcarInvalido(inputApellido);
      alertaValidacion(
        "Apellido inválido",
        "El apellido no puede superar 100 caracteres.",
      );
      return false;
    }

    if (idRol === "") {
      marcarInvalido(selectRol);
      alertaValidacion("Rol requerido", "Selecciona un rol para el usuario.");
      return false;
    }

    if (usuario === "") {
      marcarInvalido(inputUsuario);
      alertaValidacion("Usuario requerido", "Ingresa el nombre de usuario.");
      return false;
    }

    if (usuario.length > 50) {
      marcarInvalido(inputUsuario);
      alertaValidacion(
        "Usuario inválido",
        "El usuario no puede superar 50 caracteres.",
      );
      return false;
    }

    if (correo !== "" && !validarCorreo(correo)) {
      marcarInvalido(inputCorreo);
      alertaValidacion(
        "Correo inválido",
        "Ingresa un correo válido o deja el campo vacío.",
      );
      return false;
    }

    if (idUsuario === "") {
      if (password === "") {
        marcarInvalido(inputPassword);
        alertaValidacion(
          "Contraseña requerida",
          "La contraseña es obligatoria al registrar un usuario.",
        );
        return false;
      }

      if (confirmarPassword === "") {
        marcarInvalido(inputConfirmarPassword);
        alertaValidacion(
          "Confirmación requerida",
          "Confirma la contraseña del usuario.",
        );
        return false;
      }
    }

    if (password !== "" || confirmarPassword !== "") {
      if (password.length < 6) {
        marcarInvalido(inputPassword);
        alertaValidacion(
          "Contraseña débil",
          "La contraseña debe tener mínimo 6 caracteres.",
        );
        return false;
      }

      if (password !== confirmarPassword) {
        marcarInvalido(inputConfirmarPassword);
        alertaValidacion(
          "Contraseñas distintas",
          "La contraseña y la confirmación no coinciden.",
        );
        return false;
      }
    }

    marcarValido(inputNombre);
    marcarValido(selectRol);
    marcarValido(inputUsuario);

    if (correo !== "") {
      marcarValido(inputCorreo);
    }

    if (password !== "") {
      marcarValido(inputPassword);
      marcarValido(inputConfirmarPassword);
    }

    return true;
  }

  function limpiarFormularioUsuario() {
    if (formUsuario) {
      formUsuario.reset();
    }

    if (inputIdUsuario) inputIdUsuario.value = "";
    if (selectEstado) selectEstado.value = "1";
    if (inputPassword) inputPassword.type = "password";
    if (inputConfirmarPassword) inputConfirmarPassword.type = "password";

    limpiarValidaciones();
    setLoading(false);
    refrescarIconos();
  }

  function limpiarValidaciones() {
    const campos = [
      inputNombre,
      inputApellido,
      selectRol,
      inputUsuario,
      inputCorreo,
      inputPassword,
      inputConfirmarPassword,
      selectEstado,
    ];

    campos.forEach(function (campo) {
      if (campo) {
        campo.classList.remove("is-invalid", "is-valid");
      }
    });
  }

  function setPasswordModoRegistro() {
    const marks = document.querySelectorAll(".password-required");

    marks.forEach(function (mark) {
      mark.classList.remove("d-none");
    });

    if (passwordHelp) {
      passwordHelp.textContent = "Obligatoria al registrar un usuario.";
    }

    if (inputPassword) {
      inputPassword.placeholder = "Contraseña segura";
    }

    if (inputConfirmarPassword) {
      inputConfirmarPassword.placeholder = "Repite la contraseña";
    }
  }

  function setPasswordModoEdicion() {
    const marks = document.querySelectorAll(".password-required");

    marks.forEach(function (mark) {
      mark.classList.add("d-none");
    });

    if (passwordHelp) {
      passwordHelp.textContent =
        "Deja la contraseña vacía si no deseas cambiarla.";
    }

    if (inputPassword) {
      inputPassword.placeholder = "Nueva contraseña opcional";
      inputPassword.value = "";
    }

    if (inputConfirmarPassword) {
      inputConfirmarPassword.placeholder = "Confirmar nueva contraseña";
      inputConfirmarPassword.value = "";
    }
  }

  function togglePassword() {
    if (!inputPassword || !inputConfirmarPassword || !btnTogglePassword) return;

    const visible = inputPassword.type === "text";
    const nuevoTipo = visible ? "password" : "text";

    inputPassword.type = nuevoTipo;
    inputConfirmarPassword.type = nuevoTipo;

    btnTogglePassword.innerHTML = visible
      ? `<i data-feather="eye"></i>`
      : `<i data-feather="eye-off"></i>`;

    refrescarIconos();
  }

  function pintarErrorTabla(mensaje) {
    if (!tbodyUsuarios) return;

    tbodyUsuarios.innerHTML = `
      <tr>
        <td colspan="6">
          <div class="empty-state">
            <div class="empty-state-icon">
              <i data-feather="alert-triangle"></i>
            </div>
            <h6 class="fw-bold mb-1">Error</h6>
            <div>${escapeHtml(mensaje)}</div>
          </div>
        </td>
      </tr>
    `;

    refrescarIconos();
  }

  function marcarInvalido(input) {
    if (input) {
      input.classList.add("is-invalid");
      input.classList.remove("is-valid");
      input.focus();
    }
  }

  function marcarValido(input) {
    if (input) {
      input.classList.add("is-valid");
      input.classList.remove("is-invalid");
    }
  }

  function validarCorreo(correo) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(correo);
  }

  function alertaValidacion(titulo, mensaje) {
    Swal.fire({
      icon: "warning",
      title: titulo,
      text: mensaje,
    });
  }

  function setLoading(state) {
    if (!btnGuardarUsuario || !guardarText || !guardarLoading) return;

    if (state) {
      btnGuardarUsuario.disabled = true;
      guardarText.classList.add("d-none");
      guardarLoading.classList.remove("d-none");
    } else {
      btnGuardarUsuario.disabled = false;
      guardarText.classList.remove("d-none");
      guardarLoading.classList.add("d-none");
    }
  }

  function refrescarIconos() {
    if (window.feather) {
      feather.replace();
    }
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
});
