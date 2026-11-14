<?php
// CU-032 Autenticar usuario
require __DIR__ . '/_init.php';

if (usuarioActual() !== null) {
    redirigir(puestoActual() ? 'index.php' : 'puesto.php');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidar();
    try {
        $usuario = $auth->autenticar(trim((string)($_POST['usuario'] ?? '')), (string)($_POST['password'] ?? ''));
        session_regenerate_id(true);
        $_SESSION['usuario'] = $usuario;
        redirigir('puesto.php');
    } catch (AuthException $ex) {
        $error = $ex->getMessage();
    }
}

$titulo = 'Iniciar sesión';
require __DIR__ . '/partials/cabecera.php';
?>
<h1 class="marca">Angela's</h1>
<?php if ($error): ?><div class="aviso error"><?= e($error) ?></div><?php endif; ?>
<form method="post" autocomplete="off">
    <?= csrfCampo() ?>
    <input name="usuario" placeholder="Nombre de usuario" required autofocus>
    <input name="password" type="password" placeholder="Contraseña" required style="margin-top:10px">
    <button class="boton" type="submit">Continuar</button>
</form>
<?php require __DIR__ . '/partials/pie.php'; ?>
