<?php

class ErroresModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ============================================================
       RESUMEN / KPIS
    ============================================================ */
    public function resumenErrores()
    {
        $sql = "SELECT
                    COUNT(*) AS total_errores,

                    SUM(CASE 
                        WHEN estado = 'pendiente' 
                        THEN 1 ELSE 0 
                    END) AS errores_pendientes,

                    SUM(CASE 
                        WHEN nivel = 'critico' 
                        THEN 1 ELSE 0 
                    END) AS errores_criticos,

                    SUM(CASE 
                        WHEN DATE(creado_en) = CURDATE() 
                        THEN 1 ELSE 0 
                    END) AS errores_hoy

                FROM errores_sistema";

        $data = $this->select($sql);

        return [
            'total_errores' => (int)($data['total_errores'] ?? 0),
            'errores_pendientes' => (int)($data['errores_pendientes'] ?? 0),
            'errores_criticos' => (int)($data['errores_criticos'] ?? 0),
            'errores_hoy' => (int)($data['errores_hoy'] ?? 0)
        ];
    }

    /* ============================================================
       LISTAR ERRORES
    ============================================================ */
    public function listarErrores(
        string $busqueda = '',
        string $tipo_error = '',
        string $nivel = '',
        string $estado = '',
        string $modulo = '',
        string $fecha_inicio = '',
        string $fecha_fin = '',
        int $limite = 50
    ) {
        $where = "WHERE 1=1";
        $params = [];

        if ($busqueda !== '') {
            $where .= " AND (
                            e.mensaje LIKE ?
                            OR e.archivo LIKE ?
                            OR e.url LIKE ?
                            OR e.controlador LIKE ?
                            OR e.metodo LIKE ?
                            OR e.datos_adicionales LIKE ?
                            OR u.usuario LIKE ?
                            OR u.correo LIKE ?
                            OR CONCAT_WS(' ', u.nombre, u.apellido) LIKE ?
                        )";

            $like = '%' . $busqueda . '%';

            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($tipo_error !== '' && $this->tipoValido($tipo_error)) {
            $where .= " AND e.tipo_error = ?";
            $params[] = $tipo_error;
        }

        if ($nivel !== '' && $this->nivelValido($nivel)) {
            $where .= " AND e.nivel = ?";
            $params[] = $nivel;
        }

        if ($estado !== '' && $this->estadoValido($estado)) {
            $where .= " AND e.estado = ?";
            $params[] = $estado;
        }

        if ($modulo !== '') {
            $where .= " AND e.modulo LIKE ?";
            $params[] = '%' . $modulo . '%';
        }

        if ($fecha_inicio !== '') {
            $where .= " AND DATE(e.creado_en) >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where .= " AND DATE(e.creado_en) <= ?";
            $params[] = $fecha_fin;
        }

        $limite = $this->normalizarLimite($limite);

        $sql = "SELECT
                    e.id_error,
                    e.tipo_error,
                    e.nivel,
                    e.modulo,
                    e.controlador,
                    e.metodo,
                    e.mensaje,
                    e.archivo,
                    e.linea,
                    e.url,
                    e.estado,
                    e.id_usuario,
                    e.creado_en,

                    COALESCE(
                        NULLIF(CONCAT_WS(' ', u.nombre, u.apellido), ''),
                        u.usuario,
                        'Sistema'
                    ) AS usuario_nombre

                FROM errores_sistema e
                LEFT JOIN usuarios u ON u.id_usuario = e.id_usuario
                $where
                ORDER BY e.creado_en DESC, e.id_error DESC
                LIMIT $limite";

        return $this->selectAll($sql, $params);
    }

    /* ============================================================
       OBTENER DETALLE
    ============================================================ */
    public function obtenerError(int $id_error)
    {
        $sql = "SELECT
                    e.id_error,
                    e.tipo_error,
                    e.nivel,
                    e.modulo,
                    e.controlador,
                    e.metodo,
                    e.mensaje,
                    e.archivo,
                    e.linea,
                    e.url,
                    e.datos_adicionales,
                    e.id_usuario,
                    e.ip,
                    e.user_agent,
                    e.estado,
                    e.nota_revision,
                    e.revisado_por,
                    e.revisado_en,
                    e.creado_en,

                    COALESCE(
                        NULLIF(CONCAT_WS(' ', u.nombre, u.apellido), ''),
                        u.usuario,
                        'Sistema'
                    ) AS usuario_nombre,

                    COALESCE(
                        NULLIF(CONCAT_WS(' ', ur.nombre, ur.apellido), ''),
                        ur.usuario,
                        ''
                    ) AS revisado_por_nombre

                FROM errores_sistema e
                LEFT JOIN usuarios u ON u.id_usuario = e.id_usuario
                LEFT JOIN usuarios ur ON ur.id_usuario = e.revisado_por
                WHERE e.id_error = ?
                LIMIT 1";

        return $this->select($sql, [$id_error]);
    }

    /* ============================================================
       REGISTRAR ERROR
       Este método se podrá usar desde otros controladores.
    ============================================================ */
    public function registrarError(
        string $tipo_error,
        string $nivel,
        ?string $modulo,
        ?string $controlador,
        ?string $metodo,
        string $mensaje,
        ?string $archivo = null,
        ?int $linea = null,
        ?string $url = null,
        $datos_adicionales = null,
        ?int $id_usuario = null,
        ?string $ip = null,
        ?string $user_agent = null
    ) {
        if (!$this->tipoValido($tipo_error)) {
            $tipo_error = 'SISTEMA';
        }

        if (!$this->nivelValido($nivel)) {
            $nivel = 'error';
        }

        $datos = null;

        if ($datos_adicionales !== null) {
            if (is_array($datos_adicionales) || is_object($datos_adicionales)) {
                $datos = json_encode($datos_adicionales, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } else {
                $datos = (string)$datos_adicionales;
            }
        }

        $sql = "INSERT INTO errores_sistema (
                    tipo_error,
                    nivel,
                    modulo,
                    controlador,
                    metodo,
                    mensaje,
                    archivo,
                    linea,
                    url,
                    datos_adicionales,
                    id_usuario,
                    ip,
                    user_agent,
                    estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->insertar($sql, [
            $tipo_error,
            $nivel,
            $modulo,
            $controlador,
            $metodo,
            $mensaje,
            $archivo,
            $linea,
            $url,
            $datos,
            $id_usuario,
            $ip,
            $user_agent,
            'pendiente'
        ]);
    }

    /* ============================================================
       ACTUALIZAR REVISIÓN
    ============================================================ */
    public function actualizarRevision(
        int $id_error,
        string $estado,
        ?string $nota_revision,
        ?int $revisado_por
    ) {
        $sql = "UPDATE errores_sistema
                SET estado = ?,
                    nota_revision = ?,
                    revisado_por = ?,
                    revisado_en = NOW()
                WHERE id_error = ?";

        return $this->save($sql, [
            $estado,
            $nota_revision,
            $revisado_por,
            $id_error
        ]);
    }

    /* ============================================================
       CAMBIAR ESTADO RÁPIDO
    ============================================================ */
    public function cambiarEstado(
        int $id_error,
        string $estado,
        ?int $revisado_por
    ) {
        $sql = "UPDATE errores_sistema
                SET estado = ?,
                    revisado_por = ?,
                    revisado_en = NOW()
                WHERE id_error = ?";

        return $this->save($sql, [
            $estado,
            $revisado_por,
            $id_error
        ]);
    }

    /* ============================================================
       ELIMINAR ERROR
       Solo para limpiar registros cuando sea necesario.
    ============================================================ */
    public function eliminarError(int $id_error)
    {
        $sql = "DELETE FROM errores_sistema
                WHERE id_error = ?";

        return $this->save($sql, [$id_error]);
    }

    /* ============================================================
       LIMPIAR ERRORES ANTIGUOS
       Útil si después quieres un botón para limpiar historial.
    ============================================================ */
    public function eliminarErroresAntiguos(int $dias)
    {
        $dias = max(1, min($dias, 365));

        $sql = "DELETE FROM errores_sistema
                WHERE creado_en < DATE_SUB(NOW(), INTERVAL $dias DAY)
                AND estado = 'resuelto'";

        return $this->save($sql, []);
    }

    /* ============================================================
       CATÁLOGOS PARA FILTROS
    ============================================================ */
    public function modulosDisponibles()
    {
        $sql = "SELECT DISTINCT modulo
                FROM errores_sistema
                WHERE modulo IS NOT NULL
                AND modulo != ''
                ORDER BY modulo ASC";

        return $this->selectAll($sql);
    }

    public function usuariosConErrores()
    {
        $sql = "SELECT DISTINCT
                    u.id_usuario,
                    COALESCE(
                        NULLIF(CONCAT_WS(' ', u.nombre, u.apellido), ''),
                        u.usuario
                    ) AS usuario_nombre
                FROM errores_sistema e
                INNER JOIN usuarios u ON u.id_usuario = e.id_usuario
                ORDER BY usuario_nombre ASC";

        return $this->selectAll($sql);
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
            'Reporte de errores',
            $accion,
            $entidad,
            $entidad_id,
            $detalle
        ]);
    }

    /* ============================================================
       HELPERS INTERNOS
    ============================================================ */
    private function tipoValido(string $tipo): bool
    {
        return in_array($tipo, ['PHP', 'SQL', 'AJAX', 'VALIDACION', 'SISTEMA'], true);
    }

    private function nivelValido(string $nivel): bool
    {
        return in_array($nivel, ['info', 'warning', 'error', 'critico'], true);
    }

    private function estadoValido(string $estado): bool
    {
        return in_array($estado, ['pendiente', 'revisado', 'resuelto'], true);
    }

    private function normalizarLimite(int $limite): int
    {
        $permitidos = [50, 100, 250, 500];

        if (!in_array($limite, $permitidos, true)) {
            return 50;
        }

        return $limite;
    }
}
