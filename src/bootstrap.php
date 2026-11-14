<?php
declare(strict_types=1);

date_default_timezone_set('America/Lima');

require_once __DIR__ . '/Excepciones.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Texto.php';
require_once __DIR__ . '/auth/AuthService.php';
require_once __DIR__ . '/productos/ProductoService.php';
require_once __DIR__ . '/inventario/TransferenciaModel.php';
require_once __DIR__ . '/inventario/InventarioRepository.php';
require_once __DIR__ . '/inventario/IngresoService.php';
require_once __DIR__ . '/inventario/TransferenciaService.php';
