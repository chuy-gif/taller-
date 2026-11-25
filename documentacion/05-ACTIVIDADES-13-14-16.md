# Nexo CRM para Dulce Rincón

**Evidencia de actividades 13, 14 y 16**

Jesús Antonio Cuellar Yosida · Ingeniería en Desarrollo de Software · Tecmilenio

Nexo reúne clientes, contactos, pedidos y seguimientos de una pastelería en una aplicación Laravel. Este avance corrige dos fallas que aparecían al editar ventas o completar actividades, aligera la carga del tablero e integra los avisos de privacidad de la demostración.

## Actividad 13 Pruebas y optimización

Se ejecutó la suite de PHPUnit con PHP 8.5.12 y SQLite en memoria. La versión de las actividades 10 y 11 pasó 18 pruebas con 122 comprobaciones. Se agregaron dos pruebas de regresión que fallaron en esa versión y pasaron después de aplicar las correcciones. La suite final pasó **22 pruebas con 149 comprobaciones**.

### Pruebas funcionales

| Flujo | Comportamiento comprobado | Resultado |
| --- | --- | --- |
| Acceso y roles | Registro en un negocio propio, inicio y cierre de sesión, cuenta desactivada y perfil de consulta sin permiso de escritura. | Aprobado |
| Clientes y contactos | Captura, edición y archivo de clientes, contacto principal y retiro de contactos. | Aprobado |
| Ventas y pedidos | Anticipo dentro del monto, centavos conservados, cierre con historial y edición con cliente archivado. | Aprobado |
| Actividades | El pedido corresponde al cliente, una tarea pendiente conserva la fecha de seguimiento y repetir el cierre conserva la fecha original. | Aprobado |
| Tablero | Los indicadores mantienen sus totales al paginar entregas y cotizaciones por separado. | Aprobado |
| Privacidad y acceso | Avisos públicos desde el registro, restricciones entre negocios y rechazo de una petición de escritura sin token CSRF. | Aprobado |

### Corrección de errores

| Falla reproducida | Corrección aplicada |
| --- | --- |
| Al editar una venta abierta, si el cliente estaba archivado, el sistema rechazaba el guardado. | El formulario muestra el cliente original en modo de lectura. Al editar se conserva su asociación y solo una venta nueva exige un cliente activo. |
| Repetir la acción de completar una actividad cambiaba otra vez la fecha del último seguimiento. | La fecha se actualiza cuando la actividad pasa de pendiente a completada. Si ya estaba completada, el sistema lo informa y conserva la fecha. |

### Rendimiento y usabilidad

El tablero carga hasta 10 entregas y 10 cotizaciones por página. Cada lista cuenta con navegación independiente y conserva el total del indicador. La fecha de entrega se consulta mediante un rango que admite los valores de fecha guardados. En el menú móvil, al abrirlo se enfoca el primer enlace y Escape devuelve el foco al botón.

Para medir el cambio se agregaron 1,000 ventas ficticias al conjunto de demostración, 500 entregas de hoy y 500 cotizaciones sin seguimiento. Se hicieron 3 solicitudes de calentamiento y después se tomaron 15 mediciones por versión en el mismo entorno local.

| Medida | Antes | Después |
| --- | ---: | ---: |
| Mediana de respuesta del tablero | 42.95 ms | 6.09 ms |
| Tamaño del HTML de la primera página | 371,142 B | 15,167 B |
| Filas renderizadas por lista | 502 | 10 |
| Total visible por indicador | 502 | 502 |

El HTML fue aproximadamente 96 % más pequeño en este escenario. La medición corresponde a solicitudes locales del servidor con SQLite y datos ficticios.

**Cómo repetir las pruebas.** Desde la carpeta donde está `artisan`, con las dependencias instaladas, ejecuta `php vendor/bin/phpunit`. Para la medición del tablero usa `php vendor/bin/phpunit tests/Benchmark/DashboardBenchmarkTest.php`. El archivo `tests/Feature/ModulosCrmTest.php` contiene los casos funcionales.

## Actividad 14 Documentación del sistema

### Manual de usuario

**Inicio.** Sigue la instalación del `README.md` y abre `http://127.0.0.1:8000`. Si cargaste los datos de muestra, entra con `ana@dulcerincon.demo` y la contraseña de demostración indicada en el README. Crear cuenta abre un negocio nuevo. Si ya perteneces a un equipo, la administradora puede agregarte desde **Mi equipo**.

| Perfil | Acciones habituales |
| --- | --- |
| Administradora | Gestiona clientes, contactos, ventas, actividades, integrantes y permisos. |
| Mostrador | Registra clientes, contactos, pedidos y actividades. Actualiza etapas y cierra ventas. |
| Cocina | Consulta los pedidos asignados y registra avances relacionados con ellos. |
| Consulta | Revisa el tablero y las fichas disponibles en su negocio. |

1. **Mi tablero.** Revisa las entregas de hoy, las cotizaciones que llevan más de siete días sin actividad ni anticipo y las tareas pendientes. Usa **Siguiente** para ver más entregas o cotizaciones. Desde cada resultado puedes abrir su ficha.
2. **Clientes.** Abre **Nuevo cliente**, captura el nombre y el canal de contacto y guarda. En la ficha puedes editar o archivar al cliente conservando su historial. Usa datos ficticios en el campo de alergias e indicaciones.
3. **Contactos.** Agrega un contacto, selecciona el cliente activo al que pertenece e indica al menos un correo o teléfono. Marca **Contacto principal** si será la persona que atienda el pedido.
4. **Ventas y pedidos.** Selecciona **Nueva venta**, elige un cliente activo y captura producto, evento, porciones, fecha, hora, monto y anticipo. En la ficha, usa **Guardar etapa** para moverla entre **Consulta**, **Cotización**, **Pedido** y **Entrega**. Para cerrarla como ganada, pásala a **Entrega**. Para cerrarla como perdida, escribe el motivo. Al confirmar la entrega, el CRM registra el cierre y muestra el saldo pendiente.
5. **Actividades.** Registra una llamada, correo, nota o tarea desde **Nueva actividad** o **Seguimiento** en la ficha de la venta. Selecciona el mismo cliente para asociar un pedido. Marca la actividad como completada cuando la hayas realizado.
6. **Mi equipo.** Con el perfil de administradora, selecciona **Agregar integrante**. Después puedes cambiar el rol o el estado del usuario con **Guardar permisos**. Al terminar, selecciona **Cerrar sesión**.

### Herramientas de colaboración

El repositorio `chuy-gif/taller-` reúne el código y la documentación. La rama `actividades-10-11` sirvió como base. El avance se trabajó en una rama local llamada `actividades-13-14-16`, con Git para comparar cambios y PHPUnit para comprobarlos.

| Herramienta | Uso en el equipo | Evidencia que se puede revisar |
| --- | --- | --- |
| Git | Trabajar en una rama por actividad y revisar las diferencias antes del commit. | Rama, lista de archivos y diferencias. |
| GitHub Issues | Registrar fallas con pasos, resultado esperado y resultado observado. | Incidencia y referencia al cambio que la corrige cuando se publique. |
| GitHub Pull Requests | Pedir a otro integrante que reproduzca los casos, revise el cambio y deje sus observaciones. | Comentarios y resultado compartido de PHPUnit cuando se haga la revisión. |

Para la revisión, adjunta resultados con datos ficticios y cita el caso que comprobaste. Deja fuera `.env`, `vendor/`, las bases locales y cualquier registro de clientes.

### Avisos de privacidad

**Aviso simplificado de la demostración.** Nexo CRM para Dulce Rincón usa datos ficticios para mostrar la gestión de cuentas, clientes, contactos, pedidos y seguimientos. En una operación real se tratarían datos de identificación, contacto y pedidos. Una alergia asociada a una persona puede revelar información de salud. En la demostración se puede omitir información opcional y usar ejemplos ficticios. En operación real, el responsable debe identificarse, indicar su domicilio y ofrecer un canal para limitar el uso o divulgación. El aviso integral se consulta en `/privacidad`.

**Aviso integral para completar antes de una operación real.** La identidad o razón social, el domicilio del responsable y el canal de privacidad deben corresponder al negocio que opere el CRM. Los datos previstos incluyen nombre, correo, teléfono, cuenta y rol, cotizaciones, pedidos, anticipos y entregas. Se usan para administrar cuentas, atender pedidos y coordinar el trabajo. Si se justifica tratar alergias vinculadas a una persona, se debe documentar la finalidad y obtener consentimiento expreso y por escrito cuando corresponda.

El canal de privacidad debe admitir solicitudes de acceso, rectificación, cancelación y oposición (ARCO), revocación del consentimiento y limitación de uso o divulgación. La solicitud debe indicar qué derecho se desea ejercer, los datos involucrados y un medio para recibir respuesta. El responsable debe verificar la identidad por un medio seguro. La demostración funciona localmente. En una operación real se deben especificar las transferencias aplicables, el plazo de conservación y la forma de avisar sobre cambios, además de publicar la versión vigente en `/privacidad`.

**Referencia.** Ley Federal de Protección de Datos Personales en Posesión de los Particulares, artículos 8, 15 y 16. [Texto vigente de la Cámara de Diputados](https://www.diputados.gob.mx/LeyesBiblio/pdf/LFPDPPP.pdf).

## Actividad 16 Evaluación y retroalimentación

### Autoevaluación propuesta

Escala de 1 a 5, donde 5 indica que se alcanzó el criterio con evidencia verificable.

| Criterio | Puntaje | Evidencia y oportunidad de mejora |
| --- | ---: | --- |
| Pruebas y correcciones | 5/5 | Las dos fallas se reprodujeron y la suite final pasó. |
| Rendimiento y facilidad de uso | 4/5 | Bajó la carga del tablero. Falta repetir la medición con usuarios concurrentes. |
| Documentación | 4/5 | El manual, la guía de colaboración y ambos avisos están disponibles. El negocio debe definir sus datos de contacto y operación real. |
| Seguimiento del trabajo | 4/5 | Los cambios se pueden revisar en Git. La revisión por otro integrante queda por registrar. |

**Resultado propuesto.** 17 de 20 puntos, equivalente al 85 %. La mejora principal fue pasar de probar solamente el flujo normal a cubrir acciones repetidas y cambios de estado que ocurren durante el uso diario.

### Coevaluación por un integrante

**Nombre del evaluador:** _____________________  **Fecha:** _____________________

| Criterio | Puntaje de 1 a 5 | Evidencia o comentario del evaluador |
| --- | --- | --- |
| Comunicación y seguimiento | ____ | ______________________________ |
| Aporte y calidad de los cambios | ____ | ______________________________ |
| Apoyo en la verificación de las pruebas | ____ | ______________________________ |
| Claridad del manual para repetir un flujo | ____ | ______________________________ |

**Total:** ____ / 20. **Sugerencia prioritaria:** ____________________________________

### Análisis de productividad

En el avance se verificaron dos correcciones, se añadieron cuatro pruebas funcionales y se entregó una medición reproducible del tablero. La suite pasó de 18 a 22 pruebas, un aumento del 22 % en el número de casos. Se puede ver el avance en los resultados y los archivos modificados. La productividad por integrante requiere registrar el tiempo y la revisión en cada tarea.

Para la siguiente entrega, registra por incidencia la fecha de apertura, fecha de aprobación, horas dedicadas y número de reaperturas. Con esos datos el equipo podrá comparar el tiempo de ciclo y saber qué correcciones requirieron retrabajo.

### Propuestas de mejora

| Prioridad | Propuesta | Criterio para verificarla |
| --- | --- | --- |
| Alta | Ejecutar PHPUnit automáticamente en los Pull Requests. | El equipo revisa el resultado de las pruebas desde el PR antes de integrar los cambios. |
| Alta | Verificar el CRM con PostgreSQL si se requiere en la siguiente fase. | Las migraciones y los flujos de clientes, ventas y actividades pasan con ese motor. |
| Media | Probar el flujo con otro integrante desde un teléfono o con teclado. | Completa un pedido y registra los pasos que causaron confusión. |
| Media | Definir el canal de privacidad y el tratamiento de alergias antes del uso real. | El aviso identifica al responsable y el equipo documenta el proceso de consentimiento y las solicitudes ARCO. |
