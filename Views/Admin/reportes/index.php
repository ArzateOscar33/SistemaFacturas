<?php require_once 'Views/Template/header-admin.php'; ?>

<main class="content">

    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="page-title">Reportes</h1>
            <p class="page-subtitle">
                Consulta indicadores de facturación, ventas por cliente, facturas emitidas y movimientos cancelados.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="user-pill">
                <i data-feather="user" class="me-1"></i>
                <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
            </div>

            <button type="button" class="btn btn-primary-soft" id="btnExportarReporte">
                <i data-feather="download" class="me-1"></i>
                Exportar reporte
            </button>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="card-module mb-4">
        <div class="card-module-header">
            <div class="row g-3 align-items-end">

                <div class="col-lg-3">
                    <h5 class="card-module-title">Filtros del reporte</h5>
                    <p class="card-module-subtitle">
                        Define el rango y tipo de información a consultar.
                    </p>
                </div>

                <div class="col-lg-3">
                    <label for="filtroClienteReporte" class="form-label">Cliente</label>
                    <select id="filtroClienteReporte" class="form-select">
                        <option value="">Todos los clientes</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label for="filtroEstadoReporte" class="form-label">Estado</label>
                    <select id="filtroEstadoReporte" class="form-select">
                        <option value="">Todos</option>
                        <option value="emitida">Emitidas</option>
                        <option value="borrador">Borradores</option>
                        <option value="cancelada">Canceladas</option>
                    </select>
                </div>

                <div class="col-lg-3">
                    <label class="form-label">Rango de fechas</label>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control" id="fechaInicioReporte">
                        <input type="date" class="form-control" id="fechaFinReporte">
                    </div>
                </div>

                <div class="col-lg-1 d-grid">
                    <button type="button" class="btn btn-primary-soft" id="btnFiltrarReporte">
                        <i data-feather="filter"></i>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- KPIS -->
    <div class="row g-4 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Total facturado</div>
                        <p class="kpi-value" id="kpiTotalFacturado">$0.00</p>
                        <div class="kpi-note">Facturas emitidas en el periodo</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Facturas emitidas</div>
                        <p class="kpi-value" id="kpiFacturasEmitidas">0</p>
                        <div class="kpi-note">Documentos generados</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="file-text"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Facturas canceladas</div>
                        <p class="kpi-value" id="kpiFacturasCanceladas">0</p>
                        <div class="kpi-note">Cancelaciones registradas</div>
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
                        <div class="kpi-title">Promedio por factura</div>
                        <p class="kpi-value" id="kpiPromedioFactura">$0.00</p>
                        <div class="kpi-note">Ticket promedio</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="trending-up"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- CHARTS -->
    <div class="row g-4 mb-4">

        <div class="col-xl-8">
            <div class="chart-card h-100">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="chart-title">Facturación por mes</h5>
                        <p class="chart-subtitle">
                            Evolución del total facturado en el periodo seleccionado.
                        </p>
                    </div>

                    <div class="quick-icon">
                        <i data-feather="bar-chart-2"></i>
                    </div>
                </div>

                <<div class="chart-wrapper chart-wrapper-line">
                    <canvas id="chartFacturacionMensual"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="chart-card h-100">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="chart-title">Estados de facturas</h5>
                    <p class="chart-subtitle">
                        Distribución por estado.
                    </p>
                </div>

                <div class="quick-icon">
                    <i data-feather="pie-chart"></i>
                </div>
            </div>

            <div class="chart-wrapper chart-wrapper-doughnut">
                <canvas id="chartEstadosFacturas"></canvas>
            </div>
        </div>
    </div>

    </div>

    <!-- TOP CLIENTES + ÚLTIMAS FACTURAS -->
    <div class="row g-4 mb-4">

        <div class="col-xl-5">
            <div class="card-module h-100">
                <div class="card-module-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="card-module-title">Top clientes</h5>
                            <p class="card-module-subtitle">
                                Clientes con mayor facturación en el periodo.
                            </p>
                        </div>

                        <div class="quick-icon">
                            <i data-feather="award"></i>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle" id="tablaTopClientes">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Facturas</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyTopClientes">
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i data-feather="users"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Sin datos</h6>
                                        <div>El top de clientes se cargará desde el controlador.</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <div class="col-xl-7">
            <div class="card-module h-100">
                <div class="card-module-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="card-module-title">Últimas facturas</h5>
                            <p class="card-module-subtitle">
                                Documentos recientes dentro del periodo consultado.
                            </p>
                        </div>

                        <div class="quick-icon">
                            <i data-feather="clock"></i>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle" id="tablaUltimasFacturasReporte">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyUltimasFacturasReporte">
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i data-feather="file-text"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Sin datos</h6>
                                        <div>Las facturas recientes se cargarán desde el controlador.</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>

    <!-- REPORTE DETALLADO -->
    <div class="card-module">
        <div class="card-module-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="card-module-title">Reporte detallado</h5>
                    <p class="card-module-subtitle">
                        Listado completo de facturas según los filtros seleccionados.
                    </p>
                </div>

                <div class="quick-icon">
                    <i data-feather="list"></i>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="tablaReporteFacturas">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Subtotal</th>
                        <th>Tax</th>
                        <th>Shipping</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tbodyReporteFacturas">
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i data-feather="database"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Sin datos cargados</h6>
                                <div>El reporte detallado se cargará desde el controlador de reportes.</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script src="<?php echo BASE_URL; ?>Assets/Js/reportes.js"></script>

<?php require_once 'Views/Template/footer-admin.php'; ?>