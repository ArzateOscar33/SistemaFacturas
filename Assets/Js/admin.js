document.addEventListener("DOMContentLoaded", function () {
  let chartMensual = null;
  let chartEstados = null;

  cargarDashboard();

  function cargarDashboard() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "admin/resumen", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        console.error("Error HTTP:", xhr.status);
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          console.error(res.msg || "No fue posible cargar el dashboard.");
          return;
        }

        pintarKpis(res.data);
        pintarChartMensual(res.data.facturacion_mensual || []);
        pintarChartEstados(res.data.facturas_estado || []);

        if (window.feather) {
          feather.replace();
        }
      } catch (error) {
        console.error("Respuesta inválida del servidor:", xhr.responseText);
      }
    };

    xhr.send();
  }

  function pintarKpis(data) {
    setText("kpiFacturasEmitidas", data.facturas_emitidas || 0);
    setText("kpiClientes", data.clientes_activos || 0);
    setText("kpiTotalFacturado", formatoMoneda(data.total_facturado || 0));
    setText("kpiCanceladas", data.facturas_canceladas || 0);
  }

  function pintarChartMensual(rows) {
    const canvas = document.getElementById("chartFacturacionMensual");
    if (!canvas) return;

    const labels = rows.map((row) => formatearMes(row.mes));
    const values = rows.map((row) => Number(row.total || 0));

    if (chartMensual) {
      chartMensual.destroy();
    }

    chartMensual = new Chart(canvas, {
      type: "line",
      data: {
        labels: labels.length ? labels : ["Sin datos"],
        datasets: [
          {
            label: "Total facturado",
            data: values.length ? values : [0],
            tension: 0.35,
            fill: true,
            borderWidth: 3,
            pointRadius: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                return formatoMoneda(context.parsed.y || 0);
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

  function pintarChartEstados(rows) {
    const canvas = document.getElementById("chartEstadoFacturas");
    if (!canvas) return;

    const mapa = {
      emitida: 0,
      borrador: 0,
      cancelada: 0,
    };

    rows.forEach((row) => {
      const estado = String(row.estado_factura || "").toLowerCase();
      if (Object.prototype.hasOwnProperty.call(mapa, estado)) {
        mapa[estado] = Number(row.total || 0);
      }
    });

    if (chartEstados) {
      chartEstados.destroy();
    }

    chartEstados = new Chart(canvas, {
      type: "doughnut",
      data: {
        labels: ["Emitidas", "Borrador", "Canceladas"],
        datasets: [
          {
            data: [mapa.emitida, mapa.borrador, mapa.cancelada],
            borderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: "bottom",
          },
        },
      },
    });
  }

  function setText(id, value) {
    const el = document.getElementById(id);
    if (el) {
      el.textContent = value;
    }
  }

  function formatoMoneda(value) {
    const numero = Number(value || 0);

    return numero.toLocaleString("en-US", {
      style: "currency",
      currency: "USD",
    });
  }

  function formatearMes(mes) {
    if (!mes) return "Sin fecha";

    const partes = String(mes).split("-");
    if (partes.length !== 2) return mes;

    const year = partes[0];
    const month = Number(partes[1]);

    const nombres = [
      "",
      "Ene",
      "Feb",
      "Mar",
      "Abr",
      "May",
      "Jun",
      "Jul",
      "Ago",
      "Sep",
      "Oct",
      "Nov",
      "Dic",
    ];

    return `${nombres[month] || mes} ${year}`;
  }
});
