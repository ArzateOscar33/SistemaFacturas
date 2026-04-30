<?php

class Roles extends Controller
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
       RESUMEN / KPIS
    ============================================================ */
    public function resumen()
    {
        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $resumen = $this->model->resumenRoles();

            $this->jsonResponse(true, 'Resumen cargado correctamente.', [
                'resumen' => $resumen
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al cargar el resumen de roles.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       LISTAR ROLES
    ============================================================ */
    public function listar()
    {
        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $busqueda = trim($_GET['busqueda'] ?? '');
            $estado = trim($_GET['estado'] ?? '');

            if ($estado !== '' && !in_array($estado, ['0', '1'], true)) {
                $this->jsonResponse(false, 'El estado seleccionado no es válido.');
                return;
            }

            $roles = $this->model->listarRoles($busqueda, $estado);

            $this->jsonResponse(true, 'Roles cargados correctamente.', [
                'roles' => $roles
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al listar roles.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       OBTENER ROL PARA EDITAR
    ============================================================ */
    public function obtener($id_rol = null)
    {
        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_rol = (int)$id_rol;

            if ($id_rol <= 0) {
                $this->jsonResponse(false, 'ID de rol no válido.');
                return;
            }

            $rol = $this->model->obtenerRolConPermisos($id_rol);

            if (!$rol) {
                $this->jsonResponse(false, 'El rol no existe.');
                return;
            }

            $this->jsonResponse(true, 'Rol cargado correctamente.', [
                'rol' => $rol
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al obtener el rol.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       REGISTRAR ROL
    ============================================================ */
    public function registrar()
    {
        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $estado = trim($_POST['estado'] ?? '1');
            $permisos = $_POST['permisos'] ?? [];

            if ($nombre === '') {
                $this->jsonResponse(false, 'El nombre del rol es obligatorio.');
                return;
            }

            if (mb_strlen($nombre) > 50) {
                $this->jsonResponse(false, 'El nombre del rol no puede exceder 50 caracteres.');
                return;
            }

            if ($descripcion !== '' && mb_strlen($descripcion) > 150) {
                $this->jsonResponse(false, 'La descripción no puede exceder 150 caracteres.');
                return;
            }

            if (!in_array($estado, ['0', '1'], true)) {
                $this->jsonResponse(false, 'El estado del rol no es válido.');
                return;
            }

            if ($this->model->existeNombreRol($nombre)) {
                $this->jsonResponse(false, 'Ya existe un rol con ese nombre.');
                return;
            }

            $id_rol = $this->model->registrarRol(
                $nombre,
                $descripcion !== '' ? $descripcion : null,
                (int)$estado
            );

            if (!$id_rol) {
                $this->jsonResponse(false, 'No fue posible registrar el rol.');
                return;
            }

            $this->model->guardarPermisosRol((int)$id_rol, is_array($permisos) ? $permisos : []);

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'REGISTRAR',
                'roles',
                (int)$id_rol,
                'Se registró el rol: ' . $nombre
            );

            $this->jsonResponse(true, 'Rol registrado correctamente.', [
                'id_rol' => (int)$id_rol
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al registrar el rol.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       ACTUALIZAR ROL
    ============================================================ */
    public function actualizar()
    {
        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_rol = (int)($_POST['id_rol'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $estado = trim($_POST['estado'] ?? '1');
            $permisos = $_POST['permisos'] ?? [];

            if ($id_rol <= 0) {
                $this->jsonResponse(false, 'ID de rol no válido.');
                return;
            }

            $rolActual = $this->model->obtenerRol($id_rol);

            if (!$rolActual) {
                $this->jsonResponse(false, 'El rol no existe.');
                return;
            }

            if ($nombre === '') {
                $this->jsonResponse(false, 'El nombre del rol es obligatorio.');
                return;
            }

            if (mb_strlen($nombre) > 50) {
                $this->jsonResponse(false, 'El nombre del rol no puede exceder 50 caracteres.');
                return;
            }

            if ($descripcion !== '' && mb_strlen($descripcion) > 150) {
                $this->jsonResponse(false, 'La descripción no puede exceder 150 caracteres.');
                return;
            }

            if (!in_array($estado, ['0', '1'], true)) {
                $this->jsonResponse(false, 'El estado del rol no es válido.');
                return;
            }

            if ($this->model->existeNombreRol($nombre, $id_rol)) {
                $this->jsonResponse(false, 'Ya existe otro rol con ese nombre.');
                return;
            }

            /*
                Protección básica:
                El rol Administrador no debería desactivarse accidentalmente.
                Si tu rol administrador no es ID 1, puedes ajustar esta regla.
            */
            if ($id_rol === 1 && (int)$estado === 0) {
                $this->jsonResponse(false, 'No puedes desactivar el rol Administrador principal.');
                return;
            }

            $actualizado = $this->model->actualizarRol(
                $id_rol,
                $nombre,
                $descripcion !== '' ? $descripcion : null,
                (int)$estado
            );

            $this->model->guardarPermisosRol($id_rol, is_array($permisos) ? $permisos : []);

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'ACTUALIZAR',
                'roles',
                $id_rol,
                'Se actualizó el rol: ' . $nombre
            );

            $this->jsonResponse(true, 'Rol actualizado correctamente.', [
                'actualizado' => $actualizado
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al actualizar el rol.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       CAMBIAR ESTADO
    ============================================================ */
    public function cambiarEstado()
    {
        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_rol = (int)($_POST['id_rol'] ?? 0);
            $estado = trim($_POST['estado'] ?? '');

            if ($id_rol <= 0) {
                $this->jsonResponse(false, 'ID de rol no válido.');
                return;
            }

            if (!in_array($estado, ['0', '1'], true)) {
                $this->jsonResponse(false, 'Estado no válido.');
                return;
            }

            $rol = $this->model->obtenerRol($id_rol);

            if (!$rol) {
                $this->jsonResponse(false, 'El rol no existe.');
                return;
            }

            if ($id_rol === 1 && (int)$estado === 0) {
                $this->jsonResponse(false, 'No puedes desactivar el rol Administrador principal.');
                return;
            }

            $this->model->cambiarEstado($id_rol, (int)$estado);

            $accionTexto = (int)$estado === 1 ? 'activó' : 'desactivó';

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'CAMBIAR_ESTADO',
                'roles',
                $id_rol,
                'Se ' . $accionTexto . ' el rol: ' . ($rol['nombre'] ?? '')
            );

            $this->jsonResponse(true, 'Estado actualizado correctamente.');
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al cambiar el estado del rol.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       ELIMINAR ROL
    ============================================================ */
    public function eliminar()
    {
        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_rol = (int)($_POST['id_rol'] ?? 0);

            if ($id_rol <= 0) {
                $this->jsonResponse(false, 'ID de rol no válido.');
                return;
            }

            if ($id_rol === 1) {
                $this->jsonResponse(false, 'No puedes eliminar el rol Administrador principal.');
                return;
            }

            $rol = $this->model->obtenerRol($id_rol);

            if (!$rol) {
                $this->jsonResponse(false, 'El rol no existe.');
                return;
            }

            $usuarios = $this->model->contarUsuariosPorRol($id_rol);
            $totalUsuarios = (int)($usuarios['total'] ?? 0);

            if ($totalUsuarios > 0) {
                $this->jsonResponse(false, 'No puedes eliminar este rol porque tiene usuarios asignados.');
                return;
            }

            $this->model->eliminarRol($id_rol);

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'ELIMINAR',
                'roles',
                $id_rol,
                'Se eliminó el rol: ' . ($rol['nombre'] ?? '')
            );

            $this->jsonResponse(true, 'Rol eliminado correctamente.');
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al eliminar el rol.', [
                'error' => $e->getMessage()
            ]);
        }
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
