 <?php require_once 'Views/Template/header-admin.php'; ?>






 <main class="content">

     <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
         <div>
             <h1 class="page-title">Dashboard</h1>
             <p class="page-subtitle">Resumen general del sistema de facturación.</p>
         </div>

         <div class="user-pill">
             <i data-feather="user" class="me-1"></i>
             <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
         </div>
     </div>

     <div class="row g-4 mb-4">

         <div class="col-xl-3 col-md-6">
             <div class="kpi-card">
                 <div class="d-flex justify-content-between align-items-start">
                     <div>
                         <div class="kpi-title">Facturas emitidas</div>
                         <p class="kpi-value" id="kpiFacturasEmitidas">0</p>
                         <div class="kpi-note">Total histórico</div>
                     </div>
                     <div class="kpi-icon">
                         <i data-feather="file-text"></i>
                     </div>
                 </div>
             </div>
         </div>

         <div class="col-xl-3 col-md-6">
             <div class="kpi-card">
                 <div class="d-flex justify-content-between align-items-start">
                     <div>
                         <div class="kpi-title">Clientes activos</div>
                         <p class="kpi-value" id="kpiClientes">0</p>
                         <div class="kpi-note">Clientes registrados</div>
                     </div>
                     <div class="kpi-icon">
                         <i data-feather="users"></i>
                     </div>
                 </div>
             </div>
         </div>

         <div class="col-xl-3 col-md-6">
             <div class="kpi-card">
                 <div class="d-flex justify-content-between align-items-start">
                     <div>
                         <div class="kpi-title">Total facturado</div>
                         <p class="kpi-value" id="kpiTotalFacturado">$0.00</p>
                         <div class="kpi-note">Facturas emitidas</div>
                     </div>
                     <div class="kpi-icon">
                         <i data-feather="dollar-sign"></i>
                     </div>
                 </div>
             </div>
         </div>

         <div class="col-xl-3 col-md-6">
             <div class="kpi-card">
                 <div class="d-flex justify-content-between align-items-start">
                     <div>
                         <div class="kpi-title">Facturas canceladas</div>
                         <p class="kpi-value" id="kpiCanceladas">0</p>
                         <div class="kpi-note">Control administrativo</div>
                     </div>
                     <div class="kpi-icon">
                         <i data-feather="x-circle"></i>
                     </div>
                 </div>
             </div>
         </div>

     </div>

     <div class="row g-4 mb-4">

         <div class="col-lg-8">
             <div class="chart-card">
                 <h5 class="chart-title">Facturación mensual</h5>
                 <p class="chart-subtitle">Importe total generado por mes.</p>
                 <canvas id="chartFacturacionMensual" height="120"></canvas>
             </div>
         </div>

         <div class="col-lg-4">
             <div class="chart-card">
                 <h5 class="chart-title">Estado de facturas</h5>
                 <p class="chart-subtitle">Distribución por estado.</p>
                 <canvas id="chartEstadoFacturas" height="220"></canvas>
             </div>
         </div>

     </div>

     <div class="row g-4">

         <div class="col-md-4">
             <a href="<?php echo BASE_URL; ?>admin/facturas/" class="quick-action">
                 <div class="quick-icon">
                     <i data-feather="plus-circle"></i>
                 </div>
                 <div>
                     <strong>Nueva factura</strong>
                     <div class="small text-secondary">Crear factura desde formulario</div>
                 </div>
             </a>
         </div>

         <div class="col-md-4">
             <a href="<?php echo BASE_URL; ?>admin/clientes" class="quick-action">
                 <div class="quick-icon">
                     <i data-feather="user-plus"></i>
                 </div>
                 <div>
                     <strong>Registrar cliente</strong>
                     <div class="small text-secondary">Administrar catálogo de clientes</div>
                 </div>
             </a>
         </div>

         <div class="col-md-4">
             <a href="<?php echo BASE_URL; ?>admin/usuarios" class="quick-action">
                 <div class="quick-icon">
                     <i data-feather="shield"></i>
                 </div>
                 <div>
                     <strong>Control de usuarios</strong>
                     <div class="small text-secondary">Roles y accesos del sistema</div>
                 </div>
             </a>
         </div>

     </div>

 </main>
 <!-- JS del módulo admin -->
 <script src="<?php echo BASE_URL; ?>Assets/Js/admin.js"></script>
 <?php require_once 'Views/Template/footer-admin.php'; ?>