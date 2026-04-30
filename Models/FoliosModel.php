<?php

class FoliosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ============================================================
       RESUMEN / KPIS
    ============================================================ */
    public function resumenFolios()
    {
        $sql = "SELECT
                    COUNT(*) AS total_series,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) AS series_activas,
                    SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) AS series_inactivas,
                    COALESCE(MAX(ultimo_numero), 0) AS ultimo_folio_global
                FROM folios_factura";

        $data = $this->select($sql);

        return [
            'total_series' => (int)($data['total_series'] ?? 0),
            'series_activas' => (int)($data['series_activas'] ?? 0),
            'series_inactivas' => (int)($data['series_inactivas'] ?? 0),
            'ultimo_folio_global' => (int)($data['ultimo_folio_global'] ?? 0)
        ];
    }

    /* ============================================================
       LISTAR FOLIOS / SERIES
    ============================================================ */
    public function listarFolios(string $busqueda = '', string $estado = '')
    {
        $where = "WHERE 1=1";
        $params = [];

        if ($busqueda !== '') {
            $where .= " AND serie LIKE ?";
            $params[] = '%' . $busqueda . '%';
        }

        if ($estado !== '' && in_array($estado, ['0', '1'], true)) {
            $where .= " AND activo = ?";
            $params[] = (int)$estado;
        }

        $sql = "SELECT
                    id_folio,
                    serie,
                    ultimo_numero,
                    activo,
                    creado_en,
                    actualizado_en
                FROM folios_factura
                $where
                ORDER BY id_folio ASC";

        return $this->selectAll($sql, $params);
    }

    /* ============================================================
       OBTENER SERIE
    ============================================================ */
    public function obtenerFolio(int $id_folio)
    {
        $sql = "SELECT
                    id_folio,
                    serie,
                    ultimo_numero,
                    activo,
                    creado_en,
                    actualizado_en
                FROM folios_factura
                WHERE id_folio = ?
                LIMIT 1";

        return $this->select($sql, [$id_folio]);
    }

    public function obtenerFolioPorSerie(string $serie)
    {
        $sql = "SELECT
                    id_folio,
                    serie,
                    ultimo_numero,
                    activo,
                    creado_en,
                    actualizado_en
                FROM folios_factura
                WHERE serie = ?
                LIMIT 1";

        return $this->select($sql, [$serie]);
    }

    /* ============================================================
       VALIDAR DUPLICADO
    ============================================================ */
    public function existeSerie(string $serie, int $id_folio = 0)
    {
        if ($id_folio > 0) {
            $sql = "SELECT id_folio
                    FROM folios_factura
                    WHERE serie = ?
                    AND id_folio != ?
                    LIMIT 1";

            return $this->select($sql, [$serie, $id_folio]);
        }

        $sql = "SELECT id_folio
                FROM folios_factura
                WHERE serie = ?
                LIMIT 1";

        return $this->select($sql, [$serie]);
    }

    /* ============================================================
       REGISTRAR SERIE
    ============================================================ */
    public function registrarFolio(string $serie, int $ultimo_numero, int $activo)
    {
        $sql = "INSERT INTO folios_factura (
                    serie,
                    ultimo_numero,
                    activo
                ) VALUES (?, ?, ?)";

        return $this->insertar($sql, [
            $serie,
            $ultimo_numero,
            $activo
        ]);
    }

    /* ============================================================
       ACTUALIZAR SERIE
    ============================================================ */
    public function actualizarFolio(
        int $id_folio,
        string $serie,
        int $ultimo_numero,
        int $activo
    ) {
        $sql = "UPDATE folios_factura
                SET serie = ?,
                    ultimo_numero = ?,
                    activo = ?,
                    actualizado_en = NOW()
                WHERE id_folio = ?";

        return $this->save($sql, [
            $serie,
            $ultimo_numero,
            $activo,
            $id_folio
        ]);
    }

    /* ============================================================
       CAMBIAR ESTADO
    ============================================================ */
    public function cambiarEstado(int $id_folio, int $activo)
    {
        $sql = "UPDATE folios_factura
                SET activo = ?,
                    actualizado_en = NOW()
                WHERE id_folio = ?";

        return $this->save($sql, [
            $activo,
            $id_folio
        ]);
    }

    /* ============================================================
       ELIMINAR SERIE
    ============================================================ */
    public function eliminarFolio(int $id_folio)
    {
        $sql = "DELETE FROM folios_factura
                WHERE id_folio = ?";

        return $this->save($sql, [$id_folio]);
    }

    /* ============================================================
       VALIDACIONES CON FACTURAS
    ============================================================ */
    public function contarFacturasPorSerie(string $serie)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM facturas
                WHERE serie = ?";

        return $this->select($sql, [$serie]);
    }

    public function obtenerMaximoNumeroFacturaPorSerie(string $serie)
    {
        $sql = "SELECT COALESCE(MAX(numero_factura), 0) AS max_numero
                FROM facturas
                WHERE serie = ?";

        return $this->select($sql, [$serie]);
    }

    public function existeFacturaConFolio(string $serie, int $numero_factura)
    {
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

    /* ============================================================
       SUGERIR SIGUIENTE FOLIO
       No reserva el folio, solo lo muestra para vista previa.
       La reserva real debe hacerse al crear factura con transacción.
    ============================================================ */
    public function siguienteFolioPreview(string $serie)
    {
        $folio = $this->obtenerFolioPorSerie($serie);

        if (!$folio) {
            return [
                'serie' => $serie,
                'ultimo_numero' => 0,
                'siguiente_numero' => 1,
                'folio_preview' => $serie . '-00000001'
            ];
        }

        $ultimo = (int)($folio['ultimo_numero'] ?? 0);
        $siguiente = $ultimo + 1;

        return [
            'serie' => $folio['serie'],
            'ultimo_numero' => $ultimo,
            'siguiente_numero' => $siguiente,
            'folio_preview' => $folio['serie'] . '-' . str_pad((string)$siguiente, 8, '0', STR_PAD_LEFT)
        ];
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
            'Folios y series',
            $accion,
            $entidad,
            $entidad_id,
            $detalle
        ]);
    }
}
