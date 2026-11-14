<?php
// CU-009 Consultar stock por puesto
require __DIR__ . '/_init.php';
$usuario = requerirPuesto();

$puestos = $repo->listarPuestosActivos();
$puestoId = (int)($_GET['puesto'] ?? (puestoActual()['id'] ?? 0));
$stock = $puestoId ? $repo->consultarStockPorPuesto($puestoId) : [];

$titulo = 'Stock por puesto';
require __DIR__ . '/partials/cabecera.php';
?>
<h2>Stock por puesto</h2>
<form method="get">
    <label>Puesto</label>
    <select name="puesto" onchange="this.form.submit()">
        <?php foreach ($puestos as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= (int)$p['id'] === $puestoId ? 'selected' : '' ?>><?= e($p['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <noscript><button class="boton chico" type="submit">Ver</button></noscript>
</form>

<?php if (!$stock): ?>
    <div class="aviso error">Este puesto no tiene existencias registradas.</div>
<?php else: ?>
    <table style="margin-top:14px">
        <tr><th>Producto</th><th>Talla</th><th>Color</th><th>Cant.</th></tr>
        <?php foreach ($stock as $f): ?>
            <tr>
                <td><?= e($f['producto']) ?></td>
                <td><?= e($f['talla']) ?></td>
                <td><?= e($f['color']) ?></td>
                <td><?= (int)$f['cantidad'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php require __DIR__ . '/partials/pie.php'; ?>
