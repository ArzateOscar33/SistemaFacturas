<script>
    if (window.feather) {
        feather.replace();
    }
</script>
<script src="<?php echo BASE_URL; ?>Assets/Js/reporte_error_global.js"></script>
</div>
<!-- MODAL REPORTAR ERROR -->
<div class="modal fade" id="modalReportarError" tabindex="-1" aria-labelledby="modalReportarErrorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-admin">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="modalReportarErrorLabel">Reportar error</h5>
                    <p class="modal-subtitle mb-0">
                        Describe el problema que encontraste para que pueda revisarse.
                    </p>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formReportarError" autocomplete="off">
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="reporteModulo" class="form-label">Módulo donde ocurrió</label>
                        <input type="text"
                            class="form-control"
                            id="reporteModulo"
                            name="modulo"
                            placeholder="Ej. Facturas, Clientes, Reportes">
                    </div>

                    <div class="mb-3">
                        <label for="reporteMensaje" class="form-label">
                            ¿Qué ocurrió? <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control"
                            id="reporteMensaje"
                            name="mensaje"
                            rows="5"
                            placeholder="Describe qué estabas haciendo, qué botón presionaste y qué error apareció."
                            required></textarea>
                    </div>

                    <div class="security-note">
                        <i data-feather="shield" class="me-1"></i>
                        No escribas contraseñas, datos bancarios ni información sensible.
                        El sistema adjuntará automáticamente la URL actual, tu usuario y datos básicos del navegador.
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary-soft" id="btnEnviarReporteError">
                        <span class="btn-text">
                            <i data-feather="send" class="me-1"></i>
                            Enviar reporte
                        </span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Enviando...
                        </span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
</body>

</html>