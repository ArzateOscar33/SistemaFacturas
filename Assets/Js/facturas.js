document.addEventListener("DOMContentLoaded", function () {
  const selectFolio = document.getElementById("id_folio");
  const btnNuevaFactura = document.getElementById("btnNuevaFactura");
  const modalFacturaEl = document.getElementById("modalFactura");
  const formFactura = document.getElementById("formFactura");
  const modalFacturaLabel = document.getElementById("modalFacturaLabel");

  const tbodyFacturas = document.getElementById("tbodyFacturas");
  const tbodyPartidasFactura = document.getElementById("tbodyPartidasFactura");

  const buscarFactura = document.getElementById("buscarFactura");
  const filtroEstadoFactura = document.getElementById("filtroEstadoFactura");
  const fechaInicio = document.getElementById("fechaInicio");
  const fechaFin = document.getElementById("fechaFin");

  const inputIdFactura = document.getElementById("id_factura");
  const inputFolioFactura = document.getElementById("folio_factura");
  const inputFechaFactura = document.getElementById("fecha_factura");
  const selectEstadoFactura = document.getElementById("estado_factura");
  const selectCliente = document.getElementById("id_cliente");
  const selectClienteDireccion = document.getElementById(
    "id_cliente_direccion",
  );
  const inputDireccionFacturacion = document.getElementById(
    "direccion_facturacion",
  );
  const inputSalesMan = document.getElementById("sales_man");
  const inputTerms = document.getElementById("terms");
  const inputTasaImpuesto = document.getElementById("tasa_impuesto");
  const inputOtrosCargos = document.getElementById("otros_cargos");
  const inputNotas = document.getElementById("notas");

  const resumenSubtotal = document.getElementById("resumenSubtotal");
  const resumenImpuesto = document.getElementById("resumenImpuesto");
  const resumenOtrosCargos = document.getElementById("resumenOtrosCargos");
  const resumenTotal = document.getElementById("resumenTotal");

  const btnAgregarPartida = document.getElementById("btnAgregarPartida");
  const btnGuardarFactura = document.getElementById("btnGuardarFactura");
  const btnDescargarFacturaModal = document.getElementById(
    "btnDescargarFacturaModal",
  );

  const guardarText = btnGuardarFactura
    ? btnGuardarFactura.querySelector(".guardar-text")
    : null;

  const guardarLoading = btnGuardarFactura
    ? btnGuardarFactura.querySelector(".guardar-loading")
    : null;

  let modalFactura = null;
  let timerBusqueda = null;
  let contadorPartidas = 0;
  let clientesCache = [];
  let direccionesClienteCache = [];

  if (modalFacturaEl) {
    modalFactura = new bootstrap.Modal(modalFacturaEl, {
      backdrop: "static",
      keyboard: false,
    });
  }

  cargarClientes();
  cargarFolios();
  cargarFacturas();

  if (btnNuevaFactura) {
    btnNuevaFactura.addEventListener("click", function () {
      abrirModalNuevaFactura();
    });
  }

  if (btnAgregarPartida) {
    btnAgregarPartida.addEventListener("click", function () {
      agregarPartida();
    });
  }

  if (formFactura) {
    formFactura.addEventListener("submit", function (e) {
      e.preventDefault();

      if (!validarFactura()) {
        return;
      }

      guardarFactura();
    });
  }

  if (tbodyPartidasFactura) {
    tbodyPartidasFactura.addEventListener("input", function (e) {
      if (
        e.target.classList.contains("input-cantidad") ||
        e.target.classList.contains("input-precio")
      ) {
        recalcularTotales();
      }
    });

    tbodyPartidasFactura.addEventListener("click", function (e) {
      const btnQuitar = e.target.closest(".btnQuitarPartida");

      if (btnQuitar) {
        const fila = btnQuitar.closest("tr");

        if (fila) {
          fila.remove();
          verificarPartidasVacias();
          recalcularTotales();
        }
      }
    });
  }

  if (inputTasaImpuesto) {
    inputTasaImpuesto.addEventListener("input", recalcularTotales);
  }

  if (inputOtrosCargos) {
    inputOtrosCargos.addEventListener("input", recalcularTotales);
  }

  if (buscarFactura) {
    buscarFactura.addEventListener("input", function () {
      clearTimeout(timerBusqueda);
      timerBusqueda = setTimeout(cargarFacturas, 350);
    });
  }

  if (filtroEstadoFactura) {
    filtroEstadoFactura.addEventListener("change", cargarFacturas);
  }

  if (fechaInicio) {
    fechaInicio.addEventListener("change", cargarFacturas);
  }

  if (fechaFin) {
    fechaFin.addEventListener("change", cargarFacturas);
  }

  if (selectFolio) {
    selectFolio.addEventListener("change", function () {
      actualizarPreviewFolio();
    });
  }

  if (selectCliente) {
    selectCliente.addEventListener("change", function () {
      const idCliente = selectCliente.value.trim();

      limpiarDireccionesCliente();

      if (!idCliente) {
        return;
      }

      cargarDireccionesCliente(idCliente);
    });
  }

  if (selectClienteDireccion) {
    selectClienteDireccion.addEventListener("change", function () {
      llenarDireccionFacturacionDesdeSelect();
    });
  }

  if (tbodyFacturas) {
    tbodyFacturas.addEventListener("click", function (e) {
      const btnEditar = e.target.closest(".btnEditarFactura");
      const btnCancelar = e.target.closest(".btnCancelarFactura");
      const btnDescargar = e.target.closest(".btnDescargarFactura");

      if (btnEditar) {
        abrirModalEditarFactura(btnEditar.getAttribute("data-id"));
        return;
      }

      if (btnCancelar) {
        confirmarCancelarFactura(btnCancelar.getAttribute("data-id"));
        return;
      }

      if (btnDescargar) {
        descargarFactura(btnDescargar.getAttribute("data-id"));
      }
    });
  }

  if (btnDescargarFacturaModal) {
    btnDescargarFacturaModal.addEventListener("click", function () {
      const idFactura = inputIdFactura ? inputIdFactura.value.trim() : "";

      if (idFactura) {
        descargarFactura(idFactura);
      }
    });
  }

  /* ============================================================
     CLIENTES / DIRECCIONES
  ============================================================ */

  function cargarClientes(callback = null) {
    if (!selectCliente) {
      if (typeof callback === "function") callback();
      return;
    }

    selectCliente.innerHTML = `<option value="">Cargando clientes...</option>`;

    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "facturas/clientes", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        selectCliente.innerHTML = `<option value="">Error al cargar clientes</option>`;
        console.error("Error HTTP clientes:", xhr.status, xhr.responseText);

        if (typeof callback === "function") callback();
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          selectCliente.innerHTML = `<option value="">Error al cargar clientes</option>`;
          console.error(res.msg || "No se pudieron cargar los clientes.");

          if (typeof callback === "function") callback();
          return;
        }

        clientesCache = res.data || [];

        let html = `<option value="">Seleccione un cliente</option>`;

        clientesCache.forEach(function (cliente) {
          const direccionPreview =
            cliente.direccion_principal || cliente.direccion || "";

          html += `
            <option value="${escapeHtml(cliente.id_cliente)}"
                    data-id-direccion-principal="${escapeAttr(cliente.id_direccion_principal || "")}"
                    data-direccion-principal="${escapeAttr(direccionPreview)}">
              ${escapeHtml(cliente.codigo_cliente)} - ${escapeHtml(cliente.nombre_cliente)}
            </option>
          `;
        });

        selectCliente.innerHTML = html;

        if (typeof callback === "function") callback();
      } catch (error) {
        console.error("JSON inválido clientes:", error, xhr.responseText);
        selectCliente.innerHTML = `<option value="">Respuesta inválida</option>`;

        if (typeof callback === "function") callback();
      }
    };

    xhr.send();
  }

  function cargarDireccionesCliente(
    idCliente,
    idDireccionSeleccionada = "",
    direccionSnapshot = "",
  ) {
    if (!selectClienteDireccion) return;

    selectClienteDireccion.disabled = true;
    selectClienteDireccion.innerHTML = `<option value="">Cargando direcciones...</option>`;

    if (inputDireccionFacturacion && !direccionSnapshot) {
      inputDireccionFacturacion.value = "";
    }

    const xhr = new XMLHttpRequest();
    xhr.open(
      "GET",
      base_url + "facturas/direccionesCliente/" + encodeURIComponent(idCliente),
      true,
    );

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      selectClienteDireccion.disabled = false;

      if (xhr.status !== 200) {
        console.error(
          "Error HTTP direcciones cliente:",
          xhr.status,
          xhr.responseText,
        );

        renderDireccionesCliente([]);
        aplicarFallbackDireccionCliente(direccionSnapshot);
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          console.error(res.msg || "No se pudieron cargar las direcciones.");
          renderDireccionesCliente([]);
          aplicarFallbackDireccionCliente(direccionSnapshot);
          return;
        }

        direccionesClienteCache = res.data || [];

        renderDireccionesCliente(direccionesClienteCache);

        if (idDireccionSeleccionada && selectClienteDireccion) {
          selectClienteDireccion.value = String(idDireccionSeleccionada);
        }

        if (
          selectClienteDireccion &&
          !selectClienteDireccion.value &&
          selectClienteDireccion.options.length === 2
        ) {
          selectClienteDireccion.selectedIndex = 1;
        }

        if (direccionSnapshot && inputDireccionFacturacion) {
          inputDireccionFacturacion.value = direccionSnapshot;
        } else {
          llenarDireccionFacturacionDesdeSelect();
        }
      } catch (error) {
        console.error("JSON inválido direcciones:", error, xhr.responseText);
        renderDireccionesCliente([]);
        aplicarFallbackDireccionCliente(direccionSnapshot);
      }
    };

    xhr.send();
  }

  function renderDireccionesCliente(direcciones) {
    if (!selectClienteDireccion) return;

    selectClienteDireccion.innerHTML = `<option value="">Seleccione una dirección</option>`;

    if (!direcciones.length) {
      selectClienteDireccion.innerHTML = `<option value="">Sin direcciones registradas</option>`;
      return;
    }

    direcciones.forEach(function (direccion) {
      const option = document.createElement("option");

      const alias = direccion.alias ? direccion.alias : "Dirección";
      const principal = Number(direccion.es_principal || 0) === 1;
      const etiquetaPrincipal = principal ? "Principal - " : "";

      option.value = direccion.id_direccion;
      option.dataset.direccion = direccion.direccion || "";
      option.dataset.alias = direccion.alias || "";
      option.textContent = `${etiquetaPrincipal}${alias}`;

      selectClienteDireccion.appendChild(option);
    });
  }

  function llenarDireccionFacturacionDesdeSelect() {
    if (!selectClienteDireccion || !inputDireccionFacturacion) return;

    const option =
      selectClienteDireccion.options[selectClienteDireccion.selectedIndex];

    if (!option || !option.value) {
      inputDireccionFacturacion.value = "";
      return;
    }

    inputDireccionFacturacion.value = option.dataset.direccion || "";
  }

  function limpiarDireccionesCliente() {
    direccionesClienteCache = [];

    if (selectClienteDireccion) {
      selectClienteDireccion.disabled = false;
      selectClienteDireccion.innerHTML = `<option value="">Seleccione una dirección</option>`;
      selectClienteDireccion.value = "";
      selectClienteDireccion.classList.remove("is-invalid", "is-valid");
    }

    if (inputDireccionFacturacion) {
      inputDireccionFacturacion.value = "";
      inputDireccionFacturacion.classList.remove("is-invalid", "is-valid");
    }
  }

  function aplicarFallbackDireccionCliente(direccionSnapshot = "") {
    if (!inputDireccionFacturacion) return;

    if (direccionSnapshot) {
      inputDireccionFacturacion.value = direccionSnapshot;
      return;
    }

    if (!selectCliente) {
      inputDireccionFacturacion.value = "";
      return;
    }

    const option = selectCliente.options[selectCliente.selectedIndex];

    if (!option || !option.value) {
      inputDireccionFacturacion.value = "";
      return;
    }

    inputDireccionFacturacion.value = option.dataset.direccionPrincipal || "";
  }

  /* ============================================================
     FOLIOS / SERIES
  ============================================================ */

  function cargarFolios(callback = null) {
    if (!selectFolio) {
      if (typeof callback === "function") callback();
      return;
    }

    selectFolio.disabled = true;
    selectFolio.innerHTML = `<option value="">Cargando series...</option>`;

    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "facturas/folios", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        selectFolio.innerHTML = `<option value="">Error al cargar series</option>`;
        console.error(
          "Error HTTP al cargar folios:",
          xhr.status,
          xhr.responseText,
        );

        if (typeof callback === "function") callback();
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          selectFolio.innerHTML = `<option value="">Error al cargar series</option>`;
          console.error(res.msg || "No se pudieron cargar las series.");

          if (typeof callback === "function") callback();
          return;
        }

        renderFolios(res.data || []);
        selectFolio.disabled = false;

        if (typeof callback === "function") callback();
      } catch (error) {
        selectFolio.innerHTML = `<option value="">Respuesta inválida</option>`;
        console.error(
          "JSON inválido al cargar folios:",
          error,
          xhr.responseText,
        );

        if (typeof callback === "function") callback();
      }
    };

    xhr.send();
  }

  function renderFolios(folios) {
    if (!selectFolio) return;

    selectFolio.innerHTML = `<option value="">Seleccione una serie</option>`;

    if (!folios.length) {
      selectFolio.innerHTML = `<option value="">No hay series activas</option>`;
      return;
    }

    folios.forEach(function (folio) {
      const option = document.createElement("option");

      const ultimoNumero = Number(folio.ultimo_numero || 0);
      const siguienteNumero = ultimoNumero + 1;
      const preview = `${folio.serie}-${String(siguienteNumero).padStart(8, "0")}`;

      option.value = folio.id_folio;
      option.dataset.serie = folio.serie;
      option.dataset.ultimoNumero = ultimoNumero;
      option.dataset.preview = preview;
      option.textContent = `${folio.serie} - Siguiente: ${preview}`;

      selectFolio.appendChild(option);
    });
  }

  function actualizarPreviewFolio() {
    if (!selectFolio || !inputFolioFactura) return;

    const option = selectFolio.options[selectFolio.selectedIndex];

    if (!option || !option.value) {
      inputFolioFactura.value = "";
      inputFolioFactura.placeholder = "Se genera automáticamente";
      return;
    }

    inputFolioFactura.value = option.dataset.preview || "";
  }

  function bloquearSerieParaEdicion(factura) {
    if (!selectFolio) return;

    selectFolio.disabled = true;
    selectFolio.innerHTML = "";

    const option = document.createElement("option");
    option.value = "";
    option.textContent = factura.serie
      ? `${factura.serie} - Folio asignado: ${factura.folio_factura || ""}`
      : "Serie asignada";

    selectFolio.appendChild(option);
  }

  /* ============================================================
     LISTADO FACTURAS
  ============================================================ */

  function cargarFacturas() {
    if (!tbodyFacturas) return;

    const buscar = buscarFactura ? buscarFactura.value.trim() : "";
    const estado = filtroEstadoFactura ? filtroEstadoFactura.value.trim() : "";
    const inicio = fechaInicio ? fechaInicio.value.trim() : "";
    const fin = fechaFin ? fechaFin.value.trim() : "";

    tbodyFacturas.innerHTML = `
      <tr>
        <td colspan="8">
          <div class="empty-state">
            <div class="empty-state-icon">
              <i data-feather="loader"></i>
            </div>
            <h6 class="fw-bold mb-1">Cargando facturas...</h6>
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
      encodeURIComponent(estado) +
      "&fecha_inicio=" +
      encodeURIComponent(inicio) +
      "&fecha_fin=" +
      encodeURIComponent(fin);

    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "facturas/listar?" + params, true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        pintarErrorTabla("No se pudo conectar con el servidor.");
        console.error(
          "Error HTTP listar facturas:",
          xhr.status,
          xhr.responseText,
        );
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          pintarErrorTabla(res.msg || "No fue posible listar las facturas.");
          return;
        }

        pintarFacturas(res.data || []);
      } catch (error) {
        console.error(
          "JSON inválido listar facturas:",
          error,
          xhr.responseText,
        );
        pintarErrorTabla("La respuesta del servidor no es válida.");
      }
    };

    xhr.send();
  }

  function pintarFacturas(facturas) {
    if (!tbodyFacturas) return;

    if (!facturas.length) {
      tbodyFacturas.innerHTML = `
        <tr>
          <td colspan="8">
            <div class="empty-state">
              <div class="empty-state-icon">
                <i data-feather="file-text"></i>
              </div>
              <h6 class="fw-bold mb-1">No hay facturas registradas</h6>
              <div>Presiona “Nueva factura” para registrar la primera.</div>
            </div>
          </td>
        </tr>
      `;

      refrescarIconos();
      return;
    }

    let html = "";

    facturas.forEach(function (factura) {
      const estado = String(factura.estado_factura || "").toLowerCase();
      const cancelada = estado === "cancelada";

      html += `
        <tr>
          <td>
            <strong>${escapeHtml(factura.folio_factura || "")}</strong>
          </td>

          <td>
            <div class="fw-semibold">${escapeHtml(factura.nombre_cliente || "")}</div>
            ${
              factura.direccion
                ? `<div class="text-secondary small">${escapeHtml(factura.direccion)}</div>`
                : ""
            }
          </td>

          <td>${formatearFecha(factura.fecha_factura)}</td>

          <td>${formatoMoneda(factura.subtotal || 0)}</td>

          <td>${formatoMoneda(factura.impuesto || 0)}</td>

          <td>
            <strong>${formatoMoneda(factura.total || 0)}</strong>
          </td>

          <td>${badgeEstadoFactura(estado)}</td>

          <td class="text-center">
            <button type="button"
                    class="btn-icon btnEditarFactura me-1"
                    data-id="${escapeHtml(factura.id_factura)}"
                    title="Editar factura"
                    ${cancelada ? "disabled" : ""}>
              <i data-feather="edit-3"></i>
            </button>

            <button type="button"
                    class="btn-icon btnDescargarFactura me-1"
                    data-id="${escapeHtml(factura.id_factura)}"
                    title="Descargar PDF">
              <i data-feather="download"></i>
            </button>

            <button type="button"
                    class="btn-icon btnCancelarFactura text-danger"
                    data-id="${escapeHtml(factura.id_factura)}"
                    title="Cancelar factura"
                    ${cancelada ? "disabled" : ""}>
              <i data-feather="x-circle"></i>
            </button>
          </td>
        </tr>
      `;
    });

    tbodyFacturas.innerHTML = html;
    refrescarIconos();
  }

  /* ============================================================
     NUEVA / EDITAR FACTURA
  ============================================================ */

  function abrirModalNuevaFactura() {
    limpiarFormularioFactura();

    if (modalFacturaLabel) {
      modalFacturaLabel.textContent = "Nueva factura";
    }

    if (inputFechaFactura) {
      inputFechaFactura.value = obtenerFechaActual();
    }

    if (selectEstadoFactura) {
      selectEstadoFactura.value = "emitida";
    }

    if (inputTasaImpuesto) {
      inputTasaImpuesto.value = "0";
    }

    if (inputOtrosCargos) {
      inputOtrosCargos.value = "0";
    }

    if (inputFolioFactura) {
      inputFolioFactura.value = "";
      inputFolioFactura.placeholder = "Se genera automáticamente";
    }

    cargarFolios(function () {
      if (selectFolio) {
        selectFolio.disabled = false;
        selectFolio.value = "";

        if (selectFolio.options.length === 2) {
          selectFolio.selectedIndex = 1;
        }
      }

      actualizarPreviewFolio();
    });

    agregarPartida();

    if (btnDescargarFacturaModal) {
      btnDescargarFacturaModal.classList.add("d-none");
    }

    if (modalFactura) {
      modalFactura.show();
    }

    setTimeout(function () {
      if (selectFolio && !selectFolio.disabled) {
        selectFolio.focus();
      }
    }, 350);
  }

  function abrirModalEditarFactura(idFactura) {
    if (!idFactura) return;

    limpiarFormularioFactura();

    const xhr = new XMLHttpRequest();
    xhr.open(
      "GET",
      base_url + "facturas/obtener/" + encodeURIComponent(idFactura),
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

        console.error(
          "Error HTTP obtener factura:",
          xhr.status,
          xhr.responseText,
        );
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: res.msg || "No fue posible obtener la factura.",
          });
          return;
        }

        llenarFormularioFactura(res.data || {});

        if (modalFacturaLabel) {
          modalFacturaLabel.textContent = "Editar factura";
        }

        if (btnDescargarFacturaModal) {
          btnDescargarFacturaModal.classList.remove("d-none");
        }

        if (modalFactura) {
          modalFactura.show();
        }
      } catch (error) {
        console.error(
          "JSON inválido obtener factura:",
          error,
          xhr.responseText,
        );

        Swal.fire({
          icon: "error",
          title: "Error inesperado",
          text: "Respuesta inválida del servidor.",
        });
      }
    };

    xhr.send();
  }

  function llenarFormularioFactura(factura) {
    if (inputIdFactura) inputIdFactura.value = factura.id_factura || "";
    if (inputFolioFactura)
      inputFolioFactura.value = factura.folio_factura || "";
    if (inputFechaFactura)
      inputFechaFactura.value = factura.fecha_factura || "";
    if (selectEstadoFactura) {
      selectEstadoFactura.value = factura.estado_factura || "emitida";
    }

    if (selectCliente) {
      selectCliente.value = String(factura.id_cliente || "");
    }

    if (inputSalesMan) inputSalesMan.value = factura.sales_man || "";
    if (inputTerms) inputTerms.value = factura.terms || "";
    if (inputTasaImpuesto) inputTasaImpuesto.value = factura.tasa_impuesto || 0;
    if (inputOtrosCargos) inputOtrosCargos.value = factura.otros_cargos || 0;
    if (inputNotas) inputNotas.value = factura.notas || "";

    if (inputDireccionFacturacion) {
      inputDireccionFacturacion.value =
        factura.direccion_facturacion || factura.direccion || "";
    }

    if (factura.id_cliente) {
      cargarDireccionesCliente(
        factura.id_cliente,
        factura.id_cliente_direccion || "",
        factura.direccion_facturacion || factura.direccion || "",
      );
    }

    bloquearSerieParaEdicion(factura);

    limpiarPartidas();

    const detalle = factura.detalle || [];

    if (detalle.length) {
      detalle.forEach(function (partida) {
        agregarPartida({
          cantidad: partida.cantidad,
          descripcion: partida.descripcion,
          precio_unitario: partida.precio_unitario,
        });
      });
    } else {
      agregarPartida();
    }

    recalcularTotales();
  }

  /* ============================================================
     GUARDAR FACTURA
  ============================================================ */

  function guardarFactura() {
    if (!formFactura) return;

    const idFactura = inputIdFactura ? inputIdFactura.value.trim() : "";

    const url = idFactura
      ? base_url + "facturas/actualizar"
      : base_url + "facturas/registrar";

    const formData = new FormData(formFactura);

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

        console.error(
          "Error HTTP guardar factura:",
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
            title: "Aviso",
            text: res.msg || "No fue posible guardar la factura.",
          });
          return;
        }

        if (inputIdFactura) {
          inputIdFactura.value = res.id_factura || inputIdFactura.value;
        }

        if (inputFolioFactura && res.folio_factura) {
          inputFolioFactura.value = res.folio_factura;
        }

        Swal.fire({
          icon: "success",
          title: "Correcto",
          text: res.msg || "Factura guardada correctamente.",
          timer: 1400,
          showConfirmButton: false,
        });

        if (modalFactura) {
          modalFactura.hide();
        }

        limpiarFormularioFactura();
        cargarFolios();
        cargarFacturas();
      } catch (error) {
        console.error(
          "JSON inválido guardar factura:",
          error,
          xhr.responseText,
        );

        Swal.fire({
          icon: "error",
          title: "Error inesperado",
          text: "Respuesta inválida del servidor.",
        });
      }
    };

    xhr.send(formData);
  }

  /* ============================================================
     CANCELAR FACTURA
  ============================================================ */

  function confirmarCancelarFactura(idFactura) {
    if (!idFactura) return;

    Swal.fire({
      icon: "warning",
      title: "Cancelar factura",
      text: "La factura quedará marcada como cancelada. Esta acción no liberará el folio.",
      showCancelButton: true,
      confirmButtonText: "Sí, cancelar",
      cancelButtonText: "No cancelar",
      confirmButtonColor: "#dc2626",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      cancelarFactura(idFactura);
    });
  }

  function cancelarFactura(idFactura) {
    const formData = new FormData();
    formData.append("id_factura", idFactura);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", base_url + "facturas/cancelar", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No se pudo conectar con el servidor.",
        });

        console.error(
          "Error HTTP cancelar factura:",
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
            title: "Aviso",
            text: res.msg || "No fue posible cancelar la factura.",
          });
          return;
        }

        Swal.fire({
          icon: "success",
          title: "Correcto",
          text: res.msg || "Factura cancelada correctamente.",
          timer: 1300,
          showConfirmButton: false,
        });

        cargarFacturas();
      } catch (error) {
        console.error(
          "JSON inválido cancelar factura:",
          error,
          xhr.responseText,
        );

        Swal.fire({
          icon: "error",
          title: "Error inesperado",
          text: "Respuesta inválida del servidor.",
        });
      }
    };

    xhr.send(formData);
  }

  /* ============================================================
     PARTIDAS
  ============================================================ */

  function agregarPartida(data = {}) {
    if (!tbodyPartidasFactura) return;

    quitarEmptyPartidas();

    contadorPartidas++;

    const cantidad = data.cantidad || "";
    const descripcion = data.descripcion || "";
    const precioUnitario = data.precio_unitario || "";

    const tr = document.createElement("tr");
    tr.classList.add("fila-partida");

    tr.innerHTML = `
      <td>
        <input type="number"
               class="form-control input-cantidad"
               name="cantidad[]"
               min="0.01"
               step="0.01"
               value="${escapeHtml(cantidad)}"
               placeholder="0">
      </td>

      <td>
        <textarea class="form-control input-descripcion"
                  name="descripcion[]"
                  rows="2"
                  placeholder="Descripción de la partida">${escapeHtml(descripcion)}</textarea>
      </td>

      <td>
        <input type="number"
               class="form-control input-precio"
               name="precio_unitario[]"
               min="0"
               step="0.01"
               value="${escapeHtml(precioUnitario)}"
               placeholder="0.00">
      </td>

      <td>
        <input type="text"
               class="form-control input-total-linea"
               value="$0.00"
               readonly>
      </td>

      <td class="text-center">
        <button type="button"
                class="btn-icon btn-remove-partida btnQuitarPartida"
                title="Quitar partida">
          <i data-feather="trash-2"></i>
        </button>
      </td>
    `;

    tbodyPartidasFactura.appendChild(tr);
    recalcularTotales();
    refrescarIconos();
  }

  function limpiarPartidas() {
    if (!tbodyPartidasFactura) return;

    tbodyPartidasFactura.innerHTML = "";
    contadorPartidas = 0;
    verificarPartidasVacias();
  }

  function quitarEmptyPartidas() {
    if (!tbodyPartidasFactura) return;

    const empty = tbodyPartidasFactura.querySelector(".empty-state");

    if (empty) {
      tbodyPartidasFactura.innerHTML = "";
    }
  }

  function verificarPartidasVacias() {
    if (!tbodyPartidasFactura) return;

    const filas = tbodyPartidasFactura.querySelectorAll(".fila-partida");

    if (!filas.length) {
      tbodyPartidasFactura.innerHTML = `
        <tr>
          <td colspan="5">
            <div class="empty-state py-4">
              <div class="empty-state-icon">
                <i data-feather="list"></i>
              </div>
              <h6 class="fw-bold mb-1">Sin partidas</h6>
              <div>Agrega al menos una partida para registrar la factura.</div>
            </div>
          </td>
        </tr>
      `;

      refrescarIconos();
    }
  }

  function recalcularTotales() {
    let subtotal = 0;

    const filas = tbodyPartidasFactura
      ? tbodyPartidasFactura.querySelectorAll(".fila-partida")
      : [];

    filas.forEach(function (fila) {
      const cantidadInput = fila.querySelector(".input-cantidad");
      const precioInput = fila.querySelector(".input-precio");
      const totalLineaInput = fila.querySelector(".input-total-linea");

      const cantidad = cantidadInput ? Number(cantidadInput.value || 0) : 0;
      const precio = precioInput ? Number(precioInput.value || 0) : 0;
      const totalLinea = cantidad * precio;

      subtotal += totalLinea;

      if (totalLineaInput) {
        totalLineaInput.value = formatoMoneda(totalLinea);
      }
    });

    const tasa = inputTasaImpuesto ? Number(inputTasaImpuesto.value || 0) : 0;
    const otros = inputOtrosCargos ? Number(inputOtrosCargos.value || 0) : 0;

    const impuesto = subtotal * (tasa / 100);
    const total = subtotal + impuesto + otros;

    if (resumenSubtotal) resumenSubtotal.textContent = formatoMoneda(subtotal);
    if (resumenImpuesto) resumenImpuesto.textContent = formatoMoneda(impuesto);
    if (resumenOtrosCargos) {
      resumenOtrosCargos.textContent = formatoMoneda(otros);
    }
    if (resumenTotal) resumenTotal.textContent = formatoMoneda(total);
  }

  /* ============================================================
     VALIDACIONES
  ============================================================ */

  function validarFactura() {
    limpiarValidaciones();

    const idFactura = inputIdFactura ? inputIdFactura.value.trim() : "";

    if (idFactura === "" && selectFolio && selectFolio.value === "") {
      marcarInvalido(selectFolio);

      alertaValidacion(
        "Serie requerida",
        "Selecciona la serie que se usará para generar la factura.",
      );

      return false;
    }

    if (!selectCliente || selectCliente.value.trim() === "") {
      marcarInvalido(selectCliente);

      alertaValidacion(
        "Cliente requerido",
        "Selecciona un cliente para la factura.",
      );

      return false;
    }

    if (
      !inputDireccionFacturacion ||
      inputDireccionFacturacion.value.trim() === ""
    ) {
      marcarInvalido(inputDireccionFacturacion);

      alertaValidacion(
        "Dirección requerida",
        "Selecciona o escribe la dirección de facturación.",
      );

      return false;
    }

    if (inputDireccionFacturacion.value.trim().length > 1000) {
      marcarInvalido(inputDireccionFacturacion);

      alertaValidacion(
        "Dirección demasiado larga",
        "La dirección de facturación no puede superar 1000 caracteres.",
      );

      return false;
    }

    if (!inputFechaFactura || inputFechaFactura.value.trim() === "") {
      marcarInvalido(inputFechaFactura);

      alertaValidacion("Fecha requerida", "Selecciona la fecha de la factura.");

      return false;
    }

    if (!selectEstadoFactura || selectEstadoFactura.value.trim() === "") {
      marcarInvalido(selectEstadoFactura);

      alertaValidacion(
        "Estado requerido",
        "Selecciona el estado de la factura.",
      );

      return false;
    }

    const tasa = inputTasaImpuesto ? Number(inputTasaImpuesto.value || 0) : 0;
    const otros = inputOtrosCargos ? Number(inputOtrosCargos.value || 0) : 0;

    if (tasa < 0) {
      marcarInvalido(inputTasaImpuesto);

      alertaValidacion(
        "Tax inválido",
        "La tasa de impuesto no puede ser negativa.",
      );

      return false;
    }

    if (otros < 0) {
      marcarInvalido(inputOtrosCargos);

      alertaValidacion(
        "Cargo inválido",
        "Shipping & handling no puede ser negativo.",
      );

      return false;
    }

    const filas = tbodyPartidasFactura
      ? tbodyPartidasFactura.querySelectorAll(".fila-partida")
      : [];

    if (!filas.length) {
      alertaValidacion(
        "Partidas requeridas",
        "Agrega al menos una partida a la factura.",
      );

      return false;
    }

    for (let i = 0; i < filas.length; i++) {
      const fila = filas[i];
      const cantidadInput = fila.querySelector(".input-cantidad");
      const descripcionInput = fila.querySelector(".input-descripcion");
      const precioInput = fila.querySelector(".input-precio");

      const cantidad = cantidadInput ? Number(cantidadInput.value || 0) : 0;
      const descripcion = descripcionInput ? descripcionInput.value.trim() : "";
      const precio = precioInput ? Number(precioInput.value || 0) : 0;

      if (cantidad <= 0) {
        marcarInvalido(cantidadInput);

        alertaValidacion(
          "Cantidad inválida",
          "Todas las partidas deben tener cantidad mayor a 0.",
        );

        return false;
      }

      if (descripcion === "") {
        marcarInvalido(descripcionInput);

        alertaValidacion(
          "Descripción requerida",
          "Todas las partidas deben tener descripción.",
        );

        return false;
      }

      if (precio < 0) {
        marcarInvalido(precioInput);

        alertaValidacion(
          "Precio inválido",
          "El precio unitario no puede ser negativo.",
        );

        return false;
      }
    }

    if (selectFolio && idFactura === "") marcarValido(selectFolio);
    marcarValido(selectCliente);
    marcarValido(inputDireccionFacturacion);
    marcarValido(inputFechaFactura);

    return true;
  }

  function limpiarFormularioFactura() {
    if (formFactura) {
      formFactura.reset();
    }

    if (inputIdFactura) inputIdFactura.value = "";
    if (inputFolioFactura) {
      inputFolioFactura.value = "";
      inputFolioFactura.placeholder = "Se genera automáticamente";
    }

    if (selectFolio) {
      selectFolio.disabled = false;
      selectFolio.value = "";
    }

    limpiarDireccionesCliente();
    limpiarPartidas();
    limpiarValidaciones();
    recalcularTotales();
    setLoading(false);

    if (btnDescargarFacturaModal) {
      btnDescargarFacturaModal.classList.add("d-none");
    }
  }

  function limpiarValidaciones() {
    const campos = [
      selectFolio,
      selectCliente,
      selectClienteDireccion,
      inputDireccionFacturacion,
      inputFechaFactura,
      selectEstadoFactura,
      inputSalesMan,
      inputTerms,
      inputTasaImpuesto,
      inputOtrosCargos,
      inputNotas,
    ];

    campos.forEach(function (campo) {
      if (campo) {
        campo.classList.remove("is-invalid", "is-valid");
      }
    });

    const inputsPartidas = tbodyPartidasFactura
      ? tbodyPartidasFactura.querySelectorAll("input, textarea")
      : [];

    inputsPartidas.forEach(function (input) {
      input.classList.remove("is-invalid", "is-valid");
    });
  }

  /* ============================================================
     PDF
  ============================================================ */

  function descargarFactura(idFactura) {
    if (!idFactura) return;

    window.open(
      base_url + "facturas/pdf/" + encodeURIComponent(idFactura),
      "_blank",
    );
  }

  /* ============================================================
     UI HELPERS
  ============================================================ */

  function pintarErrorTabla(mensaje) {
    if (!tbodyFacturas) return;

    tbodyFacturas.innerHTML = `
      <tr>
        <td colspan="8">
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

  function badgeEstadoFactura(estado) {
    switch (estado) {
      case "emitida":
        return `<span class="badge-emitida">Emitida</span>`;

      case "borrador":
        return `<span class="badge-borrador">Borrador</span>`;

      case "cancelada":
        return `<span class="badge-cancelada">Cancelada</span>`;

      default:
        return `<span class="badge-borrador">${escapeHtml(estado || "Sin estado")}</span>`;
    }
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

  function alertaValidacion(titulo, mensaje) {
    Swal.fire({
      icon: "warning",
      title: titulo,
      text: mensaje,
    });
  }

  function setLoading(state) {
    if (!btnGuardarFactura || !guardarText || !guardarLoading) return;

    if (state) {
      btnGuardarFactura.disabled = true;
      guardarText.classList.add("d-none");
      guardarLoading.classList.remove("d-none");
    } else {
      btnGuardarFactura.disabled = false;
      guardarText.classList.remove("d-none");
      guardarLoading.classList.add("d-none");
    }
  }

  function formatoMoneda(value) {
    const numero = Number(value || 0);

    return numero.toLocaleString("en-US", {
      style: "currency",
      currency: "USD",
    });
  }

  function formatearFecha(fecha) {
    if (!fecha) return "Sin fecha";

    const partes = String(fecha).split("-");

    if (partes.length !== 3) return fecha;

    return `${partes[2]}/${partes[1]}/${partes[0]}`;
  }

  function obtenerFechaActual() {
    const hoy = new Date();
    const year = hoy.getFullYear();
    const month = String(hoy.getMonth() + 1).padStart(2, "0");
    const day = String(hoy.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
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

  function escapeAttr(value) {
    return escapeHtml(value).replace(/`/g, "&#096;");
  }
});
