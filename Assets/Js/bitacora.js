document.addEventListener("DOMContentLoaded", function () {
  const tbodyBitacora = document.getElementById("tbodyBitacora");

  const filtroUsuario = document.getElementById("filtroUsuario");
  const filtroModulo = document.getElementById("filtroModulo");
  const filtroAccion = document.getElementById("filtroAccion");
  const buscarBitacora = document.getElementById("buscarBitacora");
  const fechaInicio = document.getElementById("fechaInicioBitacora");
  const fechaFin = document.getElementById("fechaFinBitacora");
  const limiteBitacora = document.getElementById("limiteBitacora");

  const btnLimpiar = document.getElementById("btnLimpiarFiltrosBitacora");
  const btnRecargar = document.getElementById("btnRecargarBitacora");

  const kpiEventos = document.getElementById("kpiEventos");
  const kpiLogins = document.getElementById("kpiLogins");
  const kpiCambios = document.getElementById("kpiCambios");
  const kpiFallidos = document.getElementById("kpiFallidos");

  let timerBusqueda = null;

  cargarFiltros();
  cargarBitacora();

  if (buscarBitacora) {
    buscarBitacora.addEventListener("input", function () {
      clearTimeout(timerBusqueda);
      timerBusqueda = setTimeout(cargarBitacora, 350);
    });
  }

  [
    filtroUsuario,
    filtroModulo,
    filtroAccion,
    fechaInicio,
    fechaFin,
    limiteBitacora,
  ].forEach(function (campo) {
    if (campo) {
      campo.addEventListener("change", cargarBitacora);
    }
  });

  if (btnRecargar) {
    btnRecargar.addEventListener("click", function () {
      cargarBitacora();
    });
  }

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", function () {
      limpiarFiltros();
      cargarBitacora();
    });
  }

  function cargarFiltros() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "bitacora/filtros", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        console.error("No se pudieron cargar los filtros de bitácora.");
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          console.error(res.msg || "No se pudieron cargar los filtros.");
          return;
        }

        llenarUsuarios(res.data.usuarios || []);
        llenarModulos(res.data.modulos || []);
        llenarAcciones(res.data.acciones || []);
      } catch (error) {
        console.error(error, xhr.responseText);
      }
    };

    xhr.send();
  }

  function cargarBitacora() {
    if (!tbodyBitacora) return;

    tbodyBitacora.innerHTML = `
      <tr>
        <td colspan="7">
          <div class="empty-state">
            <div class="empty-state-icon">
              <i data-feather="loader"></i>
            </div>
            <h6 class="fw-bold mb-1">Cargando bitácora...</h6>
            <div>Espera un momento.</div>
          </div>
        </td>
      </tr>
    `;

    refrescarIconos();

    const params = new URLSearchParams();

    params.append("buscar", buscarBitacora ? buscarBitacora.value.trim() : "");
    params.append(
      "id_usuario",
      filtroUsuario ? filtroUsuario.value.trim() : "",
    );
    params.append("modulo", filtroModulo ? filtroModulo.value.trim() : "");
    params.append("accion", filtroAccion ? filtroAccion.value.trim() : "");
    params.append("fecha_inicio", fechaInicio ? fechaInicio.value.trim() : "");
    params.append("fecha_fin", fechaFin ? fechaFin.value.trim() : "");
    params.append(
      "limite",
      limiteBitacora ? limiteBitacora.value.trim() : "50",
    );

    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "bitacora/listar?" + params.toString(), true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        pintarErrorTabla("No se pudo conectar con el servidor.");
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          pintarErrorTabla(res.msg || "No fue posible cargar la bitácora.");
          pintarKpis({});
          return;
        }

        pintarKpis(res.resumen || {});
        pintarBitacora(res.data || []);
      } catch (error) {
        console.error(error, xhr.responseText);
        pintarErrorTabla("La respuesta del servidor no es válida.");
        pintarKpis({});
      }
    };

    xhr.send();
  }

  function llenarUsuarios(usuarios) {
    if (!filtroUsuario) return;

    const valorActual = filtroUsuario.value;

    let html = `<option value="">Todos los usuarios</option>`;

    usuarios.forEach(function (usuario) {
      const nombre = [usuario.nombre || "", usuario.apellido || ""]
        .join(" ")
        .trim();

      const etiqueta = nombre
        ? `${nombre} (${usuario.usuario || "sin usuario"})`
        : usuario.usuario || usuario.correo || `Usuario #${usuario.id_usuario}`;

      html += `
        <option value="${escapeHtml(usuario.id_usuario)}">
          ${escapeHtml(etiqueta)}
        </option>
      `;
    });

    filtroUsuario.innerHTML = html;
    filtroUsuario.value = valorActual;
  }

  function llenarModulos(modulos) {
    if (!filtroModulo) return;

    const valorActual = filtroModulo.value;

    let html = `<option value="">Todos</option>`;

    modulos.forEach(function (row) {
      if (!row.modulo) return;

      html += `
        <option value="${escapeHtml(row.modulo)}">
          ${escapeHtml(row.modulo)}
        </option>
      `;
    });

    filtroModulo.innerHTML = html;
    filtroModulo.value = valorActual;
  }

  function llenarAcciones(acciones) {
    if (!filtroAccion) return;

    const valorActual = filtroAccion.value;

    let html = `<option value="">Todas</option>`;

    acciones.forEach(function (row) {
      if (!row.accion) return;

      html += `
        <option value="${escapeHtml(row.accion)}">
          ${escapeHtml(formatearAccion(row.accion))}
        </option>
      `;
    });

    filtroAccion.innerHTML = html;
    filtroAccion.value = valorActual;
  }

  function pintarBitacora(eventos) {
    if (!tbodyBitacora) return;

    if (!eventos.length) {
      tbodyBitacora.innerHTML = `
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <div class="empty-state-icon">
                <i data-feather="activity"></i>
              </div>
              <h6 class="fw-bold mb-1">Sin eventos encontrados</h6>
              <div>No hay registros para los filtros seleccionados.</div>
            </div>
          </td>
        </tr>
      `;

      refrescarIconos();
      return;
    }

    let html = "";

    eventos.forEach(function (evento) {
      const usuario = obtenerNombreUsuario(evento);

      html += `
        <tr>
          <td>${formatearFechaHora(evento.creado_en)}</td>
          <td>
            <div class="${usuario === "Sistema" ? "audit-user-muted" : "audit-user"}">
              ${escapeHtml(usuario)}
            </div>
            ${
              evento.correo
                ? `<div class="small text-secondary">${escapeHtml(evento.correo)}</div>`
                : ""
            }
          </td>
          <td>${escapeHtml(evento.modulo || "No aplica")}</td>
          <td>${badgeAccion(evento.accion || "")}</td>
          <td>${escapeHtml(evento.entidad || "No aplica")}</td>
          <td>${escapeHtml(evento.entidad_id || "—")}</td>
          <td>
            <div class="audit-detail">
              ${escapeHtml(evento.detalle || "Sin detalle")}
            </div>
          </td>
        </tr>
      `;
    });

    tbodyBitacora.innerHTML = html;
    refrescarIconos();
  }

  function pintarKpis(resumen) {
    setText(kpiEventos, resumen.total_eventos || 0);
    setText(kpiLogins, resumen.total_logins || 0);
    setText(kpiCambios, resumen.total_cambios || 0);
    setText(kpiFallidos, resumen.total_fallidos || 0);
  }

  function limpiarFiltros() {
    if (filtroUsuario) filtroUsuario.value = "";
    if (filtroModulo) filtroModulo.value = "";
    if (filtroAccion) filtroAccion.value = "";
    if (buscarBitacora) buscarBitacora.value = "";
    if (fechaInicio) fechaInicio.value = "";
    if (fechaFin) fechaFin.value = "";
    if (limiteBitacora) limiteBitacora.value = "50";
  }

  function badgeAccion(accion) {
    const accionUpper = String(accion || "").toUpperCase();

    let clase = "badge-accion-neutral";

    if (accionUpper.includes("LOGIN_EXITOSO")) {
      clase = "badge-accion-login";
    } else if (
      accionUpper.includes("REGISTRAR") ||
      accionUpper.includes("ACTUALIZAR") ||
      accionUpper.includes("ACTIVAR")
    ) {
      clase = "badge-accion-success";
    } else if (
      accionUpper.includes("CANCELAR") ||
      accionUpper.includes("DESACTIVAR")
    ) {
      clase = "badge-accion-warning";
    } else if (
      accionUpper.includes("FALLIDO") ||
      accionUpper.includes("BLOQUEADO") ||
      accionUpper.includes("ERROR")
    ) {
      clase = "badge-accion-danger";
    }

    return `
      <span class="badge-accion ${clase}">
        ${escapeHtml(formatearAccion(accionUpper || "SIN_ACCION"))}
      </span>
    `;
  }

  function formatearAccion(accion) {
    return String(accion || "")
      .replaceAll("_", " ")
      .toLowerCase()
      .replace(/\b\w/g, function (char) {
        return char.toUpperCase();
      });
  }

  function obtenerNombreUsuario(evento) {
    const nombre = String(evento.nombre_usuario || "").trim();

    if (nombre !== "" && nombre.toLowerCase() !== "sistema") {
      return nombre;
    }

    if (evento.usuario) {
      return evento.usuario;
    }

    return "Sistema";
  }

  function formatearFechaHora(fecha) {
    if (!fecha) return "Sin fecha";

    const normalizada = String(fecha).replace(" ", "T");
    const date = new Date(normalizada);

    if (Number.isNaN(date.getTime())) {
      return fecha;
    }

    return date.toLocaleString("es-MX", {
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  function pintarErrorTabla(mensaje) {
    if (!tbodyBitacora) return;

    tbodyBitacora.innerHTML = `
      <tr>
        <td colspan="7">
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

  function setText(element, value) {
    if (element) {
      element.textContent = value;
    }
  }

  function refrescarIconos() {
    if (window.feather) {
      feather.replace();
    }
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
