import { parseArgs } from 'node:util';
import { performance } from 'node:perf_hooks';

const configuracion = {
    puerto: { type: 'string', default: '8000' },
    niveles: { type: 'string', default: '1,5,10' },
    peticiones: { type: 'string', default: '80' },
    espera: { type: 'string', default: '10000' },
    ruta: { type: 'string', multiple: true, default: ['/dashboard', '/clientes', '/ventas', '/actividades'] },
    ayuda: { type: 'boolean', short: 'h' },
};

function entero(valor, nombre) {
    const numero = Number(valor);
    if (!/^\d+$/.test(valor) || !Number.isSafeInteger(numero) || numero < 1) {
        throw new Error(`${nombre} debe ser un entero positivo.`);
    }
    return numero;
}

function guardarCookies(cookies, respuesta) {
    for (const cookie of respuesta.headers.getSetCookie()) {
        const [par] = cookie.split(';');
        const separador = par.indexOf('=');
        cookies.set(par.slice(0, separador), par);
    }
}

function cookieDeSesion(cookies) {
    return [...cookies.values()].join('; ');
}

async function iniciarSesion(origen, espera) {
    const cookies = new Map();
    const entrada = await fetch(`${origen}/login`, { redirect: 'manual', signal: AbortSignal.timeout(espera) });
    if (entrada.status !== 200) throw new Error(`GET /login: HTTP ${entrada.status}.`);
    guardarCookies(cookies, entrada);
    const html = await entrada.text();
    const token = html.match(/name="_token"\s+value="([^"]+)"/)?.[1];
    if (!token) throw new Error('Falta el token CSRF en el formulario de Nexo.');

    const autenticacion = await fetch(`${origen}/login`, {
        method: 'POST', redirect: 'manual', signal: AbortSignal.timeout(espera),
        headers: { cookie: cookieDeSesion(cookies), accept: 'text/html' },
        body: new URLSearchParams({
            _token: token,
            email: process.env.NEXO_PRUEBA_CORREO ?? 'ana@dulcerincon.demo',
            password: process.env.NEXO_PRUEBA_PASSWORD ?? 'NexoDemo2026!',
        }),
    });
    guardarCookies(cookies, autenticacion);
    const destino = autenticacion.headers.get('location');
    await autenticacion.arrayBuffer();
    if (autenticacion.status !== 302 || !destino || new URL(destino, origen).pathname !== '/dashboard') {
        throw new Error(`Inicio de sesion: HTTP ${autenticacion.status}. Revisa la cuenta demo y las sesiones.`);
    }
    return cookieDeSesion(cookies);
}

async function medir(origen, ruta, cookie, espera) {
    const inicio = performance.now();
    try {
        const respuesta = await fetch(`${origen}${ruta}`, {
            headers: { cookie, accept: 'text/html' },
            redirect: 'manual', signal: AbortSignal.timeout(espera),
        });
        const tipo = respuesta.headers.get('content-type');
        await respuesta.arrayBuffer();
        const error = respuesta.status === 200
            ? (tipo?.includes('text/html') ? null : 'La respuesta no es HTML')
            : `HTTP ${respuesta.status}`;
        return { ruta, tiempo: performance.now() - inicio, error };
    } catch (error) {
        const motivo = error.name === 'TimeoutError' ? 'Tiempo de espera agotado' : (error.cause?.code ?? error.message);
        return { ruta, tiempo: performance.now() - inicio, error: motivo };
    }
}

async function ejecutarNivel(origen, rutas, cookie, espera, simultaneas, total) {
    let siguiente = 0;
    const resultados = [];
    const inicio = performance.now();

    async function enviarPeticiones() {
        while (siguiente < total) {
            const ruta = rutas[siguiente % rutas.length];
            siguiente += 1;
            resultados.push(await medir(origen, ruta, cookie, espera));
        }
    }

    await Promise.all(Array.from({ length: Math.min(total, simultaneas) }, enviarPeticiones));
    return { resultados, segundos: (performance.now() - inicio) / 1000 };
}

function resumir(ruta, resultados) {
    const tiempos = resultados.filter(r => !r.error).map(r => r.tiempo).sort((a, b) => a - b);
    function percentil(p) {
        if (!tiempos.length) return '-';
        const posicion = (tiempos.length - 1) * p;
        const inferior = Math.floor(posicion);
        const superior = Math.ceil(posicion);
        return (tiempos[inferior] + (tiempos[superior] - tiempos[inferior]) * (posicion - inferior)).toFixed(2);
    }
    return {
        Ruta: ruta, Peticiones: resultados.length, OK: tiempos.length, Errores: resultados.length - tiempos.length,
        'p50 ms': percentil(0.5), 'p95 ms': percentil(0.95), 'Max ms': percentil(1),
    };
}

function imprimirTabla(filas) {
    const encabezados = Object.keys(filas[0]);
    const anchos = encabezados.map(columna =>
        Math.max(columna.length, ...filas.map(fila => String(fila[columna]).length)),
    );
    const formatear = valores => valores.map((valor, indice) =>
        String(valor).padEnd(anchos[indice]),
    ).join(' | ').trimEnd();

    console.log(formatear(encabezados));
    for (const fila of filas) console.log(formatear(encabezados.map(columna => fila[columna])));
}

async function main() {
    const { values: opciones } = parseArgs({ options: configuracion });
    if (opciones.ayuda) {
        console.log('node scripts/prueba-http.mjs [--puerto 8000] [--niveles 1,5,10] [--peticiones 80] [--espera 10000] [--ruta /dashboard]');
        console.log('--peticiones es el total por nivel. --espera se expresa en milisegundos. Puedes repetir --ruta.');
        return;
    }

    const puerto = entero(opciones.puerto, '--puerto');
    if (puerto > 65535) throw new Error('--puerto debe estar entre 1 y 65535.');
    const niveles = opciones.niveles.split(',').map(valor => entero(valor.trim(), '--niveles'));
    const total = entero(opciones.peticiones, '--peticiones');
    const espera = entero(opciones.espera, '--espera');
    const rutas = [...new Set(opciones.ruta)];
    if (rutas.some(ruta => !/^\/[a-z0-9][a-z0-9/_-]*(?:\?[^\s#]*)?$/i.test(ruta))) {
        throw new Error('Cada --ruta debe ser una ruta local, por ejemplo /dashboard.');
    }
    if (total < rutas.length) throw new Error('Indica al menos una petición por ruta en cada nivel.');

    const origen = `http://127.0.0.1:${puerto}`;
    console.log(`Nexo CRM | Prueba HTTP en ${origen}`);
    console.log(`${total} peticiones por nivel | Una sesion autenticada | Tiempo limite ${espera} ms`);
    const cookie = await iniciarSesion(origen, espera);
    console.log(`Sesion iniciada. Rutas: ${rutas.join(', ')}`);
    for (const ruta of rutas) await medir(origen, ruta, cookie, espera);

    let fallidas = 0;
    for (const nivel of niveles) {
        const { resultados, segundos } = await ejecutarNivel(origen, rutas, cookie, espera, nivel, total);
        const errores = resultados.filter(resultado => resultado.error);
        fallidas += errores.length;
        const filas = rutas.map(ruta => resumir(ruta, resultados.filter(resultado => resultado.ruta === ruta)));
        filas.push(resumir('TOTAL', resultados));

        console.log(`\nNivel ${nivel} | Hasta ${Math.min(nivel, total)} en paralelo | ${segundos.toFixed(2)} s | ${((total - errores.length) / segundos).toFixed(1)} respuestas correctas/s`);
        imprimirTabla(filas);
        if (errores.length) {
            const porMotivo = new Map();
            for (const { ruta, error } of errores) {
                const motivo = `${ruta}: ${error}`;
                porMotivo.set(motivo, (porMotivo.get(motivo) ?? 0) + 1);
            }
            for (const [motivo, cantidad] of porMotivo) console.log(`  ${cantidad} x ${motivo}`);
        }
    }

    console.log(`\nResultado: ${niveles.length * total - fallidas}/${niveles.length * total} respuestas correctas.`);
    if (fallidas) process.exitCode = 1;
}

main().catch(error => {
    const codigo = error.cause?.code;
    const mensajes = {
        ECONNREFUSED: 'No hay acceso a Nexo. Confirma que el servidor y el puerto son correctos.',
        EACCES: 'Tu entorno impide el acceso al servidor local. Ejecuta la prueba desde tu PowerShell.',
    };
    console.error(`Prueba HTTP: ${mensajes[codigo] ?? codigo ?? error.message}`);
    process.exitCode = 1;
});
