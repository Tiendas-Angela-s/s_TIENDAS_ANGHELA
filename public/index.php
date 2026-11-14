<?php
require __DIR__ . '/_init.php';
$usuario = requerirPuesto();
$esAdmin = $usuario['rol'] === 'ADMINISTRADORA';
$pendientes = count($repo->listarTransferenciasDetalle([
    'estado' => 'PENDIENTE',
    'responsableDestino' => $esAdmin ? null : $usuario['id'],
]));

$titulo = 'Inicio';
require __DIR__ . '/partials/cabecera.php';
?>
<h2>Hola, <?= e($usuario['nombre']) ?></h2>
<a class="boton" href="stock.php">Consultar stock por puesto</a>
<a class="boton" href="transferencia.php">Nueva transferencia entre puestos</a>
<a class="boton claro" href="recepcion.php">Confirmar recepciones (<?= $pendientes ?> pendientes)</a>
<?php if ($esAdmin): ?>
    <a class="boton claro" href="productos_registrar.php">Registrar producto</a>
    <a class="boton claro" href="ingreso.php">Registrar ingreso de mercadería</a>
<?php endif; ?>
<?php require __DIR__ . '/partials/pie.php'; ?>
