<?php require_once 'Views/Template/header-admin.php'; ?>

<main class="content">

    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="page-title">Usuarios</h1>
            <p class="page-subtitle">
                Administra los accesos, roles y estado de los usuarios del sistema.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="user-pill">
                <i data-feather="user" class="me-1"></i>
                <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
            </div>

            <button type="button" class="btn btn-primary-soft" id="btnNuevoUsuario">
                <i data-feather="user-plus" class="me-1"></i>
                Nuevo usuario
            </button>
        </div>
    </div>

    <div class="card-module mb-4">
        <div class="card-module-header">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <h5 class="card-module-title">Listado de usuarios</h5>
                    <p class="card-module-subtitle">
                        Busca, edita o desactiva usuarios registrados.
                    </p>
                </div>

                <div class="col-lg-4">
                    <label for="buscarUsuario" class="form-label">Buscar usuario</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i data-feather="search"></i>
                        </span>
                        <input type="text"
                            class="form-control"
                            id="buscarUsuario"
                            placeholder="Nombre, usuario, correo o rol">
                    </div>
                </div>

                <div class="col-lg-3">
                    <label for="filtroEstado" class="form-label">Estado</label>
                    <select id="filtroEstado" class="form-select">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th style="width: 150px;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyUsuarios">
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i data-feather="database"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Sin datos cargados</h6>
                                <div>El listado se cargará desde el controlador de usuarios.</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- MODAL USUARIO -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="formUsuario" autocomplete="off" novalidate>

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalUsuarioLabel">Registrar usuario</h5>
                        <div class="text-secondary small">
                            Los campos marcados con <span class="required-mark">*</span> son obligatorios.
                        </div>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="id_usuario" name="id_usuario">

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label for="nombre" class="form-label">
                                Nombre <span class="required-mark">*</span>
                            </label>
                            <input type="text"
                                class="form-control"
                                id="nombre"
                                name="nombre"
                                maxlength="100"
                                placeholder="Nombre"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text"
                                class="form-control"
                                id="apellido"
                                name="apellido"
                                maxlength="100"
                                placeholder="Apellido">
                        </div>

                        <div class="col-md-4">
                            <label for="id_rol" class="form-label">
                                Rol <span class="required-mark">*</span>
                            </label>
                            <select id="id_rol" name="id_rol" class="form-select" required>
                                <option value="">Seleccione un rol</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="usuario" class="form-label">
                                Usuario <span class="required-mark">*</span>
                            </label>
                            <input type="text"
                                class="form-control"
                                id="usuario"
                                name="usuario"
                                maxlength="50"
                                placeholder="Ej. jlopez"
                                required>
                            <div class="helper-text">
                                Debe ser único dentro del sistema.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="correo" class="form-label">Correo</label>
                            <input type="email"
                                class="form-control"
                                id="correo"
                                name="correo"
                                maxlength="120"
                                placeholder="usuario@correo.com">
                            <div class="helper-text">
                                Opcional, pero si se captura debe tener formato válido.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">
                                Contraseña <span class="required-mark password-required">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i data-feather="lock"></i>
                                </span>
                                <input type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    maxlength="100"
                                    placeholder="Contraseña segura">
                                <button type="button"
                                    class="input-group-text btn-toggle-password"
                                    id="btnTogglePassword"
                                    tabindex="-1">
                                    <i data-feather="eye"></i>
                                </button>
                            </div>
                            <div class="helper-text" id="passwordHelp">
                                Obligatoria al registrar. En edición puedes dejarla vacía para conservar la actual.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="confirmar_password" class="form-label">
                                Confirmar contraseña <span class="required-mark password-required">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i data-feather="shield"></i>
                                </span>
                                <input type="password"
                                    class="form-control"
                                    id="confirmar_password"
                                    name="confirmar_password"
                                    maxlength="100"
                                    placeholder="Repite la contraseña">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado del usuario</label>
                            <select id="estado" name="estado" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border rounded-3" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary-soft" id="btnGuardarUsuario">
                        <span class="guardar-text">
                            <i data-feather="save" class="me-1"></i>
                            Guardar usuario
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

<!-- JS del módulo Usuarios -->
<script src="<?php echo BASE_URL; ?>Assets/Js/usuarios.js"></script>

<?php require_once 'Views/Template/footer-admin.php'; ?>