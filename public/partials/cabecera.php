<?php
// Variables esperadas: $titulo (string), $sinNav (bool, opcional)
$u = usuarioActual();
$p = puestoActual();
$aviso = flash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · Angela's</title>
    <link rel="stylesheet" href="assets/estilos.css">
</head>
<body>
<header class="barra">
    <span class="marca-mini">Angela's</span>
    <?php if ($u): ?>
        <span class="sesion"><?= e($u['nombre']) ?><?= $p ? ' · ' . e($p['nombre']) : '' ?></span>
    <?php endif; ?>
</header>
<main class="contenedor">
<?php if ($aviso): ?>
    <div class="aviso <?= $aviso[0] === 'ok' ? 'ok' : 'error' ?>"><?= e($aviso[1]) ?></div>
<?php endif; ?>
