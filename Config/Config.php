<?php
// ======================================================
// CONFIGURACIÓN DINÁMICA DE BASE_URL
// ======================================================
$esHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
);

$protocolo = $esHttps ? 'https://' : 'http://';

// Host dinámico (localhost, IP local, Tailscale, dominio, etc.)
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Carpeta del proyecto (AJÚSTALA si cambia)
$carpetaProyecto = '/SistemaFacturas/';

// Definir BASE_URL dinámica
define('BASE_URL', $protocolo . $host . $carpetaProyecto);

// ======================================================
// BASE DE DATOS
// ======================================================
const HOST = "localhost";
const USER = "root";
const PASS = "@Osc4r4rz4t3";
const DB = "sistema_facturacion";
const CHARSET = "charset=utf8";

// ======================================================
// GENERALES
// ======================================================
const TITLE = "Sistema Facturas";
const MONEDA = "USD";

// ======================================================
// SMTP
// ======================================================
const USER_SMTP = "sistemas@pacificnort.com";
const PASS_SMTP = "Pacific2025.";
const PUERTO_SMTP = 465;
const HOST_SMTP = "mailc75.carrierzone.com";

// ======================================================
// RUTA FÍSICA DEL PROYECTO
// ======================================================
define('UPLOAD_ROOT', rtrim(dirname(__DIR__), "/\\"));
