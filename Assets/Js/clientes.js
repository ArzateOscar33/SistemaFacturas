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

  /*
   * DIRECCIONES
   */
  const modalDireccionesEl = document.getElementById("modalDireccionesCliente");
  const txtClienteDirecciones = document.getElementById(
    "txtClienteDirecciones",
  );
  const dirIdCliente = document.getElementById("dir_id_cliente");
  const formDireccionCliente = document.getElementById("formDireccionCliente");
  const inputIdDireccion = document.getElementById("id_direccion");
  const inputDireccionIdCliente = document.getElementById(
    "direccion_id_cliente",
  );
  const inputAliasDireccion = document.getElementById("alias_direccion");
  const inputDireccionCliente = document.getElementById("direccion_cliente");
  const selectEsPrincipal = document.getElementById("es_principal");
  const selectEstadoDireccion = document.getElementById("estado_direccion");
  const tbodyDireccionesCliente = document.getElementById(
    "tbodyDireccionesCliente",
  );
  const btnGuardarDireccion = document.getElementById("btnGuardarDireccion");
  const btnCancelarEdicionDireccion = document.getElementById(
    "btnCancelarEdicionDireccion",
  );
  const btnRecargarDirecciones = document.getElementById(
    "btnRecargarDirecciones",
  );
  const tituloFormDireccion = document.getElementById("tituloFormDireccion");

  const direccionGuardarText = btnGuardarDireccion
    ? btnGuardarDireccion.querySelector(".direccion-guardar-text")
    : null;

  const direccionGuardarLoading = btnGuardarDireccion
    ? btnGuardarDireccion.querySelector(".direccion-guardar-loading")
    : null;

  let modalCliente = null;
  let modalDirecciones = null;
  let timerBusqueda = null;
  let clienteDireccionesActual = null;

  if (modalClienteEl) {
    modalCliente = new bootstrap.Modal(modalClienteEl, {
      backdrop: "static",
      keyboard: false,
    });
  }

  if (modalDireccionesEl) {
    modalDirecciones = new bootstrap.Modal(modalDireccionesEl, {
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
      const btnDirecciones = e.target.closest(".btnDireccionesCliente");

      if (btnEditar) {
        const idCliente = btnEditar.getAttribute("data-id");
        abrirModalEditarCliente(idCliente);
        return;
      }

      if (btnEstado) {
        const idCliente = btnEstado.getAttribute("data-id");
        const estadoActual = btnEstado.getAttribute("data-estado");
        confirmarCambioEstado(idCliente, estadoActual);
        return;
      }

      if (btnDirecciones) {
        const idCliente = btnDirecciones.getAttribute("data-id");
        const nombreCliente = btnDirecciones.getAttribute("data-nombre") || "";
        abrirModalDirecciones(idCliente, nombreCliente);
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

  if (formDireccionCliente) {
    formDireccionCliente.addEventListener("submit", function (e) {
      e.preventDefault();

      if (!validarFormularioDireccion()) {
        return;
      }

      guardarDireccionCliente();
    });
  }

  if (btnCancelarEdicionDireccion) {
    btnCancelarEdicionDireccion.addEventListener("click", function () {
      limpiarFormularioDireccion();
    });
  }

  if (btnRecargarDirecciones) {
    btnRecargarDirecciones.addEventListener("click", function () {
      const idCliente = dirIdCliente ? dirIdCliente.value.trim() : "";

      if (idCliente) {
        cargarDireccionesCliente(idCliente);
      }
    });
  }

  if (modalDireccionesEl) {
    modalDireccionesEl.addEventListener("hidden.bs.modal", function () {
      limpiarFormularioDireccion();
      clienteDireccionesActual = null;

      if (dirIdCliente) {
        dirIdCliente.value = "";
      }

      if (inputDireccionIdCliente) {
        inputDireccionIdCliente.value = "";
      }

      if (txtClienteDirecciones) {
        txtClienteDirecciones.textContent =
          "Administra las direcciones de facturación del cliente seleccionado.";
      }
    });
  }

  if (tbodyDireccionesCliente) {
    tbodyDireccionesCliente.addEventListener("click", function (e) {
      const btnEditar = e.target.closest(".btnEditarDireccion");
      const btnEliminar = e.target.closest(".btnEliminarDireccion");
      const btnPrincipal = e.target.closest(".btnMarcarPrincipalDireccion");

      if (btnEditar) {
        const idDireccion = btnEditar.getAttribute("data-id");
        obtenerDireccionParaEditar(idDireccion);
        return;
      }

      if (btnEliminar) {
        const idDireccion = btnEliminar.getAttribute("data-id");
        const idCliente = btnEliminar.getAttribute("data-cliente");
        confirmarEliminarDireccion(idDireccion, idCliente);
        return;
      }

      if (btnPrincipal) {
        const idDireccion = btnPrincipal.getAttribute("data-id");
        const idCliente = btnPrincipal.getAttribute("data-cliente");
        confirmarDireccionPrincipal(idDireccion, idCliente);
      }
    });
  }

  /* ============================================================
     CLIENTES
  ============================================================ */

  function cargarClientes() {
    if (!tbodyClientes) return;

    const buscar = buscarCliente ? buscarCliente.value.trim() : "";
    const estado = filtroEstado ? filtroEstado.value.trim() : "";

    tbodyClientes.innerHTML = `
      <tr>
        <td colspan="8">
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
          <td colspan="8">
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
      const totalDirecciones = Number(cliente.total_direcciones || 0);

      const badgeEstado = activo
        ? `<span class="badge-activo">Activo</span>`
        : `<span class="badge-inactivo">Inactivo</span>`;

      const badgeDirecciones =
        totalDirecciones > 0
          ? `<span class="badge-activo">${totalDirecciones}</span>`
          : `<span class="badge-inactivo">0</span>`;

      const iconoEstado = activo ? "user-x" : "user-check";
      const tituloEstado = activo ? "Dar de baja" : "Activar";
      const claseEstado = activo ? "text-danger" : "text-success";

      html += `
        <tr>
          <td>
            <strong>${escapeHtml(cliente.codigo_cliente || "")}</strong>
          </td>
          <td>
            <div class="fw-semibold">${escapeHtml(cliente.nombre_cliente || "")}</div>
            ${
              cliente.direccion_principal
                ? `<div class="text-secondary small">${escapeHtml(cliente.direccion_principal)}</div>`
                : `<div class="text-secondary small">Sin dirección principal</div>`
            }
          </td>
          <td>${escapeHtml(cliente.rfc || "No aplica")}</td>
          <td>${escapeHtml(cliente.correo || "No aplica")}</td>
          <td>${escapeHtml(cliente.telefono || "No aplica")}</td>
          <td class="text-center">
            <button type="button"
                    class="btn btn-sm btn-light border rounded-3 btnDireccionesCliente"
                    data-id="${cliente.id_cliente}"
                    data-nombre="${escapeAttr(cliente.nombre_cliente || "")}"
                    title="Administrar direcciones">
              <i data-feather="map-pin" class="me-1"></i>
              ${badgeDirecciones}
            </button>
          </td>
          <td>${badgeEstado}</td>
          <td class="text-center">
            <button type="button"
                    class="btn-icon btnEditarCliente me-1"
                    data-id="${cliente.id_cliente}"
                    title="Editar cliente">
              <i data-feather="edit-3"></i>
            </button>

            <button type="button"
                    class="btn-icon btnDireccionesCliente me-1"
                    data-id="${cliente.id_cliente}"
                    data-nombre="${escapeAttr(cliente.nombre_cliente || "")}"
                    title="Direcciones">
              <i data-feather="map-pin"></i>
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

  /* ============================================================
     DIRECCIONES
  ============================================================ */

  function abrirModalDirecciones(idCliente, nombreCliente) {
    if (!idCliente) return;

    clienteDireccionesActual = {
      id_cliente: idCliente,
      nombre_cliente: nombreCliente || "",
    };

    if (dirIdCliente) {
      dirIdCliente.value = idCliente;
    }

    if (inputDireccionIdCliente) {
      inputDireccionIdCliente.value = idCliente;
    }

    if (txtClienteDirecciones) {
      txtClienteDirecciones.textContent =
        "Cliente: " + (nombreCliente || "Cliente seleccionado");
    }

    limpiarFormularioDireccion();
    cargarDireccionesCliente(idCliente);

    if (modalDirecciones) {
      modalDirecciones.show();
    }
  }

  function cargarDireccionesCliente(idCliente) {
    if (!tbodyDireccionesCliente) return;

    tbodyDireccionesCliente.innerHTML = `
      <tr>
        <td colspan="5">
          <div class="empty-state py-4">
            <div class="empty-state-icon">
              <i data-feather="loader"></i>
            </div>
            <h6 class="fw-bold mb-1">Cargando direcciones...</h6>
            <div>Espera un momento.</div>
          </div>
        </td>
      </tr>
    `;

    refrescarIconos();

    const xhr = new XMLHttpRequest();
    xhr.open(
      "GET",
      base_url + "clientes/listarDirecciones/" + encodeURIComponent(idCliente),
      true,
    );

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        pintarErrorDirecciones("No se pudo conectar con el servidor.");
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          pintarErrorDirecciones(
            res.msg || "No fue posible listar las direcciones.",
          );
          return;
        }

        pintarDireccionesCliente(res.data || []);
      } catch (error) {
        console.error(error, xhr.responseText);
        pintarErrorDirecciones("La respuesta del servidor no es válida.");
      }
    };

    xhr.send();
  }

  function pintarDireccionesCliente(direcciones) {
    if (!tbodyDireccionesCliente) return;

    if (!direcciones.length) {
      tbodyDireccionesCliente.innerHTML = `
        <tr>
          <td colspan="5">
            <div class="empty-state py-4">
              <div class="empty-state-icon">
                <i data-feather="map-pin"></i>
              </div>
              <h6 class="fw-bold mb-1">Sin direcciones registradas</h6>
              <div>Agrega una dirección para este cliente.</div>
            </div>
          </td>
        </tr>
      `;
      refrescarIconos();
      return;
    }

    let html = "";

    direcciones.forEach(function (direccion) {
      const estado = Number(direccion.estado || 0);
      const activa = estado === 1;
      const principal = Number(direccion.es_principal || 0) === 1;

      const badgePrincipal = principal
        ? `<span class="badge-activo">Sí</span>`
        : `<span class="badge-inactivo">No</span>`;

      const badgeEstado = activa
        ? `<span class="badge-activo">Activa</span>`
        : `<span class="badge-inactivo">Inactiva</span>`;

      html += `
        <tr>
          <td>${escapeHtml(direccion.alias || "Sin alias")}</td>
          <td>
            <div>${escapeHtml(direccion.direccion || "")}</div>
          </td>
          <td class="text-center">${badgePrincipal}</td>
          <td class="text-center">${badgeEstado}</td>
          <td class="text-center">
            <button type="button"
                    class="btn-icon btnEditarDireccion me-1"
                    data-id="${direccion.id_direccion}"
                    title="Editar dirección">
              <i data-feather="edit-3"></i>
            </button>

            ${
              principal
                ? ""
                : `
                  <button type="button"
                          class="btn-icon btnMarcarPrincipalDireccion text-success me-1"
                          data-id="${direccion.id_direccion}"
                          data-cliente="${direccion.id_cliente}"
                          title="Marcar como principal">
                    <i data-feather="check-circle"></i>
                  </button>
                `
            }

            <button type="button"
                    class="btn-icon btnEliminarDireccion text-danger"
                    data-id="${direccion.id_direccion}"
                    data-cliente="${direccion.id_cliente}"
                    title="Eliminar dirección">
              <i data-feather="trash-2"></i>
            </button>
          </td>
        </tr>
      `;
    });

    tbodyDireccionesCliente.innerHTML = html;
    refrescarIconos();
  }

  function obtenerDireccionParaEditar(idDireccion) {
    if (!idDireccion) return;

    const xhr = new XMLHttpRequest();
    xhr.open(
      "GET",
      base_url + "clientes/obtenerDireccion/" + encodeURIComponent(idDireccion),
      true,
    );

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
            text: res.msg || "No fue posible obtener la dirección.",
          });
          return;
        }

        llenarFormularioDireccion(res.data);
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

  function llenarFormularioDireccion(direccion) {
    if (inputIdDireccion) inputIdDireccion.value = direccion.id_direccion || "";
    if (inputDireccionIdCliente)
      inputDireccionIdCliente.value = direccion.id_cliente || "";
    if (inputAliasDireccion) inputAliasDireccion.value = direccion.alias || "";
    if (inputDireccionCliente)
      inputDireccionCliente.value = direccion.direccion || "";
    if (selectEsPrincipal)
      selectEsPrincipal.value = String(direccion.es_principal ?? "0");
    if (selectEstadoDireccion)
      selectEstadoDireccion.value = String(direccion.estado ?? "1");

    if (tituloFormDireccion) {
      tituloFormDireccion.textContent = "Editar dirección";
    }

    if (btnCancelarEdicionDireccion) {
      btnCancelarEdicionDireccion.classList.remove("d-none");
    }

    limpiarValidacionesDireccion();

    setTimeout(function () {
      if (inputAliasDireccion) {
        inputAliasDireccion.focus();
      }
    }, 150);
  }

  function guardarDireccionCliente() {
    const idDireccion = inputIdDireccion ? inputIdDireccion.value.trim() : "";
    const idCliente = inputDireccionIdCliente
      ? inputDireccionIdCliente.value.trim()
      : "";

    if (!idCliente) {
      Swal.fire({
        icon: "warning",
        title: "Cliente requerido",
        text: "No se encontró el cliente para guardar la dirección.",
      });
      return;
    }

    const url = idDireccion
      ? base_url + "clientes/actualizarDireccion"
      : base_url + "clientes/registrarDireccion";

    const formData = new FormData(formDireccionCliente);

    setLoadingDireccion(true);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      setLoadingDireccion(false);

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
            text: res.msg || "No fue posible guardar la dirección.",
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

        limpiarFormularioDireccion();
        cargarDireccionesCliente(idCliente);
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

  function confirmarEliminarDireccion(idDireccion, idCliente) {
    Swal.fire({
      icon: "warning",
      title: "Eliminar dirección",
      text: "La dirección quedará inactiva y ya no aparecerá como opción activa.",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#dc2626",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      eliminarDireccionCliente(idDireccion, idCliente);
    });
  }

  function eliminarDireccionCliente(idDireccion, idCliente) {
    const formData = new FormData();
    formData.append("id_direccion", idDireccion);
    formData.append("id_cliente", idCliente);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", base_url + "clientes/eliminarDireccion", true);

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
            text: res.msg || "No fue posible eliminar la dirección.",
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

        limpiarFormularioDireccion();
        cargarDireccionesCliente(idCliente);
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

  function confirmarDireccionPrincipal(idDireccion, idCliente) {
    Swal.fire({
      icon: "question",
      title: "Marcar como principal",
      text: "Esta dirección se sincronizará como dirección principal del cliente.",
      showCancelButton: true,
      confirmButtonText: "Sí, marcar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#0d47a1",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      marcarDireccionPrincipal(idDireccion, idCliente);
    });
  }

  function marcarDireccionPrincipal(idDireccion, idCliente) {
    const formData = new FormData();
    formData.append("id_direccion", idDireccion);
    formData.append("id_cliente", idCliente);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", base_url + "clientes/marcarDireccionPrincipal", true);

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
            text:
              res.msg || "No fue posible marcar la dirección como principal.",
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

        limpiarFormularioDireccion();
        cargarDireccionesCliente(idCliente);
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

  /* ============================================================
     LIMPIEZA / VALIDACIONES CLIENTE
  ============================================================ */

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

  /* ============================================================
     LIMPIEZA / VALIDACIONES DIRECCIONES
  ============================================================ */

  function limpiarFormularioDireccion() {
    if (formDireccionCliente) {
      formDireccionCliente.reset();
    }

    const idClienteActual = dirIdCliente ? dirIdCliente.value.trim() : "";

    if (inputIdDireccion) inputIdDireccion.value = "";
    if (inputDireccionIdCliente)
      inputDireccionIdCliente.value = idClienteActual;

    if (selectEsPrincipal) selectEsPrincipal.value = "0";
    if (selectEstadoDireccion) selectEstadoDireccion.value = "1";

    if (tituloFormDireccion) {
      tituloFormDireccion.textContent = "Nueva dirección";
    }

    if (btnCancelarEdicionDireccion) {
      btnCancelarEdicionDireccion.classList.add("d-none");
    }

    limpiarValidacionesDireccion();
    setLoadingDireccion(false);
  }

  function validarFormularioDireccion() {
    const idCliente = inputDireccionIdCliente
      ? inputDireccionIdCliente.value.trim()
      : "";
    const alias = inputAliasDireccion ? inputAliasDireccion.value.trim() : "";
    const direccion = inputDireccionCliente
      ? inputDireccionCliente.value.trim()
      : "";

    limpiarValidacionesDireccion();

    if (idCliente === "") {
      alertaValidacion(
        "Cliente requerido",
        "No se encontró el cliente para registrar la dirección.",
      );
      return false;
    }

    if (alias.length > 100) {
      marcarInvalido(inputAliasDireccion);
      alertaValidacion(
        "Alias inválido",
        "El alias no puede superar 100 caracteres.",
      );
      return false;
    }

    if (direccion === "") {
      marcarInvalido(inputDireccionCliente);
      alertaValidacion(
        "Dirección requerida",
        "Ingresa la dirección de facturación.",
      );
      return false;
    }

    marcarValido(inputDireccionCliente);

    if (alias !== "") {
      marcarValido(inputAliasDireccion);
    }

    return true;
  }

  function limpiarValidacionesDireccion() {
    const campos = [
      inputAliasDireccion,
      inputDireccionCliente,
      selectEsPrincipal,
      selectEstadoDireccion,
    ];

    campos.forEach(function (campo) {
      if (campo) {
        campo.classList.remove("is-invalid", "is-valid");
      }
    });
  }

  /* ============================================================
     HELPERS UI
  ============================================================ */

  function pintarErrorTabla(mensaje) {
    if (!tbodyClientes) return;

    tbodyClientes.innerHTML = `
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

  function pintarErrorDirecciones(mensaje) {
    if (!tbodyDireccionesCliente) return;

    tbodyDireccionesCliente.innerHTML = `
      <tr>
        <td colspan="5">
          <div class="empty-state py-4">
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

  function setLoadingDireccion(state) {
    if (
      !btnGuardarDireccion ||
      !direccionGuardarText ||
      !direccionGuardarLoading
    ) {
      return;
    }

    if (state) {
      btnGuardarDireccion.disabled = true;
      direccionGuardarText.classList.add("d-none");
      direccionGuardarLoading.classList.remove("d-none");
    } else {
      btnGuardarDireccion.disabled = false;
      direccionGuardarText.classList.remove("d-none");
      direccionGuardarLoading.classList.add("d-none");
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

  function escapeAttr(value) {
    return escapeHtml(value).replace(/`/g, "&#096;");
  }
});
