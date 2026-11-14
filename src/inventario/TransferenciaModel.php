<?php
declare(strict_types=1);

/** Define los atributos de una transferencia de mercadería entre puestos (CU-011). */
class TransferenciaModel
{
    public const PENDIENTE = 'PENDIENTE';
    public const CONFIRMADA = 'CONFIRMADA';
    public const CONFIRMADA_CON_DIFERENCIA = 'CONFIRMADA_CON_DIFERENCIA';

    public function __construct(
        public ?int $id,
        public int $productoId,
        public string $talla,
        public string $color,
        public int $cantidad,
        public int $puestoOrigen,
        public int $puestoDestino,
        public int $responsableOrigen,
        public int $responsableDestino,
        public string $estado = self::PENDIENTE,
        // Datos adicionales necesarios para el flujo A3 (diferencias) y para la trazabilidad:
        public ?int $cantidadRecibida = null,
        public ?string $observacion = null,
        public ?int $creadoPor = null,
        public ?string $fechaCreacion = null,
        public ?string $fechaConfirmacion = null
    ) {
    }

    /** Construye el modelo desde una fila de la tabla `transferencias`. */
    public static function desdeFila(array $f): self
    {
        return new self(
            (int)$f['id'],
            (int)$f['producto_id'],
            (string)$f['talla'],
            (string)$f['color'],
            (int)$f['cantidad'],
            (int)$f['puesto_origen_id'],
            (int)$f['puesto_destino_id'],
            (int)$f['responsable_origen_id'],
            (int)$f['responsable_destino_id'],
            (string)$f['estado'],
            $f['cantidad_recibida'] === null ? null : (int)$f['cantidad_recibida'],
            $f['observacion'] === null ? null : (string)$f['observacion'],
            (int)$f['creado_por'],
            (string)$f['fecha_creacion'],
            $f['fecha_confirmacion'] === null ? null : (string)$f['fecha_confirmacion']
        );
    }

    public function estaPendiente(): bool
    {
        return $this->estado === self::PENDIENTE;
    }

    /** Unidades enviadas que no llegaron (0 si todavía no se confirmó o si llegó todo). */
    public function diferencia(): int
    {
        return $this->cantidadRecibida === null ? 0 : $this->cantidad - $this->cantidadRecibida;
    }
}
