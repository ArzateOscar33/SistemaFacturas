<?php

class LoginModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function buscarUsuario(string $usuario)
    {
        $sql = "SELECT 
                    u.id_usuario,
                    u.id_rol,
                    r.nombre AS rol,
                    u.nombre,
                    u.apellido,
                    u.usuario,
                    u.correo,
                    u.password,
                    u.estado
                FROM usuarios u
                INNER JOIN roles r ON r.id_rol = u.id_rol
                WHERE (u.usuario = ? OR u.correo = ?)
                LIMIT 1";

        return $this->select($sql, [$usuario, $usuario]);
    }

    public function actualizarUltimoAcceso(int $id_usuario)
    {
        $sql = "UPDATE usuarios 
                SET actualizado_en = NOW()
                WHERE id_usuario = ?";

        return $this->save($sql, [$id_usuario]);
    }

    public function registrarBitacora(?int $id_usuario, string $accion, string $detalle)
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
            'Login',
            $accion,
            'usuarios',
            $id_usuario,
            $detalle
        ]);
    }

    public function obtenerPermisosPorRol(int $id_rol)
    {
        $sql = "SELECT 
                modulo,
                puede_ver,
                puede_crear,
                puede_editar,
                puede_eliminar
            FROM roles_permisos
            WHERE id_rol = ?";

        return $this->selectAll($sql, [$id_rol]);
    }
}
