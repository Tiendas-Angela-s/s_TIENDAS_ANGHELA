<?php
// Uso:  php tests/unit/TransferenciaService.test.php
declare(strict_types=1);

require __DIR__ . '/../helpers.php';

/** Repositorio que simula una falla al sumar en el destino (para probar el flujo A4). */
class InventarioRepositoryQueFalla extends InventarioRepository
{
    public function sumarStock(int $puestoId, int $productoId, string $talla, string $color, int $cantidad): void
    {
        throw new RuntimeException('Falla simulada al sumar stock en el destino');
    }
}

echo "TransferenciaService (CU-011)\n";

probar('Registrar transferencia válida la deja PENDIENTE y no toca el stock', function () {
    [, $repo, $servicio] = nuevoEscenario();
    $t = $servicio->registrar(datosTransferencia(), 2);

    afirmarVerdadero($t->id !== null && $t->id > 0, 'debe tener id');
    afirmarIgual(TransferenciaModel::PENDIENTE, $t->estado);
    afirmarIgual(10, $repo->stockDisponible(1, 1, 'M', 'beige'), 'stock origen');
    afirmarIgual(0, $repo->stockDisponible(2, 1, 'M', 'beige'), 'stock destino');
});

probar('Transferencia exitosa: confirmar descuenta origen, suma destino y registra movimientos', function () {
    [$pdo, $repo, $servicio] = nuevoEscenario();
    $t = $servicio->registrar(datosTransferencia(['cantidad' => 3]), 2);
    $confirmada = $servicio->confirmarRecepcion($t->id, 3);

    afirmarIgual(TransferenciaModel::CONFIRMADA, $confirmada->estado);
    afirmarIgual(3, $confirmada->cantidadRecibida);
    afirmarIgual(7, $repo->stockDisponible(1, 1, 'M', 'beige'), 'stock origen');
    afirmarIgual(3, $repo->stockDisponible(2, 1, 'M', 'beige'), 'stock destino');
    $movs = (int)$pdo->query("SELECT COUNT(*) FROM movimientos_inventario WHERE tipo LIKE 'TRANSFERENCIA_%'")->fetchColumn();
    afirmarIgual(2, $movs, 'movimientos de salida y entrada');
});

probar('Rechazo por stock insuficiente (A1) informa la cantidad disponible', function () {
    [, $repo, $servicio] = nuevoEscenario();
    $e = esperarFlujo(fn() => $servicio->registrar(datosTransferencia(['cantidad' => 11]), 2), 'A1');
    afirmarIgual(10, $e->disponible, 'cantidad disponible informada');
    afirmarIgual(10, $repo->stockDisponible(1, 1, 'M', 'beige'), 'el stock no cambia');
});

probar('Rechazo por mismo puesto de origen y destino (A2)', function () {
    [, , $servicio] = nuevoEscenario();
    esperarFlujo(fn() => $servicio->registrar(datosTransferencia(['puestoDestino' => 1]), 2), 'A2');
});

probar('Validación de campos obligatorios', function () {
    [, , $servicio] = nuevoEscenario();
    foreach (['cantidad', 'productoId', 'talla', 'color', 'puestoOrigen', 'puestoDestino',
              'responsableOrigen', 'responsableDestino'] as $campo) {
        $datos = datosTransferencia();
        unset($datos[$campo]);
        esperarFlujo(fn() => $servicio->registrar($datos, 2), 'VALIDACION');
    }
    esperarFlujo(fn() => $servicio->registrar(datosTransferencia(['cantidad' => '']), 2), 'VALIDACION');
});

probar('La cantidad debe ser un entero mayor a 0', function () {
    [, , $servicio] = nuevoEscenario();
    foreach ([0, -2, 'abc', '1.5'] as $cantidad) {
        esperarFlujo(fn() => $servicio->registrar(datosTransferencia(['cantidad' => $cantidad]), 2), 'VALIDACION');
    }
});

probar('La talla/color debe pertenecer al producto', function () {
    [, , $servicio] = nuevoEscenario();
    esperarFlujo(fn() => $servicio->registrar(datosTransferencia(['talla' => 'XXL']), 2), 'VALIDACION');
    esperarFlujo(fn() => $servicio->registrar(datosTransferencia(['color' => 'verde']), 2), 'VALIDACION');
});

probar('Diferencia en recepción (A3): exige motivo y deja constancia', function () {
    [, $repo, $servicio] = nuevoEscenario();
    $t = $servicio->registrar(datosTransferencia(['cantidad' => 3]), 2);

    esperarFlujo(fn() => $servicio->confirmarRecepcion($t->id, 3, 2, null), 'A3');
    afirmarIgual(10, $repo->stockDisponible(1, 1, 'M', 'beige'), 'sin motivo no se mueve stock');

    $c = $servicio->confirmarRecepcion($t->id, 3, 2, 'Llegó una prenda manchada');
    afirmarIgual(TransferenciaModel::CONFIRMADA_CON_DIFERENCIA, $c->estado);
    afirmarIgual(1, $c->diferencia(), 'diferencia enviada - recibida');
    afirmarIgual('Llegó una prenda manchada', $c->observacion);
    afirmarIgual(7, $repo->stockDisponible(1, 1, 'M', 'beige'), 'origen descuenta lo enviado');
    afirmarIgual(2, $repo->stockDisponible(2, 1, 'M', 'beige'), 'destino suma lo recibido');
});

probar('Falla al actualizar (A4): se revierten todos los cambios', function () {
    [$pdo, $repo, $servicio] = nuevoEscenario(InventarioRepositoryQueFalla::class);
    $t = $servicio->registrar(datosTransferencia(['cantidad' => 3]), 2);

    esperarFlujo(fn() => $servicio->confirmarRecepcion($t->id, 3), 'A4');

    afirmarIgual(10, $repo->stockDisponible(1, 1, 'M', 'beige'), 'el origen vuelve a su valor');
    afirmarIgual(0, $repo->stockDisponible(2, 1, 'M', 'beige'), 'el destino no recibe nada');
    afirmarIgual(TransferenciaModel::PENDIENTE, $repo->obtenerTransferencia($t->id)->estado, 'sigue pendiente');
    $movs = (int)$pdo->query("SELECT COUNT(*) FROM movimientos_inventario WHERE tipo LIKE 'TRANSFERENCIA_%'")->fetchColumn();
    afirmarIgual(0, $movs, 'no quedan movimientos registrados');
});

probar('Solo el responsable del destino (o la administradora) puede confirmar', function () {
    [, $repo, $servicio] = nuevoEscenario();
    $t = $servicio->registrar(datosTransferencia(), 2);

    esperarFlujo(fn() => $servicio->confirmarRecepcion($t->id, 2), 'PERMISO');   // vendedora1 es el origen
    afirmarIgual(10, $repo->stockDisponible(1, 1, 'M', 'beige'), 'sin cambios');

    $c = $servicio->confirmarRecepcion($t->id, 1);                               // administradora
    afirmarIgual(TransferenciaModel::CONFIRMADA, $c->estado);
});

probar('Confirmar dos veces la misma transferencia no duplica el movimiento', function () {
    [, $repo, $servicio] = nuevoEscenario();
    $t = $servicio->registrar(datosTransferencia(['cantidad' => 3]), 2);
    $servicio->confirmarRecepcion($t->id, 3);

    esperarFlujo(fn() => $servicio->confirmarRecepcion($t->id, 3), 'ESTADO');
    afirmarIgual(7, $repo->stockDisponible(1, 1, 'M', 'beige'), 'stock origen');
    afirmarIgual(3, $repo->stockDisponible(2, 1, 'M', 'beige'), 'stock destino');
});

probar('Si el stock bajó entre el registro y la confirmación, se rechaza (A1) sin dejar negativos', function () {
    [$pdo, $repo, $servicio] = nuevoEscenario();
    $t = $servicio->registrar(datosTransferencia(['cantidad' => 8]), 2);
    $repo->restarStockSiHaySuficiente(1, 1, 'M', 'beige', 5);   // alguien vendió 5 mientras tanto

    $e = esperarFlujo(fn() => $servicio->confirmarRecepcion($t->id, 3), 'A1');
    afirmarIgual(5, $e->disponible);
    afirmarIgual(5, $repo->stockDisponible(1, 1, 'M', 'beige'), 'origen intacto');
    afirmarIgual(0, $repo->stockDisponible(2, 1, 'M', 'beige'), 'destino intacto');
    afirmarIgual(TransferenciaModel::PENDIENTE, $repo->obtenerTransferencia($t->id)->estado, 'sigue pendiente');
});

terminarPruebas();
