document.addEventListener("DOMContentLoaded", function () {
  const tbodyErrores = document.getElementById("tbodyErrores");

  const filtroBusquedaError = document.getElementById("filtroBusquedaError");
  const filtroTipoError = document.getElementById("filtroTipoError");
  const filtroNivelError = document.getElementById("filtroNivelError");
  const filtroEstadoError = document.getElementById("filtroEstadoError");
  const filtroModuloError = document.getElementById("filtroModuloError");
  const fechaInicioError = document.getElementById("fechaInicioError");
  const fechaFinError = document.getElementById("fechaFinError");
  const filtroLimiteErrores = document.getElementById("filtroLimiteErrores");

  const btnFiltrarErrores = document.getElementById("btnFiltrarErrores");
  const btnLimpiarErrores = document.getElementById("btnLimpiarErrores");
  const btnActualizarErrores = document.getElementById("btnActualizarErrores");

  const modalDetalleErrorEl = document.getElementById("modalDetalleError");
  const modalDetalleError = modalDetalleErrorEl
    ? new bootstrap.Modal(modalDetalleErrorEl)
    : null;

  const btnGuardarRevisionError = document.getElementById(
    "btnGuardarRevisionError",
  );
  const btnRevisionText = btnGuardarRevisionError
    ? btnGuardarRevisionError.querySelector(".btn-text")
    : null;
  const btnRevisionLoading = btnGuardarRevisionError
    ? btnGuardarRevisionError.querySelector(".btn-loading")
    : null;

  cargarResumen();
  listarErrores();

  if (btnFiltrarErrores) {
    btnFiltrarErrores.addEventListener("click", function () {
      listarErrores();
    });
  }

  if (btnLimpiarErrores) {
    btnLimpiarErrores.addEventListener("click", function () {
      limpiarFiltros();
    });
  }

  if (btnActualizarErrores) {
    btnActualizarErrores.addEventListener("click", function () {
      cargarResumen();
      listarErrores();
    });
  }

  if (filtroBusquedaError) {
    filtroBusquedaError.addEventListener("keyup", function (e) {
      if (e.key === "Enter") {
        listarErrores();
      }
    });
  }

  if (filtroModuloError) {
    filtroModuloError.addEventListener("keyup", function (e) {
      if (e.key === "Enter") {
        listarErrores();
      }
    });
  }

  [
    filtroTipoError,
    filtroNivelError,
    filtroEstadoError,
    filtroLimiteErrores,
  ].forEach(function (el) {
    if (el) {
      el.addEventListener("change", function () {
        listarErrores();
      });
    }
  });

  if (tbodyErrores) {
    tbodyErrores.addEventListener("click", function (e) {
      const btnVer = e.target.closest(".btn-ver-error");
      const btnEstado = e.target.closest(".btn-estado-error");
      const btnEliminar = e.target.closest(".btn-eliminar-error");

      if (btnVer) {
        obtenerDetalleError(btnVer.dataset.id);
      }

      if (btnEstado) {
        cambiarEstadoRapido(btnEstado.dataset.id, btnEstado.dataset.estado);
      }

      if (btnEliminar) {
        eliminarError(btnEliminar.dataset.id);
      }
    });
  }

  if (btnGuardarRevisionError) {
    btnGuardarRevisionError.addEventListener("click", function () {
      guardarRevisionError();
    });
  }

  /* ============================================================
     RESUMEN / KPIS
  ============================================================ */
  function cargarResumen() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "errores/resumen", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        console.error(
          "Error HTTP resumen errores:",
          xhr.status,
          xhr.responseText,
        );
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          console.error(res.msg || "No se pudo cargar el resumen de errores.");
          return;
        }

        const resumen = res.resumen || {};

        setText("kpiErroresRegistrados", resumen.total_errores || 0);
        setText("kpiErroresPendientes", resumen.errores_pendientes || 0);
        setText("kpiErroresCriticos", resumen.errores_criticos || 0);
        setText("kpiErroresHoy", resumen.errores_hoy || 0);
      } catch (error) {
        console.error(
          "JSON inválido resumen errores:",
          error,
          xhr.responseText,
        );
      }
    };

    xhr.send();
  }

  /* ============================================================
     LISTAR
  ============================================================ */
  function listarErrores() {
    if (!tbodyErrores) return;

    if (!validarFechas()) return;

    tbodyErrores.innerHTML = emptyRow(
      9,
      "loader",
      "Cargando errores",
      "Espera un momento mientras se consulta la información.",
    );

    if (window.feather) feather.replace();

    const params = new URLSearchParams();

    if (filtroBusquedaError && filtroBusquedaError.value.trim() !== "") {
      params.append("busqueda", filtroBusquedaError.value.trim());
    }

    if (filtroTipoError && filtroTipoError.value !== "") {
      params.append("tipo_error", filtroTipoError.value);
    }

    if (filtroNivelError && filtroNivelError.value !== "") {
      params.append("nivel", filtroNivelError.value);
    }

    if (filtroEstadoError && filtroEstadoError.value !== "") {
      params.append("estado", filtroEstadoError.value);
    }

    if (filtroModuloError && filtroModuloError.value.trim() !== "") {
      params.append("modulo", filtroModuloError.value.trim());
    }

    if (fechaInicioError && fechaInicioError.value !== "") {
      params.append("fecha_inicio", fechaInicioError.value);
    }

    if (fechaFinError && fechaFinError.value !== "") {
      params.append("fecha_fin", fechaFinError.value);
    }

    if (filtroLimiteErrores && filtroLimiteErrores.value !== "") {
      params.append("limite", filtroLimiteErrores.value);
    }

    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "errores/listar?" + params.toString(), true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        tbodyErrores.innerHTML = emptyRow(
          9,
          "alert-triangle",
          "Error de conexión",
          "No fue posible conectar con el servidor.",
        );

        if (window.feather) feather.replace();
        console.error(
          "Error HTTP listar errores:",
          xhr.status,
          xhr.responseText,
        );
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          tbodyErrores.innerHTML = emptyRow(
            9,
            "alert-circle",
            "No fue posible cargar errores",
            res.msg || "Ocurrió un error al consultar la información.",
          );

          if (window.feather) feather.replace();
          return;
        }

        renderErrores(res.errores || []);
      } catch (error) {
        tbodyErrores.innerHTML = emptyRow(
          9,
          "alert-triangle",
          "Respuesta inválida",
          "El servidor no regresó un JSON válido.",
        );

        if (window.feather) feather.replace();
        console.error("JSON inválido listar errores:", error, xhr.responseText);
      }
    };

    xhr.send();
  }

  function renderErrores(errores) {
    if (!tbodyErrores) return;

    if (errores.length === 0) {
      tbodyErrores.innerHTML = emptyRow(
        9,
        "bug",
        "Sin errores encontrados",
        "No hay errores que coincidan con los filtros seleccionados.",
      );

      if (window.feather) feather.replace();
      return;
    }

    let html = "";

    errores.forEach(function (errorItem) {
      const idError = Number(errorItem.id_error || 0);
      const estado = String(errorItem.estado || "pendiente").toLowerCase();
      const siguienteEstado = estado === "resuelto" ? "pendiente" : "resuelto";

      html += `
        <tr>
          <td class="fw-bold">#${escapeHtml(idError)}</td>

          <td>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
              ${escapeHtml(errorItem.tipo_error || "SISTEMA")}
            </span>
          </td>

          <td>
            ${badgeNivel(errorItem.nivel)}
          </td>

          <td>
            <div class="fw-bold">${escapeHtml(errorItem.modulo || "Sistema")}</div>
            <div class="small text-secondary">
              ${escapeHtml(compactControllerMethod(errorItem.controlador, errorItem.metodo))}
            </div>
          </td>

          <td>
            <div class="fw-bold text-truncate" style="max-width: 360px;" title="${escapeHtml(errorItem.mensaje || "")}">
              ${escapeHtml(errorItem.mensaje || "Sin mensaje")}
            </div>
            <div class="small text-secondary text-truncate" style="max-width: 360px;">
              ${escapeHtml(errorItem.archivo || errorItem.url || "Sin archivo/URL")}
            </div>
          </td>

          <td>
            ${escapeHtml(errorItem.usuario_nombre || "Sistema")}
          </td>

          <td>
            ${badgeEstado(errorItem.estado)}
          </td>

          <td>
            ${formatoFechaHora(errorItem.creado_en)}
          </td>

          <td class="text-end">
            <div class="btn-group">
              <button type="button"
                      class="btn btn-sm btn-outline-primary btn-ver-error"
                      data-id="${idError}"
                      title="Ver detalle">
                <i data-feather="eye"></i>
              </button>

              <button type="button"
                      class="btn btn-sm ${estado === "resuelto" ? "btn-outline-warning" : "btn-outline-success"} btn-estado-error"
                      data-id="${idError}"
                      data-estado="${siguienteEstado}"
                      title="${estado === "resuelto" ? "Marcar pendiente" : "Marcar resuelto"}">
                <i data-feather="${estado === "resuelto" ? "clock" : "check-circle"}"></i>
              </button>

              <button type="button"
                      class="btn btn-sm btn-outline-danger btn-eliminar-error"
                      data-id="${idError}"
                      title="Eliminar registro">
                <i data-feather="trash-2"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    });

    tbodyErrores.innerHTML = html;

    if (window.feather) feather.replace();
  }

  /* ============================================================
     DETALLE
  ============================================================ */
  function obtenerDetalleError(idError) {
    idError = Number(idError || 0);

    if (idError <= 0) {
      Swal.fire({
        icon: "warning",
        title: "ID inválido",
        text: "No fue posible identificar el error.",
      });
      return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open(
      "GET",
      base_url + "errores/obtener/" + encodeURIComponent(idError),
      true,
    );

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No fue posible obtener el detalle del error.",
        });

        console.error(
          "Error HTTP obtener error:",
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
            title: "No fue posible obtener el error",
            text: res.msg || "Intenta nuevamente.",
          });

          return;
        }

        llenarDetalleError(res.error || {});

        if (modalDetalleError) {
          modalDetalleError.show();
        }

        if (window.feather) feather.replace();
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Respuesta inválida",
          text: "El servidor no regresó un JSON válido.",
        });

        console.error("JSON inválido obtener error:", error, xhr.responseText);
      }
    };

    xhr.send();
  }

  function llenarDetalleError(errorItem) {
    setValue("detalleIdError", errorItem.id_error || "");
    setHtml("detalleTipoError", badgeTipo(errorItem.tipo_error));
    setHtml("detalleNivelError", badgeNivel(errorItem.nivel));
    setHtml("detalleEstadoError", badgeEstado(errorItem.estado));
    setTextRaw("detalleFechaError", formatoFechaHora(errorItem.creado_en));

    setTextRaw("detalleModuloError", errorItem.modulo || "Sistema");
    setTextRaw(
      "detalleControladorError",
      compactControllerMethod(errorItem.controlador, errorItem.metodo),
    );
    setTextRaw("detalleUsuarioError", errorItem.usuario_nombre || "Sistema");

    setTextRaw("detalleMensajeError", errorItem.mensaje || "No disponible");
    setTextRaw("detalleArchivoError", errorItem.archivo || "No disponible");
    setTextRaw("detalleLineaError", errorItem.linea || "No disponible");
    setTextRaw("detalleUrlError", errorItem.url || "No disponible");

    setTextRaw(
      "detalleDatosError",
      formatearDatosAdicionales(errorItem.datos_adicionales),
    );

    const nota = document.getElementById("notaRevisionError");
    if (nota) nota.value = errorItem.nota_revision || "";

    const estadoRevision = document.getElementById("estadoRevisionError");
    if (estadoRevision) estadoRevision.value = errorItem.estado || "pendiente";
  }

  /* ============================================================
     GUARDAR REVISIÓN
  ============================================================ */
  function guardarRevisionError() {
    const idError = document.getElementById("detalleIdError")
      ? document.getElementById("detalleIdError").value
      : "";

    const estado = document.getElementById("estadoRevisionError")
      ? document.getElementById("estadoRevisionError").value
      : "";

    const nota = document.getElementById("notaRevisionError")
      ? document.getElementById("notaRevisionError").value.trim()
      : "";

    if (idError === "" || Number(idError) <= 0) {
      Swal.fire({
        icon: "warning",
        title: "Error no seleccionado",
        text: "No fue posible identificar el error.",
      });
      return;
    }

    if (!["pendiente", "revisado", "resuelto"].includes(estado)) {
      Swal.fire({
        icon: "warning",
        title: "Estado inválido",
        text: "Selecciona un estado válido.",
      });
      return;
    }

    const formData = new FormData();
    formData.append("id_error", idError);
    formData.append("estado", estado);
    formData.append("nota_revision", nota);

    setLoadingRevision(true);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", base_url + "errores/guardarRevision", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      setLoadingRevision(false);

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No fue posible guardar la revisión.",
        });

        console.error(
          "Error HTTP guardar revisión:",
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
            title: "No fue posible guardar",
            text: res.msg || "Intenta nuevamente.",
          });

          return;
        }

        Swal.fire({
          icon: "success",
          title: "Correcto",
          text: res.msg || "Revisión guardada correctamente.",
          timer: 1300,
          showConfirmButton: false,
        });

        if (modalDetalleError) {
          modalDetalleError.hide();
        }

        cargarResumen();
        listarErrores();
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Respuesta inválida",
          text: "El servidor no regresó un JSON válido.",
        });

        console.error(
          "JSON inválido guardar revisión:",
          error,
          xhr.responseText,
        );
      }
    };

    xhr.send(formData);
  }

  /* ============================================================
     CAMBIAR ESTADO RÁPIDO
  ============================================================ */
  function cambiarEstadoRapido(idError, estado) {
    idError = Number(idError || 0);

    if (idError <= 0) {
      Swal.fire({
        icon: "warning",
        title: "ID inválido",
        text: "No fue posible identificar el error.",
      });
      return;
    }

    if (!["pendiente", "revisado", "resuelto"].includes(estado)) {
      Swal.fire({
        icon: "warning",
        title: "Estado inválido",
        text: "No se pudo determinar el nuevo estado.",
      });
      return;
    }

    Swal.fire({
      icon: "question",
      title: "¿Cambiar estado?",
      text: `El error se marcará como: ${estado}.`,
      showCancelButton: true,
      confirmButtonText: "Sí, cambiar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: estado === "resuelto" ? "#198754" : "#d97706",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      const formData = new FormData();
      formData.append("id_error", idError);
      formData.append("estado", estado);

      const xhr = new XMLHttpRequest();
      xhr.open("POST", base_url + "errores/cambiarEstado", true);

      xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) return;

        if (xhr.status !== 200) {
          Swal.fire({
            icon: "error",
            title: "Error de conexión",
            text: "No fue posible cambiar el estado.",
          });

          console.error(
            "Error HTTP cambiar estado error:",
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
          listarErrores();
        } catch (error) {
          Swal.fire({
            icon: "error",
            title: "Respuesta inválida",
            text: "El servidor no regresó un JSON válido.",
          });

          console.error(
            "JSON inválido cambiar estado error:",
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
  function eliminarError(idError) {
    idError = Number(idError || 0);

    if (idError <= 0) {
      Swal.fire({
        icon: "warning",
        title: "ID inválido",
        text: "No fue posible identificar el error.",
      });
      return;
    }

    Swal.fire({
      icon: "warning",
      title: "¿Eliminar registro?",
      text: "Esta acción eliminará el registro del reporte de errores.",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#dc3545",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      const formData = new FormData();
      formData.append("id_error", idError);

      const xhr = new XMLHttpRequest();
      xhr.open("POST", base_url + "errores/eliminar", true);

      xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) return;

        if (xhr.status !== 200) {
          Swal.fire({
            icon: "error",
            title: "Error de conexión",
            text: "No fue posible eliminar el registro.",
          });

          console.error(
            "Error HTTP eliminar error:",
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
            text: res.msg || "Registro eliminado correctamente.",
            timer: 1200,
            showConfirmButton: false,
          });

          cargarResumen();
          listarErrores();
        } catch (error) {
          Swal.fire({
            icon: "error",
            title: "Respuesta inválida",
            text: "El servidor no regresó un JSON válido.",
          });

          console.error(
            "JSON inválido eliminar error:",
            error,
            xhr.responseText,
          );
        }
      };

      xhr.send(formData);
    });
  }

  /* ============================================================
     FILTROS
  ============================================================ */
  function limpiarFiltros() {
    if (filtroBusquedaError) filtroBusquedaError.value = "";
    if (filtroTipoError) filtroTipoError.value = "";
    if (filtroNivelError) filtroNivelError.value = "";
    if (filtroEstadoError) filtroEstadoError.value = "";
    if (filtroModuloError) filtroModuloError.value = "";
    if (fechaInicioError) fechaInicioError.value = "";
    if (fechaFinError) fechaFinError.value = "";
    if (filtroLimiteErrores) filtroLimiteErrores.value = "50";

    listarErrores();
  }

  function validarFechas() {
    if (!fechaInicioError || !fechaFinError) return true;

    if (
      fechaInicioError.value !== "" &&
      fechaFinError.value !== "" &&
      fechaInicioError.value > fechaFinError.value
    ) {
      Swal.fire({
        icon: "warning",
        title: "Rango de fechas inválido",
        text: "La fecha inicial no puede ser mayor que la fecha final.",
      });

      return false;
    }

    return true;
  }

  /* ============================================================
     LOADING
  ============================================================ */
  function setLoadingRevision(state) {
    if (!btnGuardarRevisionError || !btnRevisionText || !btnRevisionLoading)
      return;

    btnGuardarRevisionError.disabled = state;

    if (state) {
      btnRevisionText.classList.add("d-none");
      btnRevisionLoading.classList.remove("d-none");
    } else {
      btnRevisionText.classList.remove("d-none");
      btnRevisionLoading.classList.add("d-none");
    }
  }

  /* ============================================================
     HELPERS DOM
  ============================================================ */
  function setText(id, value) {
    const el = document.getElementById(id);

    if (el) {
      el.textContent = formatoNumero(value || 0);
    }
  }

  function setTextRaw(id, value) {
    const el = document.getElementById(id);

    if (el) {
      el.textContent =
        value === null || value === undefined || value === ""
          ? "No disponible"
          : String(value);
    }
  }

  function setValue(id, value) {
    const el = document.getElementById(id);

    if (el) {
      el.value = value;
    }
  }

  function setHtml(id, html) {
    const el = document.getElementById(id);

    if (el) {
      el.innerHTML = html;
    }
  }

  /* ============================================================
     HELPERS VISUALES
  ============================================================ */
  function badgeTipo(tipo) {
    return `
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
        ${escapeHtml(tipo || "SISTEMA")}
      </span>
    `;
  }

  function badgeNivel(nivel) {
    nivel = String(nivel || "error").toLowerCase();

    if (nivel === "info") {
      return `<span class="badge badge-nivel-info rounded-pill">Info</span>`;
    }

    if (nivel === "warning") {
      return `<span class="badge badge-nivel-warning rounded-pill">Warning</span>`;
    }

    if (nivel === "critico") {
      return `<span class="badge badge-nivel-critico rounded-pill">Crítico</span>`;
    }

    return `<span class="badge badge-nivel-error rounded-pill">Error</span>`;
  }

  function badgeEstado(estado) {
    estado = String(estado || "pendiente").toLowerCase();

    if (estado === "resuelto") {
      return `
        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
          Resuelto
        </span>
      `;
    }

    if (estado === "revisado") {
      return `
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
          Revisado
        </span>
      `;
    }

    return `
      <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">
        Pendiente
      </span>
    `;
  }

  function compactControllerMethod(controlador, metodo) {
    const c = controlador || "";
    const m = metodo || "";

    if (c === "" && m === "") {
      return "No disponible";
    }

    if (c !== "" && m !== "") {
      return `${c}/${m}`;
    }

    return c || m;
  }

  function formatearDatosAdicionales(datos) {
    if (!datos) return "No disponible";

    try {
      const parsed = JSON.parse(datos);
      return JSON.stringify(parsed, null, 2);
    } catch (e) {
      return String(datos);
    }
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
});
