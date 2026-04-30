<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Sistema de Credenciales</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.29.2/dist/feather.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 
    <style>
        .avatar-preview {
            width: 140px;
            height: 140px;
            border-radius: 12px;
            object-fit: cover;
            background: #f3f4f6;
            border: 1px dashed #d0d7de;
        }

        .required::after {
            content: " *";
            color: #dc3545;
            font-weight: 600;
        }
    </style>
</head>

<body class="bg-light ">

    <div class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="mb-0"><i data-feather="aperture" class="me-2"></i>Sistema de Credenciales</h3>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="credTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-registro" data-bs-toggle="tab" data-bs-target="#pane-registro"
                    type="button" role="tab" aria-controls="pane-registro" aria-selected="true">
                    <i data-feather="user-plus" class="me-1"></i> Registro de usuario
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-listado" data-bs-toggle="tab" data-bs-target="#pane-listado"
                    type="button" role="tab" aria-controls="pane-listado" aria-selected="false">
                    <i data-feather="users" class="me-1"></i> Listado 
                </button>
            </li>
        </ul>

        <div class="tab-content bg-white border border-top-0 p-3 rounded-bottom shadow-sm " id="credTabsContent">
            <!-- ===================== TAB: REGISTRO ===================== -->
            <div class="tab-pane fade show active " id="pane-registro" role="tabpanel" aria-labelledby="tab-registro"
                tabindex="0">

                <form id="formRegistroUsuarioCred" class="needs-validation" novalidate autocomplete="off">
                    <!-- Encabezado -->
                    <div class="mb-3">
                        <p class="text-secondary mb-1">
                            Completa la información para generar la credencial del empleado. Los campos marcados con
                            <span class="text-danger">*</span> son obligatorios.
                        </p>
                    </div>

                    <div class="row g-3">
                        <!-- Nombre(s) -->
                        <div class="col-md-4">
                            <label for="nombre" class="form-label required">Nombre(s)</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required minlength="2"
                                maxlength="80" placeholder="Ej. Juan Carlos">
                            <div class="invalid-feedback">Ingresa el/los nombre(s).</div>
                        </div>

                        <!-- Apellido paterno -->
                        <div class="col-md-4">
                            <label for="apellido_paterno" class="form-label required">Apellido paterno</label>
                            <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno"
                                required minlength="2" maxlength="80" placeholder="Ej. Pérez">
                            <div class="invalid-feedback">Ingresa el apellido paterno.</div>
                        </div>

                        <!-- Apellido materno -->
                        <div class="col-md-4">
                            <label for="apellido_materno" class="form-label">Apellido materno</label>
                            <input type="text" class="form-control" id="apellido_materno" name="apellido_materno"
                                maxlength="80" placeholder="Opcional">
                        </div>

                        <!-- Fecha nacimiento -->
                        <div class="col-md-3">
                            <label for="fecha_nacimiento" class="form-label required">Fecha de nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                required>
                            <div class="invalid-feedback">Selecciona la fecha de nacimiento.</div>
                        </div>
                        <!-- Departamento -->
                        <div class="col-md-3">
                            <label for="departamento" class="form-label required">Departamento</label>
                            <input type="text" class="form-control" id="departamento" name="departamento" required>
                        </div>
                        <!-- Puesto -->
                        <div class="col-md-3">
                            <label for="puesto" class="form-label required">Puesto</label>
                            <input type="text" class="form-control" id="puesto" name="puesto" required>
                        </div>

                        <!-- Número de empleado -->
                        <div class="col-md-3">
                            <label for="numero_empleado" class="form-label required">Número de empleado</label>
                            <input type="text" inputmode="numeric" pattern="^\d{1,10}$" maxlength="10"
                                class="form-control" id="numero_empleado" name="numero_empleado" required
                                placeholder="Ej. 10234" aria-describedby="helpNoEmpleado">
                            <div id="helpNoEmpleado" class="form-text">Hasta 10 dígitos.</div>
                            <div class="invalid-feedback">Ingresa un número de empleado válido.</div>
                        </div>

                        <!-- CURP -->
                        <div class="col-md-6">
                            <label for="curp" class="form-label required">CURP</label>
                            <input type="text" class="form-control text-uppercase" id="curp" name="curp" required
                                maxlength="18"
                                pattern="^[A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$"
                                placeholder="Ej. GOCJ900101HBCRRN09" aria-describedby="helpCurp">
                            <div id="helpCurp" class="form-text">18 caracteres. Se valida estructura oficial.</div>
                            <div class="invalid-feedback">Ingresa una CURP válida (18 caracteres).</div>
                        </div>

                        <!-- RFC -->
                        <div class="col-md-6">
                            <label for="rfc" class="form-label required">RFC</label>
                            <input type="text" class="form-control text-uppercase" id="rfc" name="rfc" required
                                maxlength="13" pattern="^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$" placeholder="Ej. GOCJ900101ABC"
                                aria-describedby="helpRfc">
                            <div id="helpRfc" class="form-text">Formato persona física: 13 caracteres.</div>
                            <div class="invalid-feedback">Ingresa un RFC válido (13 caracteres).</div>
                        </div>

                        <!-- Teléfono -->
                        <div class="col-md-4">
                            <label for="telefono" class="form-label required">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" required
                                inputmode="tel" pattern="^\d{10}$" maxlength="10" placeholder="10 dígitos">
                            <div class="invalid-feedback">Ingresa un teléfono de 10 dígitos.</div>
                        </div>

                        <!-- Fotografía -->
                        <div class="col-md-8">
                            <label class="form-label required">Fotografía</label>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <img id="previewFoto" class="avatar-preview" alt="Previsualización">
                                <div class="flex-grow-1">
                                    <input class="form-control" type="file" id="foto" name="foto" accept="image/*"
                                        required>
                                    <div class="form-text">
                                        Sube una foto tipo credencial (frontal). Tamaño máx. 2&nbsp;MB. Formatos:
                                        JPG/PNG.
                                    </div>
                                    <div class="invalid-feedback">La fotografía es obligatoria.</div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /row -->

                    <hr class="my-4">

                    <!-- Botones -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="save" class="me-1"></i> Registrar
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            <i data-feather="rotate-ccw" class="me-1"></i> Limpiar
                        </button>
                    </div>

                </form>
            </div>

            <!-- ===================== TAB: LISTADO (placeholder) ===================== -->
            <div class="tab-pane fade" id="pane-listado" role="tabpanel" aria-labelledby="tab-listado" tabindex="0">
                
 
                    <?php include_once 'empleados.php'; ?>
 
               
            </div>
            
        </div>
    </div>
<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
    const base_url ='<?php echo BASE_URL; ?>';
</script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="<?php echo BASE_URL; ?>assets/js/validaciones.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/registrarEmpleado.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/listarEmpleados.js"></script>

</body>

</html>