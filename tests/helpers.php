<?php
// Mini framework de pruebas en PHP puro (sin PHPUnit ni Composer).
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$GLOBALS['__resultados'] = ['ok' => 0, 'fallo' => 0];

function probar(string $nombre, callable $prueba): void
{
    try {
        $prueba();
        echo "  [OK]    $nombre\n";
        $GLOBALS['__resultados']['ok']++;
    } catch (Throwable $e) {
        echo "  [FALLO] $nombre\n          -> " . get_class($e) . ': ' . $e->getMessage() . "\n";
        $GLOBALS['__resultados']['fallo']++;
    }
}

function afirmarIgual(mixed $esperado, mixed $real, string $mensaje = ''): void
{
    if ($esperado !== $real) {
        throw new RuntimeException(
            ($mensaje !== '' ? "$mensaje: " : '') .
            'se esperaba ' . var_export($esperado, true) . ' y se obtuvo ' . var_export($real, true)
        );
    }
}

function afirmarVerdadero(bool $condicion, string $mensaje = 'la condición es falsa'): void
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}

/** Ejecuta $fn y exige que lance TransferenciaException del flujo indicado. */
function esperarFlujo(callable $fn, string $flujo): TransferenciaException
{
    try {
        $fn();
    } catch (TransferenciaException $e) {
        afirmarIgual($flujo, $e->flujo, 'flujo de la excepción (' . $e->getMessage() . ')');
        return $e;
    }
    throw new RuntimeException("Se esperaba TransferenciaException del flujo $flujo y no se lanzó nada");
}

function terminarPruebas(): void
{
    $r = $GLOBALS['__resultados'];
    echo "\nResumen: {$r['ok']} correctas, {$r['fallo']} con fallo\n";
    exit($r['fallo'] > 0 ? 1 : 0);
}

/**
 * BD en memoria con datos demo. Fixtures:
 *   puestos 1..3 | usuarios 1=admin 2=vendedora1 3=vendedora2 | producto 1 = Polo satinado (S,M,XL / beige,azul,guinda)
 *   Puesto 1 arranca con 10 unidades de Polo satinado, talla M, color beige.
 */
function nuevoEscenario(?string $clase = null): array
{
    $pdo = Database::conectar('sqlite::memory:');
    Database::crearEsquema($pdo);
    Database::sembrarDemo($pdo);
    // Stock inicial por SQL directo (así los repositorios "que fallan" de las pruebas no afectan el fixture).
    $pdo->exec("INSERT INTO stock (puesto_id, producto_id, talla, color, cantidad) VALUES (1, 1, 'M', 'beige', 10)");
    $repo = $clase === null ? new InventarioRepository($pdo) : new $clase($pdo);
    return [$pdo, $repo, new TransferenciaService($repo)];
}

function datosTransferencia(array $cambios = []): array
{
    return array_merge([
        'productoId'         => 1,
        'talla'              => 'M',
        'color'              => 'beige',
        'cantidad'           => 3,
        'puestoOrigen'       => 1,
        'puestoDestino'      => 2,
        'responsableOrigen'  => 2,   // vendedora1
        'responsableDestino' => 3,   // vendedora2
    ], $cambios);
}
