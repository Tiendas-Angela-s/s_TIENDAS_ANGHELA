
# Convenciones de programación

## 1. Objetivo

Establecer normas de programación para mantener un código uniforme, legible, seguro y fácil de mantener en el Sistema Integral de Gestión de la Tienda Angela's.

Estas convenciones permiten que los integrantes del equipo sigan criterios comunes durante el desarrollo, la revisión y el mantenimiento del software.

## 2. Convenciones generales de nomenclatura

Los nombres de variables, funciones, clases y otros elementos deben ser descriptivos y mantener un formato uniforme.

| Convención | Aplicación | Ejemplo |
|---|---|---|
| camelCase | Variables, parámetros, funciones y propiedades | precioSugerido, stockMinimo, registrarVenta |
| PascalCase | Clases, componentes, servicios, interfaces y tipos | VentaComponent, StockService |
| kebab-case | Archivos del frontend y rutas URL | venta-form.component.ts, /transferencias |
| SCREAMING_SNAKE_CASE | Constantes globales | PLAZO_MODIFICACION_VENTA_MIN |

### Reglas generales

- Utilizar nombres descriptivos que permitan identificar la finalidad de cada elemento.
- Los métodos y funciones deben comenzar con un verbo que represente su acción.
- Las clases deben utilizar nombres relacionados con su responsabilidad.
- Evitar nombres ambiguos o difíciles de interpretar.
- Mantener correspondencia con los diagramas de clases cuando representen las mismas operaciones.

## 3. Convenciones de frontend

### 3.1. Nomenclatura

Para los elementos desarrollados con Angular y TypeScript, se establecen las siguientes convenciones:

| Elemento | Convención | Ejemplo |
|---|---|---|
| Servicio | NombreService | StockService |
| Archivo de servicio | nombre.service.ts | stock.service.ts |
| Componente | NombreComponent | VentaComponent |
| Archivo de componente | nombre.component.ts | venta.component.ts |
| Guard | NombreGuard | RolGuard |
| Interceptor | nombre.interceptor.ts | auth.interceptor.ts |
| DTO | NombreDto | ProductoDto |
| Observable | Sufijo $ | ventas$ |

- Los pipes, directivas, módulos y resolvers deben utilizar sus sufijos correspondientes.
- Las interfaces y modelos deben representar las entidades definidas en el sistema.
- Los componentes deben mantener sus archivos correspondientes de lógica, interfaz, estilos y pruebas cuando aplique.

### 3.2. Métodos, funciones y propiedades

- Los métodos deben comenzar con verbos, como `registrarVenta`, `confirmarTransferencia` y `calcularTotal`.
- Las clases y propiedades deben utilizar nombres descriptivos, como `Venta`, `precioUnitario` y `fechaHora`.
- Los eventos pueden utilizar nombres como `beforeSave`, `afterSave`, `onLogin` y `onLogout`.
- Cada función debe cumplir una sola responsabilidad.
- Evitar exponer más estado del necesario.
- Para el estado reactivo, preferir Signals cuando corresponda.
- Las variables booleanas deben expresar claramente una condición, como `isLoading`, `hasError` y `canSubmit`.

### 3.3. Manejo de errores

- Toda operación susceptible de fallar debe contar con manejo de errores.
- Centralizar los errores HTTP mediante interceptores cuando corresponda.
- En los flujos RxJS, utilizar `catchError` para manejar y comunicar los errores con el contexto necesario.
- Mostrar mensajes de error claros, comprensibles y neutrales.
- Evitar mostrar información técnica sensible al usuario.

### 3.4. Buenas prácticas

- Priorizar la legibilidad del código.
- Aplicar ESLint y Prettier cuando se utilicen las herramientas correspondientes.
- Utilizar tipado fuerte y evitar el uso innecesario de `any`.
- Considerar interfaces adaptables desde 360 px de ancho.
- Probar las interfaces en dispositivos móviles.
- Aplicar carga diferida de módulos y rutas cuando corresponda.
- Utilizar `trackBy` en listados extensos cuando corresponda.
- No exponer contraseñas ni credenciales en el frontend.
- Realizar las validaciones de seguridad también en el backend.

## 4. Convenciones de backend

### 4.1. Nomenclatura PHP

| Elemento | Convención | Ejemplo |
|---|---|---|
| Clases | PascalCase | VentaController |
| Servicios | PascalCase | StockService |
| Métodos y funciones | camelCase | registrarProducto |
| Variables | camelCase | precioUnitario |
| Constantes | UPPER_CASE_SNAKE_CASE | STOCK_MINIMO |
| Rutas | kebab-case y plural | /ventas, /transferencias |
| Migraciones | snake_case descriptivo | create_ventas_table.php |

Los nombres de clases y métodos relacionados con el dominio deben mantener correspondencia con los diagramas de clases.

### 4.2. Autoloading PSR-4

Cuando se utilice PSR-4:

- Mantener los namespaces coherentes con la ubicación de las clases.
- Declarar explícitamente la visibilidad de métodos y propiedades mediante `public`, `protected` o `private`.
- Utilizar tipos de parámetros y retorno, como `void`, `bool` y `array`.
- Mantener una nomenclatura uniforme entre las clases y sus namespaces.

### 4.3. Responsabilidad única

Cada clase debe cumplir una responsabilidad claramente definida.

| Tipo de servicio | Responsabilidad |
|---|---|
| Autenticación | Gestionar autenticación, sesiones e intentos de acceso. |
| Productos | Registrar, editar y consultar información de productos. |
| Stock | Consultar y actualizar existencias por puesto y variante. |
| Transferencias | Gestionar transferencias y movimientos entre puestos. |
| Ventas | Gestionar el registro y confirmación de ventas. |
| Caja | Gestionar aperturas, cierres y movimientos de caja. |
| Alertas | Registrar y comunicar situaciones que requieren atención. |
| Reportes | Generar información para consulta y análisis. |
| Actividades | Registrar las operaciones relevantes de los usuarios. |

Las responsabilidades deben mantenerse separadas para facilitar el mantenimiento y las pruebas.

### 4.4. Paginación

Cuando se implementen listados paginados:

- Utilizar los parámetros `page` y `perPage`.
- Considerar que la numeración de páginas comienza en 1.
- Calcular el desplazamiento mediante `(page - 1) * perPage`.
- Incluir información de paginación con los campos `page`, `perPage` y `total`.

## 5. Transacciones, validaciones y respuestas HTTP

### 5.1. Transacciones

Las operaciones que modifican varias tablas relacionadas deben ejecutarse dentro de una transacción de base de datos.

- Iniciar una transacción antes de realizar modificaciones relacionadas.
- Confirmar los cambios mediante `commit` cuando todas las operaciones sean correctas.
- Ejecutar `rollback` cuando ocurra un error.
- Evitar actualizaciones parciales que generen inconsistencias.

Las transacciones son especialmente importantes en:

- Confirmación de ventas y actualización de stock.
- Transferencias de mercadería entre puestos.
- Registro de movimientos y cierre de caja.

### 5.2. Validación de datos

- Validar los parámetros y campos obligatorios.
- Verificar que las cantidades y valores sean válidos.
- Comprobar la disponibilidad de stock antes de realizar operaciones que lo requieran.
- Evitar operaciones que produzcan cantidades negativas no permitidas.
- Verificar los permisos del usuario antes de ejecutar operaciones restringidas.
- Utilizar consultas SQL parametrizadas.
- Controlar los errores de las operaciones críticas.
- Utilizar excepciones con mensajes claros cuando falten parámetros o existan condiciones inválidas.

### 5.3. Códigos de respuesta HTTP

Las respuestas HTTP deben utilizar códigos adecuados para comunicar el resultado de las solicitudes.

| Código | Significado | Aplicación en el sistema |
|---|---|---|
| 401 | No autenticado | Un usuario intenta acceder a una operación sin autenticarse correctamente. |
| 403 | Acceso prohibido | Una vendedora intenta ejecutar una operación exclusiva de la administradora. |
| 404 | Recurso no encontrado | Se solicita un producto que no existe. |
| 409 | Conflicto | Se intenta iniciar una sesión simultánea no permitida. |
| 422 | Datos inválidos | Se envían datos que no cumplen las validaciones de una operación. |

Los mensajes de error deben ser claros y no exponer información interna del sistema.

Cuando ocurra un error en una operación crítica, se debe controlar la excepción y revertir los cambios correspondientes.

## 6. Seguridad

### 6.1. Autenticación y autorización

- Aplicar controles de acceso según los roles y permisos definidos.
- Verificar los permisos del usuario en cada operación restringida.
- Impedir que usuarios sin autorización ejecuten funciones administrativas.
- Controlar las sesiones simultáneas según las reglas del negocio.
- Registrar los intentos de acceso fallidos cuando corresponda.
- No utilizar las credenciales de la base de datos como mecanismo de autenticación de los usuarios del sistema.

### 6.2. Protección de información

- Almacenar las contraseñas mediante mecanismos seguros de hash.
- No almacenar contraseñas en texto plano.
- Evitar la exposición de credenciales y cadenas de conexión.
- No incluir datos personales reales en los archivos de prueba.
- Utilizar consultas parametrizadas para prevenir inyección SQL.
- Utilizar HTTPS en producción.
- Evitar mostrar información sensible en las respuestas del sistema.

### 6.3. Auditoría y trazabilidad

- Registrar las operaciones relevantes realizadas por los usuarios.
- Conservar la identificación del usuario y del puesto cuando corresponda.
- Registrar la fecha y hora de las operaciones.
- Mantener información que permita identificar modificaciones relevantes.
- Registrar las operaciones inusuales para su posterior revisión.

## 7. Convenciones de base de datos

### 7.1. Nomenclatura de objetos

Los nombres de los objetos de base de datos deben ser descriptivos, uniformes y representar claramente su función.

El documento de convenciones establece los siguientes formatos de nomenclatura:

| Objeto | Convención | Ejemplo |
|---|---|---|
| Base de datos | AliasAplicacion_SufijoBaseDatos | ANG_DATA |
| Tabla | ANG_NombreTabla | ANG_Producto |
| Campo | UpperCamelCase | IdProducto, PrecioSugerido |
| Clave primaria | PK_NombreTabla | PK_Producto |
| Clave foránea | FK_TablaOrigen_TablaReferenciada | FK_VentaDetalle_Venta |
| Restricción única | UQ_NombreTabla_NombreUnique | UQ_Producto_Codigo |
| Valor por defecto | DF_NombreTabla_NombreColumna | DF_Venta_FechaHora |
| Restricción CHECK | CH_NombreTabla_NombreCheck | CH_Producto_PrecioRango |
| Índice clustered | IC_NombreTabla_NombreIndice | IC_Venta_FechaHora |
| Índice no clustered | ID_NombreTabla_NombreIndice | ID_Stock_Variante_Puesto |
| Vista | ANG_VW_NombreVista | ANG_VW_StockPorPuesto |
| Procedimiento almacenado | SP_ANG_NombreTabla_Accion | SP_ANG_Transferencia_Confirmar |
| Función | FN_ANG_NombreTabla_Accion | FN_ANG_Venta_CalcularTotal |
| Trigger | TR_ANG_NombreTabla_Accion | TR_ANG_Stock_Actualizar |
| Tipo de dato de usuario | DT_NombreTipoDato | DT_Monto |

Estos formatos corresponden a las convenciones documentadas y no implican que todos los objetos de ejemplo estén implementados.

### 7.2. Reglas de nomenclatura

- Utilizar nombres descriptivos y sin espacios.
- Mantener una longitud de 1 a 30 caracteres para los identificadores, según la convención documentada.
- Utilizar el prefijo `Id` en campos identificadores, como `IdVenta` e `IdPuesto`.
- Utilizar nombres de tablas en singular.
- Identificar las tablas temporales mediante el sufijo `Temp` cuando corresponda.
- Mantener una nomenclatura consistente entre tablas relacionadas.

### 7.3. Integridad de datos

- Definir claves primarias para identificar los registros.
- Utilizar claves foráneas para establecer relaciones.
- Definir restricciones de unicidad cuando correspondan.
- Evitar registros duplicados que incumplan las reglas del negocio.
- Utilizar restricciones `CHECK` o tablas catálogo para representar valores controlados cuando corresponda.
- Utilizar tipos numéricos decimales con dos decimales para importes monetarios.
- No utilizar tipos de punto flotante para almacenar importes monetarios.

### 7.4. Seguridad de acceso

- Controlar los permisos de acceso a los datos.
- Proteger las credenciales de conexión.
- Utilizar roles y permisos para restringir operaciones.
- Mantener las contraseñas protegidas mediante hash.
- Evitar accesos no autorizados a la información.

### 7.5. Auditoría de datos

Cuando se requiera mantener trazabilidad sobre los registros, considerar los campos de auditoría definidos en el modelo:

| Campo | Finalidad |
|---|---|
| Activo | Identificar si el registro está disponible. |
| Eliminado | Identificar si el registro fue eliminado lógicamente. |
| UsuarioIngreso | Identificar al usuario que creó el registro. |
| UsuarioActualizo | Identificar al usuario que modificó el registro. |
| FechaIngreso | Registrar la fecha y hora de creación. |
| FechaActualizo | Registrar la fecha y hora de modificación. |
| EquipoIngreso | Identificar el equipo o puesto desde el que se creó el registro. |
| EquipoActualizo | Identificar el equipo o puesto desde el que se modificó el registro. |

Estos campos permiten conservar información sobre las modificaciones realizadas y facilitar el seguimiento de las operaciones.

## 8. Buenas prácticas de programación y mantenimiento

- Mantener el código claro, legible y uniforme.
- Aplicar las convenciones de nomenclatura correspondientes.
- Evitar duplicación de código.
- Mantener una sola responsabilidad por función o clase.
- Utilizar nombres descriptivos para facilitar la comprensión.
- Controlar los errores y validar los datos antes de procesarlos.
- Proteger la información sensible y las credenciales.
- Mantener consistencia entre las reglas del negocio y el comportamiento del sistema.
- Respetar las convenciones de seguridad y acceso a datos.
- Verificar el cumplimiento de los estándares durante las revisiones de código.
- Mantener las pruebas relacionadas con las funcionalidades desarrolladas.
- Facilitar el mantenimiento del sistema mediante código comprensible y organizado.
