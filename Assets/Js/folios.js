document.addEventListener("DOMContentLoaded", function () {
  const tbodyFolios = document.getElementById("tbodyFolios");

  const filtroSerie = document.getElementById("filtroSerie");
  const filtroEstadoSerie = document.getElementById("filtroEstadoSerie");
  const btnFiltrarFolios = document.getElementById("btnFiltrarFolios");

  const btnNuevoFolio = document.getElementById("btnNuevoFolio");
  const modalFolioEl = document.getElementById("modalFolio");
  const modalFolio = modalFolioEl ? new bootstrap.Modal(modalFolioEl) : null;

  const formFolio = document.getElementById("formFolio");
  const modalFolioLabel = document.getElementById("modalFolioLabel");

  const inputIdFolio = document.getElementById("id_folio");
  const inputSerie = document.getElementById("serie");
  const inputUltimoNumero = document.getElementById("ultimo_numero");
  const inputActivo = document.getElementById("activo");
  const previewSiguienteFolio = document.getElementById(
    "previewSiguienteFolio",
  );

  const btnGuardarFolio = document.getElementById("btnGuardarFolio");
  const btnText = btnGuardarFolio
    ? btnGuardarFolio.querySelector(".btn-text")
    : null;
  const btnLoading = btnGuardarFolio
    ? btnGuardarFolio.querySelector(".btn-loading")
    : null;

  cargarResumen();
  listarFolios();

  if (btnFiltrarFolios) {
    btnFiltrarFolios.addEventListener("click", function () {
      listarFolios();
    });
  }

  if (filtroSerie) {
    filtroSerie.addEventListener("keyup", function (e) {
      this.value = this.value.toUpperCase();

      if (e.key === "Enter") {
        listarFolios();
      }
    });
  }

  if (filtroEstadoSerie) {
    filtroEstadoSerie.addEventListener("change", function () {
      listarFolios();
    });
  }

  if (btnNuevoFolio) {
    btnNuevoFolio.addEventListener("click", function () {
      abrirModalNuevoFolio();
    });
  }

  if (formFolio) {
    formFolio.addEventListener("submit", function (e) {
      e.preventDefault();
      guardarFolio();
    });
  }

  if (inputSerie) {
    inputSerie.addEventListener("input", function () {
      this.value = limpiarSerie(this.value);
      actualizarPreviewLocal();
    });
  }

  if (inputUltimoNumero) {
    inputUltimoNumero.addEventListener("input", function () {
      if (this.value === "") {
        actualizarPreviewLocal();
        return;
      }

      this.value = this.value.replace(/\D/g, "");
      actualizarPreviewLocal();
    });
  }

  if (tbodyFolios) {
    tbodyFolios.addEventListener("click", function (e) {
      const btnEditar = e.target.closest(".btn-editar-folio");
      const btnEstado = e.target.closest(".btn-estado-folio");
      const btnEliminar = e.target.closest(".btn-eliminar-folio");

      if (btnEditar) {
        editarFolio(btnEditar.dataset.id);
      }

      if (btnEstado) {
        cambiarEstadoFolio(btnEstado.dataset.id, btnEstado.dataset.activo);
      }

      if (btnEliminar) {
        eliminarFolio(btnEliminar.dataset.id);
      }
    });
  }

  /* ============================================================
     RESUMEN / KPIS
  ============================================================ */
  function cargarResumen() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "folios/resumen", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        console.error(
          "Error HTTP resumen folios:",
          xhr.status,
          xhr.responseText,
        );
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          console.error(res.msg || "No se pudo cargar el resumen de folios.");
          return;
        }

        const resumen = res.resumen || {};

        setText("kpiSeriesRegistradas", resumen.total_series || 0);
        setText("kpiSeriesActivas", resumen.series_activas || 0);
        setText("kpiSeriesInactivas", resumen.series_inactivas || 0);
        setText("kpiUltimoFolioGlobal", resumen.ultimo_folio_global || 0);
      } catch (error) {
        console.error("JSON inválido resumen folios:", error, xhr.responseText);
      }
    };

    xhr.send();
  }

  /* ============================================================
     LISTAR
  ============================================================ */
  function listarFolios() {
    if (!tbodyFolios) return;

    tbodyFolios.innerHTML = emptyRow(
      8,
      "loader",
      "Cargando series",
      "Espera un momento mientras se consulta la información.",
    );

    if (window.feather) feather.replace();

    const params = new URLSearchParams();

    if (filtroSerie && filtroSerie.value.trim() !== "") {
      params.append("busqueda", filtroSerie.value.trim().toUpperCase());
    }

    if (filtroEstadoSerie && filtroEstadoSerie.value !== "") {
      params.append("estado", filtroEstadoSerie.value);
    }

    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "folios/listar?" + params.toString(), true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        tbodyFolios.innerHTML = emptyRow(
          8,
          "alert-triangle",
          "Error de conexión",
          "No fue posible conectar con el servidor.",
        );

        if (window.feather) feather.replace();
        console.error(
          "Error HTTP listar folios:",
          xhr.status,
          xhr.responseText,
        );
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          tbodyFolios.innerHTML = emptyRow(
            8,
            "alert-circle",
            "No fue posible cargar las series",
            res.msg || "Ocurrió un error al consultar la información.",
          );

          if (window.feather) feather.replace();
          return;
        }

        renderFolios(res.folios || []);
      } catch (error) {
        tbodyFolios.innerHTML = emptyRow(
          8,
          "alert-triangle",
          "Respuesta inválida",
          "El servidor no regresó un JSON válido.",
        );

        if (window.feather) feather.replace();
        console.error("JSON inválido listar folios:", error, xhr.responseText);
      }
    };

    xhr.send();
  }

  function renderFolios(folios) {
    if (!tbodyFolios) return;

    if (folios.length === 0) {
      tbodyFolios.innerHTML = emptyRow(
        8,
        "hash",
        "Sin series encontradas",
        "No hay series que coincidan con los filtros seleccionados.",
      );

      if (window.feather) feather.replace();
      return;
    }

    let html = "";

    folios.forEach(function (folio) {
      const idFolio = Number(folio.id_folio || 0);
      const serie = String(folio.serie || "");
      const ultimoNumero = Number(folio.ultimo_numero || 0);
      const siguienteNumero = ultimoNumero + 1;
      const activo = Number(folio.activo || 0);
      const siguienteFolio = formatearFolio(serie, siguienteNumero);

      html += `
        <tr>
          <td class="fw-bold">#${escapeHtml(idFolio)}</td>

          <td>
            <div class="fw-bold text-primary">${escapeHtml(serie)}</div>
            <div class="small text-secondary">Serie de facturación</div>
          </td>

          <td>
            <span class="fw-bold">${formatoNumero(ultimoNumero)}</span>
          </td>

          <td>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
              ${escapeHtml(siguienteFolio)}
            </span>
          </td>

          <td>
            ${badgeEstado(activo)}
          </td>

          <td>
            ${formatoFechaHora(folio.creado_en)}
          </td>

          <td>
            ${formatoFechaHora(folio.actualizado_en)}
          </td>

          <td class="text-end">
            <div class="btn-group">
              <button type="button"
                      class="btn btn-sm btn-outline-primary btn-editar-folio"
                      data-id="${idFolio}"
                      title="Editar serie">
                <i data-feather="edit-2"></i>
              </button>

              <button type="button"
                      class="btn btn-sm ${activo === 1 ? "btn-outline-warning" : "btn-outline-success"} btn-estado-folio"
                      data-id="${idFolio}"
                      data-activo="${activo === 1 ? 0 : 1}"
                      title="${activo === 1 ? "Desactivar" : "Activar"} serie">
                <i data-feather="${activo === 1 ? "slash" : "check-circle"}"></i>
              </button>

              <button type="button"
                      class="btn btn-sm btn-outline-danger btn-eliminar-folio"
                      data-id="${idFolio}"
                      title="Eliminar serie">
                <i data-feather="trash-2"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    });

    tbodyFolios.innerHTML = html;

    if (window.feather) feather.replace();
  }

  /* ============================================================
     MODAL NUEVO
  ============================================================ */
  function abrirModalNuevoFolio() {
    limpiarFormularioFolio();

    if (modalFolioLabel) {
      modalFolioLabel.textContent = "Nueva serie";
    }

    if (inputSerie) {
      inputSerie.value = "INV";
    }

    if (inputUltimoNumero) {
      inputUltimoNumero.value = "0";
    }

    if (inputActivo) {
      inputActivo.value = "1";
    }

    actualizarPreviewLocal();

    if (modalFolio) {
      modalFolio.show();
    }

    setTimeout(function () {
      if (inputSerie) inputSerie.focus();
    }, 300);
  }

  function limpiarFormularioFolio() {
    if (formFolio) formFolio.reset();

    if (inputIdFolio) inputIdFolio.value = "";
    if (previewSiguienteFolio)
      previewSiguienteFolio.textContent = "INV-00000001";

    setLoadingGuardar(false);
  }

  /* ============================================================
     GUARDAR / ACTUALIZAR
  ============================================================ */
  function guardarFolio() {
    if (!formFolio) return;

    const serie = inputSerie ? limpiarSerie(inputSerie.value.trim()) : "";
    const ultimoNumero = inputUltimoNumero
      ? inputUltimoNumero.value.trim()
      : "";
    const activo = inputActivo ? inputActivo.value : "1";

    if (serie === "") {
      Swal.fire({
        icon: "warning",
        title: "Serie requerida",
        text: "Ingresa la serie de facturación.",
      });

      if (inputSerie) inputSerie.focus();
      return;
    }

    if (!/^[A-Z0-9_-]+$/.test(serie)) {
      Swal.fire({
        icon: "warning",
        title: "Serie no válida",
        text: "La serie solo puede contener letras, números, guion y guion bajo.",
      });

      if (inputSerie) inputSerie.focus();
      return;
    }

    if (ultimoNumero === "" || !/^\d+$/.test(ultimoNumero)) {
      Swal.fire({
        icon: "warning",
        title: "Último número requerido",
        text: "El último número debe ser un entero mayor o igual a 0.",
      });

      if (inputUltimoNumero) inputUltimoNumero.focus();
      return;
    }

    if (!["0", "1"].includes(String(activo))) {
      Swal.fire({
        icon: "warning",
        title: "Estado inválido",
        text: "Selecciona un estado válido para la serie.",
      });
      return;
    }

    if (inputSerie) inputSerie.value = serie;

    const idFolio = inputIdFolio ? inputIdFolio.value.trim() : "";
    const url = idFolio === "" ? "folios/registrar" : "folios/actualizar";

    const formData = new FormData(formFolio);

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

        console.error(
          "Error HTTP guardar folio:",
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
            text: res.msg || "Revisa la información e intenta nuevamente.",
          });

          console.error("Respuesta guardar folio:", res);
          return;
        }

        Swal.fire({
          icon: "success",
          title: "Correcto",
          text: res.msg || "Serie guardada correctamente.",
          timer: 1400,
          showConfirmButton: false,
        });

        if (modalFolio) {
          modalFolio.hide();
        }

        limpiarFormularioFolio();
        cargarResumen();
        listarFolios();
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Respuesta inválida",
          text: "El servidor no regresó un JSON válido.",
        });

        console.error("JSON inválido guardar folio:", error, xhr.responseText);
      }
    };

    xhr.send(formData);
  }

  /* ============================================================
     EDITAR
  ============================================================ */
  function editarFolio(idFolio) {
    idFolio = Number(idFolio || 0);

    if (idFolio <= 0) {
      Swal.fire({
        icon: "warning",
        title: "ID inválido",
        text: "No fue posible identificar la serie.",
      });
      return;
    }

    limpiarFormularioFolio();

    const xhr = new XMLHttpRequest();
    xhr.open(
      "GET",
      base_url + "folios/obtener/" + encodeURIComponent(idFolio),
      true,
    );

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No fue posible obtener la serie.",
        });

        console.error(
          "Error HTTP obtener folio:",
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
            title: "No fue posible obtener la serie",
            text: res.msg || "Intenta nuevamente.",
          });

          return;
        }

        llenarFormularioFolio(res.folio || {});

        if (modalFolioLabel) {
          modalFolioLabel.textContent = "Editar serie";
        }

        if (modalFolio) {
          modalFolio.show();
        }

        setTimeout(function () {
          if (inputSerie) inputSerie.focus();
        }, 300);
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Respuesta inválida",
          text: "El servidor no regresó un JSON válido.",
        });

        console.error("JSON inválido obtener folio:", error, xhr.responseText);
      }
    };

    xhr.send();
  }

  function llenarFormularioFolio(folio) {
    if (inputIdFolio) inputIdFolio.value = folio.id_folio || "";
    if (inputSerie) inputSerie.value = String(folio.serie || "").toUpperCase();
    if (inputUltimoNumero) inputUltimoNumero.value = folio.ultimo_numero || "0";
    if (inputActivo) inputActivo.value = String(folio.activo ?? "1");

    actualizarPreviewLocal();
  }

  /* ============================================================
     CAMBIAR ESTADO
  ============================================================ */
  function cambiarEstadoFolio(idFolio, nuevoActivo) {
    idFolio = Number(idFolio || 0);
    nuevoActivo = Number(nuevoActivo);

    if (idFolio <= 0) {
      Swal.fire({
        icon: "warning",
        title: "ID inválido",
        text: "No fue posible identificar la serie.",
      });
      return;
    }

    const textoAccion = nuevoActivo === 1 ? "activar" : "desactivar";

    Swal.fire({
      icon: "question",
      title: `¿Deseas ${textoAccion} esta serie?`,
      text: "El cambio afectará la disponibilidad de esta serie al generar facturas.",
      showCancelButton: true,
      confirmButtonText: `Sí, ${textoAccion}`,
      cancelButtonText: "Cancelar",
      confirmButtonColor: nuevoActivo === 1 ? "#198754" : "#d97706",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      const formData = new FormData();
      formData.append("id_folio", idFolio);
      formData.append("activo", nuevoActivo);

      const xhr = new XMLHttpRequest();
      xhr.open("POST", base_url + "folios/cambiarEstado", true);

      xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) return;

        if (xhr.status !== 200) {
          Swal.fire({
            icon: "error",
            title: "Error de conexión",
            text: "No fue posible conectar con el servidor.",
          });

          console.error(
            "Error HTTP cambiar estado folio:",
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
          listarFolios();
        } catch (error) {
          Swal.fire({
            icon: "error",
            title: "Respuesta inválida",
            text: "El servidor no regresó un JSON válido.",
          });

          console.error(
            "JSON inválido cambiar estado folio:",
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
  function eliminarFolio(idFolio) {
    idFolio = Number(idFolio || 0);

    if (idFolio <= 0) {
      Swal.fire({
        icon: "warning",
        title: "ID inválido",
        text: "No fue posible identificar la serie.",
      });
      return;
    }

    Swal.fire({
      icon: "warning",
      title: "¿Eliminar serie?",
      text: "Esta acción no se podrá deshacer. Solo se permitirá si la serie no tiene facturas relacionadas.",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#dc3545",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      const formData = new FormData();
      formData.append("id_folio", idFolio);

      const xhr = new XMLHttpRequest();
      xhr.open("POST", base_url + "folios/eliminar", true);

      xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) return;

        if (xhr.status !== 200) {
          Swal.fire({
            icon: "error",
            title: "Error de conexión",
            text: "No fue posible conectar con el servidor.",
          });

          console.error(
            "Error HTTP eliminar folio:",
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
            title: "Eliminada",
            text: res.msg || "Serie eliminada correctamente.",
            timer: 1200,
            showConfirmButton: false,
          });

          cargarResumen();
          listarFolios();
        } catch (error) {
          Swal.fire({
            icon: "error",
            title: "Respuesta inválida",
            text: "El servidor no regresó un JSON válido.",
          });

          console.error(
            "JSON inválido eliminar folio:",
            error,
            xhr.responseText,
          );
        }
      };

      xhr.send(formData);
    });
  }

  /* ============================================================
     PREVIEW LOCAL
  ============================================================ */
  function actualizarPreviewLocal() {
    const serie = inputSerie ? limpiarSerie(inputSerie.value.trim()) : "INV";
    const ultimoNumero =
      inputUltimoNumero && inputUltimoNumero.value !== ""
        ? Number(inputUltimoNumero.value)
        : 0;

    const siguienteNumero = ultimoNumero + 1;
    const preview = formatearFolio(serie || "INV", siguienteNumero);

    if (previewSiguienteFolio) {
      previewSiguienteFolio.textContent = preview;
    }
  }

  /* ============================================================
     LOADING
  ============================================================ */
  function setLoadingGuardar(state) {
    if (!btnGuardarFolio || !btnText || !btnLoading) return;

    btnGuardarFolio.disabled = state;

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

  function badgeEstado(activo) {
    activo = Number(activo || 0);

    if (activo === 1) {
      return `
        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
          Activa
        </span>
      `;
    }

    return `
      <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
        Inactiva
      </span>
    `;
  }

  function limpiarSerie(value) {
    return String(value || "")
      .toUpperCase()
      .replace(/[^A-Z0-9_-]/g, "")
      .substring(0, 20);
  }

  function formatearFolio(serie, numero) {
    const serieFinal = limpiarSerie(serie || "INV") || "INV";
    const numeroFinal = Number(numero || 1);

    return serieFinal + "-" + String(numeroFinal).padStart(8, "0");
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
