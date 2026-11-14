<?php
// Proceso auxiliar de TransferenciaConcurrencia.test.php (no se ejecuta a mano).
// Uso: php worker_confirmar.php <archivo_bd> <transferenciaId> <usuarioId> <inicio_microtime>
declare(strict_types=1);

require __DIR__ . '/../../src/bootstrap.php';

[, $archivo, $transferenciaId, $usuarioId, $inicio] = $argv;

$pdo = Database::conectar('sqlite:' . $archivo);
$servicio = new TransferenciaService(new InventarioRepository($pdo));

// Barrera: ambos procesos esperan al mismo instante para competir de verdad.
while (microtime(true) < (float)$inicio) {
    usleep(500);
}

try {
    $servicio->confirmarRecepcion((int)$transferenciaId, (int)$usuarioId);
    echo json_encode(['ok' => true]);
} catch (TransferenciaException $e) {
    echo json_encode(['ok' => false, 'flujo' => $e->flujo, 'mensaje' => $e->getMessage()]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'flujo' => 'ERROR', 'mensaje' => $e->getMessage()]);
}
