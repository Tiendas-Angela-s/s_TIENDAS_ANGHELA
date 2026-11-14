<?php
// CU-011 (pasos 7–8): el responsable del destino verifica la mercadería y confirma la recepción
require __DIR__ . '/_init.php';
$usuario = requerirPuesto();
$esAdmin = $usuario['rol'] === 'ADMINISTRADORA';
$servicio = new TransferenciaService($repo);

$error = null;
$idConError = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidar();
    $idConError = (int)($_POST['id'] ?? 0);
    try {
        $t = $servicio->confirmarRecepcion(
            $idConError,
            $usuario['id'],
            $_POST['recibida'] ?? null,
            $_POST['observacion'] ?? null
        );
        $msg = "Transferencia N.° {$t->id} confirmada. El stock de ambos puestos fue actualizado.";
        if ($t->diferencia() > 0) {
            $msg .= " Se dejó constancia de una diferencia de {$t->diferencia()} unidad(es).";
        }
        flash($msg);
        redirigir('recepcion.php');
    } catch (TransferenciaException $ex) {
        $error = $ex->getMessage();          // A1, A3, A4, permisos o estado
    }
}

$pendientes = $repo->listarTransferenciasDetalle([
    'estado' => 'PENDIENTE',
    'responsableDestino' => $esAdmin ? null : $usuario['id'],
]);
$historial = $repo->listarTransferenciasDetalle([
    'participante' => $esAdmin ? null : $usuario['id'],
    'limite' => 10,
]);

$titulo = 'Recepción de transferencias';
require __DIR__ . '/partials/cabecera.php';
?>
<h2>Transferencias por confirmar</h2>
<?php if ($error): ?><div class="aviso error"><?= e($error) ?></div><?php endif; ?>

<?php if (!$pendientes): ?>
    <p style="text-align:center;color:#8a7a7a">No hay transferencias pendientes.</p>
<?php endif; ?>
<?php foreach ($pendientes as $t): ?>
    <form method="post" class="tarjeta">
        <?= csrfCampo() ?>
        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
        <h3>N.° <?= (int)$t['id'] ?> · <?= e($t['producto']) ?></h3>
        <small>Talla <?= e($t['talla']) ?> · Color <?= e($t['color']) ?> · Enviado: <strong><?= (int)$t['cantidad'] ?></strong></small>
        <small><?= e($t['puesto_origen']) ?> (<?= e($t['responsable_origen_nombre']) ?>) → <?= e($t['puesto_destino']) ?> (<?= e($t['responsable_destino_nombre']) ?>)</small>

        <label>Cantidad recibida</label>
        <input name="recibida" type="number" min="1" max="<?= (int)$t['cantidad'] ?>" step="1"
               value="<?= (int)$t['cantidad'] ?>" required>
        <label>Observación (obligatoria si hay diferencia)</label>
        <input name="observacion" placeholder="Ej.: llegó una prenda manchada">
        <button class="boton chico" type="submit">Confirmar recepción</button>
    </form>
<?php endforeach; ?>

<h2>Últimas transferencias</h2>
<?php foreach ($historial as $t): ?>
    <div class="tarjeta">
        <h3>N.° <?= (int)$t['id'] ?> · <?= e($t['producto']) ?> <span class="estado <?= e($t['estado']) ?>"><?= e(str_replace('_', ' ', $t['estado'])) ?></span></h3>
        <small>Talla <?= e($t['talla']) ?> · Color <?= e($t['color']) ?> · Enviado: <?= (int)$t['cantidad'] ?>
            <?= $t['cantidad_recibida'] !== null ? ' · Recibido: ' . (int)$t['cantidad_recibida'] : '' ?></small>
        <small><?= e($t['puesto_origen']) ?> → <?= e($t['puesto_destino']) ?></small>
        <?php if (!empty($t['observacion'])): ?><small>Obs.: <?= e($t['observacion']) ?></small><?php endif; ?>
    </div>
<?php endforeach; ?>
<?php require __DIR__ . '/partials/pie.php'; ?>
