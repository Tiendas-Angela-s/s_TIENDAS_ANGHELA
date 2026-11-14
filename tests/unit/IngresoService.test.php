<?php
// Uso:  php tests/unit/IngresoService.test.php
declare(strict_types=1);

require __DIR__ . '/../helpers.php';

echo "IngresoService (CU-008)\n";

probar('Registra ingreso sin mbstring y normaliza mayúsculas', function () {
    $pdo = Database::conectar('sqlite::memory:');
    Database::crearEsquema($pdo);
    Database::sembrarDemo($pdo);
    $repo = new InventarioRepository($pdo);
    $servicio = new IngresoService($repo);

    $servicio->registrar([
        'productoId' => 1,
        'puestoId' => 1,
        'talla' => 'm',
        'color' => 'BEIGE',
        'cantidad' => 4,
        'fecha' => date('Y-m-d'),
    ], 1);

    afirmarIgual(4, $repo->stockDisponible(1, 1, 'M', 'beige'), 'stock registrado');
    afirmarIgual('MARRÓN', Texto::mayusculas('marrón'));
    afirmarIgual('marrón', Texto::minusculas('MARRÓN'));
    afirmarIgual('José', Texto::subcadena('José', 0, 4));
});

terminarPruebas();
