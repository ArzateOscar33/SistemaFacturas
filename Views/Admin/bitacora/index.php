<?php require_once 'Views/Template/header-admin.php'; ?>

<main class="content">

    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="page-title">Bitácora / Auditoría</h1>
            <p class="page-subtitle">
                Consulta la actividad registrada en el sistema: accesos, cambios, registros, cancelaciones y eventos administrativos.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="user-pill">
                <i data-feather="user" class="me-1"></i>
                <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Eventos registrados</div>
                        <p class="kpi-value" id="kpiEventos">0</p>
                        <div class="kpi-note">Resultado de filtros actuales</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="activity"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Logins exitosos</div>
                        <p class="kpi-value" id="kpiLogins">0</p>
                        <div class="kpi-note">Inicios de sesión correctos</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="log-in"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Cambios administrativos</div>
                        <p class="kpi-value" id="kpiCambios">0</p>
                        <div class="kpi-note">Altas, ediciones y cancelaciones</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="edit-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Intentos fallidos</div>
                        <p class="kpi-value" id="kpiFallidos">0</p>
                        <div class="kpi-note">Eventos de seguridad</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="alert-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card-module mb-4">
        <div class="card-module-header">
            <div class="row g-3 align-items-end">

                <div class="col-lg-3">
                    <h5 class="card-module-title">Filtros de auditoría</h5>
                    <p class="card-module-subtitle">
                        Refina la búsqueda de eventos registrados.
                    </p>
                </div>

                <div class="col-lg-3">
                    <label for="filtroUsuario" class="form-label">Usuario</label>
                    <select id="filtroUsuario" class="form-select">
                        <option value="">Todos los usuarios</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label for="filtroModulo" class="form-label">Módulo</label>
                    <select id="filtroModulo" class="form-select">
                        <option value="">Todos</option>
                        <option value="Login">Login</option>
                        <option value="Clientes">Clientes</option>
                        <option value="Usuarios">Usuarios</option>
                        <option value="Configuración">Configuración</option>
                        <option value="Facturas">Facturas</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label for="filtroAccion" class="form-label">Acción</label>
                    <select id="filtroAccion" class="form-select">
                        <option value="">Todas</option>
                        <option value="LOGIN_EXITOSO">Login exitoso</option>
                        <option value="LOGIN_FALLIDO">Login fallido</option>
                        <option value="LOGIN_BLOQUEADO">Login bloqueado</option>
                        <option value="REGISTRAR">Registrar</option>
                        <option value="ACTUALIZAR">Actualizar</option>
                        <option value="CANCELAR">Cancelar</option>
                        <option value="ACTIVAR">Activar</option>
                        <option value="DESACTIVAR">Desactivar</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label for="buscarBitacora" class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i data-feather="search"></i>
                        </span>
                        <input type="text"
                            class="form-control"
                            id="buscarBitacora"
                            placeholder="Detalle, entidad...">
                    </div>
                </div>

                <div class="col-lg-3">
                    <label class="form-label">Rango de fechas</label>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control" id="fechaInicioBitacora">
                        <input type="date" class="form-control" id="fechaFinBitacora">
                    </div>
                </div>

                <div class="col-lg-3">
                    <label for="limiteBitacora" class="form-label">Registros</label>
                    <select id="limiteBitacora" class="form-select">
                        <option value="25">Últimos 25</option>
                        <option value="50" selected>Últimos 50</option>
                        <option value="100">Últimos 100</option>
                        <option value="250">Últimos 250</option>
                        <option value="500">Últimos 500</option>
                    </select>
                </div>

                <div class="col-lg-6 d-flex justify-content-end gap-2 flex-wrap">
                    <button type="button" class="btn btn-light border rounded-3" id="btnLimpiarFiltrosBitacora">
                        <i data-feather="rotate-ccw" class="me-1"></i>
                        Limpiar filtros
                    </button>

                    <button type="button" class="btn btn-primary-soft" id="btnRecargarBitacora">
                        <i data-feather="refresh-cw" class="me-1"></i>
                        Recargar
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="card-module">
        <div class="card-module-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="card-module-title">Eventos del sistema</h5>
                    <p class="card-module-subtitle">
                        Historial de acciones realizadas por usuarios dentro del sistema.
                    </p>
                </div>

                <div class="quick-icon">
                    <i data-feather="shield"></i>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="tablaBitacora">
                <thead>
                    <tr>
                        <th style="width: 145px;">Fecha</th>
                        <th style="width: 170px;">Usuario</th>
                        <th style="width: 135px;">Módulo</th>
                        <th style="width: 145px;">Acción</th>
                        <th style="width: 120px;">Entidad</th>
                        <th style="width: 90px;">ID</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody id="tbodyBitacora">
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i data-feather="database"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Sin datos cargados</h6>
                                <div>El historial se cargará desde el controlador de bitácora.</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script src="<?php echo BASE_URL; ?>Assets/Js/bitacora.js"></script>

<?php require_once 'Views/Template/footer-admin.php'; ?>