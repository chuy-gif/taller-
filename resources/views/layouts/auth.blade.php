<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>@yield('titulo') · Nexo</title>
        <link rel="stylesheet" href="{{ asset('css/estilos.css') }}" />
        <script defer src="{{ asset('js/interfaz.js') }}"></script>
    </head>
    <body class="pagina-acceso">
        <main class="acceso">
            <section class="acceso-formulario">
                <a class="marca" href="{{ route('login') }}">
                    <span class="marca-icono">n</span>
                    nexo
                    <span>.</span>
                </a>
                <div class="acceso-centro">
                    <span class="insignia">CRM para emprendedores</span>
                    <h1>@yield('titulo')</h1>
                    <p class="subtitulo">@yield('subtitulo')</p>
                    @include('partials.errores')
                    @yield('contenido')
                </div>
                <small class="texto-suave">Nexo · Proyecto académico · <a class="enlace" href="{{ route('privacidad') }}">Aviso de privacidad</a></small>
            </section>
            <aside class="acceso-presentacion">
                <p class="sobre-titulo">DULCE RINCÓN</p>
                <h2>
                    Cada pedido
                    <br />
                    merece su lugar.
                </h2>
                <p>Clientes, contactos, ventas y seguimientos en un mismo espacio.</p>
                <div class="muestra-agenda">
                    <strong>Una mañana en la pastelería</strong>
                    <div>
                        <span>10:00</span>
                        <p>
                            Prueba de sabor
                            <small>Familia Ruiz · Red velvet</small>
                        </p>
                    </div>
                    <div>
                        <span>13:00</span>
                        <p>
                            Preparar entrega
                            <small>Mariana López · Tres leches</small>
                        </p>
                    </div>
                    <div>
                        <span>18:00</span>
                        <p>
                            Recoger en tienda
                            <small>Ana Martínez · Chocolate</small>
                        </p>
                    </div>
                </div>
            </aside>
        </main>
    </body>
</html>
