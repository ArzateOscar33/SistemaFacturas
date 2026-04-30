<?php

class Folios extends Controller
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
        requirePermiso('folios', 'ver');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $resumen = $this->model->resumenFolios();

            $this->jsonResponse(true, 'Resumen cargado correctamente.', [
                'resumen' => $resumen
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al cargar el resumen de folios.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       LISTAR FOLIOS / SERIES
    ============================================================ */
    public function listar()
    {
        requirePermiso('folios', 'ver');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $busqueda = strtoupper(trim($_GET['busqueda'] ?? ''));
            $estado = trim($_GET['estado'] ?? '');

            if ($estado !== '' && !in_array($estado, ['0', '1'], true)) {
                $this->jsonResponse(false, 'El estado seleccionado no es válido.');
                return;
            }

            $folios = $this->model->listarFolios($busqueda, $estado);

            $this->jsonResponse(true, 'Series cargadas correctamente.', [
                'folios' => $folios
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al listar las series.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       OBTENER FOLIO / SERIE
    ============================================================ */
    public function obtener($id_folio = null)
    {
        requirePermiso('folios', 'ver');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_folio = (int)$id_folio;

            if ($id_folio <= 0) {
                $this->jsonResponse(false, 'ID de serie no válido.');
                return;
            }

            $folio = $this->model->obtenerFolio($id_folio);

            if (!$folio) {
                $this->jsonResponse(false, 'La serie no existe.');
                return;
            }

            $folio['siguiente_numero'] = (int)$folio['ultimo_numero'] + 1;
            $folio['siguiente_folio'] = $this->formatearFolio(
                $folio['serie'],
                (int)$folio['siguiente_numero']
            );

            $this->jsonResponse(true, 'Serie cargada correctamente.', [
                'folio' => $folio
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al obtener la serie.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       REGISTRAR SERIE
    ============================================================ */
    public function registrar()
    {
        requirePermiso('folios', 'crear');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $serie = strtoupper(trim($_POST['serie'] ?? ''));
            $ultimo_numero = trim($_POST['ultimo_numero'] ?? '0');
            $activo = trim($_POST['activo'] ?? '1');

            $validacion = $this->validarDatosFolio($serie, $ultimo_numero, $activo);

            if (!$validacion['ok']) {
                $this->jsonResponse(false, $validacion['msg']);
                return;
            }

            $ultimo_numero = (int)$ultimo_numero;
            $activo = (int)$activo;

            if ($this->model->existeSerie($serie)) {
                $this->jsonResponse(false, 'Ya existe una serie registrada con ese nombre.');
                return;
            }

            $maximo = $this->model->obtenerMaximoNumeroFacturaPorSerie($serie);
            $maxNumero = (int)($maximo['max_numero'] ?? 0);

            if ($ultimo_numero < $maxNumero) {
                $this->jsonResponse(
                    false,
                    'No puedes registrar la serie con un último número menor al máximo ya usado en facturas (' . $maxNumero . ').'
                );
                return;
            }

            $id_folio = $this->model->registrarFolio(
                $serie,
                $ultimo_numero,
                $activo
            );

            if (!$id_folio) {
                $this->jsonResponse(false, 'No fue posible registrar la serie.');
                return;
            }

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'REGISTRAR',
                'folios_factura',
                (int)$id_folio,
                'Se registró la serie: ' . $serie . ' con último número ' . $ultimo_numero
            );

            $this->jsonResponse(true, 'Serie registrada correctamente.', [
                'id_folio' => (int)$id_folio
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al registrar la serie.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       ACTUALIZAR SERIE
    ============================================================ */
    public function actualizar()
    {
        requirePermiso('folios', 'editar');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_folio = (int)($_POST['id_folio'] ?? 0);
            $serie = strtoupper(trim($_POST['serie'] ?? ''));
            $ultimo_numero = trim($_POST['ultimo_numero'] ?? '0');
            $activo = trim($_POST['activo'] ?? '1');

            if ($id_folio <= 0) {
                $this->jsonResponse(false, 'ID de serie no válido.');
                return;
            }

            $folioActual = $this->model->obtenerFolio($id_folio);

            if (!$folioActual) {
                $this->jsonResponse(false, 'La serie no existe.');
                return;
            }

            $validacion = $this->validarDatosFolio($serie, $ultimo_numero, $activo);

            if (!$validacion['ok']) {
                $this->jsonResponse(false, $validacion['msg']);
                return;
            }

            $ultimo_numero = (int)$ultimo_numero;
            $activo = (int)$activo;

            if ($this->model->existeSerie($serie, $id_folio)) {
                $this->jsonResponse(false, 'Ya existe otra serie registrada con ese nombre.');
                return;
            }

            $maximo = $this->model->obtenerMaximoNumeroFacturaPorSerie($serie);
            $maxNumero = (int)($maximo['max_numero'] ?? 0);

            if ($ultimo_numero < $maxNumero) {
                $this->jsonResponse(
                    false,
                    'No puedes colocar un último número menor al máximo usado en facturas para esta serie (' . $maxNumero . ').'
                );
                return;
            }

            $serieAnterior = $folioActual['serie'] ?? '';

            if ($serieAnterior !== $serie) {
                $facturasSerieAnterior = $this->model->contarFacturasPorSerie($serieAnterior);
                $totalFacturasAnterior = (int)($facturasSerieAnterior['total'] ?? 0);

                if ($totalFacturasAnterior > 0) {
                    $this->jsonResponse(
                        false,
                        'No puedes cambiar el nombre de esta serie porque ya tiene facturas relacionadas.'
                    );
                    return;
                }
            }

            $this->model->actualizarFolio(
                $id_folio,
                $serie,
                $ultimo_numero,
                $activo
            );

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'ACTUALIZAR',
                'folios_factura',
                $id_folio,
                'Se actualizó la serie: ' . $serie . ' con último número ' . $ultimo_numero
            );

            $this->jsonResponse(true, 'Serie actualizada correctamente.');
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al actualizar la serie.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       CAMBIAR ESTADO
    ============================================================ */
    public function cambiarEstado()
    {
        requirePermiso('folios', 'editar');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_folio = (int)($_POST['id_folio'] ?? 0);
            $activo = trim($_POST['activo'] ?? '');

            if ($id_folio <= 0) {
                $this->jsonResponse(false, 'ID de serie no válido.');
                return;
            }

            if (!in_array($activo, ['0', '1'], true)) {
                $this->jsonResponse(false, 'Estado no válido.');
                return;
            }

            $folio = $this->model->obtenerFolio($id_folio);

            if (!$folio) {
                $this->jsonResponse(false, 'La serie no existe.');
                return;
            }

            $this->model->cambiarEstado($id_folio, (int)$activo);

            $accionTexto = (int)$activo === 1 ? 'activó' : 'desactivó';

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'CAMBIAR_ESTADO',
                'folios_factura',
                $id_folio,
                'Se ' . $accionTexto . ' la serie: ' . ($folio['serie'] ?? '')
            );

            $this->jsonResponse(true, 'Estado actualizado correctamente.');
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al cambiar el estado de la serie.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       ELIMINAR SERIE
    ============================================================ */
    public function eliminar()
    {
        requirePermiso('folios', 'eliminar');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id_folio = (int)($_POST['id_folio'] ?? 0);

            if ($id_folio <= 0) {
                $this->jsonResponse(false, 'ID de serie no válido.');
                return;
            }

            $folio = $this->model->obtenerFolio($id_folio);

            if (!$folio) {
                $this->jsonResponse(false, 'La serie no existe.');
                return;
            }

            $serie = $folio['serie'] ?? '';

            $facturas = $this->model->contarFacturasPorSerie($serie);
            $totalFacturas = (int)($facturas['total'] ?? 0);

            if ($totalFacturas > 0) {
                $this->jsonResponse(
                    false,
                    'No puedes eliminar esta serie porque ya tiene facturas relacionadas.'
                );
                return;
            }

            $this->model->eliminarFolio($id_folio);

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'ELIMINAR',
                'folios_factura',
                $id_folio,
                'Se eliminó la serie: ' . $serie
            );

            $this->jsonResponse(true, 'Serie eliminada correctamente.');
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al eliminar la serie.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       PREVIEW DE SIGUIENTE FOLIO
    ============================================================ */
    public function preview()
    {
        requirePermiso('folios', 'ver');

        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $serie = strtoupper(trim($_GET['serie'] ?? ''));

            if ($serie === '') {
                $this->jsonResponse(false, 'La serie es obligatoria.');
                return;
            }

            if (!$this->serieValida($serie)) {
                $this->jsonResponse(false, 'La serie solo puede contener letras, números, guion y guion bajo.');
                return;
            }

            $preview = $this->model->siguienteFolioPreview($serie);

            $this->jsonResponse(true, 'Preview generado correctamente.', [
                'preview' => $preview
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al generar la vista previa.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       VALIDACIONES
    ============================================================ */
    private function validarDatosFolio(string $serie, string $ultimo_numero, string $activo): array
    {
        if ($serie === '') {
            return [
                'ok' => false,
                'msg' => 'La serie es obligatoria.'
            ];
        }

        if (mb_strlen($serie) > 20) {
            return [
                'ok' => false,
                'msg' => 'La serie no puede exceder 20 caracteres.'
            ];
        }

        if (!$this->serieValida($serie)) {
            return [
                'ok' => false,
                'msg' => 'La serie solo puede contener letras, números, guion y guion bajo.'
            ];
        }

        if ($ultimo_numero === '' || !ctype_digit($ultimo_numero)) {
            return [
                'ok' => false,
                'msg' => 'El último número debe ser un entero mayor o igual a 0.'
            ];
        }

        if ((int)$ultimo_numero < 0) {
            return [
                'ok' => false,
                'msg' => 'El último número no puede ser negativo.'
            ];
        }

        if (!in_array($activo, ['0', '1'], true)) {
            return [
                'ok' => false,
                'msg' => 'El estado de la serie no es válido.'
            ];
        }

        return [
            'ok' => true,
            'msg' => 'Datos válidos.'
        ];
    }

    private function serieValida(string $serie): bool
    {
        return (bool)preg_match('/^[A-Z0-9_-]+$/', $serie);
    }

    private function formatearFolio(string $serie, int $numero): string
    {
        return $serie . '-' . str_pad((string)$numero, 8, '0', STR_PAD_LEFT);
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
