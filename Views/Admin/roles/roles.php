<?php require_once 'Views/Template/header-admin.php'; ?>

<main class="content">

    <!-- TOPBAR -->
    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="page-title">Roles y permisos</h1>
            <p class="page-subtitle">
                Administra los roles del sistema y define qué usuarios pueden crear, editar, eliminar o consultar información.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="user-pill">
                <i data-feather="user" class="me-1"></i>
                <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
            </div>

            <button type="button" class="btn btn-primary-soft" id="btnNuevoRol">
                <i data-feather="plus-circle" class="me-1"></i>
                Nuevo rol
            </button>
        </div>
    </div>

    <!-- KPIS -->
    <div class="row g-4 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Roles registrados</div>
                        <p class="kpi-value" id="kpiRolesRegistrados">0</p>
                        <div class="kpi-note">Total de roles creados</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="shield"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Roles activos</div>
                        <p class="kpi-value" id="kpiRolesActivos">0</p>
                        <div class="kpi-note">Disponibles para usuarios</div>
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
                        <div class="kpi-title">Roles inactivos</div>
                        <p class="kpi-value" id="kpiRolesInactivos">0</p>
                        <div class="kpi-note">Deshabilitados</div>
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
                        <div class="kpi-title">Usuarios asignados</div>
                        <p class="kpi-value" id="kpiUsuariosAsignados">0</p>
                        <div class="kpi-note">Usuarios con rol</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="users"></i>
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
                        Busca roles por nombre, descripción o estado.
                    </p>
                </div>

                <div class="col-lg-4">
                    <label for="filtroRol" class="form-label">Buscar rol</label>
                    <input type="text"
                        class="form-control"
                        id="filtroRol"
                        placeholder="Ej. Administrador, Usuario, Capturista">
                </div>

                <div class="col-lg-2">
                    <label for="filtroEstadoRol" class="form-label">Estado</label>
                    <select id="filtroEstadoRol" class="form-select">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>

                <div class="col-lg-2 d-grid">
                    <button type="button" class="btn btn-primary-soft" id="btnFiltrarRoles">
                        <i data-feather="filter" class="me-1"></i>
                        Filtrar
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- TABLA ROLES -->
    <div class="card-module">
        <div class="card-module-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="card-module-title">Listado de roles</h5>
                    <p class="card-module-subtitle">
                        Controla los perfiles de acceso disponibles dentro del sistema.
                    </p>
                </div>

                <div class="quick-icon">
                    <i data-feather="lock"></i>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="tablaRoles">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Rol</th>
                        <th>Descripción</th>
                        <th>Usuarios</th>
                        <th>Estado</th>
                        <th>Creado en</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyRoles">
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i data-feather="shield"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Sin datos cargados</h6>
                                <div>Los roles se cargarán desde el controlador.</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- MODAL ROL -->
<div class="modal fade" id="modalRol" tabindex="-1" aria-labelledby="modalRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-admin">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="modalRolLabel">Nuevo rol</h5>
                    <p class="modal-subtitle mb-0">
                        Define el nombre, descripción y permisos del rol.
                    </p>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formRol" autocomplete="off">
                <div class="modal-body">

                    <input type="hidden" id="id_rol" name="id_rol">

                    <div class="row g-3 mb-4">

                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre del rol <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control"
                                id="nombre"
                                name="nombre"
                                placeholder="Ej. Administrador"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label for="estado" class="form-label">Estado</label>
                            <select id="estado" name="estado" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control"
                                id="descripcion"
                                name="descripcion"
                                rows="3"
                                placeholder="Describe el alcance de este rol dentro del sistema"></textarea>
                        </div>

                    </div>

                    <!-- PERMISOS -->
                    <div class="permissions-box">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <h6 class="fw-bold mb-1">Permisos del rol</h6>
                                <p class="text-secondary small mb-0">
                                    Selecciona las acciones permitidas para este perfil.
                                </p>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnMarcarTodosPermisos">
                                <i data-feather="check-square" class="me-1"></i>
                                Marcar todos
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-permissions align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Módulo</th>
                                        <th class="text-center">Ver</th>
                                        <th class="text-center">Crear</th>
                                        <th class="text-center">Editar</th>
                                        <th class="text-center">Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr>
                                        <td>
                                            <div class="fw-bold">Dashboard</div>
                                            <div class="small text-secondary">Panel principal e indicadores</div>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[dashboard][ver]" value="1">
                                        </td>
                                        <td class="text-center">—</td>
                                        <td class="text-center">—</td>
                                        <td class="text-center">—</td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="fw-bold">Clientes</div>
                                            <div class="small text-secondary">Catálogo de clientes</div>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[clientes][ver]" value="1">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[clientes][crear]" value="1">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[clientes][editar]" value="1">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[clientes][eliminar]" value="1">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="fw-bold">Facturas</div>
                                            <div class="small text-secondary">Creación, edición y cancelación de facturas</div>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[facturas][ver]" value="1">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[facturas][crear]" value="1">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[facturas][editar]" value="1">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[facturas][eliminar]" value="1">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="fw-bold">Reportes</div>
                                            <div class="small text-secondary">Consulta y exportación de reportes</div>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[reportes][ver]" value="1">
                                        </td>
                                        <td class="text-center">—</td>
                                        <td class="text-center">—</td>
                                        <td class="text-center">—</td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="fw-bold">Usuarios</div>
                                            <div class="small text-secondary">Alta y administración de usuarios</div>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[usuarios][ver]" value="1">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[usuarios][crear]" value="1">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[usuarios][editar]" value="1">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[usuarios][eliminar]" value="1">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="fw-bold">Roles y permisos</div>
                                            <div class="small text-secondary">Control de perfiles del sistema</div>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[roles][ver]" value="1">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[roles][crear]" value="1">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[roles][editar]" value="1">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[roles][eliminar]" value="1">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="fw-bold">Configuración</div>
                                            <div class="small text-secondary">Datos generales de la empresa</div>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[configuracion][ver]" value="1">
                                        </td>
                                        <td class="text-center">—</td>
                                        <td class="text-center">
                                            <input class="form-check-input permiso-check" type="checkbox" name="permisos[configuracion][editar]" value="1">
                                        </td>
                                        <td class="text-center">—</td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary-soft" id="btnGuardarRol">
                        <span class="btn-text">
                            <i data-feather="save" class="me-1"></i>
                            Guardar rol
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

<script src="<?php echo BASE_URL; ?>Assets/Js/roles.js"></script>

<?php require_once 'Views/Template/footer-admin.php'; ?>