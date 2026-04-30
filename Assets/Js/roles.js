document.addEventListener("DOMContentLoaded", function () {
  const tbodyRoles = document.getElementById("tbodyRoles");

  const filtroRol = document.getElementById("filtroRol");
  const filtroEstadoRol = document.getElementById("filtroEstadoRol");
  const btnFiltrarRoles = document.getElementById("btnFiltrarRoles");

  const btnNuevoRol = document.getElementById("btnNuevoRol");
  const modalRolEl = document.getElementById("modalRol");
  const modalRol = modalRolEl ? new bootstrap.Modal(modalRolEl) : null;

  const formRol = document.getElementById("formRol");
  const modalRolLabel = document.getElementById("modalRolLabel");

  const inputIdRol = document.getElementById("id_rol");
  const inputNombre = document.getElementById("nombre");
  const inputDescripcion = document.getElementById("descripcion");
  const inputEstado = document.getElementById("estado");

  const btnGuardarRol = document.getElementById("btnGuardarRol");
  const btnText = btnGuardarRol
    ? btnGuardarRol.querySelector(".btn-text")
    : null;
  const btnLoading = btnGuardarRol
    ? btnGuardarRol.querySelector(".btn-loading")
    : null;

  const btnMarcarTodosPermisos = document.getElementById(
    "btnMarcarTodosPermisos",
  );

  cargarResumen();
  listarRoles();

  if (btnFiltrarRoles) {
    btnFiltrarRoles.addEventListener("click", function () {
      listarRoles();
    });
  }

  if (filtroRol) {
    filtroRol.addEventListener("keyup", function (e) {
      if (e.key === "Enter") {
        listarRoles();
      }
    });
  }

  if (filtroEstadoRol) {
    filtroEstadoRol.addEventListener("change", function () {
      listarRoles();
    });
  }

  if (btnNuevoRol) {
    btnNuevoRol.addEventListener("click", function () {
      abrirModalNuevoRol();
    });
  }

  if (formRol) {
    formRol.addEventListener("submit", function (e) {
      e.preventDefault();
      guardarRol();
    });
  }

  if (btnMarcarTodosPermisos) {
    btnMarcarTodosPermisos.addEventListener("click", function () {
      togglePermisos();
    });
  }

  if (tbodyRoles) {
    tbodyRoles.addEventListener("click", function (e) {
      const btnEditar = e.target.closest(".btn-editar-rol");
      const btnEstado = e.target.closest(".btn-estado-rol");
      const btnEliminar = e.target.closest(".btn-eliminar-rol");

      if (btnEditar) {
        editarRol(btnEditar.dataset.id);
      }

      if (btnEstado) {
        cambiarEstadoRol(btnEstado.dataset.id, btnEstado.dataset.estado);
      }

      if (btnEliminar) {
        eliminarRol(btnEliminar.dataset.id);
      }
    });
  }

  /* ============================================================
     RESUMEN / KPIS
  ============================================================ */
  function cargarResumen() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "roles/resumen", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        console.error(
          "Error HTTP resumen roles:",
          xhr.status,
          xhr.responseText,
        );
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          console.error(res.msg || "No se pudo cargar el resumen.");
          return;
        }

        const resumen = res.resumen || {};

        setText("kpiRolesRegistrados", resumen.total_roles || 0);
        setText("kpiRolesActivos", resumen.roles_activos || 0);
        setText("kpiRolesInactivos", resumen.roles_inactivos || 0);
        setText("kpiUsuariosAsignados", resumen.usuarios_asignados || 0);
      } catch (error) {
        console.error("JSON inválido resumen roles:", error, xhr.responseText);
      }
    };

    xhr.send();
  }

  /* ============================================================
     LISTAR ROLES
  ============================================================ */
  function listarRoles() {
    if (!tbodyRoles) return;

    tbodyRoles.innerHTML = emptyRow(
      7,
      "loader",
      "Cargando roles",
      "Espera un momento mientras se consulta la información.",
    );

    if (window.feather) feather.replace();

    const params = new URLSearchParams();

    if (filtroRol && filtroRol.value.trim() !== "") {
      params.append("busqueda", filtroRol.value.trim());
    }

    if (filtroEstadoRol && filtroEstadoRol.value !== "") {
      params.append("estado", filtroEstadoRol.value);
    }

    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "roles/listar?" + params.toString(), true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        tbodyRoles.innerHTML = emptyRow(
          7,
          "alert-triangle",
          "Error de conexión",
          "No fue posible conectar con el servidor.",
        );

        if (window.feather) feather.replace();
        console.error("Error HTTP listar roles:", xhr.status, xhr.responseText);
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          tbodyRoles.innerHTML = emptyRow(
            7,
            "alert-circle",
            "No fue posible cargar roles",
            res.msg || "Ocurrió un error al consultar la información.",
          );

          if (window.feather) feather.replace();
          return;
        }

        renderRoles(res.roles || []);
      } catch (error) {
        tbodyRoles.innerHTML = emptyRow(
          7,
          "alert-triangle",
          "Respuesta inválida",
          "El servidor no regresó un JSON válido.",
        );

        if (window.feather) feather.replace();
        console.error("JSON inválido listar roles:", error, xhr.responseText);
      }
    };

    xhr.send();
  }

  function renderRoles(roles) {
    if (!tbodyRoles) return;

    if (roles.length === 0) {
      tbodyRoles.innerHTML = emptyRow(
        7,
        "shield",
        "Sin roles encontrados",
        "No hay roles que coincidan con los filtros seleccionados.",
      );

      if (window.feather) feather.replace();
      return;
    }

    let html = "";

    roles.forEach(function (rol) {
      const estado = Number(rol.estado || 0);
      const idRol = Number(rol.id_rol || 0);

      html += `
        <tr>
          <td class="fw-bold">#${escapeHtml(idRol)}</td>

          <td>
            <div class="fw-bold">${escapeHtml(rol.nombre || "")}</div>
            <div class="small text-secondary">Perfil de acceso</div>
          </td>

          <td>
            ${escapeHtml(rol.descripcion || "Sin descripción")}
          </td>

          <td>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
              ${formatoNumero(rol.total_usuarios || 0)} usuario(s)
            </span>
          </td>

          <td>
            ${badgeEstado(estado)}
          </td>

          <td>
            ${formatoFechaHora(rol.creado_en)}
          </td>

          <td class="text-end">
            <div class="btn-group">
              <button type="button"
                      class="btn btn-sm btn-outline-primary btn-editar-rol"
                      data-id="${idRol}"
                      title="Editar rol">
                <i data-feather="edit-2"></i>
              </button>

              <button type="button"
                      class="btn btn-sm ${estado === 1 ? "btn-outline-warning" : "btn-outline-success"} btn-estado-rol"
                      data-id="${idRol}"
                      data-estado="${estado === 1 ? 0 : 1}"
                      title="${estado === 1 ? "Desactivar" : "Activar"} rol">
                <i data-feather="${estado === 1 ? "slash" : "check-circle"}"></i>
              </button>

              <button type="button"
                      class="btn btn-sm btn-outline-danger btn-eliminar-rol"
                      data-id="${idRol}"
                      title="Eliminar rol">
                <i data-feather="trash-2"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    });

    tbodyRoles.innerHTML = html;

    if (window.feather) feather.replace();
  }

  /* ============================================================
     MODAL NUEVO
  ============================================================ */
  function abrirModalNuevoRol() {
    limpiarFormularioRol();

    if (modalRolLabel) {
      modalRolLabel.textContent = "Nuevo rol";
    }

    if (inputEstado) {
      inputEstado.value = "1";
    }

    if (modalRol) {
      modalRol.show();
    }

    setTimeout(function () {
      if (inputNombre) inputNombre.focus();
    }, 300);
  }

  function limpiarFormularioRol() {
    if (formRol) formRol.reset();

    if (inputIdRol) inputIdRol.value = "";
    limpiarPermisos();

    setLoadingGuardar(false);
  }

  function limpiarPermisos() {
    document.querySelectorAll(".permiso-check").forEach(function (check) {
      check.checked = false;
    });
  }

  /* ============================================================
     GUARDAR / ACTUALIZAR
  ============================================================ */
  function guardarRol() {
    if (!formRol) return;

    const nombre = inputNombre ? inputNombre.value.trim() : "";

    if (nombre === "") {
      Swal.fire({
        icon: "warning",
        title: "Nombre requerido",
        text: "Ingresa el nombre del rol.",
      });

      if (inputNombre) inputNombre.focus();
      return;
    }

    const idRol = inputIdRol ? inputIdRol.value.trim() : "";
    const url = idRol === "" ? "roles/registrar" : "roles/actualizar";

    const formData = new FormData(formRol);

    setLoadingGuardar(true);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", base_url + url, true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      setLoadingGuardar(false);

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No fue posible conectar con el servidor.",
        });

        console.error("Error HTTP guardar rol:", xhr.status, xhr.responseText);
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          Swal.fire({
            icon: "warning",
            title: "No fue posible guardar",
            text: res.msg || "Revisa la información e intenta nuevamente.",
          });

          console.error("Respuesta guardar rol:", res);
          return;
        }

        Swal.fire({
          icon: "success",
          title: "Correcto",
          text: res.msg || "Rol guardado correctamente.",
          timer: 1400,
          showConfirmButton: false,
        });

        if (modalRol) {
          modalRol.hide();
        }

        limpiarFormularioRol();
        cargarResumen();
        listarRoles();
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Respuesta inválida",
          text: "El servidor no regresó un JSON válido.",
        });

        console.error("JSON inválido guardar rol:", error, xhr.responseText);
      }
    };

    xhr.send(formData);
  }

  /* ============================================================
     EDITAR
  ============================================================ */
  function editarRol(idRol) {
    idRol = Number(idRol || 0);

    if (idRol <= 0) {
      Swal.fire({
        icon: "warning",
        title: "ID inválido",
        text: "No fue posible identificar el rol.",
      });
      return;
    }

    limpiarFormularioRol();

    const xhr = new XMLHttpRequest();
    xhr.open(
      "GET",
      base_url + "roles/obtener/" + encodeURIComponent(idRol),
      true,
    );

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No fue posible obtener el rol.",
        });

        console.error("Error HTTP obtener rol:", xhr.status, xhr.responseText);
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          Swal.fire({
            icon: "warning",
            title: "No fue posible obtener el rol",
            text: res.msg || "Intenta nuevamente.",
          });

          return;
        }

        llenarFormularioRol(res.rol || {});

        if (modalRolLabel) {
          modalRolLabel.textContent = "Editar rol";
        }

        if (modalRol) {
          modalRol.show();
        }

        setTimeout(function () {
          if (inputNombre) inputNombre.focus();
        }, 300);
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Respuesta inválida",
          text: "El servidor no regresó un JSON válido.",
        });

        console.error("JSON inválido obtener rol:", error, xhr.responseText);
      }
    };

    xhr.send();
  }

  function llenarFormularioRol(rol) {
    if (inputIdRol) inputIdRol.value = rol.id_rol || "";
    if (inputNombre) inputNombre.value = rol.nombre || "";
    if (inputDescripcion) inputDescripcion.value = rol.descripcion || "";
    if (inputEstado) inputEstado.value = String(rol.estado ?? "1");

    limpiarPermisos();

    const permisos = rol.permisos || {};

    Object.keys(permisos).forEach(function (modulo) {
      const acciones = permisos[modulo] || {};

      marcarPermiso(modulo, "ver", acciones.ver);
      marcarPermiso(modulo, "crear", acciones.crear);
      marcarPermiso(modulo, "editar", acciones.editar);
      marcarPermiso(modulo, "eliminar", acciones.eliminar);
    });
  }

  function marcarPermiso(modulo, accion, valor) {
    const selector = `.permiso-check[name="permisos[${cssEscape(modulo)}][${cssEscape(accion)}]"]`;
    const check = document.querySelector(selector);

    if (check) {
      check.checked = Number(valor || 0) === 1;
    }
  }

  /* ============================================================
     CAMBIAR ESTADO
  ============================================================ */
  function cambiarEstadoRol(idRol, nuevoEstado) {
    idRol = Number(idRol || 0);
    nuevoEstado = Number(nuevoEstado);

    if (idRol <= 0) {
      Swal.fire({
        icon: "warning",
        title: "ID inválido",
        text: "No fue posible identificar el rol.",
      });
      return;
    }

    const textoAccion = nuevoEstado === 1 ? "activar" : "desactivar";

    Swal.fire({
      icon: "question",
      title: `¿Deseas ${textoAccion} este rol?`,
      text: "El cambio afectará la disponibilidad del rol para los usuarios.",
      showCancelButton: true,
      confirmButtonText: `Sí, ${textoAccion}`,
      cancelButtonText: "Cancelar",
      confirmButtonColor: nuevoEstado === 1 ? "#198754" : "#d97706",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      const formData = new FormData();
      formData.append("id_rol", idRol);
      formData.append("estado", nuevoEstado);

      const xhr = new XMLHttpRequest();
      xhr.open("POST", base_url + "roles/cambiarEstado", true);

      xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) return;

        if (xhr.status !== 200) {
          Swal.fire({
            icon: "error",
            title: "Error de conexión",
            text: "No fue posible conectar con el servidor.",
          });

          console.error(
            "Error HTTP cambiar estado:",
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
              title: "No fue posible cambiar el estado",
              text: res.msg || "Intenta nuevamente.",
            });

            return;
          }

          Swal.fire({
            icon: "success",
            title: "Correcto",
            text: res.msg || "Estado actualizado correctamente.",
            timer: 1200,
            showConfirmButton: false,
          });

          cargarResumen();
          listarRoles();
        } catch (error) {
          Swal.fire({
            icon: "error",
            title: "Respuesta inválida",
            text: "El servidor no regresó un JSON válido.",
          });

          console.error(
            "JSON inválido cambiar estado:",
            error,
            xhr.responseText,
          );
        }
      };

      xhr.send(formData);
    });
  }

  /* ============================================================
     ELIMINAR
  ============================================================ */
  function eliminarRol(idRol) {
    idRol = Number(idRol || 0);

    if (idRol <= 0) {
      Swal.fire({
        icon: "warning",
        title: "ID inválido",
        text: "No fue posible identificar el rol.",
      });
      return;
    }

    Swal.fire({
      icon: "warning",
      title: "¿Eliminar rol?",
      text: "Esta acción no se podrá deshacer. Solo se permitirá si el rol no tiene usuarios asignados.",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#dc3545",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      const formData = new FormData();
      formData.append("id_rol", idRol);

      const xhr = new XMLHttpRequest();
      xhr.open("POST", base_url + "roles/eliminar", true);

      xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) return;

        if (xhr.status !== 200) {
          Swal.fire({
            icon: "error",
            title: "Error de conexión",
            text: "No fue posible conectar con el servidor.",
          });

          console.error(
            "Error HTTP eliminar rol:",
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
              title: "No fue posible eliminar",
              text: res.msg || "Intenta nuevamente.",
            });

            return;
          }

          Swal.fire({
            icon: "success",
            title: "Eliminado",
            text: res.msg || "Rol eliminado correctamente.",
            timer: 1200,
            showConfirmButton: false,
          });

          cargarResumen();
          listarRoles();
        } catch (error) {
          Swal.fire({
            icon: "error",
            title: "Respuesta inválida",
            text: "El servidor no regresó un JSON válido.",
          });

          console.error("JSON inválido eliminar rol:", error, xhr.responseText);
        }
      };

      xhr.send(formData);
    });
  }

  /* ============================================================
     PERMISOS
  ============================================================ */
  function togglePermisos() {
    const checks = document.querySelectorAll(".permiso-check");

    if (checks.length === 0) return;

    const todosMarcados = Array.from(checks).every(function (check) {
      return check.checked;
    });

    checks.forEach(function (check) {
      check.checked = !todosMarcados;
    });

    if (btnMarcarTodosPermisos) {
      btnMarcarTodosPermisos.innerHTML = !todosMarcados
        ? `<i data-feather="x-square" class="me-1"></i> Quitar todos`
        : `<i data-feather="check-square" class="me-1"></i> Marcar todos`;

      if (window.feather) feather.replace();
    }
  }

  /* ============================================================
     LOADING
  ============================================================ */
  function setLoadingGuardar(state) {
    if (!btnGuardarRol || !btnText || !btnLoading) return;

    btnGuardarRol.disabled = state;

    if (state) {
      btnText.classList.add("d-none");
      btnLoading.classList.remove("d-none");
    } else {
      btnText.classList.remove("d-none");
      btnLoading.classList.add("d-none");
    }
  }

  /* ============================================================
     HELPERS
  ============================================================ */
  function setText(id, value) {
    const el = document.getElementById(id);

    if (el) {
      el.textContent = formatoNumero(value || 0);
    }
  }

  function badgeEstado(estado) {
    estado = Number(estado || 0);

    if (estado === 1) {
      return `
        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
          Activo
        </span>
      `;
    }

    return `
      <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
        Inactivo
      </span>
    `;
  }

  function formatoNumero(value) {
    return Number(value || 0).toLocaleString("en-US", {
      maximumFractionDigits: 0,
    });
  }

  function formatoFechaHora(fecha) {
    if (!fecha) return "No disponible";

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

  function emptyRow(colspan, icon, title, text) {
    return `
      <tr>
        <td colspan="${colspan}">
          <div class="empty-state">
            <div class="empty-state-icon">
              <i data-feather="${icon}"></i>
            </div>
            <h6 class="fw-bold mb-1">${title}</h6>
            <div>${text}</div>
          </div>
        </td>
      </tr>
    `;
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function cssEscape(value) {
    if (window.CSS && typeof window.CSS.escape === "function") {
      return window.CSS.escape(value);
    }

    return String(value).replace(/"/g, '\\"').replace(/'/g, "\\'");
  }
});
