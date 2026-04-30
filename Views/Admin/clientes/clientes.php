<?php require_once 'Views/Template/header-admin.php'; ?>

<main class="content">

    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="page-title">Clientes</h1>
            <p class="page-subtitle">
                Administra el catálogo de clientes para la generación de facturas.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="user-pill">
                <i data-feather="user" class="me-1"></i>
                <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
            </div>

            <button type="button" class="btn btn-primary-soft" id="btnNuevoCliente">
                <i data-feather="plus-circle" class="me-1"></i>
                Nuevo cliente
            </button>
        </div>
    </div>

    <div class="card-module mb-4">
        <div class="card-module-header">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <h5 class="card-module-title">Listado de clientes</h5>
                    <p class="card-module-subtitle">
                        Busca, consulta, edita o desactiva clientes registrados.
                    </p>
                </div>

                <div class="col-lg-4">
                    <label for="buscarCliente" class="form-label">Buscar cliente</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i data-feather="search"></i>
                        </span>
                        <input type="text"
                            class="form-control"
                            id="buscarCliente"
                            placeholder="Nombre, código, RFC, correo o teléfono">
                    </div>
                </div>

                <div class="col-lg-3">
                    <label for="filtroEstado" class="form-label">Estado</label>
                    <select id="filtroEstado" class="form-select">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="tablaClientes">
                <thead>
                    <tr>
                        <th style="width: 120px;">Código</th>
                        <th>Cliente</th>
                        <th>RFC</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th style="width: 130px;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyClientes">
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i data-feather="database"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Sin datos cargados</h6>
                                <div>El listado se cargará desde el controlador de clientes.</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- MODAL CLIENTE -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="formCliente" autocomplete="off" novalidate>

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalClienteLabel">Registrar cliente</h5>
                        <div class="text-secondary small">
                            Los campos marcados con <span class="required-mark">*</span> son obligatorios.
                        </div>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="id_cliente" name="id_cliente">

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label for="codigo_cliente" class="form-label">
                                Código cliente <span class="required-mark">*</span>
                            </label>
                            <input type="text"
                                class="form-control"
                                id="codigo_cliente"
                                name="codigo_cliente"
                                maxlength="30"
                                placeholder="Ej. C-0001"
                                required>
                            <div class="helper-text">
                                Identificador interno único del cliente.
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label for="nombre_cliente" class="form-label">
                                Nombre del cliente <span class="required-mark">*</span>
                            </label>
                            <input type="text"
                                class="form-control"
                                id="nombre_cliente"
                                name="nombre_cliente"
                                maxlength="150"
                                placeholder="Nombre comercial o razón social"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label for="rfc" class="form-label">RFC</label>
                            <input type="text"
                                class="form-control"
                                id="rfc"
                                name="rfc"
                                maxlength="30"
                                placeholder="RFC del cliente">
                            <div class="helper-text">
                                Opcional.
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="correo" class="form-label">Correo</label>
                            <input type="email"
                                class="form-control"
                                id="correo"
                                name="correo"
                                maxlength="120"
                                placeholder="correo@cliente.com">
                            <div class="helper-text">
                                Opcional, pero debe tener formato válido.
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text"
                                class="form-control"
                                id="telefono"
                                name="telefono"
                                maxlength="30"
                                placeholder="Ej. 6641234567">
                            <div class="helper-text">
                                Opcional.
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control"
                                id="direccion"
                                name="direccion"
                                rows="3"
                                placeholder="Dirección que aparecerá en la factura"></textarea>
                            <div class="helper-text">
                                Opcional.
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado del cliente</label>
                            <select id="estado" name="estado" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border rounded-3" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary-soft" id="btnGuardarCliente">
                        <span class="guardar-text">
                            <i data-feather="save" class="me-1"></i>
                            Guardar cliente
                        </span>
                        <span class="guardar-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Guardando...
                        </span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- JS del módulo Clientes -->
<script src="<?php echo BASE_URL; ?>Assets/Js/clientes.js"></script>
<?php require_once 'Views/Template/footer-admin.php'; ?>