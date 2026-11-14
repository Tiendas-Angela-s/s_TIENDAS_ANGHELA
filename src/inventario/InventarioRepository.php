<?php
declare(strict_types=1);

/**
 * Acceso a datos del inventario: consultar stock por puesto y ejecutar la
 * actualización atómica de una transferencia (restar en origen + sumar en destino
 * dentro de UNA misma transacción).
 */
class InventarioRepository
{
    public function __construct(protected PDO $pdo)
    {
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    // ------------------------------------------------------------------ catálogos

    public function obtenerPuesto(int $id): ?array
    {
        return $this->uno('SELECT id, numero, nombre, activo FROM puestos WHERE id = :id', [':id' => $id]);
    }

    public function listarPuestosActivos(): array
    {
        return $this->todos('SELECT id, numero, nombre FROM puestos WHERE activo = 1 ORDER BY numero');
    }

    /** Devuelve el producto con `tallas` y `colores` ya convertidos en arrays. */
    public function obtenerProducto(int $id): ?array
    {
        $p = $this->uno('SELECT * FROM productos WHERE id = :id', [':id' => $id]);
        return $p === null ? null : $this->prepararProducto($p);
    }

    public function listarProductos(): array
    {
        return array_map(
            fn(array $p) => $this->prepararProducto($p),
            $this->todos('SELECT * FROM productos ORDER BY nombre')
        );
    }

    public function obtenerUsuario(int $id): ?array
    {
        return $this->uno('SELECT id, usuario, nombre, rol, activo FROM usuarios WHERE id = :id', [':id' => $id]);
    }

    public function listarUsuariosActivos(): array
    {
        return $this->todos('SELECT id, nombre, rol FROM usuarios WHERE activo = 1 ORDER BY nombre');
    }

    // ------------------------------------------------------------------ stock

    public function stockDisponible(int $puestoId, int $productoId, string $talla, string $color): int
    {
        $st = $this->pdo->prepare(
            'SELECT cantidad FROM stock
             WHERE puesto_id = :p AND producto_id = :pr AND talla = :t AND color = :c'
        );
        $st->execute([':p' => $puestoId, ':pr' => $productoId, ':t' => $talla, ':c' => $color]);
        $valor = $st->fetchColumn();
        $st->closeCursor();
        return $valor === false ? 0 : (int)$valor;
    }

    /** CU-009: stock del puesto por producto, talla y color (solo lo que tiene existencias). */
    public function consultarStockPorPuesto(int $puestoId): array
    {
        return $this->todos(
            'SELECT s.producto_id, p.nombre AS producto, s.talla, s.color, s.cantidad
             FROM stock s JOIN productos p ON p.id = s.producto_id
             WHERE s.puesto_id = :p AND s.cantidad > 0
             ORDER BY p.nombre, s.talla, s.color',
            [':p' => $puestoId]
        );
    }

    public function sumarStock(int $puestoId, int $productoId, string $talla, string $color, int $cantidad): void
    {
        $up = $this->pdo->prepare(
            'UPDATE stock SET cantidad = cantidad + :q
             WHERE puesto_id = :p AND producto_id = :pr AND talla = :t AND color = :c'
        );
        $up->execute([':q' => $cantidad, ':p' => $puestoId, ':pr' => $productoId, ':t' => $talla, ':c' => $color]);

        if ($up->rowCount() === 0) {   // todavía no existía esa combinación en el puesto
            $in = $this->pdo->prepare(
                'INSERT INTO stock (puesto_id, producto_id, talla, color, cantidad) VALUES (:p, :pr, :t, :c, :q)'
            );
            $in->execute([':p' => $puestoId, ':pr' => $productoId, ':t' => $talla, ':c' => $color, ':q' => $cantidad]);
        }
    }

    /**
     * Resta SOLO si hay suficiente. La condición `cantidad >= :q2` va dentro del UPDATE, por lo que
     * la verificación y el descuento son un único paso indivisible (no hay "leer y luego escribir").
     * Devuelve false si no había stock suficiente (no se modificó nada).
     */
    public function restarStockSiHaySuficiente(int $puestoId, int $productoId, string $talla, string $color, int $cantidad): bool
    {
        $st = $this->pdo->prepare(
            'UPDATE stock SET cantidad = cantidad - :q1
             WHERE puesto_id = :p AND producto_id = :pr AND talla = :t AND color = :c AND cantidad >= :q2'
        );
        $st->execute([':q1' => $cantidad, ':q2' => $cantidad, ':p' => $puestoId, ':pr' => $productoId, ':t' => $talla, ':c' => $color]);
        return $st->rowCount() === 1;
    }

    public function registrarMovimiento(
        string $tipo,
        int $puestoId,
        int $productoId,
        string $talla,
        string $color,
        int $cantidad,
        int $usuarioId,
        ?string $referencia = null
    ): void {
        $st = $this->pdo->prepare(
            'INSERT INTO movimientos_inventario (fecha, tipo, puesto_id, producto_id, talla, color, cantidad, usuario_id, referencia)
             VALUES (:f, :tipo, :p, :pr, :t, :c, :q, :u, :ref)'
        );
        $st->execute([
            ':f' => date('Y-m-d H:i:s'), ':tipo' => $tipo, ':p' => $puestoId, ':pr' => $productoId,
            ':t' => $talla, ':c' => $color, ':q' => $cantidad, ':u' => $usuarioId, ':ref' => $referencia,
        ]);
    }

    // ------------------------------------------------------------------ transferencias

    public function insertarTransferencia(TransferenciaModel $t): int
    {
        $st = $this->pdo->prepare(
            'INSERT INTO transferencias
               (producto_id, talla, color, cantidad, puesto_origen_id, puesto_destino_id,
                responsable_origen_id, responsable_destino_id, estado, creado_por, fecha_creacion)
             VALUES (:pr, :t, :c, :q, :po, :pd, :ro, :rd, :e, :cp, :f)'
        );
        $st->execute([
            ':pr' => $t->productoId, ':t' => $t->talla, ':c' => $t->color, ':q' => $t->cantidad,
            ':po' => $t->puestoOrigen, ':pd' => $t->puestoDestino,
            ':ro' => $t->responsableOrigen, ':rd' => $t->responsableDestino,
            ':e' => $t->estado, ':cp' => $t->creadoPor, ':f' => $t->fechaCreacion ?? date('Y-m-d H:i:s'),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function obtenerTransferencia(int $id): ?TransferenciaModel
    {
        $f = $this->uno('SELECT * FROM transferencias WHERE id = :id', [':id' => $id]);
        return $f === null ? null : TransferenciaModel::desdeFila($f);
    }

    /**
     * Listado con nombres legibles para las pantallas.
     * Filtros opcionales: estado, responsableDestino, participante (origen o destino), limite.
     */
    public function listarTransferenciasDetalle(array $filtros = []): array
    {
        $where = [];
        $params = [];
        if (!empty($filtros['estado'])) {
            $where[] = 't.estado = ?';
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['responsableDestino'])) {
            $where[] = 't.responsable_destino_id = ?';
            $params[] = (int)$filtros['responsableDestino'];
        }
        if (!empty($filtros['participante'])) {
            $where[] = '(t.responsable_origen_id = ? OR t.responsable_destino_id = ?)';
            $params[] = (int)$filtros['participante'];
            $params[] = (int)$filtros['participante'];
        }
        $limite = (int)($filtros['limite'] ?? 50);

        $sql = 'SELECT t.*, pr.nombre AS producto, po.nombre AS puesto_origen, pd.nombre AS puesto_destino,
                       uo.nombre AS responsable_origen_nombre, ud.nombre AS responsable_destino_nombre
                FROM transferencias t
                JOIN productos pr ON pr.id = t.producto_id
                JOIN puestos po ON po.id = t.puesto_origen_id
                JOIN puestos pd ON pd.id = t.puesto_destino_id
                JOIN usuarios uo ON uo.id = t.responsable_origen_id
                JOIN usuarios ud ON ud.id = t.responsable_destino_id'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY t.id DESC LIMIT ' . $limite;

        return $this->todos($sql, $params);
    }

    /**
     * Ejecuta la confirmación de una transferencia de forma ATÓMICA:
     *   1) reclama la transferencia (PENDIENTE -> confirmada); solo UNA confirmación puede lograrlo
     *   2) resta en el origen solo si hay stock suficiente
     *   3) suma en el destino lo recibido
     *   4) registra los movimientos
     * Si algo falla en cualquier paso se hace ROLLBACK y no queda ningún cambio a medias.
     *
     * @return string estado final de la transferencia
     * @throws TransferenciaException (ESTADO | A1) o cualquier Throwable por fallas técnicas
     */
    public function ejecutarTransferenciaAtomica(
        TransferenciaModel $t,
        int $cantidadRecibida,
        ?string $observacion,
        int $usuarioId
    ): string {
        $estadoFinal = $cantidadRecibida === $t->cantidad
            ? TransferenciaModel::CONFIRMADA
            : TransferenciaModel::CONFIRMADA_CON_DIFERENCIA;

        $this->pdo->beginTransaction();
        try {
            if (!$this->reclamarPendiente((int)$t->id, $estadoFinal, $cantidadRecibida, $observacion)) {
                throw new TransferenciaException('La transferencia ya fue procesada.', 'ESTADO');
            }

            if (!$this->restarStockSiHaySuficiente($t->puestoOrigen, $t->productoId, $t->talla, $t->color, $t->cantidad)) {
                $disp = $this->stockDisponible($t->puestoOrigen, $t->productoId, $t->talla, $t->color);
                throw new TransferenciaException(
                    "Stock insuficiente en el puesto de origen. Cantidad disponible: $disp.",
                    'A1',
                    $disp
                );
            }

            $this->sumarStock($t->puestoDestino, $t->productoId, $t->talla, $t->color, $cantidadRecibida);

            $ref = 'Transferencia N.° ' . $t->id;
            $this->registrarMovimiento('TRANSFERENCIA_SALIDA', $t->puestoOrigen, $t->productoId, $t->talla, $t->color, $t->cantidad, $usuarioId, $ref);
            $this->registrarMovimiento('TRANSFERENCIA_ENTRADA', $t->puestoDestino, $t->productoId, $t->talla, $t->color, $cantidadRecibida, $usuarioId, $ref);

            $this->pdo->commit();
            return $estadoFinal;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function reclamarPendiente(int $id, string $estadoFinal, int $cantidadRecibida, ?string $observacion): bool
    {
        $st = $this->pdo->prepare(
            "UPDATE transferencias
             SET estado = :e, cantidad_recibida = :cr, observacion = :o, fecha_confirmacion = :f
             WHERE id = :id AND estado = 'PENDIENTE'"
        );
        $st->execute([':e' => $estadoFinal, ':cr' => $cantidadRecibida, ':o' => $observacion, ':f' => date('Y-m-d H:i:s'), ':id' => $id]);
        return $st->rowCount() === 1;
    }

    // ------------------------------------------------------------------ utilidades

    private function prepararProducto(array $p): array
    {
        $p['id'] = (int)$p['id'];
        $p['tallas'] = array_values(array_filter(array_map('trim', explode(',', (string)$p['tallas']))));
        $p['colores'] = array_values(array_filter(array_map('trim', explode(',', (string)$p['colores']))));
        return $p;
    }

    private function uno(string $sql, array $params = []): ?array
    {
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        $fila = $st->fetch();
        $st->closeCursor();
        return $fila === false ? null : $fila;
    }

    private function todos(string $sql, array $params = []): array
    {
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
}
