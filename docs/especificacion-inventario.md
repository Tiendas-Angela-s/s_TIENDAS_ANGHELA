
# Especificación funcional del CU-011

## 1. Identificación del caso de uso

| Elemento | Descripción |
|---|---|
| Código | CU-011 |
| Nombre | Registrar y confirmar transferencia entre puestos |
| Módulo | Movimiento de mercadería |
| Actores principales | Administradora y Vendedora |
| Necesidad relacionada | N-004 |
| Requisitos funcionales | FRQ-17, FRQ-18, FRQ-19 |
| Requisitos no funcionales relacionados | NFRQ-03, NFRQ-05, NFRQ-06, NFRQ-08 |

### Objetivo

Trasladar productos de un puesto a otro, registrando los responsables de la operación y confirmando la recepción de la mercadería.

## 2. Necesidad del negocio

### N-004: Control de traslado de mercadería

La administradora y la vendedora necesitan controlar el traslado de mercadería entre puestos de venta para mantener correctamente distribuido y actualizado el stock de cada puesto.

## 3. Requisitos relacionados

### 3.1. Requisitos funcionales

| Código | Funcionalidad | Relación con CU-011 |
|---|---|---|
| FRQ-17 | Registrar transferencia | Permitir registrar el traslado de mercadería entre puestos. |
| FRQ-18 | Confirmar transferencia | Permitir confirmar la recepción de la mercadería transferida. |
| FRQ-19 | Actualizar stock por transferencia | Actualizar el stock de los puestos involucrados cuando se confirme la transferencia. |

El requisito FRQ-21, correspondiente a la consulta del historial de transferencias, también está relacionado con la necesidad N-004, aunque no forma parte de los tres requisitos principales asociados directamente al CU-011.

### 3.2. Requisitos no funcionales

| Código | Aspecto | Verificación relacionada |
|---|---|---|
| NFRQ-03 | Mensajes de error | Comprobar que los errores se comuniquen mediante mensajes claros. |
| NFRQ-05 | Control de acceso | Verificar que los usuarios tengan los permisos correspondientes. |
| NFRQ-06 | Trazabilidad | Comprobar el registro de las operaciones y sus responsables. |
| NFRQ-08 | Tiempo de respuesta | Verificar el tiempo de respuesta establecido para las operaciones. |

## 4. Especificación del caso de uso

### 4.1. Actores principales

- Administradora.
- Vendedora.

### 4.2. Precondiciones

- El usuario debe estar autenticado en el sistema.
- El usuario debe contar con permisos para gestionar el inventario.

### 4.3. Flujo principal

1. El usuario ingresa al módulo de movimiento de mercadería y selecciona la opción "Registrar y confirmar transferencia entre puestos".
2. El sistema solicita el puesto de origen y el de destino.
3. El usuario selecciona los productos, tallas y cantidades a transferir.
4. El usuario indica los responsables del origen y del destino.
5. El usuario confirma la transferencia.
6. El sistema valida que el origen tenga stock suficiente y registra la transferencia como pendiente.
7. El responsable del destino verifica la mercadería y confirma la recepción.
8. El sistema descuenta el stock del origen, lo suma al destino y registra el movimiento.

### 4.4. Flujos alternativos

**A1. Stock insuficiente**

Si el stock de origen es insuficiente, el sistema informa la cantidad disponible.

**A2. Puestos de origen y destino iguales**

Si el origen y el destino son el mismo puesto, el sistema solicita corregir la selección.

**A3. Diferencia en la recepción**

Si lo recibido difiere de lo enviado, el responsable registra la diferencia y el sistema deja constancia.

**A4. Error en la actualización de stock**

Si falla la actualización de stock, el sistema revierte los cambios e informa el error.

### 4.5. Postcondición

La transferencia queda confirmada y el stock de ambos puestos actualizado.

## 5. Reglas de negocio verificables

Las siguientes condiciones se desprenden del comportamiento documentado para el CU-011:

| ID | Condición verificable | Relación |
|---|---|---|
| RN-01 | El usuario debe estar autenticado y autorizado para gestionar inventario. | Precondiciones, NFRQ-05 |
| RN-02 | La transferencia debe identificar los puestos de origen y destino. | Flujo principal |
| RN-03 | Se deben identificar los productos, tallas y cantidades a transferir. | Flujo principal |
| RN-04 | Se deben registrar los responsables de origen y destino. | Flujo principal |
| RN-05 | El puesto de origen debe disponer de stock suficiente. | A1 |
| RN-06 | El origen y el destino deben ser puestos diferentes. | A2 |
| RN-07 | La transferencia debe registrarse inicialmente como pendiente. | Flujo principal |
| RN-08 | La recepción debe ser confirmada por el responsable del destino. | Flujo principal |
| RN-09 | Al confirmar la recepción, se actualiza el stock de origen y destino. | FRQ-19 |
| RN-10 | Las diferencias entre lo enviado y recibido deben registrarse. | A3 |
| RN-11 | Si falla la actualización, los cambios deben revertirse. | A4 |
| RN-12 | El movimiento debe quedar registrado. | Flujo principal, NFRQ-06 |

Los identificadores RN-01 a RN-12 se utilizan únicamente para organizar las verificaciones de QA.

## 6. Definition of Ready (DoR)

La Definition of Ready permite comprobar que la funcionalidad cuenta con información suficiente antes de iniciar su implementación.

### 6.1. Verificación del CU-011

| Criterio | Condición | Evidencia documental | Estado |
|---|---|---|---|
| DoR-01 | Requisito identificado | CU-011 está asociado a FRQ-17, FRQ-18 y FRQ-19. | Cumple |
| DoR-02 | Necesidad identificada | Los requisitos se relacionan con N-004. | Cumple |
| DoR-03 | Actores definidos | Se identifican Administradora y Vendedora. | Cumple |
| DoR-04 | Caso de uso relacionado | Existe la especificación CU-011. | Cumple |
| DoR-05 | Comportamiento esperado | Están documentados objetivo, precondiciones, flujo principal, alternativas A1-A4 y postcondición. | Cumple |
| DoR-06 | Restricciones identificadas | Se contemplan permisos, validación de stock, puestos diferentes y tratamiento de errores. | Cumple |

### 6.2. Resultado de la evaluación documental

El CU-011 cuenta con los elementos documentales correspondientes a los seis criterios DoR.

Por lo tanto, se considera preparado desde el punto de vista de la especificación funcional.

Esta evaluación no demuestra que la funcionalidad esté implementada ni que sus pruebas se hayan ejecutado satisfactoriamente.

## 7. Criterios de aceptación para las pruebas

Los siguientes criterios se utilizarán para diseñar y evaluar los escenarios del CU-011.

| ID | Criterio de aceptación |
|---|---|
| CA-011-01 | Se registra una transferencia válida entre dos puestos diferentes. |
| CA-011-02 | La transferencia queda pendiente hasta que se confirme la recepción. |
| CA-011-03 | Se rechaza una transferencia cuando el stock de origen es insuficiente y se informa la cantidad disponible. |
| CA-011-04 | Se solicita corregir la selección cuando origen y destino son iguales. |
| CA-011-05 | Se registra cualquier diferencia detectada entre lo enviado y lo recibido. |
| CA-011-06 | La confirmación actualiza correctamente el stock de ambos puestos. |
| CA-011-07 | Si falla la actualización de stock, se revierten los cambios. |
| CA-011-08 | El movimiento conserva la información necesaria para su trazabilidad. |
| CA-011-09 | Las operaciones de transferencia respetan los permisos de los usuarios. |

Estos criterios son una organización de QA derivada del CU-011 y sus requisitos relacionados. Su cumplimiento se comprobará durante las pruebas.

## 8. Estado de verificación

| Elemento | Estado |
|---|---|
| Especificación funcional del CU-011 | Documentada |
| Relación con requisitos | Documentada |
| Flujos alternativos A1-A4 | Documentados |
| Definition of Ready | Evaluado documentalmente |
| Casos de prueba | Pendientes de documentar en la matriz |
| Ejecución de pruebas | Pendiente |
| Verificación de Definition of Done | Pendiente de implementación y pruebas |

La matriz de escenarios y los resultados de las pruebas se documentarán en `docs/evidencias-pruebas.md`.
