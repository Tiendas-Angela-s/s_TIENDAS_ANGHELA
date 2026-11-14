<?php
declare(strict_types=1);

/** CU-008 Registrar ingreso de mercadería (lo mínimo para tener stock que transferir). */
final class IngresoService
{
    public function __construct(private InventarioRepository $repo)
    {
    }

    /** @param array $d productoId, puestoId, talla, color, cantidad, fecha (Y-m-d) */
    public function registrar(array $d, int $usuarioId): void
    {
        $faltan = [];
        foreach (['productoId' => 'producto', 'puestoId' => 'puesto de destino', 'talla' => 'talla',
                  'color' => 'color', 'cantidad' => 'cantidad', 'fecha' => 'fecha del ingreso'] as $campo => $etiqueta) {
            if (trim((string)($d[$campo] ?? '')) === '') {
                $faltan[] = $etiqueta;
            }
        }
        if ($faltan) {
            throw new ValidacionException('Complete los campos obligatorios: ' . implode(', ', $faltan) . '.');
        }

        // A1: cantidad válida
        $cantidad = filter_var($d['cantidad'], FILTER_VALIDATE_INT);
        if ($cantidad === false || $cantidad <= 0) {
            throw new ValidacionException('La cantidad no es válida: debe ser un entero mayor a 0.');
        }

        $fecha = DateTime::createFromFormat('Y-m-d', (string)$d['fecha']);
        if ($fecha === false || $fecha->format('Y-m-d') !== $d['fecha']) {
            throw new ValidacionException('La fecha del ingreso no es válida.');
        }

        // A2: el producto debe estar registrado
        $producto = $this->repo->obtenerProducto((int)$d['productoId']);
        if ($producto === null) {
            throw new ValidacionException('El producto no está registrado. Regístrelo primero.');
        }
        $puesto = $this->repo->obtenerPuesto((int)$d['puestoId']);
        if ($puesto === null || (int)$puesto['activo'] !== 1) {
            throw new ValidacionException('El puesto de destino no existe o está inactivo.');
        }

        $talla = Texto::mayusculas(trim((string)$d['talla']));
        $color = Texto::minusculas(trim((string)$d['color']));
        if (!in_array($talla, $producto['tallas'], true) || !in_array($color, $producto['colores'], true)) {
            throw new ValidacionException('La talla o el color no corresponden al producto.');
        }

        $pdo = $this->repo->pdo();
        $pdo->beginTransaction();
        try {
            $this->repo->sumarStock((int)$puesto['id'], $producto['id'], $talla, $color, $cantidad);
            $this->repo->registrarMovimiento('INGRESO', (int)$puesto['id'], $producto['id'], $talla, $color, $cantidad, $usuarioId, 'Ingreso del ' . $d['fecha']);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
