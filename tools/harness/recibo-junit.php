<?php

// Parser de los XML junit de PHPUnit para recibo.sh — emite JSON con valores medidos.
// Uso: php recibo-junit.php <archivo.xml> [<archivo.xml> ...]

$files = array_slice($argv, 1);
$vacio = ['tests' => 0, 'fallas' => 0, 'fallados' => [], 'nombres' => []];

if (!count($files)) {
    fwrite(STDERR, "junit inexistente: (sin argumento)\n");
    echo json_encode(['error' => 'sin-junit'] + $vacio);
    exit(2);
}

$tests = 0;
$fallas = 0;
$fallados = [];
$nombres = [];

foreach ($files as $file) {
    // El instrumento tiene que poder registrar el fallo, nunca callarlo: un junit que falta
    // invalida la fase entera, aunque los demás existan.
    if (!is_file($file)) {
        fwrite(STDERR, "junit inexistente: $file\n");
        echo json_encode(['error' => 'sin-junit'] + $vacio);
        exit(2);
    }

    $xml = @simplexml_load_file($file);

    if ($xml === false) {
        echo json_encode(['error' => 'junit-ilegible'] + $vacio);
        exit(2);
    }

    foreach ($xml->xpath('//testcase') as $tc) {
        $tests++;
        $nombre = (string) $tc['class'] . '::' . (string) $tc['name'];
        $nombres[] = $nombre;

        if (isset($tc->failure) || isset($tc->error)) {
            $fallas++;
            $fallados[] = $nombre;
        }
    }
}

echo json_encode([
    'tests' => $tests,
    'fallas' => $fallas,
    'fallados' => $fallados,
    'nombres' => $nombres,
], JSON_UNESCAPED_SLASHES);
