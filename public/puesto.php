<?php
// CU-033 Iniciar sesión en un puesto
require __DIR__ . '/_init.php';
$usuario = requerirLogin();

if (puestoActual() !== null) {
    redirigir('index.php');
}

$error = null;
$sesionActiva = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidar();
    try {
        if (($_POST['accion'] ?? '') === 'cerrar_anterior') {          // A2
            $auth->cerrarSesionAnterior($usuario['id'], (string)($_POST['password'] ?? ''));
            flash('Se cerró la sesión anterior. Ahora seleccione su puesto.');
            redirigir('puesto.php');
        }
        $_SESSION['puesto'] = $auth->iniciarSesionEnPuesto($usuario['id'], (int)($_POST['puesto'] ?? 0));
        redirigir('index.php');
    } catch (AuthException $ex) {
        $error = $ex->getMessage();
        if ($ex->codigo === 'SESION_ACTIVA') {                           // A1
            $sesionActiva = $auth->sesionActiva($usuario['id']);
        }
    }
}

$puestos = $auth->puestosActivos();
$titulo = 'Seleccione su puesto';
require __DIR__ . '/partials/cabecera.php';
?>
<h2>Seleccione el puesto en el que trabajará</h2>
<?php if ($error): ?><div class="aviso error"><?= e($error) ?></div><?php endif; ?>

<?php if ($sesionActiva): ?>
    <form method="post" class="tarjeta">
        <?= csrfCampo() ?>
        <input type="hidden" name="accion" value="cerrar_anterior">
        <strong>¿La sesión anterior quedó abierta por error?</strong>
        <label for="pw">Confirme su contraseña para cerrarla</label>
        <input id="pw" name="password" type="password" required>
        <button class="boton chico" type="submit">Cerrar sesión anterior</button>
    </form>
<?php endif; ?>

<form method="post">
    <?= csrfCampo() ?>
    <?php foreach ($puestos as $p): ?>
        <label class="tarjeta opcion">
            <input type="radio" name="puesto" value="<?= (int)$p['id'] ?>" required>
            <span><?= e($p['nombre']) ?></span>
        </label>
    <?php endforeach; ?>
    <button class="boton" type="submit">Continuar</button>
</form>
<?php require __DIR__ . '/partials/pie.php'; ?>
