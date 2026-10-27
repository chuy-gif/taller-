<?php

// Este script prepara archivos locales. No borra ni reinicia la base existente.
$raiz = dirname(__DIR__);
$carpetas = [
    'bootstrap/cache', 'storage/app/private', 'storage/app/public',
    'storage/framework/cache/data', 'storage/framework/sessions',
    'storage/framework/views', 'storage/logs',
];
foreach ($carpetas as $carpeta) {
    $ruta = $raiz.'/'.$carpeta;
    if (! is_dir($ruta)) mkdir($ruta, 0775, true);
}
if (! file_exists($raiz.'/.env')) {
    copy($raiz.'/.env.example', $raiz.'/.env');
}
$entorno = file_get_contents($raiz.'/.env');
if (preg_match('/^APP_KEY=\s*$/m', $entorno)) {
    $clave = 'base64:'.base64_encode(random_bytes(32));
    $entorno = preg_replace('/^APP_KEY=\s*$/m', 'APP_KEY='.$clave, $entorno);
    file_put_contents($raiz.'/.env', $entorno);
}
if (! file_exists($raiz.'/database/database.sqlite')) {
    touch($raiz.'/database/database.sqlite');
}
echo "Archivos locales preparados.\n";
