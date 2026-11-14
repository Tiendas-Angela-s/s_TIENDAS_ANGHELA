<?php
// Uso:  php tests/integration/TransferenciaConcurrencia.test.php
// Lanza DOS procesos PHP reales que compiten por la misma BD.
declare(strict_types=1);

require __DIR__ . '/../helpers.php';

/** Ejecuta varias confirmaciones a la vez. $trabajos = [[transferenciaId, usuarioId], ...] */
function confirmarEnParalelo(string $archivoBd, array $trabajos): array
{
    $inicio = microtime(true) + 1.0;
    $procesos = [];
    foreach ($trabajos as [$transferenciaId, $usuarioId]) {
        $cmd = [PHP_BINARY, __DIR__ . '/worker_confirmar.php', $archivoBd,
                (string)$transferenciaId, (string)$usuarioId, (string)$inicio];
        $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($proc)) {
            throw new RuntimeException('No se pudo lanzar el proceso de prueba');
        }
        $procesos[] = [$proc, $pipes];
    }

    $resultados = [];
    foreach ($procesos as [$proc, $pipes]) {
        $salida = stream_get_contents($pipes[1]);
        $errores = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);
        $resultados[] = json_decode($salida, true) ?? ['ok' => false, 'flujo' => 'SIN_SALIDA', 'mensaje' => $errores];
    }
    return $resultados;
}

echo "Concurrencia de transferencias (CU-011)\n";

$archivo = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tienda_concurrencia_' . getmypid() . '.sqlite';
@unlink($archivo);
$pdo = Database::conectar('sqlite:' . $archivo);
Database::crearEsquema($pdo);
Database::sembrarDemo($pdo);
$repo = new InventarioRepository($pdo);
$servicio = new TransferenciaService($repo);

probar('Dos solicitudes por la última prenda: una tiene éxito y la otra se rechaza sin stock negativo', function () use ($archivo, $repo, $servicio, $pdo) {
    $repo->sumarStock(1, 1, 'M', 'beige', 1);   // UNA sola unidad en el puesto 1

    // Ambas se pueden registrar (el stock se descuenta recién al confirmar)...
    $t1 = $servicio->registrar(datosTransferencia(['cantidad' => 1, 'puestoDestino' => 2, 'responsableDestino' => 2]), 2);
    $t2 = $servicio->registrar(datosTransferencia(['cantidad' => 1, 'puestoDestino' => 3, 'responsableDestino' => 3]), 3);

    // ...pero las dos confirmaciones llegan al mismo tiempo.
    $r = confirmarEnParalelo($archivo, [[$t1->id, 2], [$t2->id, 3]]);

    $exitos = array_values(array_filter($r, fn($x) => $x['ok']));
    $rechazos = array_values(array_filter($r, fn($x) => !$x['ok']));
    afirmarIgual(1, count($exitos), 'confirmaciones exitosas: ' . json_encode($r));
    afirmarIgual(1, count($rechazos), 'confirmaciones rechazadas: ' . json_encode($r));
    afirmarIgual('A1', $rechazos[0]['flujo'], 'motivo del rechazo: ' . $rechazos[0]['mensaje']);

    afirmarIgual(0, $repo->stockDisponible(1, 1, 'M', 'beige'), 'stock del origen');
    $enDestinos = $repo->stockDisponible(2, 1, 'M', 'beige') + $repo->stockDisponible(3, 1, 'M', 'beige');
    afirmarIgual(1, $enDestinos, 'la prenda llegó a un solo destino');
    $minimo = (int)$pdo->query('SELECT MIN(cantidad) FROM stock')->fetchColumn();
    afirmarVerdadero($minimo >= 0, 'ningún stock quedó negativo');
});

probar('Doble clic: la misma transferencia confirmada dos veces a la vez se aplica una sola vez', function () use ($archivo, $repo, $servicio) {
    $repo->sumarStock(1, 1, 'M', 'beige', 5);   // el origen vuelve a tener 5
    $t = $servicio->registrar(datosTransferencia(['cantidad' => 1, 'puestoDestino' => 2, 'responsableDestino' => 2]), 2);

    $r = confirmarEnParalelo($archivo, [[$t->id, 2], [$t->id, 2]]);

    $exitos = array_values(array_filter($r, fn($x) => $x['ok']));
    $rechazos = array_values(array_filter($r, fn($x) => !$x['ok']));
    afirmarIgual(1, count($exitos), 'confirmaciones exitosas: ' . json_encode($r));
    afirmarIgual('ESTADO', $rechazos[0]['flujo'] ?? null, 'motivo del rechazo');
    afirmarIgual(4, $repo->stockDisponible(1, 1, 'M', 'beige'), 'el origen se descontó una sola vez');
});

unset($pdo);
@unlink($archivo);
terminarPruebas();
