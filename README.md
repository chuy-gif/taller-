# Nexo CRM para Dulce Rincón

Aplicación académica de las actividades 10 y 11: usuarios, clientes, contactos, ventas y actividades dentro de un dashboard. Está desarrollada con PHP y Laravel, vistas Blade, CSS y JavaScript. La [evidencia de las actividades 13, 14 y 16](documentacion/05-ACTIVIDADES-13-14-16.md) incluye las pruebas, el manual, los avisos y la evaluación.

**La base de datos local ya funciona con SQLite.** La configuración de ejemplo para PostgreSQL está incluida, pero la conexión a ese servidor todavía debe configurarse y probarse.

## 1. Programas que debes descargar

### Obligatorios para ejecutar el código del repositorio

- **PHP 8.4 o superior.** Probado con PHP 8.5.12. Descarga oficial para Windows: [PHP](https://www.php.net/downloads.php?os=windows). Para ejecutar desde la terminal, utiliza una compilación x64 NTS. Agrega la carpeta que contiene `php.exe` al PATH de Windows y abre una terminal nueva.
- **Composer 2.9 o superior.** Instala las dependencias de PHP definidas en `composer.json` y `composer.lock`. Descarga oficial: [Composer](https://getcomposer.org/download/). Necesita que PHP esté instalado y disponible.
- **Git**, para clonar y actualizar el repositorio. Descarga oficial: [Git para Windows](https://git-scm.com/install/windows).

En Windows, PHP requiere el [Microsoft Visual C++ Redistributable x64](https://aka.ms/vs/17/release/vc_redist.x64.exe). Si al ejecutar PHP aparece un error por `VCRUNTIME` o `MSVCP`, instala ese componente oficial.

También necesitas un navegador y conexión a internet para descargar el repositorio y las dependencias durante la primera instalación. Un editor de código es opcional para ejecutar la aplicación.

### Extensiones de PHP

Para usar la base local, habilita **PDO y `pdo_sqlite`**. Las dependencias fijadas en este proyecto también utilizan `ctype`, `dom`, `fileinfo`, `filter`, `hash`, `iconv`, `json`, `libxml`, `mbstring`, `openssl`, `pcre`, `session` y `tokenizer`. Varias ya vienen integradas en PHP.

Para las pruebas se necesitan además `phar` y `xmlwriter`. Se recomienda habilitar `curl` y `zip` para facilitar la descarga y extracción de paquetes con Composer.

Puedes revisar la instalación con:

```powershell
php -v
composer --version
git --version
php --ini
php -m
```

`php --ini` indica qué archivo `php.ini` está usando la terminal. En ese archivo activa las extensiones disponibles que hagan falta; por ejemplo, `extension=pdo_sqlite`. No todas las extensiones integradas requieren una línea en `php.ini`.

**Laravel y PHPUnit se descargan mediante Composer.** No necesitas instalarlos globalmente. Esta versión utiliza los archivos CSS y JavaScript directamente: no requiere instalar Node.js, npm, Vite, Apache ni XAMPP para arrancar con el servidor de desarrollo de PHP.

## 2. Descargar el proyecto desde GitHub

Desde una carpeta donde quieras guardar el proyecto:

```powershell
git clone https://github.com/chuy-gif/taller-.git
cd taller-
```

El repositorio es público. Para subir cambios necesitas permisos de escritura.

Si los módulos todavía están en la rama `actividades-10-11`, selecciónala:

```powershell
git switch actividades-10-11
```

Si los cambios ya se incorporaron a la rama principal, trabaja sobre esa rama. No hace falta volver a clonar si ya tienes una copia local.

## 3. Preparar e instalar

Ejecuta estos comandos dentro de la carpeta del repositorio, donde está el archivo `artisan`:

```powershell
php scripts/preparar.php
composer install
composer check-platform-reqs
php artisan migrate
php artisan db:seed
```

Qué hace cada paso:

1. `scripts/preparar.php` crea `.env` a partir del ejemplo si no existe, genera una clave de aplicación si falta, prepara las carpetas locales y crea el archivo de SQLite si todavía no existe. No borra una base existente.
2. `composer install` descarga las versiones fijadas en `composer.lock` y crea `vendor/`.
3. `composer check-platform-reqs` comprueba que tu PHP y sus extensiones cumplan los requisitos de los paquetes instalados.
4. `php artisan migrate` crea las tablas pendientes en la base configurada.
5. `php artisan db:seed` carga usuarios y registros ficticios para la demostración. Este paso es opcional y solo está permitido en entorno local o de pruebas.

La configuración inicial utiliza:

```dotenv
DB_CONNECTION=sqlite
```

Los datos se guardan en `database/database.sqlite`. No necesitas descargar un servidor SQLite aparte ni crear manualmente las tablas.

## 4. Encender y abrir el CRM

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Abre [http://127.0.0.1:8000](http://127.0.0.1:8000) en el navegador. Mantén abierta la terminal mientras uses el CRM. Para detener el servidor, presiona **Ctrl+C**.

Para volver a utilizarlo otro día, entra en la misma carpeta y ejecuta otra vez el comando de inicio. No necesitas repetir la instalación ni recrear la base: los datos se conservan.

## 5. Cuentas de demostración

Después de ejecutar el seeder, estas cuentas usan la contraseña **`NexoDemo2026!`**, definida como `DEMO_PASSWORD` en `.env.example`:

- `ana@dulcerincon.demo`: administración y gestión del equipo.
- `luis@dulcerincon.demo`: mostrador; clientes, contactos, ventas y actividades.
- `sofia@dulcerincon.demo`: cocina; consulta de pedidos asignados y registro de actividades.
- `elena@dulcerincon.demo`: consulta; lectura del dashboard y las fichas.

Si cambias `DEMO_PASSWORD` antes de cargar los ejemplos, utiliza la contraseña que hayas configurado. Ejecutar otra vez el seeder no reemplaza las contraseñas de cuentas que ya existían.

El enlace **Crear cuenta** registra un negocio nuevo y vacío. Para agregar a alguien al negocio actual, la administradora debe usar **Mi equipo > Agregar integrante**.

## 6. Módulos incluidos

- **Usuarios:** registro, inicio y cierre de sesión, roles y cuentas activas o desactivadas.
- **Clientes:** registro, búsqueda, edición, ficha, archivo y reactivación.
- **Contactos:** personas relacionadas con un cliente, medios de contacto y contacto principal.
- **Ventas y pedidos:** etapas Consulta, Cotización, Pedido y Entrega; montos, anticipos, responsable de cocina y cierre con historial.
- **Actividades:** llamadas, correos, notas, pruebas de sabor, avances de cocina y tareas pendientes o completadas.
- **Dashboard:** entregas de hoy, cotizaciones frías, monto en pedidos, clientes activos y accesos a los módulos.

## 7. Ejecutar las pruebas

Después de instalar las dependencias:

```powershell
php vendor/bin/phpunit
```

La suite incluye **22 pruebas**, que en la validación de esta entrega completaron **149 comprobaciones**. Comprueba acceso, registro, clientes, contactos, ventas, actividades, indicadores, avisos de privacidad, aislamiento entre negocios y controles de seguridad. Las pruebas utilizan una base SQLite temporal en memoria.

Para repetir la medición local del tablero con mil ventas adicionales:

```powershell
php vendor/bin/phpunit tests/Benchmark/DashboardBenchmarkTest.php
```

### Prueba de carga HTTP

Con los datos de demostración cargados y el servidor local encendido en `127.0.0.1:8000`, abre otra terminal en la carpeta del proyecto. Para esta prueba se requiere Node.js 22 o posterior.

```powershell
node scripts/prueba-http.mjs
```

El script inicia sesión una vez y envía 80 peticiones por nivel al tablero, clientes, ventas y actividades. Usa niveles de 1, 5 y 10 solicitudes simultáneas. La tabla muestra respuestas correctas, fallas y latencias por ruta. p50 es la mediana y p95 es el tiempo dentro del cual terminó aproximadamente el 95 % de las respuestas correctas. El tiempo cubre la descarga del HTML.

Para aumentar la carga del tablero en la demostración local y capturar la salida:

```powershell
node scripts/prueba-http.mjs --ruta /dashboard --niveles 1,5,10,20,40 --peticiones 120 | Tee-Object evidencia-carga.txt
```

Usa `--puerto` si el servidor cambió de puerto. Si cambiaste las credenciales de la cuenta de demostración, configura `NEXO_PRUEBA_CORREO` y `NEXO_PRUEBA_PASSWORD` en la terminal. El servidor de desarrollo de PHP en Windows atiende las solicitudes en un solo proceso. Lee los resultados como una medición de tu entorno local.

## 8. Usar PostgreSQL en una siguiente etapa

Solo necesitas estos programas si vas a cambiar a ese motor:

- Servidor PostgreSQL: [descarga oficial para Windows](https://www.postgresql.org/download/windows/).
- pgAdmin es opcional para administrar la base mediante una interfaz gráfica; está incluido en el instalador que enlaza PostgreSQL.
- Extensión PHP `pdo_pgsql` habilitada.

Pasos:

1. Crea una base nueva, por ejemplo `nexo_dev`, y un usuario con acceso a ella.
2. En `.env`, sustituye la configuración `DB_` de SQLite por los campos de `.env.postgresql.example` y escribe tus credenciales locales.
3. Ejecuta `php artisan config:clear` y `php artisan migrate`.
4. Prueba el registro, el login y las operaciones de los módulos contra PostgreSQL.

**La ejecución de esta entrega se validó con SQLite.** Cambiar `.env` no copia automáticamente los registros de SQLite a PostgreSQL; transferirlos sería una tarea adicional.

## 9. Organización del código

- `routes/web.php`: direcciones y acciones de la aplicación.
- `app/Http/Controllers/`: procesamiento de formularios, validaciones y permisos.
- `app/Models/`: entidades relacionadas con las tablas.
- `resources/views/`: pantallas Blade separadas por módulo.
- `public/css/estilos.css`: apariencia y adaptación a diferentes pantallas.
- `public/js/interfaz.js`: ayudas visuales del menú y los formularios.
- `database/migrations/`: estructura de las tablas.
- `database/seeders/DatabaseSeeder.php`: datos ficticios.
- `tests/Feature/ModulosCrmTest.php`: pruebas del CRM.
- `GUION-EXPOSICION.txt`: guion de presentación y preguntas de repaso.
- `documentacion/`: explicación de los requisitos, el código, la base y GitHub.

## 10. Archivos locales y seguridad básica

Las contraseñas se guardan con hash. Se utilizan sesiones, validación en el servidor, protección CSRF, límites de intentos y permisos por rol y negocio.

El `.gitignore` excluye `.env`, la base local, sesiones, registros de ejecución y las dependencias de `vendor/`. Sí se comparten `composer.json`, `composer.lock`, los ejemplos de configuración y las migraciones para que cada integrante pueda instalar su propia copia.

## 11. Despliegue público de prueba

El repositorio incluye `Dockerfile` y `render.yaml` para desplegar esta aplicación Laravel en [Render](https://render.com/) con PHP y PostgreSQL. Cloudflare Workers no ejecuta directamente esta aplicación PHP.

1. Sube la rama `actividades-10-11` a GitHub.
2. En Render, crea un **Blueprint** y conecta `chuy-gif/taller-`, rama `actividades-10-11`.
3. Revisa los servicios propuestos y confirma la creación. Render construirá la aplicación y la base de datos; el contenedor ejecuta las migraciones al iniciar.
4. Cuando el servicio indique **Live**, abre su URL pública `onrender.com` y crea tu cuenta desde **Crear cuenta**. Las cuentas ficticias del seeder están deshabilitadas fuera de local y pruebas.

La configuración inicial usa los planes gratuitos de Render, adecuados para una demostración, no para producción: el servicio puede tardar cerca de un minuto en despertar tras estar inactivo y la base de datos gratuita expira a los 30 días. No uses esta base para datos importantes; para mantener la aplicación o sus datos después, cambia a servicios con almacenamiento y respaldos persistentes. Los datos de la base local SQLite no se copian al crear el servicio.

Cuando Render solicite `APP_KEY`, genera una clave en una terminal local con `php -r "echo 'base64:'.base64_encode(random_bytes(32)), PHP_EOL;"` y pega el resultado solo en el formulario de Render. No subas `.env`, contraseñas ni claves al repositorio.

Esta aplicación no incluye recuperación de contraseña por correo, verificación de correo ni doble factor.

## 12. Problemas comunes

- **La terminal no reconoce PHP o Composer:** revisa su instalación y el PATH; cierra y vuelve a abrir PowerShell.
- **`could not find driver`:** habilita `pdo_sqlite` en el `php.ini` que muestra `php --ini` para la configuración local.
- **Falta una extensión:** revisa `php -m` y la salida de `composer check-platform-reqs`.
- **No existe `vendor/autoload.php`:** ejecuta `composer install` dentro del repositorio.
- **Falta una tabla o no aparecen cuentas de ejemplo:** revisa la base seleccionada en `.env`; ejecuta las migraciones y, si quieres los ejemplos, el seeder.
- **El puerto 8000 está ocupado:** detén el otro servidor o usa `php artisan serve --host=127.0.0.1 --port=8001` y abre el puerto 8001 en el navegador.
