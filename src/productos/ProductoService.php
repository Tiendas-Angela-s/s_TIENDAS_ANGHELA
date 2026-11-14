<?php
declare(strict_types=1);

/** CU-001 Registrar producto (versión mínima: desde cero, con tallas y colores). */
final class ProductoService
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return int id del producto registrado */
    public function registrar(array $d): int
    {
        $nombre      = trim((string)($d['nombre'] ?? ''));
        $descripcion = trim((string)($d['descripcion'] ?? ''));
        $precioTexto = trim((string)($d['precio'] ?? ''));
        $tallas      = self::normalizarLista((string)($d['tallas'] ?? ''), true);
        $colores     = self::normalizarLista((string)($d['colores'] ?? ''), false);

        // A1: información obligatoria
        $faltan = [];
        if ($nombre === '')      { $faltan[] = 'nombre'; }
        if ($precioTexto === '') { $faltan[] = 'precio de venta sugerido'; }
        if (!$tallas)            { $faltan[] = 'tallas'; }
        if (!$colores)           { $faltan[] = 'colores'; }
        if ($faltan) {
            throw new ValidacionException('Complete los campos obligatorios: ' . implode(', ', $faltan) . '.');
        }
        if (!is_numeric($precioTexto) || (float)$precioTexto < 0) {
            throw new ValidacionException('El precio debe ser un número mayor o igual a 0.');
        }

        // A2: producto duplicado
        $st = $this->pdo->prepare('SELECT id FROM productos WHERE LOWER(nombre) = LOWER(:n)');
        $st->execute([':n' => $nombre]);
        $existe = $st->fetchColumn();
        $st->closeCursor();
        if ($existe !== false) {
            throw new ValidacionException('El producto «' . $nombre . '» ya está registrado. Verifique la información.');
        }

        $in = $this->pdo->prepare(
            'INSERT INTO productos (nombre, descripcion, precio_sugerido, tallas, colores)
             VALUES (:n, :d, :p, :t, :c)'
        );
        $in->execute([
            ':n' => $nombre, ':d' => $descripcion, ':p' => (float)$precioTexto,
            ':t' => implode(',', $tallas), ':c' => implode(',', $colores),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /** "s, m,M ,xl" -> ['S','M','XL'] (tallas en mayúsculas, colores en minúsculas, sin repetidos). */
    public static function normalizarLista(string $texto, bool $mayusculas): array
    {
        $items = array_filter(array_map('trim', explode(',', $texto)), fn($x) => $x !== '');
        $items = array_map(fn($x) => $mayusculas ? Texto::mayusculas($x) : Texto::minusculas($x), $items);
        return array_values(array_unique($items));
    }
}
