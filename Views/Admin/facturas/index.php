<?php require_once 'Views/Template/header-admin.php'; ?>

<main class="content">

    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="page-title">Facturas</h1>
            <p class="page-subtitle">
                Registra nuevas facturas, consulta el historial y descarga documentos en PDF.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="user-pill">
                <i data-feather="user" class="me-1"></i>
                <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
            </div>

            <button type="button" class="btn btn-primary-soft" id="btnNuevaFactura">
                <i data-feather="plus-circle" class="me-1"></i>
                Nueva factura
            </button>
        </div>
    </div>

    <div class="card-module mb-4">
        <div class="card-module-header">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <h5 class="card-module-title">Listado de facturas</h5>
                    <p class="card-module-subtitle">
                        Consulta facturas registradas, edita borradores o descarga el PDF.
                    </p>
                </div>

                <div class="col-lg-3">
                    <label for="buscarFactura" class="form-label">Buscar factura</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i data-feather="search"></i>
                        </span>
                        <input type="text"
                            class="form-control"
                            id="buscarFactura"
                            placeholder="Folio, cliente o usuario">
                    </div>
                </div>

                <div class="col-lg-2">
                    <label for="filtroEstadoFactura" class="form-label">Estado</label>
                    <select id="filtroEstadoFactura" class="form-select">
                        <option value="">Todos</option>
                        <option value="emitida">Emitidas</option>
                        <option value="borrador">Borradores</option>
                        <option value="cancelada">Canceladas</option>
                    </select>
                </div>

                <div class="col-lg-3">
                    <label class="form-label">Rango de fechas</label>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control" id="fechaInicio">
                        <input type="date" class="form-control" id="fechaFin">
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="tablaFacturas">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Subtotal</th>
                        <th>Impuesto</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th style="width: 170px;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyFacturas">
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i data-feather="database"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Sin datos cargados</h6>
                                <div>El listado se cargará desde el controlador de facturas.</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- MODAL FACTURA -->
<div class="modal fade" id="modalFactura" tabindex="-1" aria-labelledby="modalFacturaLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-xl-down modal-xl modal-dialog-centered">
        <div class="modal-content">

            <form id="formFactura" autocomplete="off" novalidate>

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalFacturaLabel">Nueva factura</h5>
                        <div class="text-secondary small">
                            Los campos marcados con <span class="required-mark">*</span> son obligatorios.
                        </div>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="id_factura" name="id_factura">

                    <div class="row g-4">

                        <!-- ENCABEZADO FACTURA -->
                        <div class="col-lg-8">

                            <div class="invoice-section">
                                <div class="invoice-section-title">
                                    <i data-feather="file-text"></i>
                                    Información de factura
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="id_folio" class="form-label">
                                            Serie <span class="required-mark">*</span>
                                        </label>
                                        <select id="id_folio" name="id_folio" class="form-select" required>
                                            <option value="">Seleccione una serie</option>
                                        </select>
                                        <div class="helper-text">
                                            El número se generará automáticamente con la serie seleccionada.
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="folio_factura" class="form-label">Folio</label>
                                        <input type="text"
                                            class="form-control"
                                            id="folio_factura"
                                            name="folio_factura"
                                            placeholder="Se genera automáticamente"
                                            readonly>
                                        <div class="helper-text">
                                            El folio se asigna al guardar la factura.
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="fecha_factura" class="form-label">
                                            Fecha <span class="required-mark">*</span>
                                        </label>
                                        <input type="date"
                                            class="form-control"
                                            id="fecha_factura"
                                            name="fecha_factura"
                                            required>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="estado_factura" class="form-label">Estado</label>
                                        <select id="estado_factura" name="estado_factura" class="form-select">
                                            <option value="emitida">Emitida</option>
                                            <option value="borrador">Borrador</option>
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label for="id_cliente" class="form-label">
                                            Cliente <span class="required-mark">*</span>
                                        </label>
                                        <select id="id_cliente" name="id_cliente" class="form-select" required>
                                            <option value="">Seleccione un cliente</option>
                                        </select>
                                        <div class="helper-text">
                                            Solo se mostrarán clientes activos.
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="id_cliente_direccion" class="form-label">
                                            Dirección de facturación <span class="required-mark">*</span>
                                        </label>
                                        <select id="id_cliente_direccion" name="id_cliente_direccion" class="form-select">
                                            <option value="">Seleccione una dirección</option>
                                        </select>
                                        <div class="helper-text">
                                            Se cargarán las direcciones del cliente seleccionado.
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="direccion_facturacion" class="form-label">
                                            Dirección usada en la factura <span class="required-mark">*</span>
                                        </label>
                                        <textarea class="form-control"
                                            id="direccion_facturacion"
                                            name="direccion_facturacion"
                                            rows="3"
                                            placeholder="Dirección de facturación que aparecerá en el PDF"
                                            readonly></textarea>
                                        <div class="helper-text">
                                            Esta dirección se guardará directamente en la factura. Puedes ajustarla sin cambiar el catálogo del cliente.
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="sales_man" class="form-label">Sales man</label>
                                        <input type="text"
                                            class="form-control"
                                            id="sales_man"
                                            name="sales_man"
                                            maxlength="120"
                                            placeholder="Ej. MONICA CHEE">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="terms" class="form-label">Terms</label>
                                        <input type="text"
                                            class="form-control"
                                            id="terms"
                                            name="terms"
                                            maxlength="80"
                                            placeholder="Ej. CASH">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="tasa_impuesto" class="form-label">Tax rate %</label>
                                        <input type="number"
                                            class="form-control"
                                            id="tasa_impuesto"
                                            name="tasa_impuesto"
                                            min="0"
                                            step="0.01"
                                            value="0">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="otros_cargos" class="form-label">Shipping & handling</label>
                                        <input type="number"
                                            class="form-control"
                                            id="otros_cargos"
                                            name="otros_cargos"
                                            min="0"
                                            step="0.01"
                                            value="0">
                                    </div>

                                    <div class="col-md-12">
                                        <label for="notas" class="form-label">Notas</label>
                                        <textarea class="form-control"
                                            id="notas"
                                            name="notas"
                                            rows="3"
                                            placeholder="Notas adicionales de la factura"></textarea>
                                    </div>

                                </div>
                            </div>

                        </div>

                        <!-- RESUMEN -->
                        <div class="col-lg-4">

                            <div class="invoice-summary-card">
                                <div class="invoice-summary-title">
                                    <i data-feather="dollar-sign"></i>
                                    Resumen
                                </div>

                                <div class="invoice-total-row">
                                    <span>Subtotal</span>
                                    <strong id="resumenSubtotal">$0.00</strong>
                                </div>

                                <div class="invoice-total-row">
                                    <span>Tax</span>
                                    <strong id="resumenImpuesto">$0.00</strong>
                                </div>

                                <div class="invoice-total-row">
                                    <span>Shipping & handling</span>
                                    <strong id="resumenOtrosCargos">$0.00</strong>
                                </div>

                                <div class="invoice-total-row invoice-total-final">
                                    <span>Total</span>
                                    <strong id="resumenTotal">$0.00</strong>
                                </div>

                                <div class="alert alert-primary mt-3 mb-0 rounded-4">
                                    <div class="d-flex gap-2">
                                        <div>
                                            <i data-feather="shield"></i>
                                        </div>
                                        <div class="small">
                                            El número de factura se asignará de forma segura al guardar, evitando duplicados.
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- DETALLE -->
                        <div class="col-12">

                            <div class="invoice-section">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                    <div class="invoice-section-title mb-0">
                                        <i data-feather="list"></i>
                                        Partidas de factura
                                    </div>

                                    <button type="button" class="btn btn-primary-soft btn-sm" id="btnAgregarPartida">
                                        <i data-feather="plus" class="me-1"></i>
                                        Agregar partida
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle invoice-detail-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 110px;">Quantity</th>
                                                <th>Description</th>
                                                <th style="width: 150px;">Unit price</th>
                                                <th style="width: 150px;">Total</th>
                                                <th style="width: 70px;" class="text-center">Quitar</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyPartidasFactura">
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
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border rounded-3" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="button" class="btn btn-outline-primary rounded-3 d-none" id="btnDescargarFacturaModal">
                        <i data-feather="download" class="me-1"></i>
                        Descargar PDF
                    </button>

                    <button type="submit" class="btn btn-primary-soft" id="btnGuardarFactura">
                        <span class="guardar-text">
                            <i data-feather="save" class="me-1"></i>
                            Guardar factura
                        </span>
                        <span class="guardar-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Guardando...
                        </span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>Assets/Js/facturas.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const formFactura = document.getElementById("formFactura");
        const modalFacturaEl = document.getElementById("modalFactura");
        const direccionFacturacion = document.getElementById("direccion_facturacion");

        /*
         * La dirección de facturación NO debe editarse manualmente.
         * Se llena únicamente desde el select de direcciones.
         * readonly sí se envía en FormData; disabled NO.
         */
        if (direccionFacturacion) {
            direccionFacturacion.setAttribute("readonly", "readonly");
            direccionFacturacion.classList.add("bg-light");
            direccionFacturacion.style.cursor = "not-allowed";
            direccionFacturacion.title = "Selecciona una dirección del cliente para llenar este campo.";
        }

        /*
         * Actualiza el texto de ayuda del textarea para que ya no diga
         * que puede ajustarse manualmente.
         */
        const helperDireccion = direccionFacturacion ?
            direccionFacturacion.closest(".col-md-12")?.querySelector(".helper-text") :
            null;

        if (helperDireccion) {
            helperDireccion.textContent =
                "Esta dirección se llena automáticamente al seleccionar una dirección del cliente.";
        }

        /*
         * Campos de texto que sí deben convertirse a mayúsculas.
         * No afecta fechas, números ni selects.
         */
        const selectorMayusculas = [
            '#folio_factura',
            '#sales_man',
            '#terms',
            '#notas',
            '#direccion_facturacion',
            '.input-descripcion'
        ].join(',');

        function convertirValorAMayusculas(campo) {
            if (!campo || typeof campo.value !== "string") return;

            const inicio = campo.selectionStart;
            const fin = campo.selectionEnd;

            campo.value = campo.value.toUpperCase();

            /*
             * Conserva la posición del cursor en campos editables.
             * En readonly puede lanzar error en algunos navegadores, por eso va protegido.
             */
            try {
                if (!campo.readOnly && document.activeElement === campo) {
                    campo.setSelectionRange(inicio, fin);
                }
            } catch (e) {}
        }

        function convertirFormularioAMayusculas() {
            if (!formFactura) return;

            const campos = formFactura.querySelectorAll(selectorMayusculas);

            campos.forEach(function(campo) {
                convertirValorAMayusculas(campo);
            });
        }

        /*
         * Convierte a mayúsculas mientras el usuario escribe.
         * Funciona también para partidas agregadas dinámicamente.
         */
        document.addEventListener("input", function(e) {
            if (!e.target.matches(selectorMayusculas)) return;

            convertirValorAMayusculas(e.target);
        });

        /*
         * Cuando cambian cliente o dirección, facturas.js llena automáticamente
         * direccion_facturacion. Después de ese llenado, forzamos mayúsculas.
         */
        document.addEventListener("change", function(e) {
            if (
                e.target.id === "id_cliente" ||
                e.target.id === "id_cliente_direccion"
            ) {
                setTimeout(function() {
                    convertirFormularioAMayusculas();
                }, 150);
            }
        });

        /*
         * Cuando se abre el modal en edición, facturas.js puede llenar datos
         * desde AJAX. Forzamos mayúsculas al mostrarse.
         */
        if (modalFacturaEl) {
            modalFacturaEl.addEventListener("shown.bs.modal", function() {
                setTimeout(function() {
                    convertirFormularioAMayusculas();
                }, 250);
            });
        }

        /*
         * Antes de enviar, convertimos todo de nuevo.
         * Esto asegura que aunque algún valor se haya llenado por JS,
         * se mande en mayúsculas al backend.
         */
        if (formFactura) {
            formFactura.addEventListener("submit", function() {
                convertirFormularioAMayusculas();
            }, true);
        }

        /*
         * Refuerzo ligero:
         * Mientras el modal esté abierto, revisa cada poco tiempo si JS llenó
         * la dirección o partidas y las normaliza a mayúsculas.
         */
        let intervaloMayusculas = null;

        if (modalFacturaEl) {
            modalFacturaEl.addEventListener("shown.bs.modal", function() {
                if (intervaloMayusculas) {
                    clearInterval(intervaloMayusculas);
                }

                intervaloMayusculas = setInterval(function() {
                    convertirFormularioAMayusculas();
                }, 500);
            });

            modalFacturaEl.addEventListener("hidden.bs.modal", function() {
                if (intervaloMayusculas) {
                    clearInterval(intervaloMayusculas);
                    intervaloMayusculas = null;
                }
            });
        }
    });
</script>

<?php require_once 'Views/Template/footer-admin.php'; ?>

<?php require_once 'Views/Template/footer-admin.php'; ?>