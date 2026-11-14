<?php
declare(strict_types=1);

/** Error de validación genérico (registrar producto, ingreso de mercadería). */
class ValidacionException extends RuntimeException
{
}

/** Errores de autenticación y de sesión en puesto (CU-032, CU-033). */
class AuthException extends RuntimeException
{
    public function __construct(string $mensaje, public string $codigo = 'CREDENCIALES')
    {
        parent::__construct($mensaje);
    }
}

/**
 * Error del CU-011. $flujo indica a qué flujo del caso de uso corresponde:
 *   VALIDACION    campos obligatorios / datos inválidos
 *   A1            stock insuficiente en el origen ($disponible trae la cantidad disponible)
 *   A2            origen y destino son el mismo puesto
 *   A3            la cantidad recibida difiere de la enviada y falta registrar el motivo
 *   A4            falló la actualización de stock y se revirtieron los cambios
 *   ESTADO        la transferencia ya fue procesada (o no existe)
 *   PERMISO       el usuario no puede confirmar esa recepción
 */
class TransferenciaException extends RuntimeException
{
    public function __construct(
        string $mensaje,
        public string $flujo = 'VALIDACION',
        public ?int $disponible = null,
        ?Throwable $previo = null
    ) {
        parent::__construct($mensaje, 0, $previo);
    }
}
