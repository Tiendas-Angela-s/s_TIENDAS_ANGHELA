<?php
namespace App\Inventario;

use Exception;

class InventarioService {
    private array $stocks = [];

    public function __construct() {
        $this->stocks['PROD-01'][1] = 10;
        $this->stocks['PROD-01'][2] = 5;
    }

    public function obtenerStock(string $productoId, int $puestoId): int {
        return $this->stocks[$productoId][$puestoId] ?? 0;
    }

    public function transferirStock(string $productoId, int $origenId, int $destinoId, int $cantidad): bool {
        // Regla Avanzada 1: Blindaje contra cantidades nulas o negativas
        if ($cantidad <= 0) {
            throw new Exception("La cantidad a transferir debe ser mayor a cero.");
        }

        // Regla Avanzada 2: Validación de Puestos (Requerimiento CU-011 Alternativo A2)
        if ($origenId === $destinoId) {
            throw new Exception("El puesto de origen y destino no pueden ser iguales.");
        }

        // Regla de Negativos (Requerimiento CU-010 Alternativo A2)
        if ($cantidad > ($this->stocks[$productoId][$origenId] ?? 0)) {
            throw new Exception("Stock insuficiente en el origen.");
        }

        // Proceso matemático seguro garantizado
        $this->stocks[$productoId][$origenId] -= $cantidad;
        $this->stocks[$productoId][$destinoId] += $cantidad;

        return true;
    }
}