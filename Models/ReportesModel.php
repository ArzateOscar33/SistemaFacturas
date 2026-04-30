<?php

class ReportesModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ============================================================
       CLIENTES PARA FILTRO
    ============================================================ */

    public function listarClientes()
    {
        $sql = "SELECT
                    id_cliente,
                    codigo_cliente,
                    nombre_cliente,
                    estado
                FROM clientes
                ORDER BY nombre_cliente ASC";

        return $this->selectAll($sql);
    }

    /* ============================================================
       RESUMEN GENERAL / KPIS
    ============================================================ */

    public function resumenReporte(
        string $id_cliente = '',
        string $estado = '',
        string $fecha_inicio = '',
        string $fecha_fin = ''
    ) {
        $where = "WHERE 1=1";
        $params = [];

        if ($id_cliente !== '' && ctype_digit($id_cliente)) {
            $where .= " AND f.id_cliente = ?";
            $params[] = (int)$id_cliente;
        }

        if ($estado !== '' && in_array($estado, ['emitida', 'borrador', 'cancelada'], true)) {
            $where .= " AND f.estado_factura = ?";
            $params[] = $estado;
        }

        if ($fecha_inicio !== '') {
            $where .= " AND f.fecha_factura >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where .= " AND f.fecha_factura <= ?";
            $params[] = $fecha_fin;
        }

        /*
            total_facturado solamente suma facturas emitidas.
            Las canceladas y borradores no deben contar como venta real.
        */
        $sql = "SELECT
                    COUNT(*) AS total_facturas,

                    SUM(CASE 
                        WHEN f.estado_factura = 'emitida' 
                        THEN 1 ELSE 0 
                    END) AS facturas_emitidas,

                    SUM(CASE 
                        WHEN f.estado_factura = 'borrador' 
                        THEN 1 ELSE 0 
                    END) AS facturas_borrador,

                    SUM(CASE 
                        WHEN f.estado_factura = 'cancelada' 
                        THEN 1 ELSE 0 
                    END) AS facturas_canceladas,

                    COALESCE(SUM(CASE 
                        WHEN f.estado_factura = 'emitida' 
                        THEN f.total ELSE 0 
                    END), 0) AS total_facturado,

                    COALESCE(AVG(CASE 
                        WHEN f.estado_factura = 'emitida' 
                        THEN f.total ELSE NULL 
                    END), 0) AS promedio_factura

                FROM facturas f
                INNER JOIN clientes c ON c.id_cliente = f.id_cliente
                $where";

        return $this->select($sql, $params);
    }

    /* ============================================================
       FACTURACIÓN POR MES
    ============================================================ */

    public function facturacionMensual(
        string $id_cliente = '',
        string $estado = '',
        string $fecha_inicio = '',
        string $fecha_fin = ''
    ) {
        $where = "WHERE 1=1";
        $params = [];

        if ($id_cliente !== '' && ctype_digit($id_cliente)) {
            $where .= " AND f.id_cliente = ?";
            $params[] = (int)$id_cliente;
        }

        if ($estado !== '' && in_array($estado, ['emitida', 'borrador', 'cancelada'], true)) {
            $where .= " AND f.estado_factura = ?";
            $params[] = $estado;
        }

        if ($fecha_inicio !== '') {
            $where .= " AND f.fecha_factura >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where .= " AND f.fecha_factura <= ?";
            $params[] = $fecha_fin;
        }

        $sql = "SELECT
                    DATE_FORMAT(f.fecha_factura, '%Y-%m') AS periodo,
                    DATE_FORMAT(f.fecha_factura, '%b %Y') AS periodo_texto,

                    COUNT(*) AS total_facturas,

                    COALESCE(SUM(CASE 
                        WHEN f.estado_factura = 'emitida' 
                        THEN f.total ELSE 0 
                    END), 0) AS total_facturado

                FROM facturas f
                INNER JOIN clientes c ON c.id_cliente = f.id_cliente
                $where
                GROUP BY DATE_FORMAT(f.fecha_factura, '%Y-%m'),
                         DATE_FORMAT(f.fecha_factura, '%b %Y')
                ORDER BY periodo ASC";

        return $this->selectAll($sql, $params);
    }

    /* ============================================================
       DISTRIBUCIÓN POR ESTADO
    ============================================================ */

    public function facturasPorEstado(
        string $id_cliente = '',
        string $estado = '',
        string $fecha_inicio = '',
        string $fecha_fin = ''
    ) {
        $where = "WHERE 1=1";
        $params = [];

        if ($id_cliente !== '' && ctype_digit($id_cliente)) {
            $where .= " AND f.id_cliente = ?";
            $params[] = (int)$id_cliente;
        }

        if ($estado !== '' && in_array($estado, ['emitida', 'borrador', 'cancelada'], true)) {
            $where .= " AND f.estado_factura = ?";
            $params[] = $estado;
        }

        if ($fecha_inicio !== '') {
            $where .= " AND f.fecha_factura >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where .= " AND f.fecha_factura <= ?";
            $params[] = $fecha_fin;
        }

        $sql = "SELECT
                    f.estado_factura,
                    COUNT(*) AS total
                FROM facturas f
                INNER JOIN clientes c ON c.id_cliente = f.id_cliente
                $where
                GROUP BY f.estado_factura
                ORDER BY total DESC";

        return $this->selectAll($sql, $params);
    }

    /* ============================================================
       TOP CLIENTES
    ============================================================ */

    public function topClientes(
        string $id_cliente = '',
        string $estado = '',
        string $fecha_inicio = '',
        string $fecha_fin = '',
        int $limite = 10
    ) {
        $where = "WHERE 1=1";
        $params = [];

        if ($id_cliente !== '' && ctype_digit($id_cliente)) {
            $where .= " AND f.id_cliente = ?";
            $params[] = (int)$id_cliente;
        }

        if ($estado !== '' && in_array($estado, ['emitida', 'borrador', 'cancelada'], true)) {
            $where .= " AND f.estado_factura = ?";
            $params[] = $estado;
        }

        if ($fecha_inicio !== '') {
            $where .= " AND f.fecha_factura >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where .= " AND f.fecha_factura <= ?";
            $params[] = $fecha_fin;
        }

        $limite = $this->normalizarLimite($limite);

        /*
            El total del top clientes solo considera facturas emitidas.
            Si se filtra por cancelada/borrador, contará documentos pero el total facturado será 0.
        */
        $sql = "SELECT
                    c.id_cliente,
                    c.codigo_cliente,
                    c.nombre_cliente,
                    COUNT(f.id_factura) AS total_facturas,

                    COALESCE(SUM(CASE 
                        WHEN f.estado_factura = 'emitida' 
                        THEN f.total ELSE 0 
                    END), 0) AS total_facturado

                FROM facturas f
                INNER JOIN clientes c ON c.id_cliente = f.id_cliente
                $where
                GROUP BY c.id_cliente,
                         c.codigo_cliente,
                         c.nombre_cliente
                ORDER BY total_facturado DESC, total_facturas DESC
                LIMIT $limite";

        return $this->selectAll($sql, $params);
    }

    /* ============================================================
       ÚLTIMAS FACTURAS
    ============================================================ */

    public function ultimasFacturas(
        string $id_cliente = '',
        string $estado = '',
        string $fecha_inicio = '',
        string $fecha_fin = '',
        int $limite = 10
    ) {
        $where = "WHERE 1=1";
        $params = [];

        if ($id_cliente !== '' && ctype_digit($id_cliente)) {
            $where .= " AND f.id_cliente = ?";
            $params[] = (int)$id_cliente;
        }

        if ($estado !== '' && in_array($estado, ['emitida', 'borrador', 'cancelada'], true)) {
            $where .= " AND f.estado_factura = ?";
            $params[] = $estado;
        }

        if ($fecha_inicio !== '') {
            $where .= " AND f.fecha_factura >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where .= " AND f.fecha_factura <= ?";
            $params[] = $fecha_fin;
        }

        $limite = $this->normalizarLimite($limite);

        $sql = "SELECT
                    f.id_factura,
                    f.folio_factura,
                    f.fecha_factura,
                    f.subtotal,
                    f.impuesto,
                    f.otros_cargos,
                    f.total,
                    f.estado_factura,
                    c.codigo_cliente,
                    c.nombre_cliente
                FROM facturas f
                INNER JOIN clientes c ON c.id_cliente = f.id_cliente
                $where
                ORDER BY f.creado_en DESC, f.id_factura DESC
                LIMIT $limite";

        return $this->selectAll($sql, $params);
    }

    /* ============================================================
       REPORTE DETALLADO
    ============================================================ */

    public function reporteDetallado(
        string $id_cliente = '',
        string $estado = '',
        string $fecha_inicio = '',
        string $fecha_fin = '',
        int $limite = 500
    ) {
        $where = "WHERE 1=1";
        $params = [];

        if ($id_cliente !== '' && ctype_digit($id_cliente)) {
            $where .= " AND f.id_cliente = ?";
            $params[] = (int)$id_cliente;
        }

        if ($estado !== '' && in_array($estado, ['emitida', 'borrador', 'cancelada'], true)) {
            $where .= " AND f.estado_factura = ?";
            $params[] = $estado;
        }

        if ($fecha_inicio !== '') {
            $where .= " AND f.fecha_factura >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where .= " AND f.fecha_factura <= ?";
            $params[] = $fecha_fin;
        }

        $limite = $this->normalizarLimiteReporte($limite);

        $sql = "SELECT
                    f.id_factura,
                    f.serie,
                    f.numero_factura,
                    f.folio_factura,
                    f.fecha_factura,
                    f.sales_man,
                    f.terms,
                    f.subtotal,
                    f.tasa_impuesto,
                    f.impuesto,
                    f.otros_cargos,
                    f.total,
                    f.estado_factura,
                    f.creado_en,
                    c.id_cliente,
                    c.codigo_cliente,
                    c.nombre_cliente,
                    COALESCE(CONCAT_WS(' ', u.nombre, u.apellido), u.usuario) AS creado_por_nombre
                FROM facturas f
                INNER JOIN clientes c ON c.id_cliente = f.id_cliente
                INNER JOIN usuarios u ON u.id_usuario = f.creado_por
                $where
                ORDER BY f.fecha_factura DESC, f.id_factura DESC
                LIMIT $limite";

        return $this->selectAll($sql, $params);
    }

    /* ============================================================
       REPORTE COMPLETO PARA EL JS
    ============================================================ */

    public function obtenerReporte(
        string $id_cliente = '',
        string $estado = '',
        string $fecha_inicio = '',
        string $fecha_fin = ''
    ) {
        return [
            'resumen' => $this->resumenReporte(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin
            ),
            'facturacion_mensual' => $this->facturacionMensual(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin
            ),
            'facturas_por_estado' => $this->facturasPorEstado(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin
            ),
            'top_clientes' => $this->topClientes(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin,
                10
            ),
            'ultimas_facturas' => $this->ultimasFacturas(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin,
                10
            ),
            'detalle' => $this->reporteDetallado(
                $id_cliente,
                $estado,
                $fecha_inicio,
                $fecha_fin,
                500
            )
        ];
    }

    /* ============================================================
       HELPERS INTERNOS
    ============================================================ */

    private function normalizarLimite(int $limite): int
    {
        $permitidos = [5, 10, 15, 25, 50];

        if (!in_array($limite, $permitidos, true)) {
            return 10;
        }

        return $limite;
    }

    private function normalizarLimiteReporte(int $limite): int
    {
        $permitidos = [100, 250, 500, 1000];

        if (!in_array($limite, $permitidos, true)) {
            return 500;
        }

        return $limite;
    }
}
