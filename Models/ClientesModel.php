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
                codigo_cliente LIKE ?
                OR nombre_cliente LIKE ?
                OR rfc LIKE ?
                OR correo LIKE ?
                OR telefono LIKE ?
            )";

            $term = '%' . $buscar . '%';

            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($estado !== '' && in_array($estado, ['0', '1'], true)) {
            $where .= " AND estado = ?";
            $params[] = (int)$estado;
        }

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
                $where
                ORDER BY id_cliente DESC";

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
