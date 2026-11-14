<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
$config = require __DIR__ . '/../config/config.php';

session_start();

if (!is_file($config['db_archivo'])) {
    http_response_code(500);
    exit('Base de datos no instalada. Ejecute en la terminal: php database/instalar.php');
}

$pdo  = Database::conectar('sqlite:' . $config['db_archivo']);
$repo = new InventarioRepository($pdo);
$auth = new AuthService($pdo);

// ---------------------------------------------------------------- helpers de las pantallas

function e(?string $texto): string
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function usuarioActual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function puestoActual(): ?array
{
    return $_SESSION['puesto'] ?? null;
}

function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function requerirLogin(): array
{
    $u = usuarioActual();
    if ($u === null) {
        redirigir('login.php');
    }
    return $u;
}

/** Usuario autenticado Y con sesión iniciada en un puesto (precondición de los CU de inventario). */
function requerirPuesto(): array
{
    $u = requerirLogin();
    if (puestoActual() === null) {
        redirigir('puesto.php');
    }
    return $u;
}

function requerirAdministradora(): array
{
    $u = requerirPuesto();
    if ($u['rol'] !== 'ADMINISTRADORA') {
        http_response_code(403);
        exit('Acceso restringido a la administradora.');
    }
    return $u;
}

function csrfCampo(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function csrfValidar(): void
{
    if (!hash_equals((string)($_SESSION['csrf'] ?? ''), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Solicitud inválida (token de seguridad).');
    }
}

/** flash('mensaje') guarda; flash() sin argumentos lee y borra. */
function flash(?string $mensaje = null, string $tipo = 'ok'): ?array
{
    if ($mensaje !== null) {
        $_SESSION['flash'] = [$tipo, $mensaje];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
