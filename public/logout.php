<?php
// CU-034 (mínimo): cerrar la sesión en el puesto
require __DIR__ . '/_init.php';
if ($u = usuarioActual()) {
    $auth->cerrarSesiones($u['id']);
}
$_SESSION = [];
session_destroy();
redirigir('login.php');
