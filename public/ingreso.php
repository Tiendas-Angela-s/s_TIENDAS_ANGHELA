<?php
// CU-008 Registrar ingreso de mercadería (mínimo)
require __DIR__ . '/_init.php';
$usuario = requerirAdministradora();
$servicio = new IngresoService($repo);

$productos = $repo->listarProductos();
$puestos = $repo->listarPuestosActivos();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidar();
    try {
        $servicio->registrar([
            'productoId' => $_POST['producto'] ?? '',
            'puestoId'   => $_POST['puesto'] ?? '',
            'talla'      => $_POST['talla'] ?? '',
            'color'      => $_POST['color'] ?? '',
            'cantidad'   => $_POST['cantidad'] ?? '',
            'fecha'      => $_POST['fecha'] ?? '',
        ], $usuario['id']);
        flash('Ingreso registrado. El stock del puesto fue incrementado.');
        redirigir('ingreso.php');
    } catch (ValidacionException $ex) {
        $error = $ex->getMessage();
    }
}

$titulo = 'Ingreso de mercadería';
require __DIR__ . '/partials/cabecera.php';
?>
<h2>Registrar ingreso de mercadería</h2>
<?php if ($error): ?><div class="aviso error"><?= e($error) ?></div><?php endif; ?>
<form method="post">
    <?= csrfCampo() ?>
    <label>Producto</label>
    <select name="producto" id="producto" required>
        <?php foreach ($productos as $p): ?>
            <option value="<?= (int)$p['id'] ?>"><?= e($p['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Puesto de destino</label>
    <select name="puesto" required>
        <?php foreach ($puestos as $p): ?>
            <option value="<?= (int)$p['id'] ?>"><?= e($p['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Talla</label>
    <select name="talla" id="talla" required></select>
    <label>Color</label>
    <select name="color" id="color" required></select>
    <label>Cantidad</label>
    <input name="cantidad" type="number" min="1" step="1" required>
    <label>Fecha del ingreso</label>
    <input name="fecha" type="date" value="<?= date('Y-m-d') ?>" required>
    <button class="boton" type="submit">Confirmar ingreso</button>
</form>
<script>
const productos = <?= json_encode(array_column($productos, null, 'id'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const selProducto = document.getElementById('producto');
const selTalla = document.getElementById('talla');
const selColor = document.getElementById('color');
function llenar() {
    selTalla.innerHTML = '';
    selColor.innerHTML = '';
    const p = productos[selProducto.value];
    if (!p) return;
    p.tallas.forEach(t => selTalla.add(new Option(t, t)));
    p.colores.forEach(c => selColor.add(new Option(c, c)));
}
selProducto.addEventListener('change', llenar);
llenar();
</script>
<?php require __DIR__ . '/partials/pie.php'; ?>
