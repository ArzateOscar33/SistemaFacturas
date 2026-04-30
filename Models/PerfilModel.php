<?php

class PerfilModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ============================================================
       OBTENER PERFIL DEL USUARIO ACTUAL
    ============================================================ */
    public function obtenerPerfil(int $id_usuario)
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

    /* ============================================================
       OBTENER PASSWORD ACTUAL
    ============================================================ */
    public function obtenerPassword(int $id_usuario)
    {
        $sql = "SELECT
                    id_usuario,
                    password
                FROM usuarios
                WHERE id_usuario = ?
                LIMIT 1";

        return $this->select($sql, [$id_usuario]);
    }

    /* ============================================================
       VALIDAR USUARIO DUPLICADO
       Evita que el usuario tome el username de otra cuenta.
    ============================================================ */
    public function existeUsuario(string $usuario, int $id_usuario)
    {
        $sql = "SELECT id_usuario
                FROM usuarios
                WHERE usuario = ?
                AND id_usuario != ?
                LIMIT 1";

        return $this->select($sql, [
            $usuario,
            $id_usuario
        ]);
    }

    /* ============================================================
       VALIDAR CORREO DUPLICADO
       Permite correo NULL o vacío.
    ============================================================ */
    public function existeCorreo(string $correo, int $id_usuario)
    {
        $sql = "SELECT id_usuario
                FROM usuarios
                WHERE correo = ?
                AND id_usuario != ?
                LIMIT 1";

        return $this->select($sql, [
            $correo,
            $id_usuario
        ]);
    }

    /* ============================================================
       ACTUALIZAR PERFIL
       No permite cambiar rol ni estado desde Mi perfil.
    ============================================================ */
    public function actualizarPerfil(
        int $id_usuario,
        string $nombre,
        ?string $apellido,
        string $usuario,
        ?string $correo
    ) {
        $sql = "UPDATE usuarios
                SET nombre = ?,
                    apellido = ?,
                    usuario = ?,
                    correo = ?,
                    actualizado_en = NOW()
                WHERE id_usuario = ?";

        return $this->save($sql, [
            $nombre,
            $apellido,
            $usuario,
            $correo,
            $id_usuario
        ]);
    }

    /* ============================================================
       CAMBIAR CONTRASEÑA
    ============================================================ */
    public function actualizarPassword(int $id_usuario, string $password_hash)
    {
        $sql = "UPDATE usuarios
                SET password = ?,
                    actualizado_en = NOW()
                WHERE id_usuario = ?";

        return $this->save($sql, [
            $password_hash,
            $id_usuario
        ]);
    }

    /* ============================================================
       BITÁCORA
    ============================================================ */
    public function registrarBitacora(
        ?int $id_usuario,
        string $accion,
        string $entidad,
        ?int $entidad_id,
        string $detalle
    ) {
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
            'Mi perfil',
            $accion,
            $entidad,
            $entidad_id,
            $detalle
        ]);
    }
}
