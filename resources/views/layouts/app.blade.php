<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>@yield('titulo', 'Dashboard') · Nexo</title>
        <link rel="stylesheet" href="{{ asset('css/estilos.css') }}" />
        <script defer src="{{ asset('js/interfaz.js') }}"></script>
    </head>
    <body>
        <a class="saltar" href="#contenido">Saltar al contenido</a>
        <div class="aplicacion">
            <button type="button" class="fondo-menu" aria-label="Cerrar menú" hidden></button>
        <aside class="barra-lateral" id="menu-principal">
                <a class="marca" href="{{ route('dashboard') }}">
                    <span class="marca-icono">n</span>
                    nexo
                    <span>.</span>
                </a>
                <div class="negocio">
                    <strong>{{ auth()->user()->negocio->nombre }}</strong>
                    <small>Mi espacio de trabajo</small>
                </div>
                <p class="etiqueta-menu">GESTIÓN DEL CRM</p>
                <nav aria-label="Navegación principal">
                    <a @class(['actual' => request()->routeIs('dashboard')]) href="{{ route('dashboard') }}">Mi tablero</a>
                    <a @class(['actual' => request()->routeIs('clientes.*')]) href="{{ route('clientes.index') }}">Clientes</a>
                    <a @class(['actual' => request()->routeIs('contactos.*')]) href="{{ route('contactos.index') }}">Contactos</a>
                    <a @class(['actual' => request()->routeIs('ventas.*')]) href="{{ route('ventas.index') }}">Ventas y pedidos</a>
                    <a @class(['actual' => request()->routeIs('actividades.*')]) href="{{ route('actividades.index') }}">Actividades</a>
                    @if(auth()->user()->rol === 'admin')
                    <a @class(['actual' => request()->routeIs('usuarios.*')]) href="{{ route('usuarios.index') }}">Mi equipo</a>
                    @endif
                </nav>
                <div class="perfil">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ \App\Models\User::ROLES[auth()->user()->rol] }}</small>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="enlace" type="submit">Cerrar sesión</button>
                    </form>
                </div>
            </aside>
            <div class="area-trabajo">
                <header class="barra-superior">
                    <button
                        class="boton-menu" aria-label="Abrir menú"
                        type="button"
                        aria-controls="menu-principal"
                        aria-expanded="false"
                    >
                        ☰
                        <span>Menú</span>
                    </button>
                    <span>
                        Mi espacio /
                        @yield('titulo', 'Dashboard')
                    </span>
                    <span class="insignia">Base de datos conectada</span>
                </header>
                <main id="contenido" class="contenido" tabindex="-1">
                    <header class="encabezado">
                        <div>
                            <p class="sobre-titulo">NEXO / {{ auth()->user()->negocio->nombre }}</p>
                            <h1>@yield('titulo')</h1>
                            <p class="subtitulo">@yield('subtitulo')</p>
                        </div>
                        <div class="acciones">@yield('acciones')</div>
                    </header>
                    @if(session('exito'))
                    <div class="aviso exito" role="status">{{ session('exito') }}</div>
                    @endif
                    @include('partials.errores')
                    @yield('contenido')
                    <footer class="pie">
                        Nexo · CRM para emprendedores
                        <a class="enlace" href="{{ route('privacidad') }}">Aviso de privacidad</a>
                    </footer>
                </main>
            </div>
        </div>
    </body>
</html>
