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

            $cliente['direcciones'] = $this->model->listarDireccionesCliente($id_cliente);

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

            /*
             * Compatibilidad:
             * Si el formulario principal todavía manda "direccion",
             * también la registramos como dirección principal en cliente_direcciones.
             */
            if ($direccion !== '') {
                $this->model->registrarDireccionCliente(
                    (int)$id_cliente,
                    'Principal',
                    $direccion,
                    1,
                    1
                );
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

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'ACTUALIZAR',
                $id_cliente,
                'Se actualizó el cliente: ' . $nombre_cliente
            );

            echo json_encode([
                'ok' => true,
                'msg' => 'Cliente actualizado correctamente.',
                'actualizado' => (bool)$actualizado
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

    /* ============================================================
       DIRECCIONES DEL CLIENTE
    ============================================================ */

    public function listarDirecciones($id_cliente = null)
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

            $direcciones = $this->model->listarDireccionesCliente($id_cliente);

            echo json_encode([
                'ok' => true,
                'data' => $direcciones
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al listar direcciones del cliente.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function listarDireccionesActivas($id_cliente = null)
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

            $direcciones = $this->model->listarDireccionesClienteActivas($id_cliente);

            echo json_encode([
                'ok' => true,
                'data' => $direcciones
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al listar direcciones activas del cliente.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtenerDireccion($id_direccion = null)
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

        $id_direccion = (int)($id_direccion ?? 0);

        if ($id_direccion <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'ID de dirección inválido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $direccion = $this->model->obtenerDireccionCliente($id_direccion);

            if (!$direccion) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Dirección no encontrada.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode([
                'ok' => true,
                'data' => $direccion
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al obtener la dirección.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function registrarDireccion()
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

        $validacion = $this->validarPayloadDireccion(false);

        if (!$validacion['ok']) {
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }

        $data = $validacion['data'];

        try {
            $cliente = $this->model->obtenerCliente($data['id_cliente']);

            if (!$cliente) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Cliente no encontrado.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $id_direccion = $this->model->registrarDireccionCliente(
                $data['id_cliente'],
                $data['alias'],
                $data['direccion'],
                $data['es_principal'],
                $data['estado']
            );

            if (!$id_direccion) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'No fue posible registrar la dirección.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            /*
             * Si es principal, sincronizamos clientes.direccion como compatibilidad.
             */
            if ($data['es_principal'] === 1) {
                $this->model->actualizarCliente(
                    (int)$cliente['id_cliente'],
                    $cliente['codigo_cliente'],
                    $cliente['nombre_cliente'],
                    $cliente['rfc'] ?? null,
                    $cliente['correo'] ?? null,
                    $cliente['telefono'] ?? null,
                    $data['direccion'],
                    (int)$cliente['estado']
                );
            }

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'REGISTRAR_DIRECCION',
                $data['id_cliente'],
                'Se registró una dirección para el cliente: ' . $cliente['nombre_cliente']
            );

            echo json_encode([
                'ok' => true,
                'msg' => 'Dirección registrada correctamente.',
                'id_direccion' => (int)$id_direccion
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al registrar la dirección.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function actualizarDireccion()
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

        $validacion = $this->validarPayloadDireccion(true);

        if (!$validacion['ok']) {
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }

        $data = $validacion['data'];

        try {
            $cliente = $this->model->obtenerCliente($data['id_cliente']);

            if (!$cliente) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Cliente no encontrado.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $direccionActual = $this->model->obtenerDireccionCliente($data['id_direccion']);

            if (!$direccionActual) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Dirección no encontrada.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ((int)$direccionActual['id_cliente'] !== $data['id_cliente']) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'La dirección no pertenece al cliente seleccionado.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $actualizado = $this->model->actualizarDireccionCliente(
                $data['id_direccion'],
                $data['id_cliente'],
                $data['alias'],
                $data['direccion'],
                $data['es_principal'],
                $data['estado']
            );

            /*
             * Si queda como principal, actualizamos clientes.direccion.
             */
            if ($data['es_principal'] === 1 && $data['estado'] === 1) {
                $this->model->actualizarCliente(
                    (int)$cliente['id_cliente'],
                    $cliente['codigo_cliente'],
                    $cliente['nombre_cliente'],
                    $cliente['rfc'] ?? null,
                    $cliente['correo'] ?? null,
                    $cliente['telefono'] ?? null,
                    $data['direccion'],
                    (int)$cliente['estado']
                );
            }

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'ACTUALIZAR_DIRECCION',
                $data['id_cliente'],
                'Se actualizó una dirección del cliente: ' . $cliente['nombre_cliente']
            );

            echo json_encode([
                'ok' => true,
                'msg' => 'Dirección actualizada correctamente.',
                'actualizado' => (bool)$actualizado
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al actualizar la dirección.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function eliminarDireccion()
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

        $id_direccion = (int)($_POST['id_direccion'] ?? 0);
        $id_cliente = (int)($_POST['id_cliente'] ?? 0);

        if ($id_direccion <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'ID de dirección inválido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

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

            $direccion = $this->model->obtenerDireccionCliente($id_direccion);

            if (!$direccion) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Dirección no encontrada.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ((int)$direccion['id_cliente'] !== $id_cliente) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'La dirección no pertenece al cliente seleccionado.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $ok = $this->model->eliminarDireccionCliente($id_direccion, $id_cliente);

            if (!$ok) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'No fue posible eliminar la dirección.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'ELIMINAR_DIRECCION',
                $id_cliente,
                'Se eliminó una dirección del cliente: ' . $cliente['nombre_cliente']
            );

            echo json_encode([
                'ok' => true,
                'msg' => 'Dirección eliminada correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al eliminar la dirección.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function marcarDireccionPrincipal()
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

        $id_direccion = (int)($_POST['id_direccion'] ?? 0);
        $id_cliente = (int)($_POST['id_cliente'] ?? 0);

        if ($id_direccion <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'ID de dirección inválido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

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

            $direccion = $this->model->obtenerDireccionCliente($id_direccion);

            if (!$direccion) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Dirección no encontrada.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ((int)$direccion['id_cliente'] !== $id_cliente) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'La dirección no pertenece al cliente seleccionado.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $ok = $this->model->marcarDireccionPrincipal($id_direccion, $id_cliente);

            if (!$ok) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'No fue posible marcar la dirección como principal.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            /*
             * Sincroniza clientes.direccion con la nueva dirección principal.
             */
            $this->model->actualizarCliente(
                (int)$cliente['id_cliente'],
                $cliente['codigo_cliente'],
                $cliente['nombre_cliente'],
                $cliente['rfc'] ?? null,
                $cliente['correo'] ?? null,
                $cliente['telefono'] ?? null,
                $direccion['direccion'],
                (int)$cliente['estado']
            );

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'DIRECCION_PRINCIPAL',
                $id_cliente,
                'Se marcó una dirección como principal para el cliente: ' . $cliente['nombre_cliente']
            );

            echo json_encode([
                'ok' => true,
                'msg' => 'Dirección marcada como principal correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al marcar la dirección como principal.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    private function validarPayloadDireccion(bool $esEdicion = false): array
    {
        $id_direccion = (int)($_POST['id_direccion'] ?? 0);
        $id_cliente = (int)($_POST['id_cliente'] ?? 0);
        $alias = trim($_POST['alias'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $es_principal = trim($_POST['es_principal'] ?? '0');
        $estado = trim($_POST['estado'] ?? '1');

        if ($esEdicion && $id_direccion <= 0) {
            return [
                'ok' => false,
                'msg' => 'ID de dirección inválido.'
            ];
        }

        if ($id_cliente <= 0) {
            return [
                'ok' => false,
                'msg' => 'ID de cliente inválido.'
            ];
        }

        if ($direccion === '') {
            return [
                'ok' => false,
                'msg' => 'La dirección es obligatoria.'
            ];
        }

        if (strlen($alias) > 100) {
            return [
                'ok' => false,
                'msg' => 'El alias no puede superar 100 caracteres.'
            ];
        }

        if (!in_array($es_principal, ['0', '1'], true)) {
            return [
                'ok' => false,
                'msg' => 'El valor de dirección principal no es válido.'
            ];
        }

        if (!in_array($estado, ['0', '1'], true)) {
            return [
                'ok' => false,
                'msg' => 'El estado de la dirección no es válido.'
            ];
        }

        return [
            'ok' => true,
            'data' => [
                'id_direccion' => $id_direccion,
                'id_cliente' => $id_cliente,
                'alias' => $alias !== '' ? $alias : null,
                'direccion' => $direccion,
                'es_principal' => (int)$es_principal,
                'estado' => (int)$estado
            ]
        ];
    }
}
