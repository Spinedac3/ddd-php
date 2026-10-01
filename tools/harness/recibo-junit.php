<?php

// Parser del XML junit de PHPUnit para recibo.sh — emite JSON con valores medidos.
// Uso: php recibo-junit.php <archivo.xml>

if (!isset($argv[1]) || !is_file($argv[1])) {
    fwrite(STDERR, "junit inexistente: " . ($argv[1] ?? '(sin argumento)') . "\n");
    // El instrumento tiene que poder registrar el fallo, nunca callarlo.
    echo json_encode(['error' => 'sin-junit', 'tests' => 0, 'fallas' => 0, 'fallados' => [], 'nombres' => []]);
    exit(2);
}

$xml = @simplexml_load_file($argv[1]);
if ($xml === false) {
    echo json_encode(['error' => 'junit-ilegible', 'tests' => 0, 'fallas' => 0, 'fallados' => [], 'nombres' => []]);
    exit(2);
}

$tests = 0;
$fallas = 0;
$fallados = [];
$nombres = [];

foreach ($xml->xpath('//testcase') as $tc) {
    $tests++;
    $nombre = (string) $tc['class'] . '::' . (string) $tc['name'];
    $nombres[] = $nombre;
    if (isset($tc->failure) || isset($tc->error)) {
        $fallas++;
        $fallados[] = $nombre;
    }
}

echo json_encode([
    'tests' => $tests,
    'fallas' => $fallas,
    'fallados' => $fallados,
    'nombres' => $nombres,
], JSON_UNESCAPED_SLASHES);
