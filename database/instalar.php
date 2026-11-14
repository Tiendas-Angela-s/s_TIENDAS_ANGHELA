<?php
// Uso:  php database/instalar.php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
$config = require __DIR__ . '/../config/config.php';

$pdo = Database::conectar('sqlite:' . $config['db_archivo']);
Database::crearEsquema($pdo);
Database::sembrarDemo($pdo);

echo "Base de datos lista en {$config['db_archivo']}\n";
echo "Usuarios demo: admin/admin123 (Administradora), vendedora1/vende123, vendedora2/vende123\n";
