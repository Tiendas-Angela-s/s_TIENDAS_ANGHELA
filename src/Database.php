<?php
declare(strict_types=1);

final class Database
{
    public static function conectar(string $dsn): PDO
    {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        if (str_starts_with($dsn, 'sqlite:')) {
            $pdo->setAttribute(PDO::ATTR_TIMEOUT, 10);   // espera si otro proceso está escribiendo
            $pdo->exec('PRAGMA foreign_keys = ON');
        }
        return $pdo;
    }

    public static function crearEsquema(PDO $pdo): void
    {
        $pdo->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
    }

    /**
     * Datos de demostración: 3 puestos, 3 usuarios y 2 productos.
     * IDs resultantes: puestos 1..3, usuarios 1=admin 2=vendedora1 3=vendedora2, productos 1..2.
     * Cambien las contraseñas antes de usarlo fuera de clase.
     */
    public static function sembrarDemo(PDO $pdo): void
    {
        if ((int)$pdo->query('SELECT COUNT(*) FROM puestos')->fetchColumn() === 0) {
            $st = $pdo->prepare('INSERT INTO puestos (numero, nombre) VALUES (:n, :nombre)');
            foreach ([1, 2, 3] as $n) {
                $st->execute([':n' => $n, ':nombre' => "Puesto $n"]);
            }
        }

        if ((int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() === 0) {
            $st = $pdo->prepare(
                'INSERT INTO usuarios (usuario, nombre, password_hash, rol) VALUES (:u, :n, :h, :r)'
            );
            $usuarios = [
                ['admin',      'Administradora', 'admin123', 'ADMINISTRADORA'],
                ['vendedora1', 'Vendedora 1',    'vende123', 'VENDEDORA'],
                ['vendedora2', 'Vendedora 2',    'vende123', 'VENDEDORA'],
            ];
            foreach ($usuarios as [$u, $n, $pass, $rol]) {
                $st->execute([':u' => $u, ':n' => $n, ':h' => password_hash($pass, PASSWORD_DEFAULT), ':r' => $rol]);
            }
        }

        if ((int)$pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn() === 0) {
            $st = $pdo->prepare(
                'INSERT INTO productos (nombre, descripcion, precio_sugerido, tallas, colores)
                 VALUES (:n, :d, :p, :t, :c)'
            );
            $st->execute([':n' => 'Polo satinado', ':d' => 'Polo de satén', ':p' => 35.00,
                          ':t' => 'S,M,XL', ':c' => 'beige,azul,guinda']);
            $st->execute([':n' => 'Polo encaje', ':d' => 'Polo con encaje', ':p' => 45.00,
                          ':t' => 'S,L,XL', ':c' => 'negro,rojo,guinda']);
        }
    }
}