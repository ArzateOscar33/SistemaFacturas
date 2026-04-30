<?php require_once 'Views/Template/header-admin.php'; ?>

<main class="content">

    <!-- TOPBAR -->
    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="page-title">Folios y series</h1>
            <p class="page-subtitle">
                Administra las series de facturación, el último folio utilizado y el estado de cada serie.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="user-pill">
                <i data-feather="user" class="me-1"></i>
                <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
            </div>

            <button type="button" class="btn btn-primary-soft" id="btnNuevoFolio">
                <i data-feather="plus-circle" class="me-1"></i>
                Nueva serie
            </button>
        </div>
    </div>

    <!-- KPIS -->
    <div class="row g-4 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Series registradas</div>
                        <p class="kpi-value" id="kpiSeriesRegistradas">0</p>
                        <div class="kpi-note">Total de series creadas</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="hash"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Series activas</div>
                        <p class="kpi-value" id="kpiSeriesActivas">0</p>
                        <div class="kpi-note">Disponibles para facturación</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Series inactivas</div>
                        <p class="kpi-value" id="kpiSeriesInactivas">0</p>
                        <div class="kpi-note">No disponibles</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="x-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Último folio global</div>
                        <p class="kpi-value" id="kpiUltimoFolioGlobal">0</p>
                        <div class="kpi-note">Número más alto usado</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="trending-up"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>



    <!-- FILTROS -->
    <div class="card-module mb-4">
        <div class="card-module-header">
            <div class="row g-3 align-items-end">

                <div class="col-lg-4">
                    <h5 class="card-module-title">Filtros de búsqueda</h5>
                    <p class="card-module-subtitle">
                        Busca series por nombre o estado.
                    </p>
                </div>

                <div class="col-lg-4">
                    <label for="filtroSerie" class="form-label">Buscar serie</label>
                    <input type="text"
                        class="form-control"
                        id="filtroSerie"
                        placeholder="Ej. INV, A, B, MX">
                </div>

                <div class="col-lg-2">
                    <label for="filtroEstadoSerie" class="form-label">Estado</label>
                    <select id="filtroEstadoSerie" class="form-select">
                        <option value="">Todas</option>
                        <option value="1">Activas</option>
                        <option value="0">Inactivas</option>
                    </select>
                </div>

                <div class="col-lg-2 d-grid">
                    <button type="button" class="btn btn-primary-soft" id="btnFiltrarFolios">
                        <i data-feather="filter" class="me-1"></i>
                        Filtrar
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
                    <h5 class="card-module-title">Listado de series</h5>
                    <p class="card-module-subtitle">
                        Controla el prefijo y el último número utilizado para cada serie de factura.
                    </p>
                </div>

                <div class="quick-icon">
                    <i data-feather="file-text"></i>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="tablaFolios">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Serie</th>
                        <th>Último número</th>
                        <th>Siguiente folio</th>
                        <th>Estado</th>
                        <th>Creado en</th>
                        <th>Actualizado en</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>

                <tbody id="tbodyFolios">
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i data-feather="hash"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Sin datos cargados</h6>
                                <div>Las series se cargarán desde el controlador.</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- MODAL FOLIO / SERIE -->
<div class="modal fade" id="modalFolio" tabindex="-1" aria-labelledby="modalFolioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-admin">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="modalFolioLabel">Nueva serie</h5>
                    <p class="modal-subtitle mb-0">
                        Configura una serie de facturación y su número inicial.
                    </p>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formFolio" autocomplete="off">
                <div class="modal-body">

                    <input type="hidden" id="id_folio" name="id_folio">

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label for="serie" class="form-label">
                                Serie <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control text-uppercase"
                                id="serie"
                                name="serie"
                                maxlength="20"
                                placeholder="Ej. INV"
                                required>
                            <div class="form-text">
                                Prefijo que aparecerá antes del número de factura.
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="ultimo_numero" class="form-label">
                                Último número usado <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                class="form-control"
                                id="ultimo_numero"
                                name="ultimo_numero"
                                min="0"
                                step="1"
                                placeholder="0"
                                required>
                            <div class="form-text">
                                Si colocas 0, el siguiente folio será 00000001.
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="activo" class="form-label">Estado</label>
                            <select id="activo" name="activo" class="form-select">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                            <div class="form-text">
                                Solo las series activas podrán usarse al generar facturas.
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="folio-preview-box">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div>
                                        <div class="folio-preview-label">Vista previa del siguiente folio</div>
                                        <div class="folio-preview-value" id="previewSiguienteFolio">INV-00000001</div>
                                    </div>

                                    <div class="quick-icon">
                                        <i data-feather="eye"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="security-note">
                                <i data-feather="shield" class="me-1"></i>
                                Recomendación: no cambies el último número a uno menor si ya existen facturas con esa serie.
                                El sistema validará duplicados desde el backend.
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary-soft" id="btnGuardarFolio">
                        <span class="btn-text">
                            <i data-feather="save" class="me-1"></i>
                            Guardar serie
                        </span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Guardando...
                        </span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>Assets/Js/folios.js"></script>

<?php require_once 'Views/Template/footer-admin.php'; ?>