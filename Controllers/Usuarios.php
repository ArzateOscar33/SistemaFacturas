<?php

class Usuarios extends Controller
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
        requirePermiso('usuarios', 'ver');

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

            $usuarios = $this->model->listarUsuarios($buscar, $estado);

            echo json_encode([
                'ok' => true,
                'data' => $usuarios
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al listar usuarios.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function roles()
    {
        requirePermiso('usuarios', 'ver');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $roles = $this->model->listarRoles();

            echo json_encode([
                'ok' => true,
                'data' => $roles
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al listar roles.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener($id_usuario = null)
    {
        requirePermiso('usuarios', 'ver');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id_usuario = (int)($id_usuario ?? 0);

        if ($id_usuario <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'ID de usuario inválido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $usuario = $this->model->obtenerUsuario($id_usuario);

            if (!$usuario) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Usuario no encontrado.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode([
                'ok' => true,
                'data' => $usuario
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al obtener el usuario.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function registrar()
    {
        requirePermiso('usuarios', 'crear');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id_rol = (int)($_POST['id_rol'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $correo = strtolower(trim($_POST['correo'] ?? ''));
        $password = trim($_POST['password'] ?? '');
        $confirmar_password = trim($_POST['confirmar_password'] ?? '');
        $estado = trim($_POST['estado'] ?? '1');

        if ($id_rol <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Selecciona un rol válido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($nombre === '') {
            echo json_encode([
                'ok' => false,
                'msg' => 'El nombre es obligatorio.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($usuario === '') {
            echo json_encode([
                'ok' => false,
                'msg' => 'El usuario es obligatorio.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($password === '') {
            echo json_encode([
                'ok' => false,
                'msg' => 'La contraseña es obligatoria al registrar un usuario.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($password !== $confirmar_password) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Las contraseñas no coinciden.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($nombre) > 100) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El nombre no puede superar 100 caracteres.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($apellido) > 100) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El apellido no puede superar 100 caracteres.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($usuario) > 50) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El usuario no puede superar 50 caracteres.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($password) < 6) {
            echo json_encode([
                'ok' => false,
                'msg' => 'La contraseña debe tener mínimo 6 caracteres.'
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
                'msg' => 'El estado del usuario no es válido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $rol = $this->model->obtenerRol($id_rol);

            if (!$rol || (int)$rol['estado'] !== 1) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'El rol seleccionado no existe o está inactivo.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($this->model->existeUsuario($usuario)) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Ya existe un usuario con ese nombre de usuario.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($correo !== '' && $this->model->existeCorreo($correo)) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Ya existe un usuario con ese correo.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $hashPassword = password_hash($password, PASSWORD_BCRYPT);

            $id_usuario = $this->model->registrarUsuario(
                $id_rol,
                $nombre,
                $apellido !== '' ? $apellido : null,
                $usuario,
                $correo !== '' ? $correo : null,
                $hashPassword,
                (int)$estado
            );

            if (!$id_usuario) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'No fue posible registrar el usuario.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'REGISTRAR',
                (int)$id_usuario,
                'Se registró el usuario: ' . $usuario
            );

            echo json_encode([
                'ok' => true,
                'msg' => 'Usuario registrado correctamente.',
                'id_usuario' => (int)$id_usuario
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al registrar el usuario.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function actualizar()
    {
        requirePermiso('usuarios', 'editar');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id_usuario = (int)($_POST['id_usuario'] ?? 0);
        $id_rol = (int)($_POST['id_rol'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $correo = strtolower(trim($_POST['correo'] ?? ''));
        $password = trim($_POST['password'] ?? '');
        $confirmar_password = trim($_POST['confirmar_password'] ?? '');
        $estado = trim($_POST['estado'] ?? '1');

        if ($id_usuario <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'ID de usuario inválido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($id_rol <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Selecciona un rol válido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($nombre === '') {
            echo json_encode([
                'ok' => false,
                'msg' => 'El nombre es obligatorio.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($usuario === '') {
            echo json_encode([
                'ok' => false,
                'msg' => 'El usuario es obligatorio.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($nombre) > 100) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El nombre no puede superar 100 caracteres.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($apellido) > 100) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El apellido no puede superar 100 caracteres.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($usuario) > 50) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El usuario no puede superar 50 caracteres.'
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

        if ($password !== '' || $confirmar_password !== '') {
            if ($password !== $confirmar_password) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Las contraseñas no coinciden.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if (strlen($password) < 6) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'La nueva contraseña debe tener mínimo 6 caracteres.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        if (!in_array($estado, ['0', '1'], true)) {
            echo json_encode([
                'ok' => false,
                'msg' => 'El estado del usuario no es válido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ((int)($_SESSION['id_usuario'] ?? 0) === $id_usuario && (int)$estado === 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'No puedes desactivar tu propio usuario mientras tienes sesión activa.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $usuarioActual = $this->model->obtenerUsuario($id_usuario);

            if (!$usuarioActual) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Usuario no encontrado.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $rol = $this->model->obtenerRol($id_rol);

            if (!$rol || (int)$rol['estado'] !== 1) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'El rol seleccionado no existe o está inactivo.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($this->model->existeUsuario($usuario, $id_usuario)) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Ya existe otro usuario con ese nombre de usuario.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($correo !== '' && $this->model->existeCorreo($correo, $id_usuario)) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Ya existe otro usuario con ese correo.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $actualizado = $this->model->actualizarUsuario(
                $id_usuario,
                $id_rol,
                $nombre,
                $apellido !== '' ? $apellido : null,
                $usuario,
                $correo !== '' ? $correo : null,
                (int)$estado
            );

            if ($password !== '') {
                $hashPassword = password_hash($password, PASSWORD_BCRYPT);
                $this->model->actualizarPassword($id_usuario, $hashPassword);
            }

            if (!$actualizado && $password === '') {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'No fue posible actualizar el usuario o no hubo cambios.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'ACTUALIZAR',
                $id_usuario,
                'Se actualizó el usuario: ' . $usuario
            );

            echo json_encode([
                'ok' => true,
                'msg' => 'Usuario actualizado correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al actualizar el usuario.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function cambiarEstado()
    {
        requirePermiso('usuarios', 'eliminar');

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id_usuario = (int)($_POST['id_usuario'] ?? 0);
        $estado = trim($_POST['estado'] ?? '');

        if ($id_usuario <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'ID de usuario inválido.'
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

        if ((int)($_SESSION['id_usuario'] ?? 0) === $id_usuario && (int)$estado === 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'No puedes desactivar tu propio usuario mientras tienes sesión activa.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $usuario = $this->model->obtenerUsuario($id_usuario);

            if (!$usuario) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Usuario no encontrado.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $ok = $this->model->cambiarEstadoUsuario($id_usuario, (int)$estado);

            if (!$ok) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'No fue posible cambiar el estado del usuario.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $accion = ((int)$estado === 1) ? 'ACTIVAR' : 'DESACTIVAR';

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                $accion,
                $id_usuario,
                'Se cambió el estado del usuario: ' . $usuario['usuario']
            );

            echo json_encode([
                'ok' => true,
                'msg' => ((int)$estado === 1)
                    ? 'Usuario activado correctamente.'
                    : 'Usuario desactivado correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al cambiar el estado del usuario.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
