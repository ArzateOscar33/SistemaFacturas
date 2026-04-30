<?php

class Configuracion extends Controller
{
    private string $uploadDir;
    private string $uploadUrl;

    public function __construct()
    {
        session_start();

        if (empty($_SESSION['activo'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        parent::__construct();

        $this->uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'configuracion' . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR;
        $this->uploadUrl = 'Uploads/configuracion/logo/';
    }

    public function obtener()
    {
        requirePermiso('configuracion', 'ver');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $empresa = $this->model->obtenerEmpresa();

            if (!$empresa) {
                $idEmpresa = $this->model->crearEmpresaInicial();

                if (!$idEmpresa) {
                    echo json_encode([
                        'ok' => false,
                        'msg' => 'No fue posible crear la configuración inicial.'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }

                $empresa = $this->model->obtenerEmpresa();
            }

            $folio = $this->model->obtenerFolioFactura();

            $siguienteFolio = null;

            if ($folio) {
                $siguienteNumero = ((int)$folio['ultimo_numero']) + 1;
                $siguienteFolio = $folio['serie'] . '-' . str_pad((string)$siguienteNumero, 8, '0', STR_PAD_LEFT);
            }

            echo json_encode([
                'ok' => true,
                'data' => [
                    'empresa' => $empresa,
                    'folio' => $folio,
                    'siguiente_folio' => $siguienteFolio
                ]
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al obtener la configuración.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function guardar()
    {
        requirePermiso('configuracion', 'editar');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id_empresa = (int)($_POST['id_empresa'] ?? 0);
        $nombre_empresa = trim($_POST['nombre_empresa'] ?? '');
        $tax_id = trim($_POST['tax_id'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $correo = strtolower(trim($_POST['correo'] ?? ''));
        $direccion = trim($_POST['direccion'] ?? '');
        $color_principal = trim($_POST['color_principal'] ?? '#0d47a1');
        $texto_pie_pagina = trim($_POST['texto_pie_pagina'] ?? '');

        if ($nombre_empresa === '') {
            echo json_encode([
                'ok' => false,
                'msg' => 'El nombre de la empresa es obligatorio.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($nombre_empresa) > 150) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El nombre de la empresa no puede superar 150 caracteres.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El correo no tiene un formato válido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($color_principal !== '' && !$this->validarColorHex($color_principal)) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El color principal debe tener formato HEX. Ejemplo: #0d47a1'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $empresaActual = $this->model->obtenerEmpresa();

            if (!$empresaActual) {
                $nuevoId = $this->model->crearEmpresaInicial();

                if (!$nuevoId) {
                    echo json_encode([
                        'ok' => false,
                        'msg' => 'No fue posible crear la configuración inicial.'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }

                $empresaActual = $this->model->obtenerEmpresa();
            }

            if ($id_empresa <= 0) {
                $id_empresa = (int)$empresaActual['id_empresa'];
            }

            $actualizado = $this->model->actualizarEmpresa(
                $id_empresa,
                $nombre_empresa,
                $tax_id !== '' ? $tax_id : null,
                $telefono !== '' ? $telefono : null,
                $correo !== '' ? $correo : null,
                $direccion !== '' ? $direccion : null,
                $color_principal !== '' ? $color_principal : '#0d47a1',
                $texto_pie_pagina !== '' ? $texto_pie_pagina : null
            );

            $logoActualizado = false;
            $logoRuta = $empresaActual['logo'] ?? null;

            if (!empty($_FILES['logo']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
                $resultadoLogo = $this->procesarLogo($_FILES['logo'], $logoRuta);

                if (!$resultadoLogo['ok']) {
                    echo json_encode([
                        'ok' => false,
                        'msg' => $resultadoLogo['msg']
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }

                $logoRuta = $resultadoLogo['ruta'];
                $logoActualizado = $this->model->actualizarLogo($id_empresa, $logoRuta);
            }

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'ACTUALIZAR',
                $id_empresa,
                'Se actualizó la configuración de empresa.'
            );

            echo json_encode([
                'ok' => true,
                'msg' => 'Configuración guardada correctamente.',
                'logo' => $logoRuta,
                'actualizado' => (bool)$actualizado,
                'logo_actualizado' => (bool)$logoActualizado
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al guardar la configuración.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    private function validarColorHex(string $color): bool
    {
        return (bool)preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
    }

    private function procesarLogo(array $archivo, ?string $logoAnterior = null): array
    {
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return [
                'ok' => false,
                'msg' => 'Error al subir el logo.'
            ];
        }

        $maxSize = 2 * 1024 * 1024;

        if ($archivo['size'] > $maxSize) {
            return [
                'ok' => false,
                'msg' => 'El logo no debe superar 2 MB.'
            ];
        }

        $permitidos = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        $mime = mime_content_type($archivo['tmp_name']);

        if (!isset($permitidos[$mime])) {
            return [
                'ok' => false,
                'msg' => 'Formato de logo no permitido. Usa JPG, PNG o WEBP.'
            ];
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        $extension = $permitidos[$mime];
        $nombreArchivo = 'logo_empresa_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

        $destinoFisico = $this->uploadDir . $nombreArchivo;
        $rutaRelativa = $this->uploadUrl . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $destinoFisico)) {
            return [
                'ok' => false,
                'msg' => 'No fue posible guardar el logo en el servidor.'
            ];
        }

        if (!empty($logoAnterior)) {
            $archivoAnterior = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $logoAnterior);

            if (is_file($archivoAnterior)) {
                @unlink($archivoAnterior);
            }
        }

        return [
            'ok' => true,
            'ruta' => $rutaRelativa
        ];
    }
}
