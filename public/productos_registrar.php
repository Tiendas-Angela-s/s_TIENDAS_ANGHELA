<?php
// CU-001 Registrar producto (mínimo)
require __DIR__ . '/_init.php';
$usuario = requerirAdministradora();
$servicio = new ProductoService($pdo);

$error = null;
$v = ['nombre' => '', 'descripcion' => '', 'precio' => '', 'tallas' => '', 'colores' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidar();
    $v = array_merge($v, array_intersect_key($_POST, $v));
    try {
        $id = $servicio->registrar($v);
        flash("Producto registrado (N.° $id).");
        redirigir('productos_registrar.php');
    } catch (ValidacionException $ex) {
        $error = $ex->getMessage();
    }
}

$productos = $repo->listarProductos();
$titulo = 'Registrar producto';
require __DIR__ . '/partials/cabecera.php';
?>
<h2>Registrar producto</h2>
<?php if ($error): ?><div class="aviso error"><?= e($error) ?></div><?php endif; ?>
<form method="post">
    <?= csrfCampo() ?>
    <label>Nombre</label>
    <input name="nombre" value="<?= e($v['nombre']) ?>" required>
    <label>Descripción</label>
    <input name="descripcion" value="<?= e($v['descripcion']) ?>">
    <label>Precio de venta sugerido (S/)</label>
    <input name="precio" type="number" step="0.01" min="0" value="<?= e($v['precio']) ?>" required>
    <label>Tallas (separadas por coma)</label>
    <input name="tallas" placeholder="S, M, XL" value="<?= e($v['tallas']) ?>" required>
    <label>Colores (separados por coma)</label>
    <input name="colores" placeholder="beige, azul, guinda" value="<?= e($v['colores']) ?>" required>
    <button class="boton" type="submit">Registrar producto</button>
</form>

<h2>Productos registrados</h2>
<?php foreach ($productos as $p): ?>
    <div class="tarjeta">
        <h3><?= e($p['nombre']) ?> — S/ <?= number_format((float)$p['precio_sugerido'], 2) ?></h3>
        <small>Tallas: <?= e(implode(', ', $p['tallas'])) ?> · Colores: <?= e(implode(', ', $p['colores'])) ?></small>
    </div>
<?php endforeach; ?>
<?php require __DIR__ . '/partials/pie.php'; ?>
