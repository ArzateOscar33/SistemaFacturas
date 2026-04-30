document.addEventListener("DOMContentLoaded", function () {
  const btnReportarError = document.getElementById("btnReportarError");
  const modalEl = document.getElementById("modalReportarError");
  const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

  const form = document.getElementById("formReportarError");
  const inputModulo = document.getElementById("reporteModulo");
  const inputMensaje = document.getElementById("reporteMensaje");
  const btnEnviar = document.getElementById("btnEnviarReporteError");

  const btnText = btnEnviar ? btnEnviar.querySelector(".btn-text") : null;
  const btnLoading = btnEnviar ? btnEnviar.querySelector(".btn-loading") : null;

  if (btnReportarError) {
    btnReportarError.addEventListener("click", function () {
      if (inputModulo) {
        inputModulo.value = detectarModuloActual();
      }

      if (inputMensaje) {
        inputMensaje.value = "";
      }

      if (modal) {
        modal.show();
      }

      setTimeout(function () {
        if (inputMensaje) inputMensaje.focus();
      }, 300);
    });
  }

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      enviarReporteError();
    });
  }

  function enviarReporteError() {
    const mensaje = inputMensaje ? inputMensaje.value.trim() : "";
    const modulo = inputModulo ? inputModulo.value.trim() : "";

    if (mensaje === "") {
      Swal.fire({
        icon: "warning",
        title: "Descripción requerida",
        text: "Describe el problema que encontraste.",
      });
      return;
    }

    const formData = new FormData();
    formData.append("mensaje", mensaje);
    formData.append("modulo", modulo || detectarModuloActual());
    formData.append("url", window.location.href);
    formData.append(
      "datos_adicionales",
      JSON.stringify({
        titulo: document.title,
        ruta: window.location.pathname,
        navegador: navigator.userAgent,
        fecha_cliente: new Date().toISOString(),
      }),
    );

    setLoading(true);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", base_url + "errores/registrarAjax", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      setLoading(false);

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "No se pudo enviar",
          text: "No fue posible conectar con el servidor.",
        });

        console.error(
          "Error HTTP reportar error:",
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
            title: "No se pudo enviar",
            text: res.msg || "Intenta nuevamente.",
          });
          return;
        }

        Swal.fire({
          icon: "success",
          title: "Reporte enviado",
          text: "El administrador podrá revisar tu reporte.",
          timer: 1600,
          showConfirmButton: false,
        });

        if (modal) {
          modal.hide();
        }

        if (form) {
          form.reset();
        }
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Respuesta inválida",
          text: "El servidor no regresó un JSON válido.",
        });

        console.error("JSON inválido reportar error:", error, xhr.responseText);
      }
    };

    xhr.send(formData);
  }

  function detectarModuloActual() {
    const path = window.location.pathname.toLowerCase();

    if (path.includes("facturas")) return "Facturas";
    if (path.includes("clientes")) return "Clientes";
    if (path.includes("usuarios")) return "Usuarios";
    if (path.includes("roles")) return "Roles";
    if (path.includes("folios")) return "Folios y series";
    if (path.includes("reportes")) return "Reportes";
    if (path.includes("errores")) return "Reporte de errores";
    if (path.includes("configuracion")) return "Configuración";
    if (path.includes("bitacora")) return "Bitácora";
    if (path.includes("perfil")) return "Mi perfil";
    if (path.includes("admin")) return "Dashboard";

    return "Sistema";
  }

  function setLoading(state) {
    if (!btnEnviar || !btnText || !btnLoading) return;

    btnEnviar.disabled = state;

    if (state) {
      btnText.classList.add("d-none");
      btnLoading.classList.remove("d-none");
    } else {
      btnText.classList.remove("d-none");
      btnLoading.classList.add("d-none");
    }
  }
});
