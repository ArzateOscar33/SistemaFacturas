<?php

class BitacoraModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listarEventos(
        string $buscar = '',
        string $id_usuario = '',
        string $modulo = '',
        string $accion = '',
        string $fecha_inicio = '',
        string $fecha_fin = '',
        int $limite = 50
    ) {
        $where = "WHERE 1=1";
        $params = [];

        if ($buscar !== '') {
            $where .= " AND (
                b.modulo LIKE ?
                OR b.accion LIKE ?
                OR b.entidad LIKE ?
                OR b.detalle LIKE ?
                OR u.nombre LIKE ?
                OR u.apellido LIKE ?
                OR u.usuario LIKE ?
                OR u.correo LIKE ?
            )";

            $term = '%' . $buscar . '%';

            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($id_usuario !== '' && ctype_digit($id_usuario)) {
            $where .= " AND b.id_usuario = ?";
            $params[] = (int)$id_usuario;
        }

        if ($modulo !== '') {
            $where .= " AND b.modulo = ?";
            $params[] = $modulo;
        }

        if ($accion !== '') {
            $where .= " AND b.accion = ?";
            $params[] = $accion;
        }

        if ($fecha_inicio !== '') {
            $where .= " AND DATE(b.creado_en) >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where .= " AND DATE(b.creado_en) <= ?";
            $params[] = $fecha_fin;
        }

        $limite = $this->normalizarLimite($limite);

        /*
            OJO:
            El LIMIT no se manda como parámetro porque algunas implementaciones
            PDO personalizadas no permiten bindear LIMIT correctamente.
            Lo normalizamos antes para evitar inyección.
        */
        $sql = "SELECT
                    b.id_bitacora,
                    b.id_usuario,
                    COALESCE(CONCAT_WS(' ', u.nombre, u.apellido), 'Sistema') AS nombre_usuario,
                    u.usuario,
                    u.correo,
                    b.modulo,
                    b.accion,
                    b.entidad,
                    b.entidad_id,
                    b.detalle,
                    b.creado_en
                FROM bitacora b
                LEFT JOIN usuarios u ON u.id_usuario = b.id_usuario
                $where
                ORDER BY b.creado_en DESC, b.id_bitacora DESC
                LIMIT $limite";

        return $this->selectAll($sql, $params);
    }

    public function resumenEventos(
        string $buscar = '',
        string $id_usuario = '',
        string $modulo = '',
        string $accion = '',
        string $fecha_inicio = '',
        string $fecha_fin = ''
    ) {
        $where = "WHERE 1=1";
        $params = [];

        if ($buscar !== '') {
            $where .= " AND (
                b.modulo LIKE ?
                OR b.accion LIKE ?
                OR b.entidad LIKE ?
                OR b.detalle LIKE ?
                OR u.nombre LIKE ?
                OR u.apellido LIKE ?
                OR u.usuario LIKE ?
                OR u.correo LIKE ?
            )";

            $term = '%' . $buscar . '%';

            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($id_usuario !== '' && ctype_digit($id_usuario)) {
            $where .= " AND b.id_usuario = ?";
            $params[] = (int)$id_usuario;
        }

        if ($modulo !== '') {
            $where .= " AND b.modulo = ?";
            $params[] = $modulo;
        }

        if ($accion !== '') {
            $where .= " AND b.accion = ?";
            $params[] = $accion;
        }

        if ($fecha_inicio !== '') {
            $where .= " AND DATE(b.creado_en) >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where .= " AND DATE(b.creado_en) <= ?";
            $params[] = $fecha_fin;
        }

        $sql = "SELECT
                    COUNT(*) AS total_eventos,

                    SUM(CASE 
                        WHEN b.accion = 'LOGIN_EXITOSO' 
                        THEN 1 ELSE 0 
                    END) AS total_logins,

                    SUM(CASE 
                        WHEN b.accion IN (
                            'REGISTRAR',
                            'ACTUALIZAR',
                            'CANCELAR',
                            'ACTIVAR',
                            'DESACTIVAR'
                        )
                        THEN 1 ELSE 0 
                    END) AS total_cambios,

                    SUM(CASE 
                        WHEN b.accion IN (
                            'LOGIN_FALLIDO',
                            'LOGIN_BLOQUEADO'
                        )
                        THEN 1 ELSE 0 
                    END) AS total_fallidos

                FROM bitacora b
                LEFT JOIN usuarios u ON u.id_usuario = b.id_usuario
                $where";

        return $this->select($sql, $params);
    }

    public function listarUsuariosFiltro()
    {
        $sql = "SELECT
                    id_usuario,
                    nombre,
                    apellido,
                    usuario,
                    correo,
                    estado
                FROM usuarios
                ORDER BY nombre ASC, apellido ASC, usuario ASC";

        return $this->selectAll($sql);
    }

    public function listarModulos()
    {
        $sql = "SELECT DISTINCT
                    modulo
                FROM bitacora
                WHERE modulo IS NOT NULL
                  AND modulo != ''
                ORDER BY modulo ASC";

        return $this->selectAll($sql);
    }

    public function listarAcciones()
    {
        $sql = "SELECT DISTINCT
                    accion
                FROM bitacora
                WHERE accion IS NOT NULL
                  AND accion != ''
                ORDER BY accion ASC";

        return $this->selectAll($sql);
    }

    private function normalizarLimite(int $limite): int
    {
        $permitidos = [25, 50, 100, 250, 500];

        if (!in_array($limite, $permitidos, true)) {
            return 50;
        }

        return $limite;
    }
}
