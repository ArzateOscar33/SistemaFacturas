<?php

class UsuariosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listarUsuarios(string $buscar = '', string $estado = '')
    {
        $where = "WHERE 1=1";
        $params = [];

        if ($buscar !== '') {
            $where .= " AND (
                u.nombre LIKE ?
                OR u.apellido LIKE ?
                OR u.usuario LIKE ?
                OR u.correo LIKE ?
                OR r.nombre LIKE ?
            )";

            $term = '%' . $buscar . '%';

            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($estado !== '' && in_array($estado, ['0', '1'], true)) {
            $where .= " AND u.estado = ?";
            $params[] = (int)$estado;
        }

        $sql = "SELECT
                    u.id_usuario,
                    u.id_rol,
                    r.nombre AS rol,
                    u.nombre,
                    u.apellido,
                    u.usuario,
                    u.correo,
                    u.estado,
                    u.creado_en,
                    u.actualizado_en
                FROM usuarios u
                INNER JOIN roles r ON r.id_rol = u.id_rol
                $where
                ORDER BY u.id_usuario DESC";

        return $this->selectAll($sql, $params);
    }

    public function obtenerUsuario(int $id_usuario)
    {
        $sql = "SELECT
                    u.id_usuario,
                    u.id_rol,
                    r.nombre AS rol,
                    u.nombre,
                    u.apellido,
                    u.usuario,
                    u.correo,
                    u.estado,
                    u.creado_en,
                    u.actualizado_en
                FROM usuarios u
                INNER JOIN roles r ON r.id_rol = u.id_rol
                WHERE u.id_usuario = ?
                LIMIT 1";

        return $this->select($sql, [$id_usuario]);
    }

    public function listarRoles()
    {
        $sql = "SELECT
                    id_rol,
                    nombre,
                    descripcion,
                    estado
                FROM roles
                WHERE estado = 1
                ORDER BY nombre ASC";

        return $this->selectAll($sql);
    }

    public function obtenerRol(int $id_rol)
    {
        $sql = "SELECT
                    id_rol,
                    nombre,
                    descripcion,
                    estado
                FROM roles
                WHERE id_rol = ?
                LIMIT 1";

        return $this->select($sql, [$id_rol]);
    }

    public function existeUsuario(string $usuario, int $id_usuario = 0)
    {
        if ($id_usuario > 0) {
            $sql = "SELECT id_usuario
                    FROM usuarios
                    WHERE usuario = ?
                      AND id_usuario != ?
                    LIMIT 1";

            return $this->select($sql, [$usuario, $id_usuario]);
        }

        $sql = "SELECT id_usuario
                FROM usuarios
                WHERE usuario = ?
                LIMIT 1";

        return $this->select($sql, [$usuario]);
    }

    public function existeCorreo(string $correo, int $id_usuario = 0)
    {
        if ($correo === '') {
            return false;
        }

        if ($id_usuario > 0) {
            $sql = "SELECT id_usuario
                    FROM usuarios
                    WHERE correo = ?
                      AND id_usuario != ?
                    LIMIT 1";

            return $this->select($sql, [$correo, $id_usuario]);
        }

        $sql = "SELECT id_usuario
                FROM usuarios
                WHERE correo = ?
                LIMIT 1";

        return $this->select($sql, [$correo]);
    }

    public function registrarUsuario(
        int $id_rol,
        string $nombre,
        ?string $apellido,
        string $usuario,
        ?string $correo,
        string $password,
        int $estado
    ) {
        $sql = "INSERT INTO usuarios (
                    id_rol,
                    nombre,
                    apellido,
                    usuario,
                    correo,
                    password,
                    estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        return $this->insertar($sql, [
            $id_rol,
            $nombre,
            $apellido,
            $usuario,
            $correo,
            $password,
            $estado
        ]);
    }

    public function actualizarUsuario(
        int $id_usuario,
        int $id_rol,
        string $nombre,
        ?string $apellido,
        string $usuario,
        ?string $correo,
        int $estado
    ) {
        $sql = "UPDATE usuarios
                SET
                    id_rol = ?,
                    nombre = ?,
                    apellido = ?,
                    usuario = ?,
                    correo = ?,
                    estado = ?,
                    actualizado_en = NOW()
                WHERE id_usuario = ?";

        return $this->save($sql, [
            $id_rol,
            $nombre,
            $apellido,
            $usuario,
            $correo,
            $estado,
            $id_usuario
        ]);
    }

    public function actualizarPassword(int $id_usuario, string $password)
    {
        $sql = "UPDATE usuarios
                SET
                    password = ?,
                    actualizado_en = NOW()
                WHERE id_usuario = ?";

        return $this->save($sql, [$password, $id_usuario]);
    }

    public function cambiarEstadoUsuario(int $id_usuario, int $estado)
    {
        $sql = "UPDATE usuarios
                SET
                    estado = ?,
                    actualizado_en = NOW()
                WHERE id_usuario = ?";

        return $this->save($sql, [$estado, $id_usuario]);
    }

    public function registrarBitacora(?int $id_usuario, string $accion, ?int $entidad_id, string $detalle)
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
            'Usuarios',
            $accion,
            'usuarios',
            $entidad_id,
            $detalle
        ]);
    }
}
