document.addEventListener("DOMContentLoaded", function () {
  let chartMensual = null;
  let chartEstados = null;

  const selectCliente = document.getElementById("filtroClienteReporte");
  const selectEstado = document.getElementById("filtroEstadoReporte");
  const fechaInicio = document.getElementById("fechaInicioReporte");
  const fechaFin = document.getElementById("fechaFinReporte");

  const btnFiltrar = document.getElementById("btnFiltrarReporte");
  const btnExportar = document.getElementById("btnExportarReporte");

  cargarClientes();
  cargarReporte();

  if (btnFiltrar) {
    btnFiltrar.addEventListener("click", function () {
      cargarReporte();
    });
  }

  if (btnExportar) {
    btnExportar.addEventListener("click", function () {
      exportarReporte();
    });
  }

  /* ============================================================
     CARGAR CLIENTES
  ============================================================ */
  function cargarClientes() {
    if (!selectCliente) return;

    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "reportes/clientes", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        console.error("Error HTTP clientes:", xhr.status, xhr.responseText);
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          console.error(res.msg || "No se pudieron cargar los clientes.");
          return;
        }

        renderClientes(res.clientes || []);
      } catch (error) {
        console.error("JSON inválido clientes:", error, xhr.responseText);
      }
    };

    xhr.send();
  }

  function renderClientes(clientes) {
    selectCliente.innerHTML = '<option value="">Todos los clientes</option>';

    clientes.forEach(function (cliente) {
      const option = document.createElement("option");
      option.value = cliente.id_cliente;
      option.textContent = `${cliente.codigo_cliente} - ${cliente.nombre_cliente}`;
      selectCliente.appendChild(option);
    });
  }

  /* ============================================================
     CARGAR REPORTE
  ============================================================ */
  function cargarReporte() {
    if (!validarFechas()) return;

    setLoading(true);

    const params = new URLSearchParams();

    if (selectCliente && selectCliente.value !== "") {
      params.append("id_cliente", selectCliente.value);
    }

    if (selectEstado && selectEstado.value !== "") {
      params.append("estado", selectEstado.value);
    }

    if (fechaInicio && fechaInicio.value !== "") {
      params.append("fecha_inicio", fechaInicio.value);
    }

    if (fechaFin && fechaFin.value !== "") {
      params.append("fecha_fin", fechaFin.value);
    }

    params.append("limite", "500");

    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "reportes/datos?" + params.toString(), true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      setLoading(false);

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No fue posible conectar con el servidor.",
        });

        console.error("Error HTTP reporte:", xhr.status, xhr.responseText);
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          Swal.fire({
            icon: "warning",
            title: "No fue posible cargar el reporte",
            text: res.msg || "Revisa los filtros e intenta nuevamente.",
          });

          console.error("Respuesta reporte:", res);
          return;
        }

        renderKPIs(res.resumen || {});
        renderChartMensual(res.facturacion_mensual || []);
        renderChartEstados(res.facturas_estado || []);
        renderTopClientes(res.top_clientes || []);
        renderUltimasFacturas(res.ultimas_facturas || []);
        renderReporteDetallado(res.reporte_detallado || []);

        if (window.feather) {
          feather.replace();
        }
      } catch (error) {
        console.error("JSON inválido reporte:", error, xhr.responseText);

        Swal.fire({
          icon: "error",
          title: "Respuesta inválida",
          text: "El servidor no regresó un JSON válido.",
        });
      }
    };

    xhr.send();
  }

  /* ============================================================
     KPIS
  ============================================================ */
  function renderKPIs(resumen) {
    setText("kpiTotalFacturado", formatoMoneda(resumen.total_facturado || 0));
    setText(
      "kpiFacturasEmitidas",
      formatoNumero(resumen.facturas_emitidas || 0),
    );
    setText(
      "kpiFacturasCanceladas",
      formatoNumero(resumen.facturas_canceladas || 0),
    );
    setText("kpiPromedioFactura", formatoMoneda(resumen.promedio_factura || 0));
  }

  /* ============================================================
     CHART: FACTURACIÓN MENSUAL
  ============================================================ */
  function renderChartMensual(datos) {
    const canvas = document.getElementById("chartFacturacionMensual");
    if (!canvas) return;

    if (typeof Chart === "undefined") {
      console.error("Chart.js no está cargado.");
      return;
    }

    const labels = datos.map(
      (item) => item.periodo_texto || item.periodo || "Sin periodo",
    );
    const valores = datos.map((item) => Number(item.total_facturado || 0));

    if (chartMensual) {
      chartMensual.destroy();
    }

    chartMensual = new Chart(canvas, {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Total facturado",
            data: valores,
            tension: 0.35,
            fill: true,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        resizeDelay: 200,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                return formatoMoneda(context.raw || 0);
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return formatoMoneda(value);
              },
            },
          },
        },
      },
    });
  }

  /* ============================================================
     CHART: ESTADOS
  ============================================================ */
  function renderChartEstados(datos) {
    const canvas = document.getElementById("chartEstadosFacturas");
    if (!canvas) return;

    if (typeof Chart === "undefined") {
      console.error("Chart.js no está cargado.");
      return;
    }

    const mapa = {
      emitida: 0,
      borrador: 0,
      cancelada: 0,
    };

    datos.forEach(function (item) {
      const estado = String(item.estado_factura || "").toLowerCase();

      if (Object.prototype.hasOwnProperty.call(mapa, estado)) {
        mapa[estado] = Number(item.total || 0);
      }
    });

    if (chartEstados) {
      chartEstados.destroy();
    }

    chartEstados = new Chart(canvas, {
      type: "doughnut",
      data: {
        labels: ["Emitidas", "Borradores", "Canceladas"],
        datasets: [
          {
            data: [mapa.emitida, mapa.borrador, mapa.cancelada],
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        resizeDelay: 200,
        plugins: {
          legend: {
            position: "bottom",
          },
        },
      },
    });
  }

  /* ============================================================
     TOP CLIENTES
  ============================================================ */
  function renderTopClientes(clientes) {
    const tbody = document.getElementById("tbodyTopClientes");
    if (!tbody) return;

    if (clientes.length === 0) {
      tbody.innerHTML = emptyRow(
        3,
        "users",
        "Sin datos",
        "No hay clientes para los filtros seleccionados.",
      );
      return;
    }

    let html = "";

    clientes.forEach(function (cliente) {
      html += `
        <tr>
          <td>
            <div class="fw-bold">${escapeHtml(cliente.nombre_cliente || "Sin nombre")}</div>
            <div class="small text-secondary">${escapeHtml(cliente.codigo_cliente || "")}</div>
          </td>
          <td>${formatoNumero(cliente.total_facturas || 0)}</td>
          <td class="text-end fw-bold">${formatoMoneda(cliente.total_facturado || 0)}</td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
  }

  /* ============================================================
     ÚLTIMAS FACTURAS
  ============================================================ */
  function renderUltimasFacturas(facturas) {
    const tbody = document.getElementById("tbodyUltimasFacturasReporte");
    if (!tbody) return;

    if (facturas.length === 0) {
      tbody.innerHTML = emptyRow(
        5,
        "file-text",
        "Sin facturas",
        "No hay facturas recientes para mostrar.",
      );
      return;
    }

    let html = "";

    facturas.forEach(function (factura) {
      html += `
        <tr>
          <td class="fw-bold">${escapeHtml(factura.folio_factura || "")}</td>
          <td>${escapeHtml(factura.nombre_cliente || "")}</td>
          <td>${formatoFecha(factura.fecha_factura)}</td>
          <td>${badgeEstado(factura.estado_factura)}</td>
          <td class="text-end fw-bold">${formatoMoneda(factura.total || 0)}</td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
  }

  /* ============================================================
     REPORTE DETALLADO
  ============================================================ */
  function renderReporteDetallado(facturas) {
    const tbody = document.getElementById("tbodyReporteFacturas");
    if (!tbody) return;

    if (facturas.length === 0) {
      tbody.innerHTML = emptyRow(
        8,
        "database",
        "Sin datos cargados",
        "No hay facturas que coincidan con los filtros.",
      );
      return;
    }

    let html = "";

    facturas.forEach(function (factura) {
      html += `
        <tr>
          <td>
            <div class="fw-bold">${escapeHtml(factura.folio_factura || "")}</div>
            <div class="small text-secondary">#${escapeHtml(factura.numero_factura || "")}</div>
          </td>
          <td>
            <div class="fw-bold">${escapeHtml(factura.nombre_cliente || "")}</div>
            <div class="small text-secondary">${escapeHtml(factura.codigo_cliente || "")}</div>
          </td>
          <td>${formatoFecha(factura.fecha_factura)}</td>
          <td>${formatoMoneda(factura.subtotal || 0)}</td>
          <td>${formatoMoneda(factura.impuesto || 0)}</td>
          <td>${formatoMoneda(factura.otros_cargos || 0)}</td>
          <td class="fw-bold">${formatoMoneda(factura.total || 0)}</td>
          <td>${badgeEstado(factura.estado_factura)}</td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
  }

  /* ============================================================
     VALIDACIONES
  ============================================================ */
  function validarFechas() {
    if (!fechaInicio || !fechaFin) return true;

    if (
      fechaInicio.value !== "" &&
      fechaFin.value !== "" &&
      fechaInicio.value > fechaFin.value
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

  function setLoading(state) {
    if (!btnFiltrar) return;

    btnFiltrar.disabled = state;

    if (state) {
      btnFiltrar.innerHTML = `
        <span class="spinner-border spinner-border-sm"></span>
      `;
    } else {
      btnFiltrar.innerHTML = `
        <i data-feather="filter"></i>
      `;

      if (window.feather) {
        feather.replace();
      }
    }
  }

  /* ============================================================
     HELPERS
  ============================================================ */
  function setText(id, value) {
    const el = document.getElementById(id);

    if (el) {
      el.textContent = value;
    }
  }

  function formatoMoneda(value) {
    const number = Number(value || 0);

    return number.toLocaleString("en-US", {
      style: "currency",
      currency: "USD",
      minimumFractionDigits: 2,
    });
  }

  function formatoNumero(value) {
    const number = Number(value || 0);

    return number.toLocaleString("en-US", {
      maximumFractionDigits: 0,
    });
  }

  function formatoFecha(fecha) {
    if (!fecha) return "No aplica";

    const partes = String(fecha).split("-");

    if (partes.length !== 3) {
      return fecha;
    }

    return `${partes[2]}/${partes[1]}/${partes[0]}`;
  }

  function badgeEstado(estado) {
    estado = String(estado || "").toLowerCase();

    if (estado === "emitida") {
      return `<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Emitida</span>`;
    }

    if (estado === "borrador") {
      return `<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Borrador</span>`;
    }

    if (estado === "cancelada") {
      return `<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Cancelada</span>`;
    }

    return `<span class="badge bg-secondary rounded-pill">No definido</span>`;
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

  /* ============================================================
   EXPORTAR REPORTE
============================================================ */
  function exportarReporte() {
    if (!validarFechas()) return;

    const params = new URLSearchParams();

    if (selectCliente && selectCliente.value !== "") {
      params.append("id_cliente", selectCliente.value);
    }

    if (selectEstado && selectEstado.value !== "") {
      params.append("estado", selectEstado.value);
    }

    if (fechaInicio && fechaInicio.value !== "") {
      params.append("fecha_inicio", fechaInicio.value);
    }

    if (fechaFin && fechaFin.value !== "") {
      params.append("fecha_fin", fechaFin.value);
    }

    params.append("limite", "1000");

    window.location.href = base_url + "reportes/exportar?" + params.toString();
  }
});
