<?php
// CU-011 (pasos 1–6): Registrar transferencia entre puestos -> queda PENDIENTE de confirmación
require __DIR__ . '/_init.php';
$usuario = requerirPuesto();
$servicio = new TransferenciaService($repo);

$puestos  = $repo->listarPuestosActivos();
$usuarios = $repo->listarUsuariosActivos();
$origenId = (int)($_POST['origen'] ?? $_GET['origen'] ?? (puestoActual()['id'] ?? 0));

$error = null;
$disponible = null;
$prendaElegida = (string)($_POST['prenda'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidar();
    [$productoId, $talla, $color] = array_pad(explode('|', $prendaElegida), 3, '');
    try {
        $t = $servicio->registrar([
            'productoId'         => $productoId,
            'talla'              => $talla,
            'color'              => $color,
            'cantidad'           => $_POST['cantidad'] ?? '',
            'puestoOrigen'       => $origenId > 0 ? $origenId : '',
            'puestoDestino'      => $_POST['destino'] ?? '',
            'responsableOrigen'  => $_POST['resp_origen'] ?? '',
            'responsableDestino' => $_POST['resp_destino'] ?? '',
        ], $usuario['id']);

        flash("Transferencia N.° {$t->id} registrada como PENDIENTE. El responsable del destino debe confirmar la recepción.");
        redirigir('recepcion.php');
    } catch (TransferenciaException $ex) {
        $error = $ex->getMessage();          // incluye A1 (stock disponible) y A2 (mismo puesto)
    }
}

$stockOrigen = $origenId > 0 ? $repo->consultarStockPorPuesto($origenId) : [];

$titulo = 'Transferencia entre puestos';
require __DIR__ . '/partials/cabecera.php';
?>
<h2>Transferencia de mercadería entre puestos</h2>
<?php if ($error): ?><div class="aviso error"><?= e($error) ?></div><?php endif; ?>

<!-- Paso 2a: elegir el puesto de origen (recarga la lista de prendas con su stock) -->
<form method="get">
    <label>Puesto de origen</label>
    <select name="origen" onchange="this.form.submit()">
        <?php foreach ($puestos as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= (int)$p['id'] === $origenId ? 'selected' : '' ?>><?= e($p['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <noscript><button class="boton chico" type="submit">Ver prendas</button></noscript>
</form>

<form method="post">
    <?= csrfCampo() ?>
    <input type="hidden" name="origen" value="<?= $origenId ?>">

    <label>Puesto destino</label>
    <select name="destino" required>
        <option value="">Seleccione…</option>
        <?php foreach ($puestos as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= (string)$p['id'] === (string)($_POST['destino'] ?? '') ? 'selected' : '' ?>><?= e($p['nombre']) ?></option>
        <?php endforeach; ?>
    </select>

    <h2 style="font-size:1.05rem">Seleccione la prenda</h2>
    <?php if (!$stockOrigen): ?>
        <div class="aviso error">El puesto de origen no tiene existencias para transferir.</div>
    <?php endif; ?>
    <?php foreach ($stockOrigen as $f):
        $valor = $f['producto_id'] . '|' . $f['talla'] . '|' . $f['color']; ?>
        <label class="tarjeta opcion">
            <input type="radio" name="prenda" value="<?= e($valor) ?>" <?= $valor === $prendaElegida ? 'checked' : '' ?> required>
            <span>
                <h3><?= e($f['producto']) ?></h3>
                <small>Talla <?= e($f['talla']) ?> · Color <?= e($f['color']) ?> · <?= (int)$f['cantidad'] ?> unidades</small>
            </span>
        </label>
    <?php endforeach; ?>

    <label>Cantidad a transferir</label>
    <input name="cantidad" type="number" min="1" step="1" value="<?= e((string)($_POST['cantidad'] ?? '')) ?>" required>

    <label>Responsable del origen</label>
    <select name="resp_origen" required>
        <?php foreach ($usuarios as $u):
            $sel = (string)($_POST['resp_origen'] ?? $usuario['id']); ?>
            <option value="<?= (int)$u['id'] ?>" <?= (string)$u['id'] === $sel ? 'selected' : '' ?>><?= e($u['nombre']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Responsable del destino</label>
    <select name="resp_destino" required>
        <option value="">Seleccione…</option>
        <?php foreach ($usuarios as $u): ?>
            <option value="<?= (int)$u['id'] ?>" <?= (string)$u['id'] === (string)($_POST['resp_destino'] ?? '') ? 'selected' : '' ?>><?= e($u['nombre']) ?></option>
        <?php endforeach; ?>
    </select>

    <button class="boton" type="submit">CONFIRMAR</button>
</form>
<?php require __DIR__ . '/partials/pie.php'; ?>
