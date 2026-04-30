<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?php echo TITLE; ?> | <?php echo $data['title'] ?? 'Panel administrativo'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>Assets/css/admin/admin.css">

    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.29.2/dist/feather.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const base_url = '<?php echo BASE_URL; ?>';
    </script>

    <script>
        const USER_PERMISOS = <?php echo json_encode($_SESSION['permisos'] ?? [], JSON_UNESCAPED_UNICODE); ?>;

        function puedeJS(modulo, accion = 'ver') {
            const idRol = <?php echo (int)($_SESSION['id_rol'] ?? 0); ?>;

            if (idRol === 1) {
                return true;
            }

            return !!(
                USER_PERMISOS &&
                USER_PERMISOS[modulo] &&
                USER_PERMISOS[modulo][accion]
            );
        }
    </script>
</head>

<body>

    <?php
    $active = $data['active'] ?? '';

    /*
        Helper local para evitar repetir lógica en el menú.
        Si ya existe la función global puede(), la usa.
        Si todavía no está cargada, usa $_SESSION['permisos'] directamente.
    */
    $puedeMenu = function (string $modulo, string $accion = 'ver'): bool {
        if ((int)($_SESSION['id_rol'] ?? 0) === 1) {
            return true;
        }

        if (function_exists('puede')) {
            return puede($modulo, $accion);
        }

        $permisos = $_SESSION['permisos'] ?? [];

        return !empty($permisos[$modulo][$accion]);
    };

    $mostrarOperacion = $puedeMenu('clientes') || $puedeMenu('facturas');
    $mostrarReportes = $puedeMenu('reportes') || $puedeMenu('bitacora');
    $mostrarAdministracion = $puedeMenu('usuarios') || $puedeMenu('roles') || $puedeMenu('folios') || $puedeMenu('configuracion');
    $mostrarSistema = $puedeMenu('errores') || $puedeMenu('perfil');
    ?>

    <div class="admin-layout">

        <aside class="sidebar">

            <div class="brand">
                <div class="brand-icon">
                    <i data-feather="file-text"></i>
                </div>
                <div>
                    <div class="brand-title">Sistema de<br>Facturación</div>
                    <div class="brand-subtitle">Panel administrativo</div>
                </div>
            </div>

            <!-- PRINCIPAL -->
            <div class="menu-label">Principal</div>

            <a href="<?php echo BASE_URL; ?>admin"
                class="menu-link <?php echo ($active === 'dashboard') ? 'active' : ''; ?>">
                <i data-feather="home"></i>
                Dashboard
            </a>

            <?php if ($mostrarOperacion) : ?>
                <!-- OPERACIÓN -->
                <div class="menu-label">Operación</div>

                <?php if ($puedeMenu('clientes', 'ver')) : ?>
                    <a href="<?php echo BASE_URL; ?>admin/clientes"
                        class="menu-link <?php echo ($active === 'clientes') ? 'active' : ''; ?>">
                        <i data-feather="users"></i>
                        Clientes
                    </a>
                <?php endif; ?>

                <?php if ($puedeMenu('facturas', 'ver')) : ?>
                    <a href="<?php echo BASE_URL; ?>admin/facturas"
                        class="menu-link <?php echo ($active === 'facturas') ? 'active' : ''; ?>">
                        <i data-feather="file-text"></i>
                        Facturas
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($mostrarReportes) : ?>
                <!-- REPORTES -->
                <div class="menu-label">Reportes</div>

                <?php if ($puedeMenu('reportes', 'ver')) : ?>
                    <a href="<?php echo BASE_URL; ?>admin/reportes"
                        class="menu-link <?php echo ($active === 'reportes') ? 'active' : ''; ?>">
                        <i data-feather="bar-chart-2"></i>
                        Reportes
                    </a>
                <?php endif; ?>

                <?php if ($puedeMenu('bitacora', 'ver')) : ?>
                    <a href="<?php echo BASE_URL; ?>admin/bitacora"
                        class="menu-link <?php echo ($active === 'bitacora') ? 'active' : ''; ?>">
                        <i data-feather="activity"></i>
                        Bitácora / Auditoría
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($mostrarAdministracion) : ?>
                <!-- ADMINISTRACIÓN -->
                <div class="menu-label">Administración</div>

                <?php if ($puedeMenu('usuarios', 'ver')) : ?>
                    <a href="<?php echo BASE_URL; ?>admin/usuarios"
                        class="menu-link <?php echo ($active === 'usuarios') ? 'active' : ''; ?>">
                        <i data-feather="user-check"></i>
                        Usuarios
                    </a>
                <?php endif; ?>

                <?php if ($puedeMenu('roles', 'ver')) : ?>
                    <a href="<?php echo BASE_URL; ?>admin/roles"
                        class="menu-link <?php echo ($active === 'roles') ? 'active' : ''; ?>">
                        <i data-feather="shield"></i>
                        Roles y permisos
                    </a>
                <?php endif; ?>

                <?php if ($puedeMenu('folios', 'ver')) : ?>
                    <a href="<?php echo BASE_URL; ?>admin/folios"
                        class="menu-link <?php echo ($active === 'folios') ? 'active' : ''; ?>">
                        <i data-feather="hash"></i>
                        Folios y series
                    </a>
                <?php endif; ?>

                <?php if ($puedeMenu('configuracion', 'ver')) : ?>
                    <a href="<?php echo BASE_URL; ?>admin/configuracion"
                        class="menu-link <?php echo ($active === 'configuracion') ? 'active' : ''; ?>">
                        <i data-feather="settings"></i>
                        Configuración
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <!-- SISTEMA -->
            <div class="menu-label">Sistema</div>

            <!-- Este botón queda visible para todos los usuarios logueados -->
            <button type="button"
                class="menu-link w-100 border-0 bg-transparent text-start"
                id="btnReportarError">
                <i data-feather="alert-circle"></i>
                Reportar error
            </button>

            <?php if ($puedeMenu('errores', 'ver')) : ?>
                <a href="<?php echo BASE_URL; ?>admin/errores"
                    class="menu-link <?php echo ($active === 'errores') ? 'active' : ''; ?>">
                    <i data-feather="alert-octagon"></i>
                    Errores del sistema
                </a>
            <?php endif; ?>

            <?php if ($puedeMenu('perfil', 'ver') || true) : ?>
                <a href="<?php echo BASE_URL; ?>admin/perfil"
                    class="menu-link <?php echo ($active === 'perfil') ? 'active' : ''; ?>">
                    <i data-feather="user"></i>
                    Mi perfil
                </a>
            <?php endif; ?>

            <!-- SESIÓN -->
            <div class="menu-label">Sesión</div>

            <a href="<?php echo BASE_URL; ?>login/salir" class="menu-link">
                <i data-feather="log-out"></i>
                Cerrar sesión
            </a>

        </aside>