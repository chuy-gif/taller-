NEXO CRM - ACTIVIDADES 10 Y 11

Aplicación de Dulce Rincón en PHP y Laravel con datos SQL persistentes.

MÓDULOS
- Registro, inicio y cierre de sesión.
- Usuarios y permisos de administración, mostrador, cocina y consulta.
- Clientes: registro, búsqueda, edición, ficha y archivo.
- Contactos: personas relacionadas con cada cliente.
- Ventas y pedidos: embudo, anticipos, entregas y cierre.
- Actividades: llamadas, notas, tareas y seguimiento.

PARA EJECUTAR
Lee LEEME-PRIMERO.txt. Contiene los pasos, requisitos y cuentas de ejemplo.
La base local es SQLite. Se incluye una configuración de ejemplo para PostgreSQL.

PARA REVISAR EL CÓDIGO
routes/web.php indica qué controlador usa cada dirección.
app/Http/Controllers procesa formularios y aplica validaciones y permisos.
app/Models representa las entidades guardadas en la base.
resources/views contiene las pantallas Blade separadas por módulo.
public/css/estilos.css tiene el diseño con reglas y propiedades por línea.
database/migrations contiene la estructura de las tablas.
tests/Feature/ModulosCrmTest.php verifica el comportamiento y la seguridad básica.

Las guías adicionales están en documentacion/ y son archivos de texto (.txt).
El proyecto incluye dependencias estándar de Laravel, instalables con Composer.
