<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo TITLE . ' | Página no encontrada'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.29.2/dist/feather.min.js"></script>

    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(13, 71, 161, 0.18), transparent 34%),
                linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
            color: #0f172a;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .error-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .error-card {
            width: 100%;
            max-width: 760px;
            background: #ffffff;
            border: 1px solid #dbeafe;
            border-radius: 28px;
            box-shadow: 0 24px 70px rgba(13, 71, 161, 0.14);
            padding: 42px;
            text-align: center;
        }

        .error-icon {
            width: 82px;
            height: 82px;
            border-radius: 24px;
            background: #eff6ff;
            color: #0d47a1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }

        .error-icon svg {
            width: 38px;
            height: 38px;
        }

        .error-code {
            font-size: clamp(4rem, 12vw, 7rem);
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.08em;
            color: #0d47a1;
            margin-bottom: 10px;
        }

        .error-title {
            font-size: 1.65rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .error-text {
            color: #64748b;
            max-width: 560px;
            margin: 0 auto 26px;
            font-size: 1rem;
        }

        .error-url {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 12px 16px;
            color: #475569;
            font-size: 0.9rem;
            word-break: break-word;
            margin-bottom: 26px;
        }

        .btn-primary-soft {
            background: #0d47a1;
            color: #ffffff;
            border: 1px solid #0d47a1;
            border-radius: 14px;
            padding: 11px 18px;
            font-weight: 700;
        }

        .btn-primary-soft:hover {
            background: #0b3d8a;
            color: #ffffff;
        }

        .btn-light-soft {
            background: #ffffff;
            color: #0d47a1;
            border: 1px solid #bfdbfe;
            border-radius: 14px;
            padding: 11px 18px;
            font-weight: 700;
        }

        .btn-light-soft:hover {
            background: #eff6ff;
            color: #0d47a1;
        }

        .brand-mini {
            margin-top: 26px;
            color: #94a3b8;
            font-size: 0.88rem;
            font-weight: 600;
        }

        @media (max-width: 576px) {
            .error-card {
                padding: 30px 22px;
                border-radius: 22px;
            }

            .error-actions {
                display: grid !important;
            }
        }
    </style>
</head>

<body>

    <div class="error-wrapper">
        <div class="error-card">

            <div class="error-icon">
                <i data-feather="alert-triangle"></i>
            </div>

            <div class="error-code">404</div>

            <h1 class="error-title">Página no encontrada</h1>

            <p class="error-text">
                La dirección que intentaste abrir no existe, fue movida o no tienes acceso desde esta ruta.
                Verifica el enlace o vuelve al panel principal.
            </p>

            <div class="error-url">
                <strong>URL solicitada:</strong>
                <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'No disponible', ENT_QUOTES, 'UTF-8'); ?>
            </div>

            <div class="d-flex justify-content-center gap-2 flex-wrap error-actions">
                <a href="<?php echo BASE_URL; ?>admin" class="btn btn-primary-soft">
                    <i data-feather="home" class="me-1"></i>
                    Ir al dashboard
                </a>

                <button type="button" class="btn btn-light-soft" onclick="history.back();">
                    <i data-feather="arrow-left" class="me-1"></i>
                    Regresar
                </button>

                <a href="<?php echo BASE_URL; ?>login/salir" class="btn btn-light-soft">
                    <i data-feather="log-out" class="me-1"></i>
                    Cerrar sesión
                </a>
            </div>

            <div class="brand-mini">
                <?php echo TITLE; ?> · Sistema de Facturación
            </div>

        </div>
    </div>

    <script>
        if (window.feather) {
            feather.replace();
        }
    </script>

</body>

</html>