</main>
<?php if (usuarioActual() && puestoActual() && empty($sinNav)):
    $actual = basename($_SERVER['SCRIPT_NAME']);
    $enlaces = ['index.php' => 'Inicio', 'stock.php' => 'Stock', 'transferencia.php' => 'Transferir', 'recepcion.php' => 'Recepción'];
    if (usuarioActual()['rol'] === 'ADMINISTRADORA') {
        $enlaces['productos_registrar.php'] = 'Productos';
        $enlaces['ingreso.php'] = 'Ingreso';
    }
    $enlaces['logout.php'] = 'Salir';
?>
<nav class="nav">
    <?php foreach ($enlaces as $url => $texto): ?>
        <a href="<?= e($url) ?>" class="<?= $actual === $url ? 'activo' : '' ?>"><?= e($texto) ?></a>
    <?php endforeach; ?>
</nav>
<?php endif; ?>
</body>
</html>
