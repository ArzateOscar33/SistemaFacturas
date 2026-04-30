<?php

class Clientes extends Controller
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
        requirePermiso('clientes', 'ver');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $buscar = trim($_GET['buscar'] ?? '');
            $estado = trim($_GET['estado'] ?? '');

            $clientes = $this->model->listarClientes($buscar, $estado);

            echo json_encode([
                'ok' => true,
                'data' => $clientes
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al listar clientes.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener($id_cliente = null)
    {
        requirePermiso('clientes', 'ver');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id_cliente = (int)($id_cliente ?? 0);

        if ($id_cliente <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'ID de cliente inválido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $cliente = $this->model->obtenerCliente($id_cliente);

            if (!$cliente) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Cliente no encontrado.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode([
                'ok' => true,
                'data' => $cliente
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al obtener el cliente.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function registrar()
    {
        requirePermiso('clientes', 'crear');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $codigo_cliente = strtoupper(trim($_POST['codigo_cliente'] ?? ''));
        $nombre_cliente = trim($_POST['nombre_cliente'] ?? '');
        $rfc = strtoupper(trim($_POST['rfc'] ?? ''));
        $correo = strtolower(trim($_POST['correo'] ?? ''));
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $estado = trim($_POST['estado'] ?? '1');

        if ($codigo_cliente === '') {
            echo json_encode([
                'ok' => false,
                'msg' => 'El código del cliente es obligatorio.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($nombre_cliente === '') {
            echo json_encode([
                'ok' => false,
                'msg' => 'El nombre del cliente es obligatorio.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($codigo_cliente) > 30) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El código del cliente no puede superar 30 caracteres.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($nombre_cliente) > 150) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El nombre del cliente no puede superar 150 caracteres.'
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

        if (!in_array($estado, ['0', '1'], true)) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El estado del cliente no es válido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $existeCodigo = $this->model->existeCodigoCliente($codigo_cliente);

            if ($existeCodigo) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Ya existe un cliente con ese código.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $id_cliente = $this->model->registrarCliente(
                $codigo_cliente,
                $nombre_cliente,
                $rfc !== '' ? $rfc : null,
                $correo !== '' ? $correo : null,
                $telefono !== '' ? $telefono : null,
                $direccion !== '' ? $direccion : null,
                (int)$estado
            );

            if (!$id_cliente) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'No fue posible registrar el cliente.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'REGISTRAR',
                (int)$id_cliente,
                'Se registró el cliente: ' . $nombre_cliente
            );

            echo json_encode([
                'ok' => true,
                'msg' => 'Cliente registrado correctamente.',
                'id_cliente' => (int)$id_cliente
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al registrar el cliente.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function actualizar()
    {
        requirePermiso('clientes', 'editar');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id_cliente = (int)($_POST['id_cliente'] ?? 0);
        $codigo_cliente = strtoupper(trim($_POST['codigo_cliente'] ?? ''));
        $nombre_cliente = trim($_POST['nombre_cliente'] ?? '');
        $rfc = strtoupper(trim($_POST['rfc'] ?? ''));
        $correo = strtolower(trim($_POST['correo'] ?? ''));
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $estado = trim($_POST['estado'] ?? '1');

        if ($id_cliente <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'ID de cliente inválido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($codigo_cliente === '') {
            echo json_encode([
                'ok' => false,
                'msg' => 'El código del cliente es obligatorio.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($nombre_cliente === '') {
            echo json_encode([
                'ok' => false,
                'msg' => 'El nombre del cliente es obligatorio.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($codigo_cliente) > 30) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El código del cliente no puede superar 30 caracteres.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($nombre_cliente) > 150) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El nombre del cliente no puede superar 150 caracteres.'
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

        if (!in_array($estado, ['0', '1'], true)) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El estado del cliente no es válido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $clienteActual = $this->model->obtenerCliente($id_cliente);

            if (!$clienteActual) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Cliente no encontrado.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $existeCodigo = $this->model->existeCodigoCliente($codigo_cliente, $id_cliente);

            if ($existeCodigo) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Ya existe otro cliente con ese código.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $actualizado = $this->model->actualizarCliente(
                $id_cliente,
                $codigo_cliente,
                $nombre_cliente,
                $rfc !== '' ? $rfc : null,
                $correo !== '' ? $correo : null,
                $telefono !== '' ? $telefono : null,
                $direccion !== '' ? $direccion : null,
                (int)$estado
            );

            if (!$actualizado) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'No fue posible actualizar el cliente o no hubo cambios.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'ACTUALIZAR',
                $id_cliente,
                'Se actualizó el cliente: ' . $nombre_cliente
            );

            echo json_encode([
                'ok' => true,
                'msg' => 'Cliente actualizado correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al actualizar el cliente.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function cambiarEstado()
    {
        requirePermiso('clientes', 'eliminar');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id_cliente = (int)($_POST['id_cliente'] ?? 0);
        $estado = trim($_POST['estado'] ?? '');

        if ($id_cliente <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'ID de cliente inválido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!in_array($estado, ['0', '1'], true)) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El estado enviado no es válido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $cliente = $this->model->obtenerCliente($id_cliente);

            if (!$cliente) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Cliente no encontrado.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $ok = $this->model->cambiarEstadoCliente($id_cliente, (int)$estado);

            if (!$ok) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'No fue posible cambiar el estado del cliente.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $accion = ((int)$estado === 1) ? 'ACTIVAR' : 'DESACTIVAR';

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                $accion,
                $id_cliente,
                'Se cambió el estado del cliente: ' . $cliente['nombre_cliente']
            );

            echo json_encode([
                'ok' => true,
                'msg' => ((int)$estado === 1)
                    ? 'Cliente activado correctamente.'
                    : 'Cliente desactivado correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al cambiar el estado del cliente.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
