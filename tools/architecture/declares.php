<?php

/**
 * Comprueba que UNA clase se declare sin errores, con el autoloader real del proyecto.
 *
 * Declarar la clase obliga a PHP a resolver su padre, sus traits y sus interfaces, y a
 * verificar que implemente todo lo que sus contratos exigen. Por eso caza el fallo más
 * caro y más silencioso: un repositorio que declara `implements Contract` y deja un método
 * del contrato sin definir pasa cualquier revisión de estilo y no se puede instanciar.
 *
 * Un fatal de PHP mata este proceso y nada más: por eso se invoca uno por clase.
 *
 * Uso:  php tools/architecture/declares.php <FQCN>
 * exit 0 = la clase se declaró bien · exit 1 = no existe o no se pudo declarar · exit 2 = no pudo correr
 */

$fqcn = $argv[1] ?? '';

if ($fqcn === '') {
    fwrite(STDERR, "uso: php tools/architecture/declares.php <FQCN>\n");
    exit(2);
}

// tools/architecture/ -> dos niveles hasta la raiz del repositorio.
$root = dirname(__DIR__, 2);

if (!is_file($root . '/vendor/autoload.php')) {
    fwrite(STDERR, "no se encontró vendor/autoload.php — corré composer install\n");
    exit(2);
}

require $root . '/vendor/autoload.php';

$exists = class_exists($fqcn, true)
    || interface_exists($fqcn, true)
    || trait_exists($fqcn, true)
    || enum_exists($fqcn, true);

exit($exists ? 0 : 1);
