<?php

class Login extends Controller
{
    public function __construct()
    {
        session_start();
        parent::__construct();
    }

    public function index()
    {
        if (!empty($_SESSION['activo']) && $_SESSION['activo'] === true) {
            header('Location: ' . BASE_URL . 'admin');
            exit;
        }

        $data['title'] = 'Iniciar Sesión';
        $this->views->getView('login', 'index', $data);
    }

    public function validar()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $usuario = trim($_POST['usuario'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($usuario === '' || $password === '') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Usuario/correo y contraseña son obligatorios.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $data = $this->model->buscarUsuario($usuario);

            if (!$data) {
                $this->model->registrarBitacora(
                    null,
                    'LOGIN_FALLIDO',
                    'Intento con usuario no encontrado: ' . $usuario
                );

                echo json_encode([
                    'ok' => false,
                    'msg' => 'Credenciales incorrectas.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ((int)$data['estado'] !== 1) {
                $this->model->registrarBitacora(
                    (int)$data['id_usuario'],
                    'LOGIN_BLOQUEADO',
                    'Usuario inactivo intentó iniciar sesión.'
                );

                echo json_encode([
                    'ok' => false,
                    'msg' => 'El usuario está inactivo. Contacta al administrador.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if (!password_verify($password, $data['password'])) {
                $this->model->registrarBitacora(
                    (int)$data['id_usuario'],
                    'LOGIN_FALLIDO',
                    'Contraseña incorrecta.'
                );

                echo json_encode([
                    'ok' => false,
                    'msg' => 'Credenciales incorrectas.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            /*
                Cargar permisos antes de guardar sesión.
                El rol administrador principal ID 1 podrá hacer todo desde el helper puede(),
                pero aun así cargamos sus permisos si existen.
            */
            $permisosRaw = $this->model->obtenerPermisosPorRol((int)$data['id_rol']);

            $permisos = [];

            foreach ($permisosRaw as $permiso) {
                $modulo = trim((string)($permiso['modulo'] ?? ''));

                if ($modulo === '') {
                    continue;
                }

                $permisos[$modulo] = [
                    'ver'      => (int)($permiso['puede_ver'] ?? 0) === 1,
                    'crear'    => (int)($permiso['puede_crear'] ?? 0) === 1,
                    'editar'   => (int)($permiso['puede_editar'] ?? 0) === 1,
                    'eliminar' => (int)($permiso['puede_eliminar'] ?? 0) === 1
                ];
            }

            session_regenerate_id(true);

            $_SESSION['activo'] = true;
            $_SESSION['id_usuario'] = (int)$data['id_usuario'];
            $_SESSION['id_rol'] = (int)$data['id_rol'];
            $_SESSION['rol'] = $data['rol'] ?? '';
            $_SESSION['nombre'] = $data['nombre'] ?? '';
            $_SESSION['apellido'] = $data['apellido'] ?? '';
            $_SESSION['usuario'] = $data['usuario'] ?? '';
            $_SESSION['correo'] = $data['correo'] ?? '';
            $_SESSION['permisos'] = $permisos;

            $this->model->actualizarUltimoAcceso((int)$data['id_usuario']);

            $this->model->registrarBitacora(
                (int)$data['id_usuario'],
                'LOGIN_EXITOSO',
                'Inicio de sesión correcto.'
            );

            echo json_encode([
                'ok' => true,
                'msg' => 'Inicio de sesión correcto.',
                'redirect' => BASE_URL . 'admin'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al iniciar sesión.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function salir()
    {
        session_unset();
        session_destroy();

        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}
