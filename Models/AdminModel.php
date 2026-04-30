<?php

class AdminModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function totalFacturasEmitidas()
    {
        $sql = "SELECT COUNT(*) AS total
                FROM facturas
                WHERE estado_factura = 'emitida'";

        return $this->select($sql);
    }

    public function totalClientesActivos()
    {
        $sql = "SELECT COUNT(*) AS total
                FROM clientes
                WHERE estado = 1";

        return $this->select($sql);
    }

    public function totalFacturado()
    {
        $sql = "SELECT COALESCE(SUM(total), 0) AS total
                FROM facturas
                WHERE estado_factura = 'emitida'";

        return $this->select($sql);
    }

    public function totalFacturasCanceladas()
    {
        $sql = "SELECT COUNT(*) AS total
                FROM facturas
                WHERE estado_factura = 'cancelada'";

        return $this->select($sql);
    }

    public function facturacionMensual()
    {
        $sql = "SELECT 
                    DATE_FORMAT(fecha_factura, '%Y-%m') AS mes,
                    COALESCE(SUM(total), 0) AS total
                FROM facturas
                WHERE estado_factura = 'emitida'
                  AND fecha_factura >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(fecha_factura, '%Y-%m')
                ORDER BY mes ASC";

        return $this->selectAll($sql);
    }

    public function facturasPorEstado()
    {
        $sql = "SELECT 
                    estado_factura,
                    COUNT(*) AS total
                FROM facturas
                GROUP BY estado_factura";

        return $this->selectAll($sql);
    }

    public function ultimasFacturas()
    {
        $sql = "SELECT 
                    f.id_factura,
                    f.folio_factura,
                    f.fecha_factura,
                    f.total,
                    f.estado_factura,
                    c.nombre_cliente,
                    u.nombre AS usuario_nombre,
                    u.apellido AS usuario_apellido
                FROM facturas f
                INNER JOIN clientes c ON c.id_cliente = f.id_cliente
                INNER JOIN usuarios u ON u.id_usuario = f.creado_por
                ORDER BY f.creado_en DESC
                LIMIT 5";

        return $this->selectAll($sql);
    }

    public function resumenDashboard()
    {
        return [
            'facturas_emitidas' => (int)($this->totalFacturasEmitidas()['total'] ?? 0),
            'clientes_activos' => (int)($this->totalClientesActivos()['total'] ?? 0),
            'total_facturado' => (float)($this->totalFacturado()['total'] ?? 0),
            'facturas_canceladas' => (int)($this->totalFacturasCanceladas()['total'] ?? 0),
            'facturacion_mensual' => $this->facturacionMensual(),
            'facturas_estado' => $this->facturasPorEstado(),
            'ultimas_facturas' => $this->ultimasFacturas()
        ];
    }
}
