<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?php echo TITLE; ?> | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.29.2/dist/feather.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --azul-principal: #0d47a1;
            --azul-secundario: #1565c0;
            --azul-claro: #e3f2fd;
            --blanco-suave: #f8fbff;
            --texto-principal: #1f2937;
        }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 45%, #bbdefb 100%);
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-card {
            width: 100%;
            max-width: 1050px;
            background: #ffffff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 25px 70px rgba(13, 71, 161, 0.18);
            border: 1px solid rgba(21, 101, 192, 0.12);
        }

        .login-image {
            min-height: 620px;
            background:
                linear-gradient(180deg, rgba(13, 71, 161, 0.30), rgba(13, 71, 161, 0.85)),
                url('https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            color: #ffffff;
            position: relative;
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-box {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(8px);
        }

        .login-image h1 {
            font-size: 2.6rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 16px;
        }

        .login-image p {
            font-size: 1.05rem;
            opacity: 0.92;
            max-width: 430px;
        }

        .login-form-area {
            padding: 54px 46px;
            background: linear-gradient(180deg, #ffffff 0%, var(--blanco-suave) 100%);
        }

        .login-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--texto-principal);
        }

        .login-subtitle {
            color: #64748b;
            font-size: 0.98rem;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
        }

        .form-control {
            height: 52px;
            border-radius: 14px;
            border: 1px solid #dbeafe;
            background-color: #ffffff;
        }

        .form-control:focus {
            border-color: var(--azul-secundario);
            box-shadow: 0 0 0 0.22rem rgba(21, 101, 192, 0.13);
        }

        .input-group-text {
            border-radius: 14px 0 0 14px;
            border: 1px solid #dbeafe;
            background: var(--azul-claro);
            color: var(--azul-principal);
        }

        .input-group .form-control {
            border-radius: 0 14px 14px 0;
        }

        .btn-login {
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--azul-principal), var(--azul-secundario));
            border: none;
            font-weight: 700;
            letter-spacing: 0.2px;
            box-shadow: 0 12px 24px rgba(13, 71, 161, 0.22);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #08306f, #0d47a1);
        }

        .security-note {
            background: #eff6ff;
            color: #1e3a8a;
            border: 1px solid #bfdbfe;
            border-radius: 16px;
            padding: 14px 16px;
            font-size: 0.9rem;
        }

        .toggle-password {
            cursor: pointer;
            background: #ffffff;
            border-left: 0;
            border-radius: 0 14px 14px 0 !important;
        }

        .password-group .form-control {
            border-radius: 0;
        }

        @media (max-width: 991px) {
            .login-image {
                min-height: 320px;
                padding: 34px;
            }

            .login-form-area {
                padding: 38px 28px;
            }

            .login-image h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <?PHP echo password_hash("@Osc4r4rz4t3", PASSWORD_BCRYPT); ?>
    <main class="login-wrapper">
        <div class="login-card">
            <div class="row g-0">

                <div class="col-lg-6 d-none d-lg-flex">
                    <section class="login-image w-100">
                        <div class="brand-box">
                            <div class="brand-icon">
                                <i data-feather="file-text"></i>
                            </div>
                            <span>Sistema de Facturación</span>
                        </div>

                        <div>
                            <h1>Control profesional de facturas</h1>
                            <p>
                                Accede de forma segura para registrar clientes, generar facturas
                                y mantener el control administrativo del sistema.
                            </p>
                        </div>

                        <div class="small opacity-75">
                            MX-EXPRESS SERVICES
                        </div>
                    </section>
                </div>

                <div class="col-lg-6">
                    <section class="login-form-area h-100 d-flex flex-column justify-content-center">

                        <div class="mb-4">
                            <h2 class="login-title mb-2">Iniciar sesión</h2>
                            <p class="login-subtitle mb-0">
                                Ingresa tus credenciales para continuar.
                            </p>
                        </div>

                        <form id="formLogin" autocomplete="off" novalidate>

                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario o correo</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i data-feather="user"></i>
                                    </span>
                                    <input type="text"
                                        class="form-control"
                                        id="usuario"
                                        name="usuario"
                                        placeholder="Ej. arzateoscar33@gmail.com"
                                        required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group password-group">
                                    <span class="input-group-text">
                                        <i data-feather="lock"></i>
                                    </span>
                                    <input type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        placeholder="Ingresa tu contraseña"
                                        required>
                                    <span class="input-group-text toggle-password" id="btnTogglePassword">
                                        <i data-feather="eye"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="security-note mb-4">
                                <i data-feather="shield" class="me-1"></i>
                                Tus datos se validan de forma segura en el servidor.
                            </div>

                            <button type="submit" class="btn btn-primary btn-login w-100" id="btnLogin">
                                <span class="login-text">
                                    <i data-feather="log-in" class="me-1"></i>
                                    Entrar al sistema
                                </span>
                                <span class="login-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Validando...
                                </span>
                            </button>

                        </form>

                        <div class="mt-4 text-center small text-secondary">
                            © <?php echo date('Y'); ?> Sistema de Facturación
                        </div>

                    </section>
                </div>

            </div>
        </div>
    </main>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const base_url = '<?php echo BASE_URL; ?>';
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>Assets/Js/login.js"></script>

    <script>
        feather.replace();
    </script>

</body>

</html>