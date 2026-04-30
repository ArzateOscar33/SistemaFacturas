<?php require_once 'Views/Template/header-admin.php'; ?>

<main class="content">

    <!-- TOPBAR -->
    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="page-title">Reporte de errores</h1>
            <p class="page-subtitle">
                Consulta, filtra y da seguimiento a los errores registrados dentro del sistema.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="user-pill">
                <i data-feather="user" class="me-1"></i>
                <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
            </div>

            <button type="button" class="btn btn-primary-soft" id="btnActualizarErrores">
                <i data-feather="refresh-cw" class="me-1"></i>
                Actualizar
            </button>
        </div>
    </div>

    <!-- KPIS -->
    <div class="row g-4 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Errores registrados</div>
                        <p class="kpi-value" id="kpiErroresRegistrados">0</p>
                        <div class="kpi-note">Total histórico</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="alert-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Errores pendientes</div>
                        <p class="kpi-value" id="kpiErroresPendientes">0</p>
                        <div class="kpi-note">Sin revisar</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Errores críticos</div>
                        <p class="kpi-value" id="kpiErroresCriticos">0</p>
                        <div class="kpi-note">Prioridad alta</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="zap"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Errores de hoy</div>
                        <p class="kpi-value" id="kpiErroresHoy">0</p>
                        <div class="kpi-note">Registrados hoy</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="calendar"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- AVISO -->
    <div class="card-module mb-4">
        <div class="card-module-header">
            <div class="d-flex gap-3 align-items-start">
                <div class="quick-icon flex-shrink-0">
                    <i data-feather="shield"></i>
                </div>

                <div>
                    <h5 class="card-module-title">Monitoreo interno del sistema</h5>
                    <p class="card-module-subtitle mb-0">
                        Este módulo está pensado para registrar errores técnicos del sistema y facilitar la depuración.
                        No debe mostrar información sensible como contraseñas, tokens o datos privados del servidor.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="card-module mb-4">
        <div class="card-module-header">
            <div class="row g-3 align-items-end">

                <div class="col-xl-3 col-lg-4">
                    <h5 class="card-module-title">Filtros de búsqueda</h5>
                    <p class="card-module-subtitle">
                        Consulta errores por tipo, módulo, estado o fecha.
                    </p>
                </div>

                <div class="col-xl-3 col-lg-4">
                    <label for="filtroBusquedaError" class="form-label">Buscar</label>
                    <input type="text"
                        class="form-control"
                        id="filtroBusquedaError"
                        placeholder="Endpoint, mensaje, archivo, usuario...">
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label for="filtroTipoError" class="form-label">Tipo</label>
                    <select id="filtroTipoError" class="form-select">
                        <option value="">Todos</option>
                        <option value="PHP">PHP</option>
                        <option value="SQL">SQL</option>
                        <option value="AJAX">AJAX</option>
                        <option value="VALIDACION">Validación</option>
                        <option value="SISTEMA">Sistema</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label for="filtroNivelError" class="form-label">Nivel</label>
                    <select id="filtroNivelError" class="form-select">
                        <option value="">Todos</option>
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="error">Error</option>
                        <option value="critico">Crítico</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label for="filtroEstadoError" class="form-label">Estado</label>
                    <select id="filtroEstadoError" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="revisado">Revisado</option>
                        <option value="resuelto">Resuelto</option>
                    </select>
                </div>

                <div class="col-xl-3 col-lg-4">
                    <label class="form-label">Rango de fechas</label>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control" id="fechaInicioError">
                        <input type="date" class="form-control" id="fechaFinError">
                    </div>
                </div>

                <div class="col-xl-3 col-lg-4">
                    <label for="filtroModuloError" class="form-label">Módulo</label>
                    <input type="text"
                        class="form-control"
                        id="filtroModuloError"
                        placeholder="Ej. Facturas, Clientes, Login">
                </div>

                <div class="col-xl-2 col-lg-4">
                    <label for="filtroLimiteErrores" class="form-label">Mostrar</label>
                    <select id="filtroLimiteErrores" class="form-select">
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                        <option value="250">250 registros</option>
                        <option value="500">500 registros</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4 d-grid">
                    <button type="button" class="btn btn-primary-soft" id="btnFiltrarErrores">
                        <i data-feather="filter" class="me-1"></i>
                        Filtrar
                    </button>
                </div>

                <div class="col-xl-2 col-lg-4 d-grid">
                    <button type="button" class="btn btn-light border" id="btnLimpiarErrores">
                        <i data-feather="x" class="me-1"></i>
                        Limpiar
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="card-module">
        <div class="card-module-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="card-module-title">Listado de errores</h5>
                    <p class="card-module-subtitle">
                        Revisa los errores registrados y consulta su detalle técnico.
                    </p>
                </div>

                <div class="quick-icon">
                    <i data-feather="bug"></i>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="tablaErrores">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Tipo</th>
                        <th>Nivel</th>
                        <th>Módulo</th>
                        <th>Mensaje</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>

                <tbody id="tbodyErrores">
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i data-feather="bug"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Sin datos cargados</h6>
                                <div>Los errores se cargarán desde el controlador.</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- MODAL DETALLE ERROR -->
<div class="modal fade" id="modalDetalleError" tabindex="-1" aria-labelledby="modalDetalleErrorLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-admin">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="modalDetalleErrorLabel">Detalle del error</h5>
                    <p class="modal-subtitle mb-0">
                        Información técnica registrada para revisión.
                    </p>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="detalleIdError">

                <div class="row g-3 mb-4">

                    <div class="col-md-3">
                        <div class="detail-box">
                            <div class="detail-label">Tipo</div>
                            <div class="detail-value" id="detalleTipoError">No disponible</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="detail-box">
                            <div class="detail-label">Nivel</div>
                            <div class="detail-value" id="detalleNivelError">No disponible</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="detail-box">
                            <div class="detail-label">Estado</div>
                            <div class="detail-value" id="detalleEstadoError">No disponible</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="detail-box">
                            <div class="detail-label">Fecha</div>
                            <div class="detail-value" id="detalleFechaError">No disponible</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="detail-box">
                            <div class="detail-label">Módulo</div>
                            <div class="detail-value" id="detalleModuloError">No disponible</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="detail-box">
                            <div class="detail-label">Controlador / Método</div>
                            <div class="detail-value" id="detalleControladorError">No disponible</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="detail-box">
                            <div class="detail-label">Usuario</div>
                            <div class="detail-value" id="detalleUsuarioError">No disponible</div>
                        </div>
                    </div>

                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mensaje</label>
                    <div class="error-message-box" id="detalleMensajeError">
                        No disponible
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Archivo</label>
                    <div class="error-code-box" id="detalleArchivoError">
                        No disponible
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Línea</label>
                    <div class="error-code-box" id="detalleLineaError">
                        No disponible
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">URL / Endpoint</label>
                    <div class="error-code-box" id="detalleUrlError">
                        No disponible
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Datos adicionales</label>
                    <pre class="error-pre" id="detalleDatosError">No disponible</pre>
                </div>

                <div class="mb-3">
                    <label for="notaRevisionError" class="form-label fw-bold">Nota de revisión</label>
                    <textarea class="form-control"
                        id="notaRevisionError"
                        rows="3"
                        placeholder="Agrega una nota interna sobre la revisión o solución aplicada"></textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="estadoRevisionError" class="form-label">Cambiar estado</label>
                        <select id="estadoRevisionError" class="form-select">
                            <option value="pendiente">Pendiente</option>
                            <option value="revisado">Revisado</option>
                            <option value="resuelto">Resuelto</option>
                        </select>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                    Cerrar
                </button>

                <button type="button" class="btn btn-primary-soft" id="btnGuardarRevisionError">
                    <span class="btn-text">
                        <i data-feather="save" class="me-1"></i>
                        Guardar revisión
                    </span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Guardando...
                    </span>
                </button>
            </div>

        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>Assets/Js/errores.js"></script>

<?php require_once 'Views/Template/footer-admin.php'; ?>