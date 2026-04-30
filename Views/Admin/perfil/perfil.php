<?php require_once 'Views/Template/header-admin.php'; ?>

<main class="content">

    <!-- TOPBAR -->
    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="page-title">Mi perfil</h1>
            <p class="page-subtitle">
                Consulta y actualiza la información de tu cuenta de usuario.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="user-pill">
                <i data-feather="user" class="me-1"></i>
                <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
            </div>
        </div>
    </div>

    <!-- KPIS / RESUMEN PERFIL -->
    <div class="row g-4 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Usuario</div>
                        <p class="kpi-value fs-4" id="kpiUsuarioPerfil">--</p>
                        <div class="kpi-note">Nombre de acceso</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="user"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Rol</div>
                        <p class="kpi-value fs-4" id="kpiRolPerfil">--</p>
                        <div class="kpi-note">Nivel de acceso</div>
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
                        <div class="kpi-title">Estado</div>
                        <p class="kpi-value fs-4" id="kpiEstadoPerfil">--</p>
                        <div class="kpi-note">Estado de la cuenta</div>
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
                        <div class="kpi-title">Última actualización</div>
                        <p class="kpi-value fs-5" id="kpiActualizadoPerfil">--</p>
                        <div class="kpi-note">Datos del perfil</div>
                    </div>
                    <div class="kpi-icon">
                        <i data-feather="clock"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        <!-- INFORMACIÓN DEL PERFIL -->
        <div class="col-xl-7">
            <div class="card-module h-100">

                <div class="card-module-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="card-module-title">Información personal</h5>
                            <p class="card-module-subtitle">
                                Actualiza tus datos básicos de usuario.
                            </p>
                        </div>

                        <div class="quick-icon">
                            <i data-feather="user-check"></i>
                        </div>
                    </div>
                </div>

                <form id="formPerfil" autocomplete="off">
                    <div class="p-4">

                        <input type="hidden" id="id_usuario" name="id_usuario">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="nombre" class="form-label">
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="nombre"
                                    name="nombre"
                                    placeholder="Nombre"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text"
                                    class="form-control"
                                    id="apellido"
                                    name="apellido"
                                    placeholder="Apellido">
                            </div>

                            <div class="col-md-6">
                                <label for="usuario" class="form-label">
                                    Usuario <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="usuario"
                                    name="usuario"
                                    placeholder="Usuario"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label for="correo" class="form-label">Correo</label>
                                <input type="email"
                                    class="form-control"
                                    id="correo"
                                    name="correo"
                                    placeholder="correo@dominio.com">
                            </div>

                            <div class="col-md-6">
                                <label for="rol" class="form-label">Rol</label>
                                <input type="text"
                                    class="form-control"
                                    id="rol"
                                    readonly>
                            </div>

                            <div class="col-md-6">
                                <label for="estado" class="form-label">Estado</label>
                                <input type="text"
                                    class="form-control"
                                    id="estado"
                                    readonly>
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" id="btnRecargarPerfil">
                            <i data-feather="refresh-cw" class="me-1"></i>
                            Recargar
                        </button>

                        <button type="submit" class="btn btn-primary-soft" id="btnGuardarPerfil">
                            <span class="btn-text">
                                <i data-feather="save" class="me-1"></i>
                                Guardar cambios
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

        <!-- CAMBIO DE CONTRASEÑA -->
        <div class="col-xl-5">
            <div class="card-module h-100">

                <div class="card-module-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="card-module-title">Seguridad</h5>
                            <p class="card-module-subtitle">
                                Cambia tu contraseña de acceso al sistema.
                            </p>
                        </div>

                        <div class="quick-icon">
                            <i data-feather="lock"></i>
                        </div>
                    </div>
                </div>

                <form id="formPassword" autocomplete="off">
                    <div class="p-4">

                        <div class="mb-3">
                            <label for="password_actual" class="form-label">
                                Contraseña actual <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password"
                                    class="form-control"
                                    id="password_actual"
                                    name="password_actual"
                                    placeholder="Contraseña actual"
                                    required>
                                <button class="btn btn-light border btn-toggle-password"
                                    type="button"
                                    data-target="password_actual">
                                    <i data-feather="eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password_nueva" class="form-label">
                                Nueva contraseña <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password"
                                    class="form-control"
                                    id="password_nueva"
                                    name="password_nueva"
                                    placeholder="Nueva contraseña"
                                    required>
                                <button class="btn btn-light border btn-toggle-password"
                                    type="button"
                                    data-target="password_nueva">
                                    <i data-feather="eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Usa al menos 8 caracteres.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmar" class="form-label">
                                Confirmar contraseña <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password"
                                    class="form-control"
                                    id="password_confirmar"
                                    name="password_confirmar"
                                    placeholder="Confirmar contraseña"
                                    required>
                                <button class="btn btn-light border btn-toggle-password"
                                    type="button"
                                    data-target="password_confirmar">
                                    <i data-feather="eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="security-note">
                            <i data-feather="shield" class="me-1"></i>
                            Por seguridad, después de cambiar tu contraseña se recomienda cerrar sesión e ingresar nuevamente.
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" id="btnLimpiarPassword">
                            <i data-feather="x" class="me-1"></i>
                            Limpiar
                        </button>

                        <button type="submit" class="btn btn-primary-soft" id="btnGuardarPassword">
                            <span class="btn-text">
                                <i data-feather="key" class="me-1"></i>
                                Cambiar contraseña
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

</main>

<script src="<?php echo BASE_URL; ?>Assets/Js/perfil.js"></script>

<?php require_once 'Views/Template/footer-admin.php'; ?>