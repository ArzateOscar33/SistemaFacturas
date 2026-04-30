document.addEventListener("DOMContentLoaded", function () {
  const btnNuevoCliente = document.getElementById("btnNuevoCliente");
  const modalClienteEl = document.getElementById("modalCliente");
  const formCliente = document.getElementById("formCliente");
  const modalTitle = document.getElementById("modalClienteLabel");

  const btnGuardarCliente = document.getElementById("btnGuardarCliente");
  const guardarText = btnGuardarCliente
    ? btnGuardarCliente.querySelector(".guardar-text")
    : null;
  const guardarLoading = btnGuardarCliente
    ? btnGuardarCliente.querySelector(".guardar-loading")
    : null;

  const tbodyClientes = document.getElementById("tbodyClientes");
  const buscarCliente = document.getElementById("buscarCliente");
  const filtroEstado = document.getElementById("filtroEstado");

  const inputIdCliente = document.getElementById("id_cliente");
  const inputCodigoCliente = document.getElementById("codigo_cliente");
  const inputNombreCliente = document.getElementById("nombre_cliente");
  const inputRfc = document.getElementById("rfc");
  const inputCorreo = document.getElementById("correo");
  const inputTelefono = document.getElementById("telefono");
  const inputDireccion = document.getElementById("direccion");
  const selectEstado = document.getElementById("estado");

  let modalCliente = null;
  let timerBusqueda = null;

  if (modalClienteEl) {
    modalCliente = new bootstrap.Modal(modalClienteEl, {
      backdrop: "static",
      keyboard: false,
    });
  }

  cargarClientes();

  if (btnNuevoCliente) {
    btnNuevoCliente.addEventListener("click", function () {
      abrirModalNuevoCliente();
    });
  }

  if (buscarCliente) {
    buscarCliente.addEventListener("input", function () {
      clearTimeout(timerBusqueda);
      timerBusqueda = setTimeout(function () {
        cargarClientes();
      }, 350);
    });
  }

  if (filtroEstado) {
    filtroEstado.addEventListener("change", function () {
      cargarClientes();
    });
  }

  if (tbodyClientes) {
    tbodyClientes.addEventListener("click", function (e) {
      const btnEditar = e.target.closest(".btnEditarCliente");
      const btnEstado = e.target.closest(".btnCambiarEstadoCliente");

      if (btnEditar) {
        const idCliente = btnEditar.getAttribute("data-id");
        abrirModalEditarCliente(idCliente);
        return;
      }

      if (btnEstado) {
        const idCliente = btnEstado.getAttribute("data-id");
        const estadoActual = btnEstado.getAttribute("data-estado");
        confirmarCambioEstado(idCliente, estadoActual);
      }
    });
  }

  if (formCliente) {
    formCliente.addEventListener("submit", function (e) {
      e.preventDefault();

      if (!validarFormularioCliente()) {
        return;
      }

      guardarCliente();
    });
  }

  function cargarClientes() {
    if (!tbodyClientes) return;

    const buscar = buscarCliente ? buscarCliente.value.trim() : "";
    const estado = filtroEstado ? filtroEstado.value.trim() : "";

    tbodyClientes.innerHTML = `
      <tr>
        <td colspan="7">
          <div class="empty-state">
            <div class="empty-state-icon">
              <i data-feather="loader"></i>
            </div>
            <h6 class="fw-bold mb-1">Cargando clientes...</h6>
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
    xhr.open("GET", base_url + "clientes/listar?" + params, true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        pintarErrorTabla("No se pudo conectar con el servidor.");
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          pintarErrorTabla(res.msg || "No fue posible listar los clientes.");
          return;
        }

        pintarClientes(res.data || []);
      } catch (error) {
        console.error(error, xhr.responseText);
        pintarErrorTabla("La respuesta del servidor no es válida.");
      }
    };

    xhr.send();
  }

  function pintarClientes(clientes) {
    if (!tbodyClientes) return;

    if (!clientes.length) {
      tbodyClientes.innerHTML = `
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <div class="empty-state-icon">
                <i data-feather="users"></i>
              </div>
              <h6 class="fw-bold mb-1">No hay clientes registrados</h6>
              <div>Presiona “Nuevo cliente” para registrar el primero.</div>
            </div>
          </td>
        </tr>
      `;
      refrescarIconos();
      return;
    }

    let html = "";

    clientes.forEach(function (cliente) {
      const estado = Number(cliente.estado || 0);
      const activo = estado === 1;

      const badgeEstado = activo
        ? `<span class="badge-activo">Activo</span>`
        : `<span class="badge-inactivo">Inactivo</span>`;

      const iconoEstado = activo ? "user-x" : "user-check";
      const tituloEstado = activo ? "Dar de baja" : "Activar";
      const claseEstado = activo ? "text-danger" : "text-success";

      html += `
        <tr>
          <td>
            <strong>${escapeHtml(cliente.codigo_cliente || "")}</strong>
          </td>
          <td>${escapeHtml(cliente.nombre_cliente || "")}</td>
          <td>${escapeHtml(cliente.rfc || "No aplica")}</td>
          <td>${escapeHtml(cliente.correo || "No aplica")}</td>
          <td>${escapeHtml(cliente.telefono || "No aplica")}</td>
          <td>${badgeEstado}</td>
          <td class="text-center">
            <button type="button"
                    class="btn-icon btnEditarCliente me-1"
                    data-id="${cliente.id_cliente}"
                    title="Editar cliente">
              <i data-feather="edit-3"></i>
            </button>

            <button type="button"
                    class="btn-icon btnCambiarEstadoCliente ${claseEstado}"
                    data-id="${cliente.id_cliente}"
                    data-estado="${estado}"
                    title="${tituloEstado}">
              <i data-feather="${iconoEstado}"></i>
            </button>
          </td>
        </tr>
      `;
    });

    tbodyClientes.innerHTML = html;
    refrescarIconos();
  }

  function abrirModalNuevoCliente() {
    limpiarFormularioCliente();

    if (modalTitle) {
      modalTitle.textContent = "Registrar cliente";
    }

    if (selectEstado) {
      selectEstado.value = "1";
    }

    if (modalCliente) {
      modalCliente.show();
    }

    setTimeout(function () {
      if (inputCodigoCliente) {
        inputCodigoCliente.focus();
      }
    }, 350);
  }

  function abrirModalEditarCliente(idCliente) {
    if (!idCliente) return;

    const xhr = new XMLHttpRequest();
    xhr.open(
      "GET",
      base_url + "clientes/obtener/" + encodeURIComponent(idCliente),
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
            text: res.msg || "No fue posible obtener el cliente.",
          });
          return;
        }

        llenarFormularioCliente(res.data);

        if (modalTitle) {
          modalTitle.textContent = "Editar cliente";
        }

        if (modalCliente) {
          modalCliente.show();
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

  function llenarFormularioCliente(cliente) {
    limpiarFormularioCliente();

    if (inputIdCliente) inputIdCliente.value = cliente.id_cliente || "";
    if (inputCodigoCliente)
      inputCodigoCliente.value = cliente.codigo_cliente || "";
    if (inputNombreCliente)
      inputNombreCliente.value = cliente.nombre_cliente || "";
    if (inputRfc) inputRfc.value = cliente.rfc || "";
    if (inputCorreo) inputCorreo.value = cliente.correo || "";
    if (inputTelefono) inputTelefono.value = cliente.telefono || "";
    if (inputDireccion) inputDireccion.value = cliente.direccion || "";
    if (selectEstado) selectEstado.value = String(cliente.estado ?? "1");
  }

  function guardarCliente() {
    const idCliente = inputIdCliente ? inputIdCliente.value.trim() : "";
    const url = idCliente
      ? base_url + "clientes/actualizar"
      : base_url + "clientes/registrar";

    const formData = new FormData(formCliente);

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
            text: res.msg || "No fue posible guardar el cliente.",
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

        if (modalCliente) {
          modalCliente.hide();
        }

        limpiarFormularioCliente();
        cargarClientes();
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

  function confirmarCambioEstado(idCliente, estadoActual) {
    const estaActivo = Number(estadoActual) === 1;
    const nuevoEstado = estaActivo ? 0 : 1;

    Swal.fire({
      icon: estaActivo ? "warning" : "question",
      title: estaActivo ? "Dar de baja cliente" : "Activar cliente",
      text: estaActivo
        ? "El cliente quedará inactivo y no debería usarse para nuevas facturas."
        : "El cliente volverá a estar activo.",
      showCancelButton: true,
      confirmButtonText: estaActivo ? "Sí, dar de baja" : "Sí, activar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: estaActivo ? "#dc2626" : "#0d47a1",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      cambiarEstadoCliente(idCliente, nuevoEstado);
    });
  }

  function cambiarEstadoCliente(idCliente, nuevoEstado) {
    const formData = new FormData();
    formData.append("id_cliente", idCliente);
    formData.append("estado", nuevoEstado);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", base_url + "clientes/cambiarEstado", true);

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

        cargarClientes();
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

  function limpiarFormularioCliente() {
    if (formCliente) {
      formCliente.reset();
    }

    if (inputIdCliente) inputIdCliente.value = "";

    limpiarValidaciones();
    setLoading(false);
  }

  function validarFormularioCliente() {
    const codigoCliente = inputCodigoCliente
      ? inputCodigoCliente.value.trim()
      : "";
    const nombreCliente = inputNombreCliente
      ? inputNombreCliente.value.trim()
      : "";
    const correo = inputCorreo ? inputCorreo.value.trim() : "";
    const telefono = inputTelefono ? inputTelefono.value.trim() : "";

    limpiarValidaciones();

    if (codigoCliente === "") {
      marcarInvalido(inputCodigoCliente);
      alertaValidacion("Código requerido", "Ingresa el código del cliente.");
      return false;
    }

    if (codigoCliente.length > 30) {
      marcarInvalido(inputCodigoCliente);
      alertaValidacion(
        "Código inválido",
        "El código del cliente no puede superar 30 caracteres.",
      );
      return false;
    }

    if (nombreCliente === "") {
      marcarInvalido(inputNombreCliente);
      alertaValidacion("Nombre requerido", "Ingresa el nombre del cliente.");
      return false;
    }

    if (nombreCliente.length > 150) {
      marcarInvalido(inputNombreCliente);
      alertaValidacion(
        "Nombre inválido",
        "El nombre del cliente no puede superar 150 caracteres.",
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

    if (telefono !== "" && telefono.length > 30) {
      marcarInvalido(inputTelefono);
      alertaValidacion(
        "Teléfono inválido",
        "El teléfono no puede superar 30 caracteres.",
      );
      return false;
    }

    marcarValido(inputCodigoCliente);
    marcarValido(inputNombreCliente);

    if (correo !== "") {
      marcarValido(inputCorreo);
    }

    return true;
  }

  function limpiarValidaciones() {
    const campos = [
      inputCodigoCliente,
      inputNombreCliente,
      inputRfc,
      inputCorreo,
      inputTelefono,
      inputDireccion,
      selectEstado,
    ];

    campos.forEach(function (campo) {
      if (campo) {
        campo.classList.remove("is-invalid", "is-valid");
      }
    });
  }

  function pintarErrorTabla(mensaje) {
    if (!tbodyClientes) return;

    tbodyClientes.innerHTML = `
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
    if (!btnGuardarCliente || !guardarText || !guardarLoading) return;

    if (state) {
      btnGuardarCliente.disabled = true;
      guardarText.classList.add("d-none");
      guardarLoading.classList.remove("d-none");
    } else {
      btnGuardarCliente.disabled = false;
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
