<?php

/**
 * Reglas de forma de un Service NUEVO (las corre layers.sh, rama src/Services/*):
 *
 *   A8  — un Service persiste SOLO su propia entity: si recibe por parámetro una entity {Y}
 *         ajena y la manda tal cual a {y}Repository->create()/update(), ese CRUD es de
 *         {Y}Service. Las señales más gruesas no sirven como regla: escribir por repositorios
 *         ajenos es legítimo (cabecera/detalle, auditorías) y recibir entities ajenas como
 *         INSUMO también.
 *   A9  — una sola escritura no lleva transacción: las transacciones son para varias
 *         escrituras que deben caer juntas. Una lectura de validación antes no es motivo —
 *         sólo un constraint hace atómico un check.
 *   A10 — los guards de argumento van inline en el método, no en un helper privado que sólo
 *         lanza InvalidArgumentException.
 *   A12 — los valores tipo enum van como LITERALES donde se usan ('shift_end', 'gate'), no como
 *         constantes de clase.
 *
 * Uso:  php tools/architecture/service-rules.php <src/Services/.../XService.php>
 * Imprime `A<n> <detalle>` por violación. exit 0 = limpio · 1 = viola · 2 = no aplica
 */

$file = $argv[1] ?? '';

if ($file === '' || !is_file($file) || !str_ends_with($file, 'Service.php')) {
    exit(2);
}

$source = (string) file_get_contents($file);
$service = basename($file, 'Service.php');
$violations = 0;

/**
 * Cuerpos de método: [visibilidad, nombre, parámetros, cuerpo] por llaves balanceadas.
 *
 * @param string $source Código fuente
 * @return array<int, array{0: string, 1: string, 2: string, 3: string}>
 */
$methods = function (string $source): array {
    $found = [];
    $pattern = '/(public|protected|private) function (\w+)\s*\(([^)]*)\)[^{]*\{/';

    if (!preg_match_all($pattern, $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        return $found;
    }

    foreach ($matches as $m) {
        $start = $m[0][1] + strlen($m[0][0]);
        $depth = 1;
        $i = $start;
        $length = strlen($source);

        while ($i < $length && $depth > 0) {
            $c = $source[$i];
            $depth += $c === '{' ? 1 : ($c === '}' ? -1 : 0);
            $i++;
        }

        $found[] = [$m[1][0], $m[2][0], $m[3][0], substr($source, $start, $i - $start - 1)];
    }

    return $found;
};

// ---------- A8 ----------
preg_match_all('/^use [\w\\\\]+\\\\Entities\\\\(?:[\w\\\\]*\\\\)?(\w+);/m', $source, $imports);
$entities = $imports[1];

foreach ($methods($source) as [$visibility, $method, $params, $body]) {
    if ($visibility !== 'public') {
        continue;
    }

    preg_match_all('/\??([A-Z]\w+)\s+\$(\w+)/', $params, $typed, PREG_SET_ORDER);

    foreach ($typed as [, $type, $var]) {
        $own = $type === $service
            || stripos($service, $type) !== false
            || stripos($type, $service) !== false;

        if ($own || !in_array($type, $entities, true)) {
            continue;
        }

        $persisted = '/[Rr]epository\s*->\s*(create|update)\s*\((?:\$\w+,\s*)?\$' . preg_quote($var, '/') . '\b/';

        if (preg_match($persisted, $source)) {
            echo "A8 {$method}({$type} \${$var}): recibe la entity {$type} y la persiste"
                . " — su CRUD va en {$type}Service\n";
            $violations++;
        }
    }
}

// ---------- A9 ----------
foreach ($methods($source) as [, $method, , $body]) {
    if (!str_contains($body, 'beginTransaction')) {
        continue;
    }

    $writes = preg_match_all('/[Rr]epository\s*->\s*(create|update|delete)\s*\(/', $body);

    if ($writes === 1) {
        echo "A9 {$method}(): una sola escritura no lleva transaccion\n";
        $violations++;
    }
}

// ---------- A10 ----------
foreach ($methods($source) as [$visibility, $method, , $body]) {
    if ($visibility === 'public' || !str_contains($body, 'InvalidArgumentException')) {
        continue;
    }

    // Solo guards = ninguna sentencia salvo `throw new InvalidArgumentException`: cualquier otra
    // linea que cierre con `;`, una asignacion o un return lo descartan. Las lineas sin `;` son
    // condiciones o llaves. Techo conocido: un throw partido en varias lineas no se detecta.
    $lines = array_values(array_filter(array_map('trim', explode("\n", $body)), fn ($l) => $l !== ''));
    $onlyGuards = str_contains($body, 'throw new InvalidArgumentException');

    foreach ($lines as $line) {
        $statement = str_ends_with($line, ';') && !str_starts_with($line, 'throw new InvalidArgumentException');
        $assignment = preg_match('/^\$\w+\s*=[^=]/', $line) === 1;

        if ($statement || $assignment || str_starts_with($line, 'return')) {
            $onlyGuards = false;
            break;
        }
    }

    if ($onlyGuards) {
        echo "A10 {$method}(): helper que solo lanza errores de argumento — los guards van inline en el metodo\n";
        $violations++;
    }
}

// ---------- A12 ----------
preg_match_all('/(?:public|protected|private) const (\w+)\s*=\s*[\'"]/', $source, $consts);

foreach ($consts[1] as $name) {
    echo "A12 {$name}: los valores tipo enum van como literales donde se usan, no como constantes de clase\n";
    $violations++;
}

exit($violations > 0 ? 1 : 0);
