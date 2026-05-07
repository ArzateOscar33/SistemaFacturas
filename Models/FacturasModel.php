<?php

class FacturasModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ============================================================
       TRANSACCIONES
       Importante para evitar folios duplicados y guardar factura
       con sus partidas de forma segura.
    ============================================================ */

    public function iniciarTransaccion()
    {
        return $this->save("START TRANSACTION", []);
    }

    public function confirmarTransaccion()
    {
        return $this->save("COMMIT", []);
    }

    public function cancelarTransaccion()
    {
        return $this->save("ROLLBACK", []);
    }

    /* ============================================================
       CLIENTES PARA SELECT
    ============================================================ */

    public function listarClientesActivos()
    {
        $sql = "SELECT 
                    c.id_cliente,
                    c.codigo_cliente,
                    c.nombre_cliente,
                    c.rfc,
                    c.correo,
                    c.telefono,
                    c.direccion,

                    (
                        SELECT cd.id_direccion
                        FROM cliente_direcciones cd
                        WHERE cd.id_cliente = c.id_cliente
                          AND cd.estado = 1
                        ORDER BY cd.es_principal DESC, cd.id_direccion ASC
                        LIMIT 1
                    ) AS id_direccion_principal,

                    (
                        SELECT cd2.direccion
                        FROM cliente_direcciones cd2
                        WHERE cd2.id_cliente = c.id_cliente
                          AND cd2.estado = 1
                        ORDER BY cd2.es_principal DESC, cd2.id_direccion ASC
                        LIMIT 1
                    ) AS direccion_principal

                FROM clientes c
                WHERE c.estado = 1
                ORDER BY c.nombre_cliente ASC";

        return $this->selectAll($sql);
    }

    public function obtenerCliente(int $id_cliente)
    {
        $sql = "SELECT 
                    c.id_cliente,
                    c.codigo_cliente,
                    c.nombre_cliente,
                    c.rfc,
                    c.correo,
                    c.telefono,
                    c.direccion,
                    c.estado,

                    (
                        SELECT cd.id_direccion
                        FROM cliente_direcciones cd
                        WHERE cd.id_cliente = c.id_cliente
                          AND cd.estado = 1
                        ORDER BY cd.es_principal DESC, cd.id_direccion ASC
                        LIMIT 1
                    ) AS id_direccion_principal,

                    (
                        SELECT cd2.direccion
                        FROM cliente_direcciones cd2
                        WHERE cd2.id_cliente = c.id_cliente
                          AND cd2.estado = 1
                        ORDER BY cd2.es_principal DESC, cd2.id_direccion ASC
                        LIMIT 1
                    ) AS direccion_principal

                FROM clientes c
                WHERE c.id_cliente = ?
                LIMIT 1";

        return $this->select($sql, [$id_cliente]);
    }

    public function listarDireccionesClienteActivas(int $id_cliente)
    {
        $sql = "SELECT
                    id_direccion,
                    id_cliente,
                    alias,
                    direccion,
                    es_principal,
                    estado,
                    creado_en,
                    actualizado_en
                FROM cliente_direcciones
                WHERE id_cliente = ?
                  AND estado = 1
                ORDER BY es_principal DESC, id_direccion ASC";

        return $this->selectAll($sql, [$id_cliente]);
    }

    public function obtenerDireccionCliente(int $id_direccion)
    {
        $sql = "SELECT
                    id_direccion,
                    id_cliente,
                    alias,
                    direccion,
                    es_principal,
                    estado,
                    creado_en,
                    actualizado_en
                FROM cliente_direcciones
                WHERE id_direccion = ?
                LIMIT 1";

        return $this->select($sql, [$id_direccion]);
    }

    public function obtenerDireccionClienteActiva(int $id_direccion, int $id_cliente)
    {
        $sql = "SELECT
                    id_direccion,
                    id_cliente,
                    alias,
                    direccion,
                    es_principal,
                    estado,
                    creado_en,
                    actualizado_en
                FROM cliente_direcciones
                WHERE id_direccion = ?
                  AND id_cliente = ?
                  AND estado = 1
                LIMIT 1";

        return $this->select($sql, [
            $id_direccion,
            $id_cliente
        ]);
    }

    public function obtenerDireccionPrincipalCliente(int $id_cliente)
    {
        $sql = "SELECT
                    id_direccion,
                    id_cliente,
                    alias,
                    direccion,
                    es_principal,
                    estado,
                    creado_en,
                    actualizado_en
                FROM cliente_direcciones
                WHERE id_cliente = ?
                  AND estado = 1
                ORDER BY es_principal DESC, id_direccion ASC
                LIMIT 1";

        return $this->select($sql, [$id_cliente]);
    }

    /* ============================================================
       CONFIGURACIÓN EMPRESA PARA PDF
    ============================================================ */

    public function obtenerEmpresa()
    {
        $sql = "SELECT 
                    id_empresa,
                    nombre_empresa,
                    tax_id,
                    telefono,
                    correo,
                    direccion,
                    logo,
                    color_principal,
                    texto_pie_pagina,
                    actualizado_en
                FROM empresa_configuracion
                ORDER BY id_empresa ASC
                LIMIT 1";

        return $this->select($sql);
    }

    /* ============================================================
       FOLIOS CON PROTECCIÓN DE CONCURRENCIA
    ============================================================ */

    public function obtenerFolioActivo()
    {
        $sql = "SELECT 
                    id_folio,
                    serie,
                    ultimo_numero,
                    activo
                FROM folios_factura
                WHERE activo = 1
                ORDER BY id_folio ASC
                LIMIT 1";

        return $this->select($sql);
    }

    public function obtenerFolioBloqueadoPorId(int $id_folio)
    {
        $sql = "SELECT 
                    id_folio,
                    serie,
                    ultimo_numero,
                    activo
                FROM folios_factura
                WHERE id_folio = ?
                  AND activo = 1
                LIMIT 1
                FOR UPDATE";

        return $this->select($sql, [$id_folio]);
    }

    public function actualizarUltimoFolio(int $id_folio, int $nuevo_numero)
    {
        $sql = "UPDATE folios_factura
                SET 
                    ultimo_numero = ?,
                    actualizado_en = NOW()
                WHERE id_folio = ?";

        return $this->save($sql, [
            $nuevo_numero,
            $id_folio
        ]);
    }

    /* ============================================================
       LISTADO DE FACTURAS
    ============================================================ */

    public function listarFacturas(
        string $buscar = '',
        string $estado = '',
        string $fecha_inicio = '',
        string $fecha_fin = ''
    ) {
        $where = "WHERE 1=1";
        $params = [];

        if ($buscar !== '') {
            $where .= " AND (
                f.folio_factura LIKE ?
                OR c.codigo_cliente LIKE ?
                OR c.nombre_cliente LIKE ?
                OR COALESCE(NULLIF(f.direccion_facturacion, ''), c.direccion) LIKE ?
                OR u.nombre LIKE ?
                OR u.apellido LIKE ?
                OR u.usuario LIKE ?
            )";

            $term = '%' . $buscar . '%';

            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($estado !== '' && in_array($estado, ['borrador', 'emitida', 'cancelada'], true)) {
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
                    f.id_factura,
                    f.serie,
                    f.numero_factura,
                    f.folio_factura,
                    f.id_cliente,
                    f.id_cliente_direccion,
                    f.direccion_facturacion,
                    COALESCE(NULLIF(f.direccion_facturacion, ''), c.direccion) AS direccion,
                    c.codigo_cliente,
                    c.nombre_cliente,
                    f.fecha_factura,
                    f.sales_man,
                    f.terms,
                    f.notas,
                    f.subtotal,
                    f.tasa_impuesto,
                    f.impuesto,
                    f.otros_cargos,
                    f.total,
                    f.estado_factura,
                    f.creado_por,
                    f.creado_en,
                    f.actualizado_en,
                    CONCAT_WS(' ', u.nombre, u.apellido) AS creado_por_nombre
                FROM facturas f
                INNER JOIN clientes c ON c.id_cliente = f.id_cliente
                INNER JOIN usuarios u ON u.id_usuario = f.creado_por
                $where
                ORDER BY f.creado_en DESC, f.id_factura DESC";

        return $this->selectAll($sql, $params);
    }

    /* ============================================================
       OBTENER FACTURA
    ============================================================ */

    public function obtenerFactura(int $id_factura)
    {
        $sql = "SELECT 
                    f.id_factura,
                    f.serie,
                    f.numero_factura,
                    f.folio_factura,
                    f.id_cliente,
                    f.id_cliente_direccion,
                    f.direccion_facturacion,
                    c.codigo_cliente,
                    c.nombre_cliente,
                    c.rfc,
                    c.correo,
                    c.telefono,
                    COALESCE(NULLIF(f.direccion_facturacion, ''), c.direccion) AS direccion,
                    cd.alias AS direccion_alias,
                    f.fecha_factura,
                    f.sales_man,
                    f.terms,
                    f.notas,
                    f.subtotal,
                    f.tasa_impuesto,
                    f.impuesto,
                    f.otros_cargos,
                    f.total,
                    f.estado_factura,
                    f.creado_por,
                    f.creado_en,
                    f.actualizado_en,
                    CONCAT_WS(' ', u.nombre, u.apellido) AS creado_por_nombre
                FROM facturas f
                INNER JOIN clientes c ON c.id_cliente = f.id_cliente
                INNER JOIN usuarios u ON u.id_usuario = f.creado_por
                LEFT JOIN cliente_direcciones cd ON cd.id_direccion = f.id_cliente_direccion
                WHERE f.id_factura = ?
                LIMIT 1";

        return $this->select($sql, [$id_factura]);
    }

    public function obtenerDetalleFactura(int $id_factura)
    {
        $sql = "SELECT 
                    id_detalle,
                    id_factura,
                    cantidad,
                    descripcion,
                    precio_unitario,
                    total_linea,
                    orden
                FROM facturas_detalle
                WHERE id_factura = ?
                ORDER BY orden ASC, id_detalle ASC";

        return $this->selectAll($sql, [$id_factura]);
    }

    public function obtenerFacturaCompleta(int $id_factura)
    {
        $factura = $this->obtenerFactura($id_factura);

        if (!$factura) {
            return null;
        }

        $factura['detalle'] = $this->obtenerDetalleFactura($id_factura);

        return $factura;
    }

    /* ============================================================
       REGISTRAR FACTURA
    ============================================================ */

    public function registrarFactura(
        string $serie,
        int $numero_factura,
        string $folio_factura,
        int $id_cliente,
        ?int $id_cliente_direccion,
        string $direccion_facturacion,
        string $fecha_factura,
        ?string $sales_man,
        ?string $terms,
        ?string $notas,
        float $subtotal,
        float $tasa_impuesto,
        float $impuesto,
        float $otros_cargos,
        float $total,
        string $estado_factura,
        int $creado_por
    ) {
        $sql = "INSERT INTO facturas (
                    serie,
                    numero_factura,
                    folio_factura,
                    id_cliente,
                    id_cliente_direccion,
                    direccion_facturacion,
                    fecha_factura,
                    sales_man,
                    terms,
                    notas,
                    subtotal,
                    tasa_impuesto,
                    impuesto,
                    otros_cargos,
                    total,
                    estado_factura,
                    creado_por
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->insertar($sql, [
            $serie,
            $numero_factura,
            $folio_factura,
            $id_cliente,
            $id_cliente_direccion,
            $direccion_facturacion,
            $fecha_factura,
            $sales_man,
            $terms,
            $notas,
            $subtotal,
            $tasa_impuesto,
            $impuesto,
            $otros_cargos,
            $total,
            $estado_factura,
            $creado_por
        ]);
    }

    public function registrarDetalleFactura(
        int $id_factura,
        float $cantidad,
        string $descripcion,
        float $precio_unitario,
        float $total_linea,
        int $orden
    ) {
        $sql = "INSERT INTO facturas_detalle (
                    id_factura,
                    cantidad,
                    descripcion,
                    precio_unitario,
                    total_linea,
                    orden
                ) VALUES (?, ?, ?, ?, ?, ?)";

        return $this->insertar($sql, [
            $id_factura,
            $cantidad,
            $descripcion,
            $precio_unitario,
            $total_linea,
            $orden
        ]);
    }

    /* ============================================================
       ACTUALIZAR FACTURA
       Nota: se permite editar facturas siempre que no estén canceladas.
    ============================================================ */

    public function actualizarFactura(
        int $id_factura,
        int $id_cliente,
        ?int $id_cliente_direccion,
        string $direccion_facturacion,
        string $fecha_factura,
        ?string $sales_man,
        ?string $terms,
        ?string $notas,
        float $subtotal,
        float $tasa_impuesto,
        float $impuesto,
        float $otros_cargos,
        float $total,
        string $estado_factura
    ) {
        $sql = "UPDATE facturas
                SET
                    id_cliente = ?,
                    id_cliente_direccion = ?,
                    direccion_facturacion = ?,
                    fecha_factura = ?,
                    sales_man = ?,
                    terms = ?,
                    notas = ?,
                    subtotal = ?,
                    tasa_impuesto = ?,
                    impuesto = ?,
                    otros_cargos = ?,
                    total = ?,
                    estado_factura = ?,
                    actualizado_en = NOW()
                WHERE id_factura = ?";

        return $this->save($sql, [
            $id_cliente,
            $id_cliente_direccion,
            $direccion_facturacion,
            $fecha_factura,
            $sales_man,
            $terms,
            $notas,
            $subtotal,
            $tasa_impuesto,
            $impuesto,
            $otros_cargos,
            $total,
            $estado_factura,
            $id_factura
        ]);
    }

    public function eliminarDetalleFactura(int $id_factura)
    {
        $sql = "DELETE FROM facturas_detalle
                WHERE id_factura = ?";

        return $this->save($sql, [$id_factura]);
    }

    /* ============================================================
       CANCELACIÓN / BAJA LÓGICA
    ============================================================ */

    public function cambiarEstadoFactura(int $id_factura, string $estado_factura)
    {
        $sql = "UPDATE facturas
                SET 
                    estado_factura = ?,
                    actualizado_en = NOW()
                WHERE id_factura = ?";

        return $this->save($sql, [
            $estado_factura,
            $id_factura
        ]);
    }

    public function cancelarFactura(int $id_factura)
    {
        return $this->cambiarEstadoFactura($id_factura, 'cancelada');
    }

    /* ============================================================
       VALIDACIONES
    ============================================================ */

    public function existeFolioFactura(string $serie, int $numero_factura, int $id_factura = 0)
    {
        if ($id_factura > 0) {
            $sql = "SELECT id_factura
                    FROM facturas
                    WHERE serie = ?
                      AND numero_factura = ?
                      AND id_factura != ?
                    LIMIT 1";

            return $this->select($sql, [
                $serie,
                $numero_factura,
                $id_factura
            ]);
        }

        $sql = "SELECT id_factura
                FROM facturas
                WHERE serie = ?
                  AND numero_factura = ?
                LIMIT 1";

        return $this->select($sql, [
            $serie,
            $numero_factura
        ]);
    }

    public function puedeEditarFactura(int $id_factura)
    {
        $factura = $this->obtenerFactura($id_factura);

        if (!$factura) {
            return false;
        }

        return $factura['estado_factura'] !== 'cancelada';
    }

    /* ============================================================
       BITÁCORA
    ============================================================ */

    public function registrarBitacora(?int $id_usuario, string $accion, ?int $id_factura, string $detalle)
    {
        $sql = "INSERT INTO bitacora (
                    id_usuario,
                    modulo,
                    accion,
                    entidad,
                    entidad_id,
                    detalle
                ) VALUES (?, ?, ?, ?, ?, ?)";

        return $this->insertar($sql, [
            $id_usuario,
            'Facturas',
            $accion,
            'facturas',
            $id_factura,
            $detalle
        ]);
    }

    /* ============================================================
       GENERACIÓN PDF / CONFIGURACIÓN
    ============================================================ */

    public function obtenerEmpresaConfiguracion()
    {
        $sql = "SELECT 
                    id_empresa,
                    nombre_empresa,
                    tax_id,
                    telefono,
                    correo,
                    direccion,
                    logo,
                    color_principal,
                    texto_pie_pagina,
                    actualizado_en
                FROM empresa_configuracion
                ORDER BY id_empresa ASC
                LIMIT 1";

        return $this->select($sql);
    }

    public function listarFoliosActivos()
    {
        $sql = "SELECT 
                    id_folio,
                    serie,
                    ultimo_numero,
                    activo
                FROM folios_factura
                WHERE activo = 1
                ORDER BY serie ASC";

        return $this->selectAll($sql);
    }
}
