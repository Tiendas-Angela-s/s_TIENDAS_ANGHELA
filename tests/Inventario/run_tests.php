<?php
// Habilitar los asserts en el script de PHP puro
ini_set('assert.exception', 1);

// Incluir manualmente la clase (Carga limpia)
require_once __DIR__ . '/../../src/Inventario/InventarioService.php';

use App\Inventario\InventarioService;

echo "=== INICIANDO PRUEBAS DE INVENTARIO (PHP PURO) ===\n";

// --- TEST 1: Transferencia Simple Exitosa ---
try {
    $service = new InventarioService();
    
    // Transferir 4 unidades del puesto 1 al puesto 2
    $resultado = $service->transferirStock('PROD-01', 1, 2, 4);
    
    // Validación nativa con assert()
    assert($resultado === true, "Error: El resultado debería ser true");
    assert($service->obtenerStock('PROD-01', 1) === 6, "Error: El puesto 1 debería quedar con 6 unidades");
    assert($service->obtenerStock('PROD-01', 2) === 9, "Error: El puesto 2 debería quedar con 9 unidades");
    
    echo " Test 1 (Simple): PASADO EXITOSAMENTE.\n";
} catch (Throwable $e) {
    echo " Test 1 (Simple) FALLÓ: " . $e->getMessage() . "\n";
}

// --- TEST 2: Rechazar Cantidades Negativas (Avanzado) ---
try {
    $service = new InventarioService();
    $excepcionLanzada = false;

    try {
        // El código simple aceptará el -5 (restando un negativo, lo que sumará stock de forma fraudulenta)
        $service->transferirStock('PROD-01', 1, 2, -5);
    } catch (Exception $e) {
        if ($e->getMessage() === "La cantidad a transferir debe ser mayor a cero.") {
            $excepcionLanzada = true;
        }
    }

    assert($excepcionLanzada === true, "Error crítico: El sistema no rechazó la cantidad negativa.");
    echo " Test 2 (Avanzado - Negativos): PASADO.\n";
} catch (Throwable $e) {
    echo " Test 2 (Avanzado - Negativos) FALLÓ: " . $e->getMessage() . "\n";
}

// --- TEST 3: Rechazar Mismo Puesto Origen/Destino (Avanzado) ---
try {
    $service = new InventarioService();
    $excepcionLanzada = false;

    try {
        $service->transferirStock('PROD-01', 1, 1, 2);
    } catch (Exception $e) {
        if ($e->getMessage() === "El puesto de origen y destino no pueden ser iguales.") {
            $excepcionLanzada = true;
        }
    }

    assert($excepcionLanzada === true, "Error crítico: El sistema permitió transferir al mismo puesto.");
    echo " Test 3 (Avanzado - Mismo Puesto): PASADO.\n";
} catch (Throwable $e) {
    echo " Test 3 (Avanzado - Mismo Puesto) FALLÓ: " . $e->getMessage() . "\n";
}