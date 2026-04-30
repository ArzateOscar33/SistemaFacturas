<?php

function puede(string $modulo, string $accion = 'ver'): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Super admin
    if ((int)($_SESSION['id_rol'] ?? 0) === 1) {
        return true;
    }

    $permisos = $_SESSION['permisos'] ?? [];

    if (!isset($permisos[$modulo])) {
        return false;
    }

    return !empty($permisos[$modulo][$accion]);
}

function requirePermiso(string $modulo, string $accion = 'ver'): void
{
    if (puede($modulo, $accion)) {
        return;
    }

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'ok' => false,
        'msg' => 'No tienes permiso para realizar esta acción.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

function requireVista(string $modulo): void
{
    if (puede($modulo, 'ver')) {
        return;
    }

    http_response_code(403);
    require_once 'Views/Errors/index.php';
    exit;
}
