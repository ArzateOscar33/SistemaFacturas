document.addEventListener("DOMContentLoaded", function () {
  const formConfiguracion = document.getElementById("formConfiguracionEmpresa");

  const inputIdEmpresa = document.getElementById("id_empresa");
  const inputNombreEmpresa = document.getElementById("nombre_empresa");
  const inputTaxId = document.getElementById("tax_id");
  const inputTelefono = document.getElementById("telefono");
  const inputCorreo = document.getElementById("correo");
  const inputDireccion = document.getElementById("direccion");
  const inputColorPrincipal = document.getElementById("color_principal");
  const inputColorPicker = document.getElementById("color_picker");
  const inputLogo = document.getElementById("logo");
  const inputTextoPiePagina = document.getElementById("texto_pie_pagina");

  const btnGuardar = document.getElementById("btnGuardarConfiguracion");
  const btnRestablecer = document.getElementById("btnRestablecerConfiguracion");

  const guardarText = btnGuardar
    ? btnGuardar.querySelector(".guardar-text")
    : null;
  const guardarLoading = btnGuardar
    ? btnGuardar.querySelector(".guardar-loading")
    : null;

  const logoPreview = document.getElementById("logoPreview");
  const logoEmpty = document.getElementById("logoEmpty");

  const previewNombreEmpresa = document.getElementById("previewNombreEmpresa");
  const previewTaxId = document.getElementById("previewTaxId");
  const previewTelefono = document.getElementById("previewTelefono");
  const previewCorreo = document.getElementById("previewCorreo");
  const previewDireccion = document.getElementById("previewDireccion");

  const folioSerie = document.getElementById("folioSerie");
  const folioUltimoNumero = document.getElementById("folioUltimoNumero");
  const folioSiguiente = document.getElementById("folioSiguiente");

  let datosOriginales = null;

  cargarConfiguracion();

  if (formConfiguracion) {
    formConfiguracion.addEventListener("submit", function (e) {
      e.preventDefault();

      if (!validarFormulario()) {
        return;
      }

      guardarConfiguracion();
    });
  }

  if (btnRestablecer) {
    btnRestablecer.addEventListener("click", function () {
      restablecerFormulario();
    });
  }

  if (inputColorPrincipal && inputColorPicker) {
    inputColorPrincipal.addEventListener("input", function () {
      const color = inputColorPrincipal.value.trim();

      if (validarColorHex(color)) {
        inputColorPicker.value = color;
      }
    });

    inputColorPicker.addEventListener("input", function () {
      inputColorPrincipal.value = inputColorPicker.value;
    });
  }

  [
    inputNombreEmpresa,
    inputTaxId,
    inputTelefono,
    inputCorreo,
    inputDireccion,
  ].forEach(function (input) {
    if (input) {
      input.addEventListener("input", actualizarPreviewTexto);
    }
  });

  if (inputLogo) {
    inputLogo.addEventListener("change", function () {
      previewLogoLocal();
    });
  }

  function cargarConfiguracion() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "configuracion/obtener", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      if (xhr.status !== 200) {
        Swal.fire({
          icon: "error",
          title: "Error de conexión",
          text: "No se pudo obtener la configuración.",
        });
        return;
      }

      try {
        const res = JSON.parse(xhr.responseText);

        if (!res.ok) {
          Swal.fire({
            icon: "warning",
            title: "Aviso",
            text: res.msg || "No fue posible cargar la configuración.",
          });
          return;
        }

        datosOriginales = res.data || null;

        llenarEmpresa(res.data.empresa || {});
        llenarFolio(res.data.folio || {}, res.data.siguiente_folio || null);
        actualizarPreviewTexto();
      } catch (error) {
        console.error(error, xhr.responseText);

        Swal.fire({
          icon: "error",
          title: "Error inesperado",
          text: "La respuesta del servidor no es válida.",
        });
      }
    };

    xhr.send();
  }

  function llenarEmpresa(empresa) {
    if (inputIdEmpresa) inputIdEmpresa.value = empresa.id_empresa || "";
    if (inputNombreEmpresa)
      inputNombreEmpresa.value = empresa.nombre_empresa || "";
    if (inputTaxId) inputTaxId.value = empresa.tax_id || "";
    if (inputTelefono) inputTelefono.value = empresa.telefono || "";
    if (inputCorreo) inputCorreo.value = empresa.correo || "";
    if (inputDireccion) inputDireccion.value = empresa.direccion || "";
    if (inputColorPrincipal)
      inputColorPrincipal.value = empresa.color_principal || "#0d47a1";
    if (inputTextoPiePagina)
      inputTextoPiePagina.value = empresa.texto_pie_pagina || "";

    if (inputColorPicker) {
      const color = empresa.color_principal || "#0d47a1";
      inputColorPicker.value = validarColorHex(color) ? color : "#0d47a1";
    }

    if (empresa.logo) {
      mostrarLogo(base_url + empresa.logo);
    } else {
      ocultarLogo();
    }
  }

  function llenarFolio(folio, siguiente) {
    const serie = folio.serie || "INV";
    const ultimo = Number(folio.ultimo_numero || 0);

    if (folioSerie) folioSerie.textContent = serie;
    if (folioUltimoNumero) folioUltimoNumero.textContent = ultimo;
    if (folioSiguiente) {
      folioSiguiente.textContent =
        siguiente || `${serie}-${String(ultimo + 1).padStart(8, "0")}`;
    }
  }

  function guardarConfiguracion() {
    const formData = new FormData(formConfiguracion);

    setLoading(true);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", base_url + "configuracion/guardar", true);

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
            text: res.msg || "No fue posible guardar la configuración.",
          });
          return;
        }

        Swal.fire({
          icon: "success",
          title: "Correcto",
          text: res.msg || "Configuración guardada correctamente.",
          timer: 1400,
          showConfirmButton: false,
        });

        if (inputLogo) {
          inputLogo.value = "";
        }

        cargarConfiguracion();
      } catch (error) {
        console.error(error, xhr.responseText);

        Swal.fire({
          icon: "error",
          title: "Error inesperado",
          text: "La respuesta del servidor no es válida.",
        });
      }
    };

    xhr.send(formData);
  }

  function validarFormulario() {
    limpiarValidaciones();

    const nombreEmpresa = inputNombreEmpresa
      ? inputNombreEmpresa.value.trim()
      : "";
    const correo = inputCorreo ? inputCorreo.value.trim() : "";
    const color = inputColorPrincipal ? inputColorPrincipal.value.trim() : "";

    if (nombreEmpresa === "") {
      marcarInvalido(inputNombreEmpresa);
      alertaValidacion("Nombre requerido", "Ingresa el nombre de la empresa.");
      return false;
    }

    if (nombreEmpresa.length > 150) {
      marcarInvalido(inputNombreEmpresa);
      alertaValidacion(
        "Nombre inválido",
        "El nombre de la empresa no puede superar 150 caracteres.",
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

    if (color !== "" && !validarColorHex(color)) {
      marcarInvalido(inputColorPrincipal);
      alertaValidacion(
        "Color inválido",
        "El color principal debe tener formato HEX. Ejemplo: #0d47a1",
      );
      return false;
    }

    if (inputLogo && inputLogo.files.length > 0) {
      const archivo = inputLogo.files[0];
      const permitidos = ["image/jpeg", "image/png", "image/webp"];
      const maxSize = 2 * 1024 * 1024;

      if (!permitidos.includes(archivo.type)) {
        alertaValidacion(
          "Logo inválido",
          "El logo debe ser una imagen JPG, PNG o WEBP.",
        );
        inputLogo.value = "";
        return false;
      }

      if (archivo.size > maxSize) {
        alertaValidacion(
          "Logo demasiado grande",
          "El logo no debe superar 2 MB.",
        );
        inputLogo.value = "";
        return false;
      }
    }

    marcarValido(inputNombreEmpresa);

    if (correo !== "") {
      marcarValido(inputCorreo);
    }

    if (color !== "") {
      marcarValido(inputColorPrincipal);
    }

    return true;
  }

  function actualizarPreviewTexto() {
    const nombre = inputNombreEmpresa ? inputNombreEmpresa.value.trim() : "";
    const taxId = inputTaxId ? inputTaxId.value.trim() : "";
    const telefono = inputTelefono ? inputTelefono.value.trim() : "";
    const correo = inputCorreo ? inputCorreo.value.trim() : "";
    const direccion = inputDireccion ? inputDireccion.value.trim() : "";

    if (previewNombreEmpresa) {
      previewNombreEmpresa.textContent = nombre || "Nombre de empresa";
    }

    if (previewTaxId) {
      previewTaxId.textContent = taxId || "Tax ID / RFC no configurado";
    }

    if (previewTelefono) {
      previewTelefono.textContent = telefono || "Teléfono no configurado";
    }

    if (previewCorreo) {
      previewCorreo.textContent = correo || "Correo no configurado";
    }

    if (previewDireccion) {
      previewDireccion.textContent = direccion || "Dirección no configurada";
    }
  }

  function previewLogoLocal() {
    if (!inputLogo || inputLogo.files.length === 0) {
      return;
    }

    const archivo = inputLogo.files[0];
    const permitidos = ["image/jpeg", "image/png", "image/webp"];

    if (!permitidos.includes(archivo.type)) {
      alertaValidacion(
        "Logo inválido",
        "Selecciona una imagen JPG, PNG o WEBP.",
      );
      inputLogo.value = "";
      return;
    }

    const urlTemporal = URL.createObjectURL(archivo);
    mostrarLogo(urlTemporal);
  }

  function mostrarLogo(src) {
    if (logoPreview) {
      logoPreview.src = src;
      logoPreview.classList.remove("d-none");
    }

    if (logoEmpty) {
      logoEmpty.classList.add("d-none");
    }

    refrescarIconos();
  }

  function ocultarLogo() {
    if (logoPreview) {
      logoPreview.src = "";
      logoPreview.classList.add("d-none");
    }

    if (logoEmpty) {
      logoEmpty.classList.remove("d-none");
    }

    refrescarIconos();
  }

  function restablecerFormulario() {
    if (!datosOriginales || !datosOriginales.empresa) {
      cargarConfiguracion();
      return;
    }

    Swal.fire({
      icon: "question",
      title: "Restablecer cambios",
      text: "Se descartarán los cambios no guardados.",
      showCancelButton: true,
      confirmButtonText: "Sí, restablecer",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#0d47a1",
    }).then(function (result) {
      if (!result.isConfirmed) return;

      llenarEmpresa(datosOriginales.empresa);
      llenarFolio(
        datosOriginales.folio || {},
        datosOriginales.siguiente_folio || null,
      );
      actualizarPreviewTexto();

      if (inputLogo) {
        inputLogo.value = "";
      }

      limpiarValidaciones();
    });
  }

  function limpiarValidaciones() {
    const campos = [
      inputNombreEmpresa,
      inputTaxId,
      inputTelefono,
      inputCorreo,
      inputDireccion,
      inputColorPrincipal,
      inputTextoPiePagina,
    ];

    campos.forEach(function (campo) {
      if (campo) {
        campo.classList.remove("is-invalid", "is-valid");
      }
    });
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

  function validarColorHex(color) {
    return /^#[0-9A-Fa-f]{6}$/.test(color);
  }

  function alertaValidacion(titulo, mensaje) {
    Swal.fire({
      icon: "warning",
      title: titulo,
      text: mensaje,
    });
  }

  function setLoading(state) {
    if (!btnGuardar || !guardarText || !guardarLoading) return;

    if (state) {
      btnGuardar.disabled = true;
      guardarText.classList.add("d-none");
      guardarLoading.classList.remove("d-none");
    } else {
      btnGuardar.disabled = false;
      guardarText.classList.remove("d-none");
      guardarLoading.classList.add("d-none");
    }
  }

  function refrescarIconos() {
    if (window.feather) {
      feather.replace();
    }
  }
});
