<?php

class Perfil extends Controller
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

    /* ============================================================
       OBTENER PERFIL
    ============================================================ */
    public function obtener()
    {
        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_usuario = (int)($_SESSION['id_usuario'] ?? 0);

            if ($id_usuario <= 0) {
                $this->jsonResponse(false, 'No fue posible identificar al usuario.');
                return;
            }

            $perfil = $this->model->obtenerPerfil($id_usuario);

            if (!$perfil) {
                $this->jsonResponse(false, 'No se encontró la información del perfil.');
                return;
            }

            $this->jsonResponse(true, 'Perfil cargado correctamente.', [
                'perfil' => $perfil
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al obtener el perfil.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       ACTUALIZAR PERFIL
    ============================================================ */
    public function actualizar()
    {
        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_usuario = (int)($_SESSION['id_usuario'] ?? 0);

            if ($id_usuario <= 0) {
                $this->jsonResponse(false, 'No fue posible identificar al usuario.');
                return;
            }

            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $usuario = trim($_POST['usuario'] ?? '');
            $correo = trim($_POST['correo'] ?? '');

            if ($nombre === '') {
                $this->jsonResponse(false, 'El nombre es obligatorio.');
                return;
            }

            if (mb_strlen($nombre) > 100) {
                $this->jsonResponse(false, 'El nombre no puede exceder 100 caracteres.');
                return;
            }

            if ($apellido !== '' && mb_strlen($apellido) > 100) {
                $this->jsonResponse(false, 'El apellido no puede exceder 100 caracteres.');
                return;
            }

            if ($usuario === '') {
                $this->jsonResponse(false, 'El usuario es obligatorio.');
                return;
            }

            if (mb_strlen($usuario) > 50) {
                $this->jsonResponse(false, 'El usuario no puede exceder 50 caracteres.');
                return;
            }

            if (!$this->usuarioValido($usuario)) {
                $this->jsonResponse(false, 'El usuario solo puede contener letras, números, punto, guion y guion bajo.');
                return;
            }

            if ($correo !== '') {
                if (mb_strlen($correo) > 120) {
                    $this->jsonResponse(false, 'El correo no puede exceder 120 caracteres.');
                    return;
                }

                if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $this->jsonResponse(false, 'El correo no tiene un formato válido.');
                    return;
                }
            }

            if ($this->model->existeUsuario($usuario, $id_usuario)) {
                $this->jsonResponse(false, 'Ese nombre de usuario ya está en uso.');
                return;
            }

            if ($correo !== '' && $this->model->existeCorreo($correo, $id_usuario)) {
                $this->jsonResponse(false, 'Ese correo ya está en uso.');
                return;
            }

            $this->model->actualizarPerfil(
                $id_usuario,
                $nombre,
                $apellido !== '' ? $apellido : null,
                $usuario,
                $correo !== '' ? $correo : null
            );

            $_SESSION['nombre'] = $nombre;
            $_SESSION['apellido'] = $apellido !== '' ? $apellido : null;
            $_SESSION['usuario'] = $usuario;
            $_SESSION['correo'] = $correo !== '' ? $correo : null;

            $this->model->registrarBitacora(
                $id_usuario,
                'ACTUALIZAR',
                'usuarios',
                $id_usuario,
                'El usuario actualizó la información de su perfil.'
            );

            $perfil = $this->model->obtenerPerfil($id_usuario);

            $this->jsonResponse(true, 'Perfil actualizado correctamente.', [
                'perfil' => $perfil
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al actualizar el perfil.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       CAMBIAR CONTRASEÑA
    ============================================================ */
    public function cambiarPassword()
    {
        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_usuario = (int)($_SESSION['id_usuario'] ?? 0);

            if ($id_usuario <= 0) {
                $this->jsonResponse(false, 'No fue posible identificar al usuario.');
                return;
            }

            $password_actual = trim($_POST['password_actual'] ?? '');
            $password_nueva = trim($_POST['password_nueva'] ?? '');
            $password_confirmar = trim($_POST['password_confirmar'] ?? '');

            if ($password_actual === '' || $password_nueva === '' || $password_confirmar === '') {
                $this->jsonResponse(false, 'Todos los campos de contraseña son obligatorios.');
                return;
            }

            if (mb_strlen($password_nueva) < 8) {
                $this->jsonResponse(false, 'La nueva contraseña debe tener al menos 8 caracteres.');
                return;
            }

            if (mb_strlen($password_nueva) > 72) {
                $this->jsonResponse(false, 'La nueva contraseña no puede exceder 72 caracteres.');
                return;
            }

            if ($password_nueva !== $password_confirmar) {
                $this->jsonResponse(false, 'La confirmación no coincide con la nueva contraseña.');
                return;
            }

            if ($password_actual === $password_nueva) {
                $this->jsonResponse(false, 'La nueva contraseña debe ser diferente a la actual.');
                return;
            }

            $usuarioPassword = $this->model->obtenerPassword($id_usuario);

            if (!$usuarioPassword) {
                $this->jsonResponse(false, 'No se encontró la cuenta del usuario.');
                return;
            }

            if (!password_verify($password_actual, $usuarioPassword['password'])) {
                $this->jsonResponse(false, 'La contraseña actual es incorrecta.');
                return;
            }

            $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);

            $this->model->actualizarPassword($id_usuario, $password_hash);

            $this->model->registrarBitacora(
                $id_usuario,
                'CAMBIAR_PASSWORD',
                'usuarios',
                $id_usuario,
                'El usuario cambió su contraseña desde Mi perfil.'
            );

            $this->jsonResponse(true, 'Contraseña actualizada correctamente.');
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al cambiar la contraseña.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       VALIDACIONES
    ============================================================ */
    private function usuarioValido(string $usuario): bool
    {
        return (bool)preg_match('/^[A-Za-z0-9._-]+$/', $usuario);
    }

    /* ============================================================
       RESPUESTAS JSON
    ============================================================ */
    private function jsonHeader()
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    private function jsonResponse(bool $ok, string $msg, array $extra = [])
    {
        echo json_encode(array_merge([
            'ok' => $ok,
            'msg' => $msg
        ], $extra), JSON_UNESCAPED_UNICODE);
    }
}
