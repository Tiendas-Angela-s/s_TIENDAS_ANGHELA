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
