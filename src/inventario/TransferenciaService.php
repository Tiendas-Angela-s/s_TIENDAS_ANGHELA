<?php
declare(strict_types=1);

/**
 * Reglas de negocio del CU-011 «Registrar y confirmar transferencia entre puestos».
 *   registrar()            pasos 2–6 del flujo principal  (+ A1, A2)
 *   confirmarRecepcion()   pasos 7–8 del flujo principal  (+ A1, A3, A4)
 */
class TransferenciaService
{
    public function __construct(private InventarioRepository $repo)
    {
    }

    /**
     * @param array $datos productoId, talla, color, cantidad, puestoOrigen, puestoDestino,
     *                     responsableOrigen, responsableDestino
     */
    public function registrar(array $datos, int $usuarioId): TransferenciaModel
    {
        // --- Campos obligatorios
        $etiquetas = [
            'productoId'         => 'producto',
            'talla'              => 'talla',
            'color'              => 'color',
            'cantidad'           => 'cantidad',
            'puestoOrigen'       => 'puesto de origen',
            'puestoDestino'      => 'puesto de destino',
            'responsableOrigen'  => 'responsable del origen',
            'responsableDestino' => 'responsable del destino',
        ];
        $faltan = [];
        foreach ($etiquetas as $campo => $etiqueta) {
            $valor = $datos[$campo] ?? null;
            if ($valor === null || trim((string)$valor) === '') {
                $faltan[] = $etiqueta;
            }
        }
        if ($faltan) {
            throw new TransferenciaException('Complete los campos obligatorios: ' . implode(', ', $faltan) . '.', 'VALIDACION');
        }

        $productoId         = $this->entero($datos['productoId'], 'producto');
        $cantidad           = $this->entero($datos['cantidad'], 'cantidad');
        $puestoOrigen       = $this->entero($datos['puestoOrigen'], 'puesto de origen');
        $puestoDestino      = $this->entero($datos['puestoDestino'], 'puesto de destino');
        $responsableOrigen  = $this->entero($datos['responsableOrigen'], 'responsable del origen');
        $responsableDestino = $this->entero($datos['responsableDestino'], 'responsable del destino');
        $talla = Texto::mayusculas(trim((string)$datos['talla']));
        $color = Texto::minusculas(trim((string)$datos['color']));

        if ($cantidad <= 0) {
            throw new TransferenciaException('La cantidad debe ser un número entero mayor a 0.', 'VALIDACION');
        }

        // --- A2: mismo puesto
        if ($puestoOrigen === $puestoDestino) {
            throw new TransferenciaException('El puesto de origen y el de destino son el mismo. Corrija la selección.', 'A2');
        }

        // --- Existencia de las entidades involucradas
        foreach ([$puestoOrigen => 'origen', $puestoDestino => 'destino'] as $id => $rol) {
            $puesto = $this->repo->obtenerPuesto($id);
            if ($puesto === null || (int)$puesto['activo'] !== 1) {
                throw new TransferenciaException("El puesto de $rol no existe o está inactivo.", 'VALIDACION');
            }
        }
        $producto = $this->repo->obtenerProducto($productoId);
        if ($producto === null) {
            throw new TransferenciaException('El producto seleccionado no está registrado.', 'VALIDACION');
        }
        if (!in_array($talla, $producto['tallas'], true) || !in_array($color, $producto['colores'], true)) {
            throw new TransferenciaException('La talla o el color no corresponden al producto seleccionado.', 'VALIDACION');
        }
        foreach ([$responsableOrigen => 'origen', $responsableDestino => 'destino'] as $id => $rol) {
            $u = $this->repo->obtenerUsuario($id);
            if ($u === null || (int)$u['activo'] !== 1) {
                throw new TransferenciaException("El responsable del $rol no existe o está inactivo.", 'VALIDACION');
            }
        }

        // --- A1: stock suficiente en el origen (paso 6)
        $disponible = $this->repo->stockDisponible($puestoOrigen, $productoId, $talla, $color);
        if ($disponible < $cantidad) {
            throw new TransferenciaException(
                "Stock insuficiente en el puesto de origen. Cantidad disponible: $disponible.",
                'A1',
                $disponible
            );
        }

        // --- Registrar como PENDIENTE (el stock todavía no se mueve)
        $t = new TransferenciaModel(
            null, $productoId, $talla, $color, $cantidad,
            $puestoOrigen, $puestoDestino, $responsableOrigen, $responsableDestino,
            TransferenciaModel::PENDIENTE, null, null, $usuarioId, date('Y-m-d H:i:s')
        );
        $t->id = $this->repo->insertarTransferencia($t);

        return $this->repo->obtenerTransferencia($t->id);
    }

    /**
     * Confirma la recepción en el destino y mueve el stock.
     *
     * @param int|string|null $cantidadRecibida null = llegó todo lo enviado
     */
    public function confirmarRecepcion(
        int $transferenciaId,
        int $usuarioId,
        int|string|null $cantidadRecibida = null,
        ?string $observacion = null
    ): TransferenciaModel {
        $t = $this->repo->obtenerTransferencia($transferenciaId);
        if ($t === null) {
            throw new TransferenciaException('La transferencia no existe.', 'ESTADO');
        }
        if (!$t->estaPendiente()) {
            throw new TransferenciaException('La transferencia ya fue confirmada.', 'ESTADO');
        }

        // Solo el responsable del destino (o la administradora) verifica y confirma
        $usuario = $this->repo->obtenerUsuario($usuarioId);
        if ($usuario === null || ((int)$usuario['id'] !== $t->responsableDestino && $usuario['rol'] !== 'ADMINISTRADORA')) {
            throw new TransferenciaException(
                'Solo el responsable del destino (o la administradora) puede confirmar la recepción.',
                'PERMISO'
            );
        }

        // --- Cantidad recibida y flujo A3 (diferencias)
        if ($cantidadRecibida === null || (is_string($cantidadRecibida) && trim($cantidadRecibida) === '')) {
            $recibida = $t->cantidad;
        } else {
            $recibida = filter_var($cantidadRecibida, FILTER_VALIDATE_INT);
            if ($recibida === false || $recibida < 1 || $recibida > $t->cantidad) {
                throw new TransferenciaException(
                    "La cantidad recibida debe ser un entero entre 1 y {$t->cantidad}.",
                    'VALIDACION'
                );
            }
        }
        $observacion = $observacion === null ? null : trim($observacion);
        if ($observacion === '') {
            $observacion = null;
        }
        if ($recibida !== $t->cantidad && $observacion === null) {
            throw new TransferenciaException(
                'Lo recibido difiere de lo enviado. Indique el motivo de la diferencia para dejar constancia.',
                'A3'
            );
        }

        // --- Actualización atómica; A4 si algo falla (el repositorio ya hizo ROLLBACK)
        try {
            $this->repo->ejecutarTransferenciaAtomica($t, $recibida, $observacion, $usuarioId);
        } catch (TransferenciaException $e) {
            throw $e;   // A1 o ESTADO: ya traen su mensaje
        } catch (Throwable $e) {
            throw new TransferenciaException(
                'No se pudo actualizar el stock. Se revirtieron los cambios; intente nuevamente.',
                'A4',
                null,
                $e
            );
        }

        return $this->repo->obtenerTransferencia($transferenciaId);
    }

    private function entero(mixed $valor, string $campo): int
    {
        $n = filter_var($valor, FILTER_VALIDATE_INT);
        if ($n === false) {
            throw new TransferenciaException("El campo «{$campo}» debe ser un número entero.", 'VALIDACION');
        }
        return $n;
    }
}
