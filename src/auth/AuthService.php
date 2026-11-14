<?php
declare(strict_types=1);

/** CU-032 Autenticar usuario  +  CU-033 Iniciar sesión en un puesto. */
final class AuthService
{
    public function __construct(private PDO $pdo)
    {
    }

    /** CU-032. Devuelve los datos del usuario (sin el hash) o lanza AuthException (A1). */
    public function autenticar(string $usuario, string $password): array
    {
        $st = $this->pdo->prepare('SELECT id, usuario, nombre, password_hash, rol, activo FROM usuarios WHERE usuario = :u');
        $st->execute([':u' => $usuario]);
        $fila = $st->fetch();
        $st->closeCursor();

        if ($fila === false || (int)$fila['activo'] !== 1 || !password_verify($password, $fila['password_hash'])) {
            $this->registrarIntentoFallido($usuario);
            throw new AuthException('Usuario o contraseña incorrectos.');
        }

        return [
            'id'      => (int)$fila['id'],
            'usuario' => $fila['usuario'],
            'nombre'  => $fila['nombre'],
            'rol'     => $fila['rol'],
        ];
    }

    public function puestosActivos(): array
    {
        return $this->pdo->query('SELECT id, numero, nombre FROM puestos WHERE activo = 1 ORDER BY numero')->fetchAll();
    }

    /** CU-033. Asocia la sesión al puesto; A1 si ya tiene una sesión activa en otro. */
    public function iniciarSesionEnPuesto(int $usuarioId, int $puestoId): array
    {
        $st = $this->pdo->prepare('SELECT id, numero, nombre FROM puestos WHERE id = :id AND activo = 1');
        $st->execute([':id' => $puestoId]);
        $puesto = $st->fetch();
        $st->closeCursor();
        if ($puesto === false) {
            throw new AuthException('El puesto seleccionado no existe o está inactivo.', 'PUESTO');
        }

        $activa = $this->sesionActiva($usuarioId);
        if ($activa !== null) {
            throw new AuthException(
                'Ya tiene una sesión activa en ' . $activa['puesto'] . '. Ciérrela antes de iniciar una nueva.',
                'SESION_ACTIVA'
            );
        }

        $in = $this->pdo->prepare('INSERT INTO sesiones_puesto (usuario_id, puesto_id, inicio) VALUES (:u, :p, :f)');
        $in->execute([':u' => $usuarioId, ':p' => $puestoId, ':f' => date('Y-m-d H:i:s')]);

        return ['id' => (int)$puesto['id'], 'numero' => (int)$puesto['numero'], 'nombre' => $puesto['nombre']];
    }

    public function sesionActiva(int $usuarioId): ?array
    {
        $st = $this->pdo->prepare(
            'SELECT s.id, p.nombre AS puesto
             FROM sesiones_puesto s JOIN puestos p ON p.id = s.puesto_id
             WHERE s.usuario_id = :u AND s.fin IS NULL
             ORDER BY s.id DESC LIMIT 1'
        );
        $st->execute([':u' => $usuarioId]);
        $fila = $st->fetch();
        $st->closeCursor();
        return $fila === false ? null : $fila;
    }

    /** CU-033 A2: cerrar una sesión que quedó abierta por error, validando la identidad. */
    public function cerrarSesionAnterior(int $usuarioId, string $password): void
    {
        $st = $this->pdo->prepare('SELECT password_hash FROM usuarios WHERE id = :id');
        $st->execute([':id' => $usuarioId]);
        $hash = $st->fetchColumn();
        $st->closeCursor();

        if ($hash === false || !password_verify($password, (string)$hash)) {
            throw new AuthException('La contraseña no es correcta.');
        }
        $this->cerrarSesiones($usuarioId);
    }

    public function cerrarSesiones(int $usuarioId): void
    {
        $st = $this->pdo->prepare('UPDATE sesiones_puesto SET fin = :f WHERE usuario_id = :u AND fin IS NULL');
        $st->execute([':f' => date('Y-m-d H:i:s'), ':u' => $usuarioId]);
    }

    private function registrarIntentoFallido(string $usuario): void
    {
        $st = $this->pdo->prepare('INSERT INTO intentos_fallidos (usuario, fecha) VALUES (:u, :f)');
        $st->execute([':u' => Texto::subcadena($usuario, 0, 60), ':f' => date('Y-m-d H:i:s')]);
    }
}