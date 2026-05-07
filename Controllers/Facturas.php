<?php



class Facturas extends Controller
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
        requirePermiso('facturas', 'ver');
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
            $fecha_inicio = trim($_GET['fecha_inicio'] ?? '');
            $fecha_fin = trim($_GET['fecha_fin'] ?? '');

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

            $facturas = $this->model->listarFacturas($buscar, $estado, $fecha_inicio, $fecha_fin);

            echo json_encode([
                'ok' => true,
                'data' => $facturas
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al listar facturas.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function clientes()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $clientes = $this->model->listarClientesActivos();

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


    public function direccionesCliente($id_cliente = null)
    {
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

            if ((int)$cliente['estado'] !== 1) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'El cliente está inactivo.'
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
                'msg' => 'Error al listar direcciones del cliente.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }



    public function obtener($id_factura = null)
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id_factura = (int)($id_factura ?? 0);

        if ($id_factura <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'ID de factura inválido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $factura = $this->model->obtenerFacturaCompleta($id_factura);

            if (!$factura) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Factura no encontrada.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode([
                'ok' => true,
                'data' => $factura
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al obtener la factura.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function registrar()
    {
        requirePermiso('facturas', 'crear');
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $validacion = $this->validarPayloadFactura(false);

        if (!$validacion['ok']) {
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }

        $payload = $validacion['data'];

        try {
            $this->model->iniciarTransaccion();

            $folio = $this->model->obtenerFolioBloqueadoPorId((int)$payload['id_folio']);

            if (!$folio) {
                $this->model->cancelarTransaccion();

                echo json_encode([
                    'ok' => false,
                    'msg' => 'La serie seleccionada no existe o está inactiva.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $serie = $folio['serie'];
            $nuevoNumero = ((int)$folio['ultimo_numero']) + 1;
            $folioFactura = $serie . '-' . str_pad((string)$nuevoNumero, 8, '0', STR_PAD_LEFT);

            $this->model->actualizarUltimoFolio((int)$folio['id_folio'], $nuevoNumero);
            $id_factura = $this->model->registrarFactura(
                $serie,
                $nuevoNumero,
                $folioFactura,
                $payload['id_cliente'],
                $payload['id_cliente_direccion'],
                $payload['direccion_facturacion'],
                $payload['fecha_factura'],
                $payload['sales_man'],
                $payload['terms'],
                $payload['notas'],
                $payload['subtotal'],
                $payload['tasa_impuesto'],
                $payload['impuesto'],
                $payload['otros_cargos'],
                $payload['total'],
                $payload['estado_factura'],
                (int)$_SESSION['id_usuario']
            );

            if (!$id_factura) {
                $this->model->cancelarTransaccion();

                echo json_encode([
                    'ok' => false,
                    'msg' => 'No fue posible registrar la factura.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            foreach ($payload['partidas'] as $index => $partida) {
                $detalleOk = $this->model->registrarDetalleFactura(
                    (int)$id_factura,
                    $partida['cantidad'],
                    $partida['descripcion'],
                    $partida['precio_unitario'],
                    $partida['total_linea'],
                    $index + 1
                );

                if (!$detalleOk) {
                    $this->model->cancelarTransaccion();

                    echo json_encode([
                        'ok' => false,
                        'msg' => 'No fue posible registrar una partida de la factura.'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'REGISTRAR',
                (int)$id_factura,
                'Se registró la factura: ' . $folioFactura
            );

            $this->model->confirmarTransaccion();

            echo json_encode([
                'ok' => true,
                'msg' => 'Factura registrada correctamente.',
                'id_factura' => (int)$id_factura,
                'folio_factura' => $folioFactura
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            $this->model->cancelarTransaccion();

            echo json_encode([
                'ok' => false,
                'msg' => 'Error al registrar la factura.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function actualizar()
    {
        requirePermiso('facturas', 'editar');
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id_factura = (int)($_POST['id_factura'] ?? 0);

        if ($id_factura <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'ID de factura inválido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $validacion = $this->validarPayloadFactura(true);

        if (!$validacion['ok']) {
            echo json_encode($validacion, JSON_UNESCAPED_UNICODE);
            return;
        }

        $payload = $validacion['data'];

        try {
            $facturaActual = $this->model->obtenerFactura($id_factura);

            if (!$facturaActual) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Factura no encontrada.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($facturaActual['estado_factura'] === 'cancelada') {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'No puedes editar una factura cancelada.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->model->iniciarTransaccion();

            $actualizado = $this->model->actualizarFactura(
                $id_factura,
                $payload['id_cliente'],
                $payload['id_cliente_direccion'],
                $payload['direccion_facturacion'],
                $payload['fecha_factura'],
                $payload['sales_man'],
                $payload['terms'],
                $payload['notas'],
                $payload['subtotal'],
                $payload['tasa_impuesto'],
                $payload['impuesto'],
                $payload['otros_cargos'],
                $payload['total'],
                $payload['estado_factura']
            );

            $this->model->eliminarDetalleFactura($id_factura);

            foreach ($payload['partidas'] as $index => $partida) {
                $detalleOk = $this->model->registrarDetalleFactura(
                    $id_factura,
                    $partida['cantidad'],
                    $partida['descripcion'],
                    $partida['precio_unitario'],
                    $partida['total_linea'],
                    $index + 1
                );

                if (!$detalleOk) {
                    $this->model->cancelarTransaccion();

                    echo json_encode([
                        'ok' => false,
                        'msg' => 'No fue posible actualizar una partida de la factura.'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'ACTUALIZAR',
                $id_factura,
                'Se actualizó la factura: ' . $facturaActual['folio_factura']
            );

            $this->model->confirmarTransaccion();

            echo json_encode([
                'ok' => true,
                'msg' => 'Factura actualizada correctamente.',
                'id_factura' => $id_factura,
                'folio_factura' => $facturaActual['folio_factura'],
                'actualizado' => (bool)$actualizado
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            $this->model->cancelarTransaccion();

            echo json_encode([
                'ok' => false,
                'msg' => 'Error al actualizar la factura.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function cancelar()
    {
        requirePermiso('facturas', 'eliminar');
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id_factura = (int)($_POST['id_factura'] ?? 0);

        if ($id_factura <= 0) {
            echo json_encode([
                'ok' => false,
                'msg' => 'ID de factura inválido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $factura = $this->model->obtenerFactura($id_factura);

            if (!$factura) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'Factura no encontrada.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($factura['estado_factura'] === 'cancelada') {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'La factura ya está cancelada.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $ok = $this->model->cancelarFactura($id_factura);

            if (!$ok) {
                echo json_encode([
                    'ok' => false,
                    'msg' => 'No fue posible cancelar la factura.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->model->registrarBitacora(
                $_SESSION['id_usuario'] ?? null,
                'CANCELAR',
                $id_factura,
                'Se canceló la factura: ' . $factura['folio_factura']
            );

            echo json_encode([
                'ok' => true,
                'msg' => 'Factura cancelada correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al cancelar la factura.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }



    private function validarPayloadFactura(bool $esEdicion = false): array
    {
        $id_folio = (int)($_POST['id_folio'] ?? 0);
        $id_cliente = (int)($_POST['id_cliente'] ?? 0);
        $id_cliente_direccion = (int)($_POST['id_cliente_direccion'] ?? 0);
        $direccion_facturacion = trim($_POST['direccion_facturacion'] ?? '');

        $fecha_factura = trim($_POST['fecha_factura'] ?? '');
        $sales_man = trim($_POST['sales_man'] ?? '');
        $terms = trim($_POST['terms'] ?? '');
        $notas = trim($_POST['notas'] ?? '');
        $tasa_impuesto = (float)($_POST['tasa_impuesto'] ?? 0);
        $otros_cargos = (float)($_POST['otros_cargos'] ?? 0);
        $estado_factura = trim($_POST['estado_factura'] ?? 'emitida');

        $cantidades = $_POST['cantidad'] ?? [];
        $descripciones = $_POST['descripcion'] ?? [];
        $precios = $_POST['precio_unitario'] ?? [];

        if ($id_cliente <= 0) {
            return [
                'ok' => false,
                'msg' => 'Selecciona un cliente válido.'
            ];
        }

        $cliente = $this->model->obtenerCliente($id_cliente);

        if (!$cliente) {
            return [
                'ok' => false,
                'msg' => 'El cliente seleccionado no existe.'
            ];
        }

        if ((int)$cliente['estado'] !== 1) {
            return [
                'ok' => false,
                'msg' => 'El cliente seleccionado está inactivo.'
            ];
        }

        /*
     * DIRECCIÓN DE FACTURACIÓN
     * Si viene id_cliente_direccion, validamos que exista,
     * esté activa y pertenezca al cliente.
     */
        if ($id_cliente_direccion > 0) {
            $direccionSeleccionada = $this->model->obtenerDireccionClienteActiva(
                $id_cliente_direccion,
                $id_cliente
            );

            if (!$direccionSeleccionada) {
                return [
                    'ok' => false,
                    'msg' => 'La dirección seleccionada no existe, está inactiva o no pertenece al cliente.'
                ];
            }

            /*
         * Si el textarea viene vacío, usamos la dirección seleccionada.
         * Si viene con texto, respetamos el texto escrito para guardar snapshot.
         */
            if ($direccion_facturacion === '') {
                $direccion_facturacion = trim($direccionSeleccionada['direccion'] ?? '');
            }
        }

        /*
     * Fallback:
     * Si no seleccionaron dirección pero el cliente tiene dirección principal,
     * usamos la dirección principal.
     */
        if ($direccion_facturacion === '') {
            $direccionPrincipal = $this->model->obtenerDireccionPrincipalCliente($id_cliente);

            if ($direccionPrincipal) {
                $id_cliente_direccion = (int)$direccionPrincipal['id_direccion'];
                $direccion_facturacion = trim($direccionPrincipal['direccion'] ?? '');
            }
        }

        /*
     * Segundo fallback:
     * Si todavía no hay dirección, usamos clientes.direccion para compatibilidad.
     */
        if ($direccion_facturacion === '') {
            $direccion_facturacion = trim($cliente['direccion'] ?? '');
        }

        if ($direccion_facturacion === '') {
            return [
                'ok' => false,
                'msg' => 'Selecciona o ingresa la dirección de facturación.'
            ];
        }

        if (strlen($direccion_facturacion) > 1000) {
            return [
                'ok' => false,
                'msg' => 'La dirección de facturación no puede superar 1000 caracteres.'
            ];
        }

        if (!$esEdicion && $id_folio <= 0) {
            return [
                'ok' => false,
                'msg' => 'Selecciona una serie válida para la factura.'
            ];
        }

        if ($fecha_factura === '' || !$this->validarFecha($fecha_factura)) {
            return [
                'ok' => false,
                'msg' => 'La fecha de factura no es válida.'
            ];
        }

        if (!in_array($estado_factura, ['borrador', 'emitida'], true)) {
            return [
                'ok' => false,
                'msg' => 'El estado de factura no es válido.'
            ];
        }

        if ($tasa_impuesto < 0) {
            return [
                'ok' => false,
                'msg' => 'La tasa de impuesto no puede ser negativa.'
            ];
        }

        if ($otros_cargos < 0) {
            return [
                'ok' => false,
                'msg' => 'Shipping & handling no puede ser negativo.'
            ];
        }

        if (!is_array($cantidades) || !is_array($descripciones) || !is_array($precios)) {
            return [
                'ok' => false,
                'msg' => 'Las partidas de la factura no son válidas.'
            ];
        }

        $partidas = [];
        $subtotal = 0;

        $totalPartidas = max(count($cantidades), count($descripciones), count($precios));

        for ($i = 0; $i < $totalPartidas; $i++) {
            $cantidad = isset($cantidades[$i]) ? (float)$cantidades[$i] : 0;
            $descripcion = isset($descripciones[$i]) ? trim($descripciones[$i]) : '';
            $precioUnitario = isset($precios[$i]) ? (float)$precios[$i] : 0;

            if ($cantidad <= 0 && $descripcion === '' && $precioUnitario <= 0) {
                continue;
            }

            if ($cantidad <= 0) {
                return [
                    'ok' => false,
                    'msg' => 'Todas las partidas deben tener cantidad mayor a 0.'
                ];
            }

            if ($descripcion === '') {
                return [
                    'ok' => false,
                    'msg' => 'Todas las partidas deben tener descripción.'
                ];
            }

            if ($precioUnitario < 0) {
                return [
                    'ok' => false,
                    'msg' => 'El precio unitario no puede ser negativo.'
                ];
            }

            $totalLinea = round($cantidad * $precioUnitario, 2);
            $subtotal += $totalLinea;

            $partidas[] = [
                'cantidad' => round($cantidad, 2),
                'descripcion' => $descripcion,
                'precio_unitario' => round($precioUnitario, 2),
                'total_linea' => $totalLinea
            ];
        }

        if (empty($partidas)) {
            return [
                'ok' => false,
                'msg' => 'Agrega al menos una partida a la factura.'
            ];
        }

        $subtotal = round($subtotal, 2);
        $impuesto = round($subtotal * ($tasa_impuesto / 100), 2);
        $total = round($subtotal + $impuesto + $otros_cargos, 2);

        return [
            'ok' => true,
            'data' => [
                'id_folio' => $id_folio,
                'id_cliente' => $id_cliente,
                'id_cliente_direccion' => $id_cliente_direccion > 0 ? $id_cliente_direccion : null,
                'direccion_facturacion' => $direccion_facturacion,
                'fecha_factura' => $fecha_factura,
                'sales_man' => $sales_man !== '' ? $sales_man : null,
                'terms' => $terms !== '' ? $terms : null,
                'notas' => $notas !== '' ? $notas : null,
                'subtotal' => $subtotal,
                'tasa_impuesto' => round($tasa_impuesto, 2),
                'impuesto' => $impuesto,
                'otros_cargos' => round($otros_cargos, 2),
                'total' => $total,
                'estado_factura' => $estado_factura,
                'partidas' => $partidas
            ]
        ];
    }

    private function validarFecha(string $fecha): bool
    {
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        return $dt && $dt->format('Y-m-d') === $fecha;
    }






    private function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    private function money($value): string
    {
        return '$' . number_format((float)$value, 2, '.', ',');
    }

    private function numeroLimpio($value): string
    {
        $numero = (float)$value;

        if (floor($numero) == $numero) {
            return (string)(int)$numero;
        }

        return number_format($numero, 2, '.', '');
    }

    private function formatearFechaIngles(string $fecha): string
    {
        $timestamp = strtotime($fecha);

        if (!$timestamp) {
            return $fecha;
        }

        return date('F d, Y', $timestamp);
    }

    private function limpiarNombreArchivo(string $nombre): string
    {
        $nombre = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre);
        return trim($nombre, '_') ?: 'factura';
    }

    private function validarColorHex(string $color): bool
    {
        return (bool)preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
    }




    //GENERAR PDF



    public function pdf($id_factura = null)
    {
        $id_factura = (int)($id_factura ?? 0);

        if ($id_factura <= 0) {
            http_response_code(400);
            echo 'ID de factura inválido.';
            return;
        }

        try {
            $factura = $this->model->obtenerFacturaCompleta($id_factura);

            if (!$factura) {
                http_response_code(404);
                echo 'Factura no encontrada.';
                return;
            }

            $empresa = $this->model->obtenerEmpresaConfiguracion();

            if (!$empresa) {
                http_response_code(500);
                echo 'No existe configuración de empresa.';
                return;
            }

            $nombreArchivo = $this->limpiarNombreArchivo($factura['folio_factura'] ?? 'factura') . '.pdf';

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

            $pdf->SetCreator('Sistema de Facturación');
            $pdf->SetAuthor($empresa['nombre_empresa'] ?? 'Sistema de Facturación');
            $pdf->SetTitle('Factura ' . ($factura['folio_factura'] ?? ''));
            $pdf->SetSubject('Factura');

            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false, 0);
            $pdf->AddPage();

            $this->dibujarFacturaTcpdf($pdf, $factura, $empresa);

            $pdf->Output($nombreArchivo, 'D');
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Error al generar el PDF: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            return;
        }
    }
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) !== 6) {
            return [176, 190, 208];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }

    private function dibujarFacturaTcpdf(TCPDF $pdf, array $factura, array $empresa): void
    {
        $detalle = $factura['detalle'] ?? [];

        $colorPrincipal = $empresa['color_principal'] ?? '#b8c5d6';

        if (!$this->validarColorHex($colorPrincipal)) {
            $colorPrincipal = '#b8c5d6';
        }

        [$rHeader, $gHeader, $bHeader] = $this->hexToRgb($colorPrincipal);

        // Si quieres que siempre sea como la plantilla original, usa este azul gris fijo:
        $rHeader = 176;
        $gHeader = 190;
        $bHeader = 208;

        $nombreEmpresa = strtoupper($empresa['nombre_empresa'] ?? 'MX-EXPRESS SERVICES');
        $taxId = $empresa['tax_id'] ?? '';
        $telefono = $empresa['telefono'] ?? '';
        $direccionEmpresa = $empresa['direccion'] ?? '';

        $folioFactura = $factura['folio_factura'] ?? '';
        $fecha = $this->formatearFechaIngles($factura['fecha_factura'] ?? date('Y-m-d'));

        $codigoCliente = $factura['codigo_cliente'] ?? '';
        $clienteNombre = strtoupper($factura['nombre_cliente'] ?? '');
        $clienteDireccion = strtoupper($factura['direccion'] ?? '');
        $clienteRfc = strtoupper($factura['rfc'] ?? '');

        $salesMan = strtoupper($factura['sales_man'] ?? '');
        $terms = strtoupper($factura['terms'] ?? '');
        $viaShip = 'DAP';

        $subtotal = (float)($factura['subtotal'] ?? 0);
        $tasaImpuesto = (float)($factura['tasa_impuesto'] ?? 0);
        $impuesto = (float)($factura['impuesto'] ?? 0);
        $otrosCargos = (float)($factura['otros_cargos'] ?? 0);
        $total = (float)($factura['total'] ?? 0);

        $notas = strtoupper($factura['notas'] ?? '');
        $textoPie = trim($empresa['texto_pie_pagina'] ?? '');

        /*
        Área principal centrada, parecida a la plantilla original.
        Hoja Letter: 216 x 279 mm.
    */
        $x = 35;
        $y = 12;
        $w = 146;

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(255, 255, 255);
        $pdf->SetLineWidth(0.35);

        /*
        LOGO / ENCABEZADO IZQUIERDO
    */
        $logoPath = null;

        if (!empty($empresa['logo'])) {
            $tmpLogo = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $empresa['logo']);
            if (is_file($tmpLogo)) {
                $logoPath = $tmpLogo;
            }
        }

        if ($logoPath) {
            // Logo pequeño como plantilla original.
            $pdf->Image($logoPath, $x + 31, $y + 2, 18, 18, '', '', '', false, 300, '', false, false, 0, true);
        }

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($x + 18, $y + 22);
        $pdf->Cell(48, 4, $nombreEmpresa, 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 4.7);
        $dirEmpresaLineas = explode("\n", str_replace(["\r\n", "\r"], "\n", $direccionEmpresa));
        $empresaTxt = '';
        foreach ($dirEmpresaLineas as $linea) {
            if (trim($linea) !== '') {
                $empresaTxt .= strtoupper(trim($linea)) . "\n";
            }
        }

        $pdf->SetXY($x + 18, $y + 27);
        $pdf->MultiCell(48, 3, trim($empresaTxt), 0, 'C', false);

        /*
        TAX ID / PHONE IZQUIERDA
    */
        $pdf->SetFont('helvetica', 'B', 5.8);
        $pdf->SetXY($x, $y + 37);
        $pdf->Cell(28, 3, 'TAX ID: ' . $taxId, 0, 1, 'L');

        $pdf->SetXY($x, $y + 42);
        $pdf->Cell(40, 3, 'PHONE ' . $telefono, 0, 1, 'L');

        /*
        EXPORT ONLY
    */
        $pdf->SetFillColor(235, 30, 30);
        $pdf->SetDrawColor(235, 30, 30);
        $pdf->Rect($x + 50, $y + 36, 45, 10, 'DF');

        // Sombra negra
        $pdf->SetFont('helvetica', 'B', 17);
        $pdf->SetTextColor(20, 20, 20);
        $pdf->SetXY($x + 51, $y + 37.6);
        $pdf->Cell(45, 8, 'EXPORT ONLY', 0, 0, 'C');

        // Texto azul/cian encima
        $pdf->SetTextColor(0, 180, 230);
        $pdf->SetXY($x + 50, $y + 37);
        $pdf->Cell(45, 8, 'EXPORT ONLY', 0, 0, 'C');

        // Restaurar colores para que las tablas vuelvan a negro
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.35);

        /*
        INVOICE + TABLA DE DATOS DERECHA
    */
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor(170, 184, 204);
        $pdf->SetXY($x + 105, $y + 12);
        $pdf->Cell(40, 8, 'INVOICE', 0, 1, 'C');

        $pdf->SetTextColor(0, 0, 0);

        $infoX = $x + 112;
        $infoY = $y + 31;
        $infoWLabel = 16;
        $infoWVal = 28;
        $rowH = 5;

        $pdf->SetFont('helvetica', 'B', 5.5);
        $pdf->SetXY($infoX - 17, $infoY);
        $pdf->Cell(16, $rowH, 'Date:', 0, 0, 'L');
        $pdf->Rect($infoX, $infoY, $infoWVal, $rowH);
        $pdf->SetFont('helvetica', 'B', 5.2);
        $pdf->SetXY($infoX, $infoY + 1.1);
        $pdf->Cell($infoWVal, 3, $fecha, 0, 0, 'C');

        $pdf->SetFont('helvetica', 'B', 5.5);
        $pdf->SetXY($infoX - 17, $infoY + $rowH);
        $pdf->Cell(16, $rowH, 'invoice#:', 0, 0, 'L');
        $pdf->Rect($infoX, $infoY + $rowH, $infoWVal, $rowH);
        $pdf->SetFont('helvetica', 'B', 5.2);
        $pdf->SetXY($infoX, $infoY + $rowH + 1.1);
        $pdf->Cell($infoWVal, 3, $folioFactura, 0, 0, 'C');

        $pdf->SetFont('helvetica', 'B', 5.5);
        $pdf->SetXY($infoX - 17, $infoY + ($rowH * 2));
        $pdf->Cell(16, $rowH, 'Costumer ID:', 0, 0, 'L');
        $pdf->Rect($infoX, $infoY + ($rowH * 2), $infoWVal, $rowH);
        $pdf->SetFont('helvetica', 'B', 5.2);
        $pdf->SetXY($infoX, $infoY + ($rowH * 2) + 1.1);
        $pdf->Cell($infoWVal, 3, $codigoCliente, 0, 0, 'C');

        /*
        BILL TO
    */
        $billY = $y + 52;

        $pdf->SetFont('helvetica', 'B', 6);
        $pdf->SetXY($x, $billY);
        $pdf->Cell(20, 4, 'Bill To:', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 5.8);
        $pdf->SetXY($x + 95, $billY);
        $pdf->Cell(50, 4, 'PAGE 1/1', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 5.6);
        $pdf->SetXY($x, $billY + 8);
        $pdf->MultiCell(115, 3.2, $clienteNombre, 0, 'L', false);

        $pdf->SetXY($x, $billY + 17);
        $billTxt = trim($clienteDireccion);
        if ($clienteRfc !== '') {
            $billTxt .= "\nRFC: " . $clienteRfc;
        }
        $pdf->MultiCell(115, 3.2, $billTxt, 0, 'L', false);

        /*
        TABLA SALES MAN / VIA SHIP / TERMS
    */
        $metaY = $y + 83;
        $metaH = 4;
        $metaW1 = 37;
        $metaW2 = 75;
        $metaW3 = 34;

        $pdf->SetFillColor($rHeader, $gHeader, $bHeader);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 5.5);

        $pdf->Rect($x, $metaY, $metaW1, $metaH, 'DF');
        $pdf->Rect($x + $metaW1, $metaY, $metaW2, $metaH, 'DF');
        $pdf->Rect($x + $metaW1 + $metaW2, $metaY, $metaW3, $metaH, 'DF');

        $pdf->SetXY($x, $metaY + 0.8);
        $pdf->Cell($metaW1, 2.5, 'SALES MAN', 0, 0, 'C');
        $pdf->Cell($metaW2, 2.5, 'VIA SHIP', 0, 0, 'C');
        $pdf->Cell($metaW3, 2.5, 'TERMS', 0, 0, 'C');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 5.5);

        $pdf->Rect($x, $metaY + $metaH, $metaW1, $metaH);
        $pdf->Rect($x + $metaW1, $metaY + $metaH, $metaW2, $metaH);
        $pdf->Rect($x + $metaW1 + $metaW2, $metaY + $metaH, $metaW3, $metaH);

        $pdf->SetXY($x, $metaY + $metaH + 0.8);
        $pdf->Cell($metaW1, 2.5, $salesMan, 0, 0, 'C');
        $pdf->Cell($metaW2, 2.5, $viaShip, 0, 0, 'C');
        $pdf->Cell($metaW3, 2.5, $terms, 0, 0, 'C');

        /*
        TABLA DE PARTIDAS
    */
        $itemsY = $metaY + 9;
        $headH = 5;
        $bodyH = 66;

        $colQty = 16;
        $colDesc = 77;
        $colUnit = 17;
        $colTotal = 36;

        $pdf->SetFillColor($rHeader, $gHeader, $bHeader);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 5.5);

        $pdf->Rect($x, $itemsY, $colQty, $headH, 'DF');
        $pdf->Rect($x + $colQty, $itemsY, $colDesc, $headH, 'DF');
        $pdf->Rect($x + $colQty + $colDesc, $itemsY, $colUnit, $headH, 'DF');
        $pdf->Rect($x + $colQty + $colDesc + $colUnit, $itemsY, $colTotal, $headH, 'DF');

        $pdf->SetXY($x, $itemsY + 1.3);
        $pdf->Cell($colQty, 2, 'QUANTITY.', 0, 0, 'C');
        $pdf->Cell($colDesc, 2, 'DESCRIPTION.', 0, 0, 'C');
        $pdf->Cell($colUnit, 2, 'UNIT PRICE.', 0, 0, 'C');
        $pdf->Cell($colTotal, 2, 'TOTAL.', 0, 0, 'C');

        $pdf->SetTextColor(0, 0, 0);

        $bodyY = $itemsY + $headH;

        // Bordes exteriores y líneas verticales.
        $pdf->Rect($x, $bodyY, $colQty, $bodyH);
        $pdf->Rect($x + $colQty, $bodyY, $colDesc, $bodyH);
        $pdf->Rect($x + $colQty + $colDesc, $bodyY, $colUnit, $bodyH);
        $pdf->Rect($x + $colQty + $colDesc + $colUnit, $bodyY, $colTotal, $bodyH);

        $pdf->SetFont('helvetica', '', 7);

        $lineY = $bodyY + 4;
        foreach ($detalle as $row) {
            if ($lineY > ($bodyY + $bodyH - 6)) {
                break;
            }

            $cantidad = $this->numeroLimpio($row['cantidad'] ?? 0);
            $descripcion = strtoupper($row['descripcion'] ?? '');
            $precio = $this->money($row['precio_unitario'] ?? 0);
            $lineTotal = $this->money($row['total_linea'] ?? 0);

            $pdf->SetXY($x, $lineY);
            $pdf->Cell($colQty, 4, $cantidad, 0, 0, 'C');

            $pdf->SetXY($x + $colQty + 2, $lineY);
            $pdf->MultiCell($colDesc - 4, 4, $descripcion, 0, 'L', false);

            $pdf->SetXY($x + $colQty + $colDesc, $lineY);
            $pdf->Cell($colUnit - 2, 4, $precio, 0, 0, 'R');

            $pdf->SetXY($x + $colQty + $colDesc + $colUnit, $lineY);
            $pdf->Cell($colTotal - 2, 4, $lineTotal, 0, 0, 'R');

            $lineY += 6;
        }

        /*
        NOTAS + PAID IN FULL
    */
        $notesY = $bodyY + $bodyH + 5;
        $notesW = 70;
        $notesH = 20;

        $pdf->SetFillColor($rHeader, $gHeader, $bHeader);
        $pdf->Rect($x, $notesY, $notesW, 5, 'DF');

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 5.2);
        $pdf->SetXY($x, $notesY + 1.2);
        $pdf->Cell($notesW, 2, 'Special Notes and instructions', 0, 0, 'C');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->Rect($x, $notesY + 5, $notesW, $notesH - 5);

        $pdf->SetFont('helvetica', '', 5.5);
        $pdf->SetXY($x + 2, $notesY + 7);
        $pdf->MultiCell($notesW - 4, 3, $notas, 0, 'L', false);

        // Marca PAID IN FULL inclinada.
        $pdf->StartTransform();
        $pdf->Rotate(15, $x + 35, $notesY + 15);
        $pdf->SetTextColor(220, 70, 70);
        $pdf->SetAlpha(0.38);
        $pdf->SetFont('times', 'B', 13);
        $pdf->SetXY($x + 19, $notesY + 10);
        $pdf->Cell(42, 7, 'PAID IN FULL', 0, 0, 'C');
        $pdf->StopTransform();
        $pdf->SetAlpha(1);
        $pdf->SetTextColor(0, 0, 0);

        /*
        TOTALES
    */
        $totX = $x + 97;
        $totY = $bodyY + $bodyH;
        $totLabelW = 24;
        $totValW = 25;
        $totRowH = 5;

        $totales = [
            ['SUBTOTAL', $this->money($subtotal)],
            ['TAX RATE', number_format($tasaImpuesto, 2) . '%'],
            ['SALES TAX', $this->money($impuesto)],
            ['SHIPPING&HANDLING', $this->money($otrosCargos)],
            ['TOTAL', $this->money($total)],
        ];

        foreach ($totales as $i => $row) {
            $yy = $totY + ($i * $totRowH);

            $pdf->SetFillColor(255, 255, 255);
            $pdf->Rect($totX, $yy, $totLabelW, $totRowH);
            $pdf->Rect($totX + $totLabelW, $yy, $totValW, $totRowH);

            $pdf->SetFont('helvetica', 'B', 5.4);
            $pdf->SetXY($totX + 1, $yy + 1.2);
            $pdf->Cell($totLabelW - 2, 2.5, $row[0], 0, 0, 'R');

            $pdf->SetFont('helvetica', '', 5.4);
            $pdf->SetXY($totX + $totLabelW + 1, $yy + 1.2);
            $pdf->Cell($totValW - 2, 2.5, $row[1], 0, 0, 'R');
        }

        /*
        FOOTER
    */
        $footerY = 228;

        /*$footerDefault = "MAKE ALL CHEKS PAYABLE TO " . $nombreEmpresa . "\n";
        $footerDefault .= "THANK YOU FOR BUSINESS!!\n\n";
        $footerDefault .= "IF YOU HAVE ANY QUESTIONS CONCERNING THIS INVOICE\n";
        $footerDefault .= "CONTACT " . ($salesMan ?: $nombreEmpresa) . " PHONE " . $telefono . "\n";
        $footerDefault .= "THANKS YOU FOR YOUR BUSINES";
 
        if ($textoPie !== '') {
            $footerDefault .= "\n" . strtoupper($textoPie);
        }
*/
        $pdf->SetFont('helvetica', '', 6.3);
        $pdf->SetXY($x, $footerY);
        $pdf->MultiCell($w, 4, $textoPie, 0, 'C', false);
    }


    public function folios()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $folios = $this->model->listarFoliosActivos();

            echo json_encode([
                'ok' => true,
                'data' => $folios
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al listar series activas.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
