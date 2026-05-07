<?php

class ClientesModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listarClientes(string $buscar = '', string $estado = '')
    {
        $where = "WHERE 1=1";
        $params = [];

        if ($buscar !== '') {
            $where .= " AND (
                c.codigo_cliente LIKE ?
                OR c.nombre_cliente LIKE ?
                OR c.rfc LIKE ?
                OR c.correo LIKE ?
                OR c.telefono LIKE ?
                OR c.direccion LIKE ?
            )";

            $term = '%' . $buscar . '%';

            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($estado !== '' && in_array($estado, ['0', '1'], true)) {
            $where .= " AND c.estado = ?";
            $params[] = (int)$estado;
        }

        $sql = "SELECT
                    c.id_cliente,
                    c.codigo_cliente,
                    c.nombre_cliente,
                    c.rfc,
                    c.correo,
                    c.telefono,
                    c.direccion,
                    c.estado,
                    c.creado_en,
                    c.actualizado_en,

                    (
                        SELECT COUNT(*)
                        FROM cliente_direcciones cd
                        WHERE cd.id_cliente = c.id_cliente
                          AND cd.estado = 1
                    ) AS total_direcciones,

                    (
                        SELECT cd2.direccion
                        FROM cliente_direcciones cd2
                        WHERE cd2.id_cliente = c.id_cliente
                          AND cd2.estado = 1
                        ORDER BY cd2.es_principal DESC, cd2.id_direccion ASC
                        LIMIT 1
                    ) AS direccion_principal

                FROM clientes c
                $where
                ORDER BY c.id_cliente DESC";

        return $this->selectAll($sql, $params);
    }

    public function obtenerCliente(int $id_cliente)
    {
        $sql = "SELECT
                    id_cliente,
                    codigo_cliente,
                    nombre_cliente,
                    rfc,
                    correo,
                    telefono,
                    direccion,
                    estado,
                    creado_en,
                    actualizado_en
                FROM clientes
                WHERE id_cliente = ?
                LIMIT 1";

        return $this->select($sql, [$id_cliente]);
    }

    public function existeCodigoCliente(string $codigo_cliente, int $id_cliente = 0)
    {
        if ($id_cliente > 0) {
            $sql = "SELECT id_cliente
                    FROM clientes
                    WHERE codigo_cliente = ?
                      AND id_cliente != ?
                    LIMIT 1";

            return $this->select($sql, [$codigo_cliente, $id_cliente]);
        }

        $sql = "SELECT id_cliente
                FROM clientes
                WHERE codigo_cliente = ?
                LIMIT 1";

        return $this->select($sql, [$codigo_cliente]);
    }

    public function registrarCliente(
        string $codigo_cliente,
        string $nombre_cliente,
        ?string $rfc,
        ?string $correo,
        ?string $telefono,
        ?string $direccion,
        int $estado
    ) {
        $sql = "INSERT INTO clientes (
                    codigo_cliente,
                    nombre_cliente,
                    rfc,
                    correo,
                    telefono,
                    direccion,
                    estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        return $this->insertar($sql, [
            $codigo_cliente,
            $nombre_cliente,
            $rfc,
            $correo,
            $telefono,
            $direccion,
            $estado
        ]);
    }

    public function actualizarCliente(
        int $id_cliente,
        string $codigo_cliente,
        string $nombre_cliente,
        ?string $rfc,
        ?string $correo,
        ?string $telefono,
        ?string $direccion,
        int $estado
    ) {
        $sql = "UPDATE clientes
                SET
                    codigo_cliente = ?,
                    nombre_cliente = ?,
                    rfc = ?,
                    correo = ?,
                    telefono = ?,
                    direccion = ?,
                    estado = ?,
                    actualizado_en = NOW()
                WHERE id_cliente = ?";

        return $this->save($sql, [
            $codigo_cliente,
            $nombre_cliente,
            $rfc,
            $correo,
            $telefono,
            $direccion,
            $estado,
            $id_cliente
        ]);
    }

    public function cambiarEstadoCliente(int $id_cliente, int $estado)
    {
        $sql = "UPDATE clientes
                SET estado = ?,
                    actualizado_en = NOW()
                WHERE id_cliente = ?";

        return $this->save($sql, [$estado, $id_cliente]);
    }

    /* ============================================================
       DIRECCIONES DEL CLIENTE
    ============================================================ */

    public function listarDireccionesCliente(int $id_cliente)
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
                ORDER BY es_principal DESC, id_direccion ASC";

        return $this->selectAll($sql, [$id_cliente]);
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

    public function registrarDireccionCliente(
        int $id_cliente,
        ?string $alias,
        string $direccion,
        int $es_principal,
        int $estado = 1
    ) {
        if ($es_principal === 1) {
            $this->quitarDireccionPrincipal($id_cliente);
        }

        $sql = "INSERT INTO cliente_direcciones (
                    id_cliente,
                    alias,
                    direccion,
                    es_principal,
                    estado
                ) VALUES (?, ?, ?, ?, ?)";

        return $this->insertar($sql, [
            $id_cliente,
            $alias,
            $direccion,
            $es_principal,
            $estado
        ]);
    }

    public function actualizarDireccionCliente(
        int $id_direccion,
        int $id_cliente,
        ?string $alias,
        string $direccion,
        int $es_principal,
        int $estado
    ) {
        if ($es_principal === 1) {
            $this->quitarDireccionPrincipal($id_cliente, $id_direccion);
        }

        $sql = "UPDATE cliente_direcciones
                SET
                    alias = ?,
                    direccion = ?,
                    es_principal = ?,
                    estado = ?,
                    actualizado_en = NOW()
                WHERE id_direccion = ?
                  AND id_cliente = ?";

        return $this->save($sql, [
            $alias,
            $direccion,
            $es_principal,
            $estado,
            $id_direccion,
            $id_cliente
        ]);
    }

    public function cambiarEstadoDireccionCliente(
        int $id_direccion,
        int $id_cliente,
        int $estado
    ) {
        $sql = "UPDATE cliente_direcciones
                SET
                    estado = ?,
                    actualizado_en = NOW()
                WHERE id_direccion = ?
                  AND id_cliente = ?";

        return $this->save($sql, [
            $estado,
            $id_direccion,
            $id_cliente
        ]);
    }

    public function eliminarDireccionCliente(
        int $id_direccion,
        int $id_cliente
    ) {
        $sql = "UPDATE cliente_direcciones
                SET
                    estado = 0,
                    es_principal = 0,
                    actualizado_en = NOW()
                WHERE id_direccion = ?
                  AND id_cliente = ?";

        return $this->save($sql, [
            $id_direccion,
            $id_cliente
        ]);
    }

    public function marcarDireccionPrincipal(
        int $id_direccion,
        int $id_cliente
    ) {
        $direccion = $this->obtenerDireccionCliente($id_direccion);

        if (!$direccion) {
            return false;
        }

        if ((int)$direccion['id_cliente'] !== $id_cliente) {
            return false;
        }

        $this->quitarDireccionPrincipal($id_cliente, $id_direccion);

        $sql = "UPDATE cliente_direcciones
                SET
                    es_principal = 1,
                    estado = 1,
                    actualizado_en = NOW()
                WHERE id_direccion = ?
                  AND id_cliente = ?";

        return $this->save($sql, [
            $id_direccion,
            $id_cliente
        ]);
    }

    private function quitarDireccionPrincipal(
        int $id_cliente,
        int $excepto_id_direccion = 0
    ) {
        $params = [$id_cliente];

        $whereExcepto = "";

        if ($excepto_id_direccion > 0) {
            $whereExcepto = " AND id_direccion != ?";
            $params[] = $excepto_id_direccion;
        }

        $sql = "UPDATE cliente_direcciones
                SET
                    es_principal = 0,
                    actualizado_en = NOW()
                WHERE id_cliente = ?
                $whereExcepto";

        return $this->save($sql, $params);
    }

    public function contarDireccionesActivasCliente(int $id_cliente)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM cliente_direcciones
                WHERE id_cliente = ?
                  AND estado = 1";

        $data = $this->select($sql, [$id_cliente]);

        return (int)($data['total'] ?? 0);
    }

    public function direccionPerteneceCliente(
        int $id_direccion,
        int $id_cliente
    ) {
        $sql = "SELECT id_direccion
                FROM cliente_direcciones
                WHERE id_direccion = ?
                  AND id_cliente = ?
                LIMIT 1";

        return $this->select($sql, [
            $id_direccion,
            $id_cliente
        ]);
    }

    /* ============================================================
       BITÁCORA
    ============================================================ */

    public function registrarBitacora(?int $id_usuario, string $accion, ?int $id_cliente, string $detalle)
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
            'Clientes',
            $accion,
            'clientes',
            $id_cliente,
            $detalle
        ]);
    }
}
