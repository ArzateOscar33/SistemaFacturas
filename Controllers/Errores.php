<?php

class Errores extends Controller
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
        requirePermiso('errores', 'ver');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $resumen = $this->model->resumenErrores();

            $this->jsonResponse(true, 'Resumen cargado correctamente.', [
                'resumen' => $resumen
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al cargar el resumen de errores.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       LISTAR ERRORES
    ============================================================ */
    public function listar()
    {
        requirePermiso('errores', 'ver');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $filtros = $this->obtenerFiltros();

            if (!$filtros['ok']) {
                $this->jsonResponse(false, $filtros['msg']);
                return;
            }

            $data = $filtros['data'];

            $errores = $this->model->listarErrores(
                $data['busqueda'],
                $data['tipo_error'],
                $data['nivel'],
                $data['estado'],
                $data['modulo'],
                $data['fecha_inicio'],
                $data['fecha_fin'],
                $data['limite']
            );

            $this->jsonResponse(true, 'Errores cargados correctamente.', [
                'errores' => $errores
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al listar los errores.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       OBTENER DETALLE
    ============================================================ */
    public function obtener($id_error = null)
    {
        requirePermiso('errores', 'ver');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_error = (int)$id_error;

            if ($id_error <= 0) {
                $this->jsonResponse(false, 'ID de error no válido.');
                return;
            }

            $error = $this->model->obtenerError($id_error);

            if (!$error) {
                $this->jsonResponse(false, 'El error solicitado no existe.');
                return;
            }

            $this->jsonResponse(true, 'Error cargado correctamente.', [
                'error' => $error
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al obtener el detalle.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       GUARDAR REVISIÓN
    ============================================================ */
    public function guardarRevision()
    {
        requirePermiso('errores', 'editar');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_error = (int)($_POST['id_error'] ?? 0);
            $estado = trim($_POST['estado'] ?? '');
            $nota_revision = trim($_POST['nota_revision'] ?? '');

            if ($id_error <= 0) {
                $this->jsonResponse(false, 'ID de error no válido.');
                return;
            }

            if (!$this->estadoValido($estado)) {
                $this->jsonResponse(false, 'Estado de revisión no válido.');
                return;
            }

            if ($nota_revision !== '' && mb_strlen($nota_revision) > 5000) {
                $this->jsonResponse(false, 'La nota de revisión no puede exceder 5000 caracteres.');
                return;
            }

            $errorActual = $this->model->obtenerError($id_error);

            if (!$errorActual) {
                $this->jsonResponse(false, 'El error no existe.');
                return;
            }

            $this->model->actualizarRevision(
                $id_error,
                $estado,
                $nota_revision !== '' ? $nota_revision : null,
                $_SESSION['id_usuario'] ?? null
            );

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'REVISAR',
                'errores_sistema',
                $id_error,
                'Se actualizó la revisión del error #' . $id_error . ' a estado: ' . $estado
            );

            $this->jsonResponse(true, 'Revisión guardada correctamente.');
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al guardar la revisión.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       CAMBIAR ESTADO RÁPIDO
    ============================================================ */
    public function cambiarEstado()
    {
        requirePermiso('errores', 'editar');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_error = (int)($_POST['id_error'] ?? 0);
            $estado = trim($_POST['estado'] ?? '');

            if ($id_error <= 0) {
                $this->jsonResponse(false, 'ID de error no válido.');
                return;
            }

            if (!$this->estadoValido($estado)) {
                $this->jsonResponse(false, 'Estado no válido.');
                return;
            }

            $errorActual = $this->model->obtenerError($id_error);

            if (!$errorActual) {
                $this->jsonResponse(false, 'El error no existe.');
                return;
            }

            $this->model->cambiarEstado(
                $id_error,
                $estado,
                $_SESSION['id_usuario'] ?? null
            );

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'CAMBIAR_ESTADO',
                'errores_sistema',
                $id_error,
                'Se cambió el estado del error #' . $id_error . ' a: ' . $estado
            );

            $this->jsonResponse(true, 'Estado actualizado correctamente.');
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al cambiar el estado.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       ELIMINAR ERROR
    ============================================================ */
    public function eliminar()
    {
        requirePermiso('errores', 'eliminar');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_error = (int)($_POST['id_error'] ?? 0);

            if ($id_error <= 0) {
                $this->jsonResponse(false, 'ID de error no válido.');
                return;
            }

            $errorActual = $this->model->obtenerError($id_error);

            if (!$errorActual) {
                $this->jsonResponse(false, 'El error no existe.');
                return;
            }

            $this->model->eliminarError($id_error);

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'ELIMINAR',
                'errores_sistema',
                $id_error,
                'Se eliminó el registro de error #' . $id_error
            );

            $this->jsonResponse(true, 'Error eliminado correctamente.');
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al eliminar el registro.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       REGISTRAR ERROR DESDE AJAX
       Este endpoint queda disponible para usuarios logueados.
    ============================================================ */
    public function registrarAjax()
    {
        /*
            No usamos requirePermiso('errores', 'crear') aquí.
            Este endpoint lo usa el botón global "Reportar error",
            por eso debe estar disponible para cualquier usuario autenticado.
        */

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $mensaje = trim($_POST['mensaje'] ?? '');
            $modulo = trim($_POST['modulo'] ?? 'Frontend');
            $url = trim($_POST['url'] ?? ($_SERVER['REQUEST_URI'] ?? ''));
            $datos = $_POST['datos_adicionales'] ?? null;

            if ($mensaje === '') {
                $this->jsonResponse(false, 'El mensaje del error es obligatorio.');
                return;
            }

            if (mb_strlen($mensaje) > 5000) {
                $this->jsonResponse(false, 'El mensaje del error no puede exceder 5000 caracteres.');
                return;
            }

            if (mb_strlen($modulo) > 100) {
                $modulo = mb_substr($modulo, 0, 100);
            }

            $id_error = $this->model->registrarError(
                'AJAX',
                'error',
                $modulo !== '' ? $modulo : 'Frontend',
                null,
                null,
                $mensaje,
                null,
                null,
                $url !== '' ? $url : null,
                $datos,
                $_SESSION['id_usuario'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            );

            $this->jsonResponse(true, 'Error registrado correctamente.', [
                'id_error' => (int)$id_error
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al registrar el error AJAX.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       LIMPIAR RESUELTOS ANTIGUOS
    ============================================================ */
    public function limpiarAntiguos()
    {
        requirePermiso('errores', 'eliminar');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $dias = (int)($_POST['dias'] ?? 30);

            if ($dias < 1) {
                $dias = 30;
            }

            if ($dias > 365) {
                $dias = 365;
            }

            $this->model->eliminarErroresAntiguos($dias);

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'LIMPIAR',
                'errores_sistema',
                null,
                'Se eliminaron errores resueltos con antigüedad mayor a ' . $dias . ' días.'
            );

            $this->jsonResponse(true, 'Errores antiguos eliminados correctamente.');
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al limpiar errores antiguos.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       FILTROS
    ============================================================ */
    private function obtenerFiltros(): array
    {
        $busqueda = trim($_GET['busqueda'] ?? '');
        $tipo_error = strtoupper(trim($_GET['tipo_error'] ?? ''));
        $nivel = strtolower(trim($_GET['nivel'] ?? ''));
        $estado = strtolower(trim($_GET['estado'] ?? ''));
        $modulo = trim($_GET['modulo'] ?? '');
        $fecha_inicio = trim($_GET['fecha_inicio'] ?? '');
        $fecha_fin = trim($_GET['fecha_fin'] ?? '');
        $limite = trim($_GET['limite'] ?? '50');

        if ($tipo_error !== '' && !$this->tipoValido($tipo_error)) {
            return [
                'ok' => false,
                'msg' => 'Tipo de error no válido.'
            ];
        }

        if ($nivel !== '' && !$this->nivelValido($nivel)) {
            return [
                'ok' => false,
                'msg' => 'Nivel de error no válido.'
            ];
        }

        if ($estado !== '' && !$this->estadoValido($estado)) {
            return [
                'ok' => false,
                'msg' => 'Estado de error no válido.'
            ];
        }

        if ($fecha_inicio !== '' && !$this->validarFecha($fecha_inicio)) {
            return [
                'ok' => false,
                'msg' => 'La fecha inicial no tiene un formato válido.'
            ];
        }

        if ($fecha_fin !== '' && !$this->validarFecha($fecha_fin)) {
            return [
                'ok' => false,
                'msg' => 'La fecha final no tiene un formato válido.'
            ];
        }

        if ($fecha_inicio !== '' && $fecha_fin !== '' && $fecha_inicio > $fecha_fin) {
            return [
                'ok' => false,
                'msg' => 'La fecha inicial no puede ser mayor que la fecha final.'
            ];
        }

        if ($limite === '' || !ctype_digit($limite)) {
            $limite = 50;
        } else {
            $limite = (int)$limite;
        }

        if (!in_array($limite, [50, 100, 250, 500], true)) {
            $limite = 50;
        }

        return [
            'ok' => true,
            'data' => [
                'busqueda' => $busqueda,
                'tipo_error' => $tipo_error,
                'nivel' => $nivel,
                'estado' => $estado,
                'modulo' => $modulo,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'limite' => $limite
            ]
        ];
    }

    private function validarFecha(string $fecha): bool
    {
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        return $dt && $dt->format('Y-m-d') === $fecha;
    }

    private function tipoValido(string $tipo): bool
    {
        return in_array($tipo, ['PHP', 'SQL', 'AJAX', 'VALIDACION', 'SISTEMA'], true);
    }

    private function nivelValido(string $nivel): bool
    {
        return in_array($nivel, ['info', 'warning', 'error', 'critico'], true);
    }

    private function estadoValido(string $estado): bool
    {
        return in_array($estado, ['pendiente', 'revisado', 'resuelto'], true);
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
