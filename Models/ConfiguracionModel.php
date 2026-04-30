<?php

class ConfiguracionModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

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

    public function crearEmpresaInicial()
    {
        $sql = "INSERT INTO empresa_configuracion (
                    nombre_empresa,
                    tax_id,
                    telefono,
                    correo,
                    direccion,
                    logo,
                    color_principal,
                    texto_pie_pagina,
                    actualizado_en
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        return $this->insertar($sql, [
            'MX-EXPRESS SERVICES',
            null,
            null,
            null,
            null,
            null,
            '#0d47a1',
            null
        ]);
    }

    public function actualizarEmpresa(
        int $id_empresa,
        string $nombre_empresa,
        ?string $tax_id,
        ?string $telefono,
        ?string $correo,
        ?string $direccion,
        ?string $color_principal,
        ?string $texto_pie_pagina
    ) {
        $sql = "UPDATE empresa_configuracion
                SET
                    nombre_empresa = ?,
                    tax_id = ?,
                    telefono = ?,
                    correo = ?,
                    direccion = ?,
                    color_principal = ?,
                    texto_pie_pagina = ?,
                    actualizado_en = NOW()
                WHERE id_empresa = ?";

        return $this->save($sql, [
            $nombre_empresa,
            $tax_id,
            $telefono,
            $correo,
            $direccion,
            $color_principal,
            $texto_pie_pagina,
            $id_empresa
        ]);
    }

    public function actualizarLogo(int $id_empresa, string $logo)
    {
        $sql = "UPDATE empresa_configuracion
                SET 
                    logo = ?,
                    actualizado_en = NOW()
                WHERE id_empresa = ?";

        return $this->save($sql, [
            $logo,
            $id_empresa
        ]);
    }

    public function obtenerFolioFactura()
    {
        $sql = "SELECT 
                    id_folio,
                    serie,
                    ultimo_numero,
                    activo,
                    creado_en,
                    actualizado_en
                FROM folios_factura
                WHERE activo = 1
                ORDER BY id_folio ASC
                LIMIT 1";

        return $this->select($sql);
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
            'Configuración',
            $accion,
            'empresa_configuracion',
            $entidad_id,
            $detalle
        ]);
    }
}
