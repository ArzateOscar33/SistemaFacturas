<?php require_once 'Views/Template/header-admin.php'; ?>

<main class="content">

    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="page-title">Configuración</h1>
            <p class="page-subtitle">
                Administra los datos de la empresa, logo y configuración base de facturación.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="user-pill">
                <i data-feather="user" class="me-1"></i>
                <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- CONFIGURACIÓN EMPRESA -->
        <div class="col-xl-8">
            <div class="card-module">
                <div class="card-module-header">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h5 class="card-module-title">Datos de la empresa</h5>
                            <p class="card-module-subtitle">
                                Esta información se usará para generar las facturas en PDF.
                            </p>
                        </div>

                        <div class="quick-icon">
                            <i data-feather="briefcase"></i>
                        </div>
                    </div>
                </div>

                <form id="formConfiguracionEmpresa" autocomplete="off" enctype="multipart/form-data" novalidate>
                    <div class="p-4">

                        <input type="hidden" id="id_empresa" name="id_empresa">

                        <div class="row g-3">

                            <div class="col-md-8">
                                <label for="nombre_empresa" class="form-label">
                                    Nombre de empresa <span class="required-mark">*</span>
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="nombre_empresa"
                                    name="nombre_empresa"
                                    maxlength="150"
                                    placeholder="Ej. MX-EXPRESS SERVICES"
                                    required>
                            </div>

                            <div class="col-md-4">
                                <label for="tax_id" class="form-label">Tax ID / RFC</label>
                                <input type="text"
                                    class="form-control"
                                    id="tax_id"
                                    name="tax_id"
                                    maxlength="50"
                                    placeholder="Ej. 87-1919460">
                            </div>

                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i data-feather="phone"></i>
                                    </span>
                                    <input type="text"
                                        class="form-control"
                                        id="telefono"
                                        name="telefono"
                                        maxlength="50"
                                        placeholder="Ej. 619-8824820">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="correo" class="form-label">Correo</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i data-feather="mail"></i>
                                    </span>
                                    <input type="email"
                                        class="form-control"
                                        id="correo"
                                        name="correo"
                                        maxlength="120"
                                        placeholder="empresa@correo.com">
                                </div>
                                <div class="helper-text">
                                    Opcional, pero si se captura debe tener formato válido.
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea class="form-control"
                                    id="direccion"
                                    name="direccion"
                                    rows="3"
                                    placeholder="Dirección que aparecerá en la factura"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="color_principal" class="form-label">Color principal del PDF</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i data-feather="droplet"></i>
                                    </span>
                                    <input type="text"
                                        class="form-control"
                                        id="color_principal"
                                        name="color_principal"
                                        maxlength="20"
                                        placeholder="#0d47a1">
                                    <input type="color"
                                        class="form-control form-control-color"
                                        id="color_picker"
                                        value="#0d47a1"
                                        title="Seleccionar color">
                                </div>
                                <div class="helper-text">
                                    Se usará como color institucional en el PDF.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="logo" class="form-label">Logo de empresa</label>
                                <input type="file"
                                    class="form-control"
                                    id="logo"
                                    name="logo"
                                    accept="image/png,image/jpeg,image/jpg,image/webp">
                                <div class="helper-text">
                                    Formatos permitidos: JPG, PNG o WEBP.
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="texto_pie_pagina" class="form-label">Texto de pie de página</label>
                                <textarea class="form-control"
                                    id="texto_pie_pagina"
                                    name="texto_pie_pagina"
                                    rows="3"
                                    placeholder="Texto adicional para mostrar al final de la factura"></textarea>
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border rounded-3" id="btnRestablecerConfiguracion">
                            <i data-feather="rotate-ccw" class="me-1"></i>
                            Restablecer
                        </button>

                        <button type="submit" class="btn btn-primary-soft" id="btnGuardarConfiguracion">
                            <span class="guardar-text">
                                <i data-feather="save" class="me-1"></i>
                                Guardar configuración
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

        <!-- PANEL DERECHO -->
        <div class="col-xl-4">

            <!-- LOGO PREVIEW -->
            <div class="card-module mb-4">
                <div class="card-module-header">
                    <h5 class="card-module-title">Vista previa</h5>
                    <p class="card-module-subtitle">
                        Logo y datos principales configurados.
                    </p>
                </div>

                <div class="p-4">
                    <div class="config-logo-preview mb-3" id="logoPreviewBox">
                        <div class="config-logo-empty" id="logoEmpty">
                            <i data-feather="image"></i>
                            <span>Sin logo cargado</span>
                        </div>

                        <img src=""
                            alt="Logo empresa"
                            class="config-logo-img d-none"
                            id="logoPreview">
                    </div>

                    <div class="config-preview-card">
                        <div class="config-preview-title" id="previewNombreEmpresa">
                            Nombre de empresa
                        </div>

                        <div class="config-preview-line">
                            <i data-feather="hash"></i>
                            <span id="previewTaxId">Tax ID / RFC no configurado</span>
                        </div>

                        <div class="config-preview-line">
                            <i data-feather="phone"></i>
                            <span id="previewTelefono">Teléfono no configurado</span>
                        </div>

                        <div class="config-preview-line">
                            <i data-feather="mail"></i>
                            <span id="previewCorreo">Correo no configurado</span>
                        </div>

                        <div class="config-preview-line">
                            <i data-feather="map-pin"></i>
                            <span id="previewDireccion">Dirección no configurada</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- CONTROL DE FOLIOS ABAJO A LA IZQUIERDA -->
        <div class="col-xl-8">
            <div class="card-module">
                <div class="card-module-header">
                    <h5 class="card-module-title">Control de folios</h5>
                    <p class="card-module-subtitle">
                        Información actual de la numeración de facturas.
                    </p>
                </div>

                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="folio-info-card h-100">
                                <div class="folio-label">Serie actual</div>
                                <div class="folio-value" id="folioSerie">INV</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="folio-info-card h-100">
                                <div class="folio-label">Último número usado</div>
                                <div class="folio-value" id="folioUltimoNumero">0</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="folio-info-card h-100">
                                <div class="folio-label">Siguiente folio estimado</div>
                                <div class="folio-value text-primary" id="folioSiguiente">INV-00000001</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-primary mt-4 mb-0 rounded-4">
                        <div class="d-flex gap-2">
                            <div>
                                <i data-feather="shield"></i>
                            </div>
                            <div>
                                <strong>Concurrencia protegida</strong>
                                <div class="small">
                                    El número real de factura se asignará al guardar la factura usando bloqueo de base de datos.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    </div>

</main>

<script src="<?php echo BASE_URL; ?>Assets/Js/configuracion.js"></script>

<?php require_once 'Views/Template/footer-admin.php'; ?>