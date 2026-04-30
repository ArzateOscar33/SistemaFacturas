<?php

class Bitacora extends Controller
{
    public function __construct()
    {
        session_start();

        if (empty($_SESSION['activo'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        parent::__construct();
    }

    public function listar()
    {
        requirePermiso('bitacora', 'ver');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $buscar = trim($_GET['buscar'] ?? '');
        $id_usuario = trim($_GET['id_usuario'] ?? '');
        $modulo = trim($_GET['modulo'] ?? '');
        $accion = trim($_GET['accion'] ?? '');
        $fecha_inicio = trim($_GET['fecha_inicio'] ?? '');
        $fecha_fin = trim($_GET['fecha_fin'] ?? '');
        $limite = (int)($_GET['limite'] ?? 50);

        if ($id_usuario !== '' && !ctype_digit($id_usuario)) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El usuario seleccionado no es válido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($fecha_inicio !== '' && !$this->validarFecha($fecha_inicio)) {
            echo json_encode([
                'ok' => false,
                'msg' => 'La fecha inicial no es válida.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($fecha_fin !== '' && !$this->validarFecha($fecha_fin)) {
            echo json_encode([
                'ok' => false,
                'msg' => 'La fecha final no es válida.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($fecha_inicio !== '' && $fecha_fin !== '' && $fecha_inicio > $fecha_fin) {
            echo json_encode([
                'ok' => false,
                'msg' => 'La fecha inicial no puede ser mayor que la fecha final.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $eventos = $this->model->listarEventos(
                $buscar,
                $id_usuario,
                $modulo,
                $accion,
                $fecha_inicio,
                $fecha_fin,
                $limite
            );

            $resumen = $this->model->resumenEventos(
                $buscar,
                $id_usuario,
                $modulo,
                $accion,
                $fecha_inicio,
                $fecha_fin
            );

            echo json_encode([
                'ok' => true,
                'data' => $eventos,
                'resumen' => [
                    'total_eventos' => (int)($resumen['total_eventos'] ?? 0),
                    'total_logins' => (int)($resumen['total_logins'] ?? 0),
                    'total_cambios' => (int)($resumen['total_cambios'] ?? 0),
                    'total_fallidos' => (int)($resumen['total_fallidos'] ?? 0)
                ]
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al listar la bitácora.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function filtros()
    {
        requirePermiso('bitacora', 'ver');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $usuarios = $this->model->listarUsuariosFiltro();
            $modulos = $this->model->listarModulos();
            $acciones = $this->model->listarAcciones();

            echo json_encode([
                'ok' => true,
                'data' => [
                    'usuarios' => $usuarios,
                    'modulos' => $modulos,
                    'acciones' => $acciones
                ]
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al cargar filtros de bitácora.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    private function validarFecha(string $fecha): bool
    {
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        return $dt && $dt->format('Y-m-d') === $fecha;
    }
}
