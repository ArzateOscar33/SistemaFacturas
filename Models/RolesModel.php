<?php

class RolesModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ============================================================
       RESUMEN / KPIS
    ============================================================ */
    public function resumenRoles()
    {
        $sql = "SELECT
                    COUNT(*) AS total_roles,
                    SUM(CASE WHEN estado = 1 THEN 1 ELSE 0 END) AS roles_activos,
                    SUM(CASE WHEN estado = 0 THEN 1 ELSE 0 END) AS roles_inactivos
                FROM roles";

        $roles = $this->select($sql);

        $sqlUsuarios = "SELECT COUNT(*) AS usuarios_asignados
                        FROM usuarios
                        WHERE id_rol IS NOT NULL";

        $usuarios = $this->select($sqlUsuarios);

        return [
            'total_roles' => (int)($roles['total_roles'] ?? 0),
            'roles_activos' => (int)($roles['roles_activos'] ?? 0),
            'roles_inactivos' => (int)($roles['roles_inactivos'] ?? 0),
            'usuarios_asignados' => (int)($usuarios['usuarios_asignados'] ?? 0)
        ];
    }

    /* ============================================================
       LISTAR ROLES
    ============================================================ */
    public function listarRoles(string $busqueda = '', string $estado = '')
    {
        $where = "WHERE 1=1";
        $params = [];

        if ($busqueda !== '') {
            $where .= " AND (
                            r.nombre LIKE ?
                            OR r.descripcion LIKE ?
                        )";
            $like = '%' . $busqueda . '%';
            $params[] = $like;
            $params[] = $like;
        }

        if ($estado !== '' && in_array($estado, ['0', '1'], true)) {
            $where .= " AND r.estado = ?";
            $params[] = (int)$estado;
        }

        $sql = "SELECT
                    r.id_rol,
                    r.nombre,
                    r.descripcion,
                    r.estado,
                    r.creado_en,
                    COUNT(u.id_usuario) AS total_usuarios
                FROM roles r
                LEFT JOIN usuarios u ON u.id_rol = r.id_rol
                $where
                GROUP BY
                    r.id_rol,
                    r.nombre,
                    r.descripcion,
                    r.estado,
                    r.creado_en
                ORDER BY r.id_rol ASC";

        return $this->selectAll($sql, $params);
    }

    /* ============================================================
       OBTENER ROL
    ============================================================ */
    public function obtenerRol(int $id_rol)
    {
        $sql = "SELECT
                    id_rol,
                    nombre,
                    descripcion,
                    estado,
                    creado_en
                FROM roles
                WHERE id_rol = ?
                LIMIT 1";

        return $this->select($sql, [$id_rol]);
    }

    /* ============================================================
       VALIDAR NOMBRE DUPLICADO
    ============================================================ */
    public function existeNombreRol(string $nombre, int $id_rol = 0)
    {
        if ($id_rol > 0) {
            $sql = "SELECT id_rol
                    FROM roles
                    WHERE nombre = ?
                    AND id_rol != ?
                    LIMIT 1";

            return $this->select($sql, [$nombre, $id_rol]);
        }

        $sql = "SELECT id_rol
                FROM roles
                WHERE nombre = ?
                LIMIT 1";

        return $this->select($sql, [$nombre]);
    }

    /* ============================================================
       REGISTRAR ROL
    ============================================================ */
    public function registrarRol(string $nombre, ?string $descripcion, int $estado)
    {
        $sql = "INSERT INTO roles (
                    nombre,
                    descripcion,
                    estado
                ) VALUES (?, ?, ?)";

        return $this->insertar($sql, [
            $nombre,
            $descripcion,
            $estado
        ]);
    }

    /* ============================================================
       ACTUALIZAR ROL
    ============================================================ */
    public function actualizarRol(
        int $id_rol,
        string $nombre,
        ?string $descripcion,
        int $estado
    ) {
        $sql = "UPDATE roles
                SET nombre = ?,
                    descripcion = ?,
                    estado = ?
                WHERE id_rol = ?";

        return $this->save($sql, [
            $nombre,
            $descripcion,
            $estado,
            $id_rol
        ]);
    }

    /* ============================================================
       CAMBIAR ESTADO
    ============================================================ */
    public function cambiarEstado(int $id_rol, int $estado)
    {
        $sql = "UPDATE roles
                SET estado = ?
                WHERE id_rol = ?";

        return $this->save($sql, [
            $estado,
            $id_rol
        ]);
    }

    /* ============================================================
       VALIDAR SI EL ROL TIENE USUARIOS
    ============================================================ */
    public function contarUsuariosPorRol(int $id_rol)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM usuarios
                WHERE id_rol = ?";

        return $this->select($sql, [$id_rol]);
    }

    /* ============================================================
       ELIMINAR ROL
       OJO: solo si no tiene usuarios asignados
    ============================================================ */
    public function eliminarRol(int $id_rol)
    {
        $sql = "DELETE FROM roles
                WHERE id_rol = ?";

        return $this->save($sql, [$id_rol]);
    }

    /* ============================================================
       OBTENER PERMISOS DEL ROL
    ============================================================ */
    public function obtenerPermisosRol(int $id_rol)
    {
        $sql = "SELECT
                    id_permiso,
                    id_rol,
                    modulo,
                    puede_ver,
                    puede_crear,
                    puede_editar,
                    puede_eliminar
                FROM roles_permisos
                WHERE id_rol = ?";

        return $this->selectAll($sql, [$id_rol]);
    }

    /* ============================================================
       ELIMINAR PERMISOS DEL ROL
       Se usa antes de volver a guardar los permisos actualizados
    ============================================================ */
    public function eliminarPermisosRol(int $id_rol)
    {
        $sql = "DELETE FROM roles_permisos
                WHERE id_rol = ?";

        return $this->save($sql, [$id_rol]);
    }

    /* ============================================================
       GUARDAR PERMISO POR MÓDULO
    ============================================================ */
    public function guardarPermisoModulo(
        int $id_rol,
        string $modulo,
        int $puede_ver,
        int $puede_crear,
        int $puede_editar,
        int $puede_eliminar
    ) {
        $sql = "INSERT INTO roles_permisos (
                    id_rol,
                    modulo,
                    puede_ver,
                    puede_crear,
                    puede_editar,
                    puede_eliminar
                ) VALUES (?, ?, ?, ?, ?, ?)";

        return $this->insertar($sql, [
            $id_rol,
            $modulo,
            $puede_ver,
            $puede_crear,
            $puede_editar,
            $puede_eliminar
        ]);
    }

    /* ============================================================
       GUARDAR PERMISOS COMPLETOS
    ============================================================ */
    public function guardarPermisosRol(int $id_rol, array $permisos)
    {
        $this->eliminarPermisosRol($id_rol);

        if (empty($permisos)) {
            return true;
        }

        foreach ($permisos as $modulo => $acciones) {
            $modulo = trim((string)$modulo);

            if ($modulo === '') {
                continue;
            }

            $puede_ver = isset($acciones['ver']) ? 1 : 0;
            $puede_crear = isset($acciones['crear']) ? 1 : 0;
            $puede_editar = isset($acciones['editar']) ? 1 : 0;
            $puede_eliminar = isset($acciones['eliminar']) ? 1 : 0;

            $this->guardarPermisoModulo(
                $id_rol,
                $modulo,
                $puede_ver,
                $puede_crear,
                $puede_editar,
                $puede_eliminar
            );
        }

        return true;
    }

    /* ============================================================
       OBTENER ROL CON PERMISOS
       Para editar desde JS
    ============================================================ */
    public function obtenerRolConPermisos(int $id_rol)
    {
        $rol = $this->obtenerRol($id_rol);

        if (!$rol) {
            return null;
        }

        $permisosRaw = $this->obtenerPermisosRol($id_rol);
        $permisos = [];

        foreach ($permisosRaw as $permiso) {
            $modulo = $permiso['modulo'];

            $permisos[$modulo] = [
                'ver' => (int)$permiso['puede_ver'],
                'crear' => (int)$permiso['puede_crear'],
                'editar' => (int)$permiso['puede_editar'],
                'eliminar' => (int)$permiso['puede_eliminar']
            ];
        }

        $rol['permisos'] = $permisos;

        return $rol;
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
            'Roles',
            $accion,
            $entidad,
            $entidad_id,
            $detalle
        ]);
    }
}
