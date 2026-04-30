# Sistema de Facturación

Sistema web administrativo para la gestión de facturación, clientes, usuarios, roles, permisos, folios, reportes, configuración empresarial, bitácora y monitoreo de errores.

El proyecto fue desarrollado con arquitectura MVC en PHP, base de datos MySQL, Bootstrap 5 y JavaScript vanilla, buscando una interfaz limpia, profesional y fácil de utilizar para operación administrativa.

---

## Características principales

### Dashboard administrativo

- Vista principal del sistema.
- Acceso rápido a los módulos administrativos.
- Diseño responsive basado en Bootstrap 5.
- Interfaz visual moderna con tarjetas, KPIs y navegación lateral.

### Clientes

- Registro de clientes.
- Edición de información del cliente.
- Activación y desactivación de clientes.
- Validación de código de cliente duplicado.
- Filtros por búsqueda y estado.

### Facturas

- Creación de facturas con partidas.
- Cálculo automático de subtotal, impuestos, cargos adicionales y total.
- Edición de facturas.
- Cancelación de facturas.
- Descarga de factura en PDF.
- Relación con clientes.
- Selección de serie y folio desde el módulo de Folios y series.
- Generación segura del folio desde backend.

### Folios y series

- Administración de series de facturación.
- Control del último número utilizado por serie.
- Activación y desactivación de series.
- Validación para evitar duplicidad de folios.
- Protección para no reducir folios por debajo de facturas ya emitidas.
- Preparado para concurrencia segura mediante bloqueo de fila en backend.

### Usuarios

- Registro de usuarios.
- Edición de datos de usuario.
- Cambio de rol.
- Cambio opcional de contraseña desde administración.
- Activación y desactivación de usuarios.
- Protección para evitar que un usuario se desactive a sí mismo.

### Roles y permisos

- Creación y administración de roles.
- Asignación de permisos por módulo.
- Permisos disponibles:
  - Ver
  - Crear
  - Editar
  - Eliminar
- Validación visual en menú y botones.
- Validación real desde backend mediante helpers de permisos.
- Soporte para rol administrador principal.

### Reportes

- Consulta de información administrativa.
- Filtros para análisis de datos.
- Exportación de información según el módulo implementado.

### Bitácora / Auditoría

- Registro de eventos importantes del sistema.
- Seguimiento de inicios de sesión.
- Registro de altas, cambios, desactivaciones y acciones administrativas.
- Filtros por usuario, módulo, acción y fecha.

### Reporte de errores

- Módulo para consultar errores registrados en el sistema.
- Filtros por tipo, nivel, estado, módulo y rango de fechas.
- Detalle técnico del error.
- Cambio de estado: pendiente, revisado o resuelto.
- Notas internas de revisión.
- Reporte manual de errores por parte del usuario desde el menú lateral.

### Mi perfil

- Consulta de datos del usuario actual.
- Edición de información básica.
- Cambio de contraseña.
- Visualización de rol y estado de cuenta.

### Configuración

- Datos generales de la empresa.
- Logo empresarial.
- Datos de contacto.
- Color principal.
- Texto de pie de página para documentos.

### Página de error 404

- Vista personalizada para rutas inexistentes.
- Botón para regresar al dashboard.
- Diseño consistente con el sistema.

---

## Tecnologías utilizadas

- PHP
- MySQL
- JavaScript vanilla
- Bootstrap 5.3
- SweetAlert2
- Feather Icons
- Chart.js
- HTML5
- CSS3
- XAMPP / Apache

---

## Arquitectura del proyecto

El sistema utiliza una estructura basada en MVC:

```text
Controllers/
Models/
Views/
Assets/
Config/
Uploads/
