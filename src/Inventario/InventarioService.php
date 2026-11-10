<?php
namespace App\Inventario;

use Exception;

class InventarioService {
    private array $stocks = [];

    public function __construct() {
        // Inicialización nativa correcta: [Producto][Puesto] = Cantidad
        $this->stocks['PROD-01'][1] = 10; // Puesto 1 tiene 10 unidades
        $this->stocks['PROD-01'][2] = 5;  // Puesto 2 tiene 5 unidades
    }

    public function obtenerStock(string $productoId, int $puestoId): int {
        return $this->stocks[$productoId][$puestoId] ?? 0;
    }

    public function transferirStock(string $productoId, int $origenId, int $destinoId, int $cantidad): bool {
        // Validación básica inicial
        if ($cantidad > ($this->stocks[$productoId][$origenId] ?? 0)) {
            throw new Exception("Stock insuficiente en el origen.");
        }

        // Ejecución matemática directa
        $this->stocks[$productoId][$origenId] -= $cantidad;
        $this->stocks[$productoId][$destinoId] += $cantidad;

        return true;
    }
}
