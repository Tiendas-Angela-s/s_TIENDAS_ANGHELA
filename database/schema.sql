-- Esquema mínimo para llegar al CU-011 (SQLite).
-- Si migran a MySQL: INTEGER PRIMARY KEY AUTOINCREMENT -> INT AUTO_INCREMENT PRIMARY KEY.

CREATE TABLE IF NOT EXISTS usuarios (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario       TEXT    NOT NULL UNIQUE,
    nombre        TEXT    NOT NULL,
    password_hash TEXT    NOT NULL,
    rol           TEXT    NOT NULL CHECK (rol IN ('ADMINISTRADORA','VENDEDORA')),
    activo        INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS intentos_fallidos (
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario TEXT NOT NULL,
    fecha   TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS puestos (
    id     INTEGER PRIMARY KEY AUTOINCREMENT,
    numero INTEGER NOT NULL UNIQUE,
    nombre TEXT    NOT NULL,
    activo INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS sesiones_puesto (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL REFERENCES usuarios(id),
    puesto_id  INTEGER NOT NULL REFERENCES puestos(id),
    inicio     TEXT    NOT NULL,
    fin        TEXT    NULL
);

CREATE TABLE IF NOT EXISTS productos (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre          TEXT NOT NULL UNIQUE,
    descripcion     TEXT NOT NULL DEFAULT '',
    precio_sugerido REAL NOT NULL CHECK (precio_sugerido >= 0),
    tallas          TEXT NOT NULL,   -- lista separada por comas: "S,M,XL"
    colores         TEXT NOT NULL    -- lista separada por comas: "beige,azul"
);

-- Stock por puesto + producto + talla + color. El CHECK es la red de seguridad: nunca negativo.
CREATE TABLE IF NOT EXISTS stock (
    puesto_id   INTEGER NOT NULL REFERENCES puestos(id),
    producto_id INTEGER NOT NULL REFERENCES productos(id),
    talla       TEXT    NOT NULL,
    color       TEXT    NOT NULL,
    cantidad    INTEGER NOT NULL CHECK (cantidad >= 0),
    PRIMARY KEY (puesto_id, producto_id, talla, color)
);

CREATE TABLE IF NOT EXISTS transferencias (
    id                     INTEGER PRIMARY KEY AUTOINCREMENT,
    producto_id            INTEGER NOT NULL REFERENCES productos(id),
    talla                  TEXT    NOT NULL,
    color                  TEXT    NOT NULL,
    cantidad               INTEGER NOT NULL CHECK (cantidad > 0),
    puesto_origen_id       INTEGER NOT NULL REFERENCES puestos(id),
    puesto_destino_id      INTEGER NOT NULL REFERENCES puestos(id),
    responsable_origen_id  INTEGER NOT NULL REFERENCES usuarios(id),
    responsable_destino_id INTEGER NOT NULL REFERENCES usuarios(id),
    estado                 TEXT    NOT NULL CHECK (estado IN ('PENDIENTE','CONFIRMADA','CONFIRMADA_CON_DIFERENCIA')),
    cantidad_recibida      INTEGER NULL CHECK (cantidad_recibida IS NULL OR cantidad_recibida >= 0),
    observacion            TEXT    NULL,
    creado_por             INTEGER NOT NULL REFERENCES usuarios(id),
    fecha_creacion         TEXT    NOT NULL,
    fecha_confirmacion     TEXT    NULL,
    CHECK (puesto_origen_id <> puesto_destino_id)
);

-- Historial de movimientos (lo usan CU-008 y CU-011; la consulta CU-013 queda fuera del mínimo).
CREATE TABLE IF NOT EXISTS movimientos_inventario (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    fecha       TEXT    NOT NULL,
    tipo        TEXT    NOT NULL CHECK (tipo IN ('INGRESO','AJUSTE','EGRESO_VENTA','TRANSFERENCIA_SALIDA','TRANSFERENCIA_ENTRADA')),
    puesto_id   INTEGER NOT NULL REFERENCES puestos(id),
    producto_id INTEGER NOT NULL REFERENCES productos(id),
    talla       TEXT    NOT NULL,
    color       TEXT    NOT NULL,
    cantidad    INTEGER NOT NULL,
    usuario_id  INTEGER NOT NULL REFERENCES usuarios(id),
    referencia  TEXT    NULL
);