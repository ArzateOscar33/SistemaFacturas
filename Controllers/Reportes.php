<?php

class Reportes extends Controller
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
       CLIENTES PARA FILTRO
    ============================================================ */
    public function clientes()
    {
        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $clientes = $this->model->listarClientes();

            $this->jsonResponse(true, 'Clientes cargados correctamente.', [
                'clientes' => $clientes
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al cargar clientes.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       DATOS GENERALES DEL REPORTE
       KPIS + CHARTS + TABLAS
    ============================================================ */
    public function datos()
    {
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

            $id_cliente   = $filtros['data']['id_cliente'];
            $estado       = $filtros['data']['estado'];
            $fecha_inicio = $filtros['data']['fecha_inicio'];
            $fecha_fin    = $filtros['data']['fecha_fin'];
            $limite       = $filtros['data']['limite'];

            $resumen = $this->model->resumenReporte(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin
            );

            $facturacionMensual = $this->model->facturacionMensual(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin
            );

            $facturasEstado = $this->model->facturasPorEstado(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin
            );

            $topClientes = $this->model->topClientes(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin,
                10
            );

            $ultimasFacturas = $this->model->ultimasFacturas(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin,
                8
            );

            $reporteDetallado = $this->model->reporteDetallado(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin,
                $limite
            );

            $this->jsonResponse(true, 'Reporte cargado correctamente.', [
                'resumen' => [
                    'total_facturas'      => (int)($resumen['total_facturas'] ?? 0),
                    'facturas_emitidas'   => (int)($resumen['facturas_emitidas'] ?? 0),
                    'facturas_borrador'   => (int)($resumen['facturas_borrador'] ?? 0),
                    'facturas_canceladas' => (int)($resumen['facturas_canceladas'] ?? 0),
                    'total_facturado'     => (float)($resumen['total_facturado'] ?? 0),
                    'promedio_factura'    => (float)($resumen['promedio_factura'] ?? 0),
                ],
                'facturacion_mensual' => $facturacionMensual,
                'facturas_estado'     => $facturasEstado,
                'top_clientes'        => $topClientes,
                'ultimas_facturas'    => $ultimasFacturas,
                'reporte_detallado'   => $reporteDetallado,
                'filtros'             => [
                    'id_cliente'   => $id_cliente,
                    'estado'       => $estado,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin'    => $fecha_fin,
                    'limite'       => $limite
                ]
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Error al cargar el reporte.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
       FILTROS
    ============================================================ */
    private function obtenerFiltros()
    {
        $id_cliente   = trim($_GET['id_cliente'] ?? '');
        $estado       = trim($_GET['estado'] ?? '');
        $fecha_inicio = trim($_GET['fecha_inicio'] ?? '');
        $fecha_fin    = trim($_GET['fecha_fin'] ?? '');
        $limite       = trim($_GET['limite'] ?? '500');

        if ($id_cliente !== '' && !ctype_digit($id_cliente)) {
            return [
                'ok' => false,
                'msg' => 'El cliente seleccionado no es válido.'
            ];
        }

        if ($estado !== '' && !in_array($estado, ['emitida', 'borrador', 'cancelada'], true)) {
            return [
                'ok' => false,
                'msg' => 'El estado seleccionado no es válido.'
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
            $limite = 500;
        } else {
            $limite = (int)$limite;
        }

        if ($limite < 1) {
            $limite = 500;
        }

        if ($limite > 5000) {
            $limite = 5000;
        }

        return [
            'ok' => true,
            'data' => [
                'id_cliente'   => $id_cliente,
                'estado'       => $estado,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin'    => $fecha_fin,
                'limite'       => $limite
            ]
        ];
    }

    private function validarFecha(string $fecha): bool
    {
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        return $dt && $dt->format('Y-m-d') === $fecha;
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


    /*exportar*/
    /* ============================================================
   EXPORTAR REPORTE A EXCEL
============================================================ */
    public function exportar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo 'Método no permitido.';
            return;
        }

        try {
            $filtros = $this->obtenerFiltros();

            if (!$filtros['ok']) {
                echo $filtros['msg'];
                return;
            }

            $id_cliente   = $filtros['data']['id_cliente'];
            $estado       = $filtros['data']['estado'];
            $fecha_inicio = $filtros['data']['fecha_inicio'];
            $fecha_fin    = $filtros['data']['fecha_fin'];
            $limite       = $filtros['data']['limite'];

            $resumen = $this->model->resumenReporte(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin
            );

            $topClientes = $this->model->topClientes(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin,
                10
            );

            $reporteDetallado = $this->model->reporteDetallado(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin,
                $limite
            );

            $nombreArchivo = 'reporte_facturacion_' . date('Ymd_His') . '.xls';

            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo "\xEF\xBB\xBF"; // BOM para acentos en Excel

?>
            <html>

            <head>
                <meta charset="UTF-8">
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 12px;
                    }

                    .titulo {
                        font-size: 20px;
                        font-weight: bold;
                        color: #0d47a1;
                    }

                    .subtitulo {
                        font-size: 13px;
                        color: #333333;
                    }

                    table {
                        border-collapse: collapse;
                        width: 100%;
                        margin-top: 12px;
                    }

                    th {
                        background: #0d47a1;
                        color: #ffffff;
                        font-weight: bold;
                        border: 1px solid #0d47a1;
                        padding: 7px;
                        text-align: left;
                    }

                    td {
                        border: 1px solid #cccccc;
                        padding: 6px;
                    }

                    .seccion {
                        background: #e3f2fd;
                        color: #0d47a1;
                        font-weight: bold;
                        font-size: 15px;
                        padding: 8px;
                        margin-top: 18px;
                    }

                    .numero {
                        text-align: right;
                    }

                    .estado-emitida {
                        color: #0f7b3f;
                        font-weight: bold;
                    }

                    .estado-borrador {
                        color: #b7791f;
                        font-weight: bold;
                    }

                    .estado-cancelada {
                        color: #b91c1c;
                        font-weight: bold;
                    }
                </style>
            </head>

            <body>

                <div class="titulo">Reporte de Facturación</div>
                <div class="subtitulo">Sistema de Facturación</div>
                <div class="subtitulo">Generado el: <?php echo date('d/m/Y H:i:s'); ?></div>

                <br>

                <table>
                    <tr>
                        <th>Filtro</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td>Cliente</td>
                        <td><?php echo $id_cliente !== '' ? $this->limpiarExcel($id_cliente) : 'Todos'; ?></td>
                    </tr>
                    <tr>
                        <td>Estado</td>
                        <td><?php echo $estado !== '' ? ucfirst($this->limpiarExcel($estado)) : 'Todos'; ?></td>
                    </tr>
                    <tr>
                        <td>Fecha inicio</td>
                        <td><?php echo $fecha_inicio !== '' ? $this->limpiarExcel($fecha_inicio) : 'Sin filtro'; ?></td>
                    </tr>
                    <tr>
                        <td>Fecha fin</td>
                        <td><?php echo $fecha_fin !== '' ? $this->limpiarExcel($fecha_fin) : 'Sin filtro'; ?></td>
                    </tr>
                </table>

                <div class="seccion">Resumen general</div>

                <table>
                    <tr>
                        <th>Total facturas</th>
                        <th>Facturas emitidas</th>
                        <th>Facturas borrador</th>
                        <th>Facturas canceladas</th>
                        <th>Total facturado</th>
                        <th>Promedio por factura</th>
                    </tr>
                    <tr>
                        <td class="numero"><?php echo (int)($resumen['total_facturas'] ?? 0); ?></td>
                        <td class="numero"><?php echo (int)($resumen['facturas_emitidas'] ?? 0); ?></td>
                        <td class="numero"><?php echo (int)($resumen['facturas_borrador'] ?? 0); ?></td>
                        <td class="numero"><?php echo (int)($resumen['facturas_canceladas'] ?? 0); ?></td>
                        <td class="numero"><?php echo number_format((float)($resumen['total_facturado'] ?? 0), 2, '.', ''); ?></td>
                        <td class="numero"><?php echo number_format((float)($resumen['promedio_factura'] ?? 0), 2, '.', ''); ?></td>
                    </tr>
                </table>

                <div class="seccion">Top clientes</div>

                <table>
                    <tr>
                        <th>Código cliente</th>
                        <th>Cliente</th>
                        <th>Total facturas</th>
                        <th>Total facturado</th>
                    </tr>

                    <?php if (empty($topClientes)) : ?>
                        <tr>
                            <td colspan="4">Sin información disponible.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($topClientes as $cliente) : ?>
                            <tr>
                                <td><?php echo $this->limpiarExcel($cliente['codigo_cliente'] ?? ''); ?></td>
                                <td><?php echo $this->limpiarExcel($cliente['nombre_cliente'] ?? ''); ?></td>
                                <td class="numero"><?php echo (int)($cliente['total_facturas'] ?? 0); ?></td>
                                <td class="numero"><?php echo number_format((float)($cliente['total_facturado'] ?? 0), 2, '.', ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>

                <div class="seccion">Reporte detallado</div>

                <table>
                    <tr>
                        <th>Folio</th>
                        <th>Serie</th>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Sales Man</th>
                        <th>Terms</th>
                        <th>Subtotal</th>
                        <th>Tasa impuesto</th>
                        <th>Impuesto</th>
                        <th>Otros cargos</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Creado por</th>
                        <th>Creado en</th>
                    </tr>

                    <?php if (empty($reporteDetallado)) : ?>
                        <tr>
                            <td colspan="15">No hay facturas que coincidan con los filtros seleccionados.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($reporteDetallado as $factura) : ?>
                            <?php
                            $estadoFactura = strtolower($factura['estado_factura'] ?? '');
                            $claseEstado = '';

                            if ($estadoFactura === 'emitida') {
                                $claseEstado = 'estado-emitida';
                            } elseif ($estadoFactura === 'borrador') {
                                $claseEstado = 'estado-borrador';
                            } elseif ($estadoFactura === 'cancelada') {
                                $claseEstado = 'estado-cancelada';
                            }
                            ?>

                            <tr>
                                <td><?php echo $this->limpiarExcel($factura['folio_factura'] ?? ''); ?></td>
                                <td><?php echo $this->limpiarExcel($factura['serie'] ?? ''); ?></td>
                                <td><?php echo $this->limpiarExcel($factura['numero_factura'] ?? ''); ?></td>
                                <td><?php echo $this->limpiarExcel($factura['nombre_cliente'] ?? ''); ?></td>
                                <td><?php echo $this->limpiarExcel($factura['fecha_factura'] ?? ''); ?></td>
                                <td><?php echo $this->limpiarExcel($factura['sales_man'] ?? ''); ?></td>
                                <td><?php echo $this->limpiarExcel($factura['terms'] ?? ''); ?></td>
                                <td class="numero"><?php echo number_format((float)($factura['subtotal'] ?? 0), 2, '.', ''); ?></td>
                                <td class="numero"><?php echo number_format((float)($factura['tasa_impuesto'] ?? 0), 2, '.', ''); ?></td>
                                <td class="numero"><?php echo number_format((float)($factura['impuesto'] ?? 0), 2, '.', ''); ?></td>
                                <td class="numero"><?php echo number_format((float)($factura['otros_cargos'] ?? 0), 2, '.', ''); ?></td>
                                <td class="numero"><?php echo number_format((float)($factura['total'] ?? 0), 2, '.', ''); ?></td>
                                <td class="<?php echo $claseEstado; ?>">
                                    <?php echo ucfirst($this->limpiarExcel($factura['estado_factura'] ?? '')); ?>
                                </td>
                                <td><?php echo $this->limpiarExcel($factura['creado_por_nombre'] ?? ''); ?></td>
                                <td><?php echo $this->limpiarExcel($factura['creado_en'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>

            </body>

            </html>
<?php

        } catch (Throwable $e) {
            echo 'Error al exportar el reporte: ' . $e->getMessage();
        }
    }





    private function limpiarExcel($valor): string
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}
