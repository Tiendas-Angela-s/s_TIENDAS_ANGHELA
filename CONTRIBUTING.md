
# Guía de contribución

## 1. Objetivo

Establecer convenciones para organizar el trabajo colaborativo en Git y GitHub durante el desarrollo del Sistema Integral de Gestión de la Tienda Angela's.

Estas convenciones buscan facilitar la coordinación del equipo, mantener un historial comprensible de los cambios y permitir el seguimiento de las tareas mediante Issues, ramas, commits y Pull Requests.

Las recomendaciones se aplicarán de acuerdo con las necesidades de cada tarea, evitando procedimientos innecesarios.

## 2. Convenciones de ramas

Las ramas permiten desarrollar funcionalidades, realizar correcciones y actualizar documentación sin modificar directamente la rama principal.

### 2.1. Tipos de ramas

| Tipo | Uso | Ejemplo |
|---|---|---|
| feature | Desarrollo de funcionalidades | feature/TECH-002-login |
| bugfix | Corrección de errores | bugfix/TECH-003-error-login |
| hotfix | Correcciones urgentes | hotfix/TECH-004-seguridad |
| chore | Mantenimiento y configuración | chore/TECH-001-pipeline-ci |
| refactor | Mejoras internas del código | refactor/TECH-005-servicio |
| docs | Documentación | docs/TECH-006-readme |
| test | Desarrollo de pruebas | test/TECH-007-unitarias |

### 2.2. Recomendaciones para las ramas

- Utilizar nombres cortos y descriptivos.
- Separar las palabras mediante guiones.
- Evitar espacios y caracteres innecesarios.
- Mantener los cambios relacionados con una tarea.
- Trabajar en ramas independientes antes de integrar cambios en `main`.
- Actualizar las ramas cuando sea necesario para evitar conflictos.

Se recomienda utilizar el formato:

`<tipo>/<ID>-<descripcion-corta>`

Cuando no exista un identificador técnico, puede utilizarse una descripción breve, como `feature/conventions`.

## 3. Convenciones de commits

Los commits permiten registrar los cambios realizados durante el desarrollo.

### 3.1. Formato

Se utilizará preferentemente el siguiente formato:

`<tipo>(<modulo>): <descripcion-corta>`

### 3.2. Tipos de commits

| Tipo | Uso | Ejemplo |
|---|---|---|
| feat | Agregar una funcionalidad | feat(productos): implementar registro |
| fix | Corregir errores | fix(inventario): corregir validacion de stock |
| chore | Realizar mantenimiento o configuración | chore(ci): actualizar configuracion |
| docs | Crear o modificar documentación | docs(conventions): actualizar estandares |
| test | Agregar o modificar pruebas | test(usuarios): agregar pruebas de acceso |
| refactor | Mejorar código sin cambiar su comportamiento | refactor(ventas): simplificar calculo |

### 3.3. Buenas prácticas para los commits

- Utilizar el tipo que mejor describa el cambio.
- Escribir mensajes breves y comprensibles.
- Procurar que cada commit represente cambios relacionados.
- Evitar mensajes ambiguos como `cambios` o `arreglos`.
- No incluir credenciales ni información sensible.

Se recomienda realizar commits pequeños para facilitar la revisión y el seguimiento de los cambios.

## 4. Gestión de Issues

Las Issues permiten organizar las tareas del proyecto, asignar responsables y registrar su progreso.

### 4.1. Información de una Issue

Se recomienda incluir la información necesaria para comprender el trabajo:

- Título descriptivo.
- Objetivo o descripción.
- Responsable asignado.
- Tareas técnicas.
- Criterios de aceptación, cuando correspondan.
- Etiquetas y relaciones con otras tareas, cuando sean necesarias.

El nivel de detalle puede variar según la complejidad de la actividad.

### 4.2. Estados de las Issues

Las tareas se organizan mediante los siguientes estados del tablero GitHub Projects:

| Estado | Descripción |
|---|---|
| Ready | La tarea está definida y pendiente de comenzar. |
| In Progress | El responsable se encuentra trabajando en la tarea. |
| In Review | Los cambios están pendientes de revisión. |
| Done | La tarea fue completada e integrada. |

En GitHub, una Issue puede permanecer `Open` mientras avanza por Ready, In Progress e In Review.

### 4.3. Seguimiento de las tareas

El equipo seguirá el flujo establecido para sus Issues:

1. Revisar la tarea asignada y sus criterios de aceptación.
2. Cambiarla de `Ready` a `In Progress` al comenzar.
3. Realizar los cambios en la rama correspondiente.
4. Registrar los cambios mediante commits.
5. Marcar las casillas de las tareas completadas.
6. Abrir un Pull Request relacionado con la Issue.
7. Mover la tarea a `In Review`.
8. Atender las observaciones de la revisión.
9. Integrar los cambios aprobados.
10. Comprobar el cierre de la Issue y su estado `Done`.

Las Issues vinculadas a Pull Requests se cerrarán mediante la integración de los cambios, siguiendo el procedimiento acordado por el equipo, en lugar de utilizar manualmente `Close issue`.

El estado `Done` del tablero puede requerir una actualización manual dependiendo de la configuración de GitHub Projects.

## 5. Flujo de trabajo y Pull Requests

### 5.1. Flujo de contribución

El trabajo colaborativo sigue este proceso general:

1. Seleccionar una tarea.
2. Crear o utilizar una rama de trabajo.
3. Realizar los cambios necesarios.
4. Registrar los avances mediante commits.
5. Abrir un Pull Request hacia `main`.
6. Solicitar la revisión correspondiente.
7. Atender las observaciones que resulten necesarias.
8. Integrar los cambios aprobados.

### 5.2. Contenido de un Pull Request

Cada Pull Request debe permitir comprender qué se modificó y por qué.

Se recomienda incluir, según el alcance del cambio:

- **Título:** descripción breve.
- **Contexto:** motivo del cambio e Issue relacionada.
- **Alcance:** archivos o funcionalidades modificadas.
- **Criterios de aceptación:** condiciones verificables, cuando correspondan.
- **Pruebas:** verificaciones realizadas, si son aplicables.
- **Riesgos y decisiones:** aspectos importantes identificados.
- **Evidencias:** capturas o resultados disponibles, cuando sean necesarios.

No todos estos apartados son necesarios para cambios pequeños o exclusivamente documentales.

Cuando una actividad académica exija una plantilla específica de Pull Request, se utilizarán los campos solicitados en dicha actividad.

### 5.3. Vinculación con Issues

Para cerrar automáticamente una Issue mediante un Pull Request integrado en la rama predeterminada, puede utilizarse en la descripción:

`Closes #N`

Donde `N` es el número de la Issue.

Ejemplo:

`Closes #15`

Esta expresión debe utilizarse cuando el Pull Request realmente complete el trabajo de la Issue correspondiente.

La vinculación y el cierre automático dependen de las condiciones establecidas por GitHub.

### 5.4. Integración de cambios

Antes de integrar un Pull Request, se debe realizar la revisión correspondiente.

Se recomienda:

- Comprobar que los cambios cumplan el objetivo de la tarea.
- Revisar los criterios de aceptación aplicables.
- Ejecutar las pruebas necesarias según el tipo de cambio.
- Resolver las observaciones importantes.
- Evitar integrar cambios que introduzcan errores conocidos sin resolver.

## 6. Revisión y aprobación de cambios

La revisión permite comprobar que los cambios realizados sean adecuados y cumplan con los requisitos del proyecto.

### 6.1. Responsabilidades de revisión

| Rol | Responsabilidad |
|---|---|
| Desarrollador | Verificar su implementación, realizar las pruebas correspondientes y atender observaciones. |
| Líder técnico | Revisar la calidad del código, arquitectura, seguridad y estándares técnicos. |
| Analista funcional / QA | Comprobar los criterios de aceptación, reglas del negocio y resultados de las pruebas. |
| Jefe de proyecto | Supervisar el seguimiento de las tareas, la trazabilidad y el proceso de revisión. |

### 6.2. Aspectos recomendados para la revisión

Según el tipo de cambio, se pueden considerar:

- Cumplimiento de los requisitos.
- Criterios de aceptación.
- Convenciones de programación.
- Funcionamiento de las funcionalidades.
- Manejo de errores.
- Seguridad y permisos.
- Resultados de pruebas normales, límite y negativas.
- Evidencias disponibles.
- Cumplimiento de la Definition of Done (DoD).

No todos los aspectos serán aplicables a cada Pull Request.

### 6.3. Observaciones y aprobación

Cuando se detecte un problema, se recomienda dejar un comentario claro que explique qué debe revisarse o corregirse.

Las observaciones pueden incluir una descripción del problema, el archivo afectado y una sugerencia de solución cuando corresponda.

Una vez comprobado el cumplimiento de los criterios aplicables, el revisor podrá registrar su aprobación mediante las herramientas de GitHub.

Las revisiones funcionales y técnicas se realizarán de acuerdo con las responsabilidades asignadas por el equipo.

## 7. Registro de errores y buenas prácticas

### 7.1. Registro de errores

Los errores identificados durante el desarrollo o las pruebas pueden registrarse mediante Issues de GitHub, utilizando la etiqueta `tipo:bug`.

Se recomienda que los reportes incluyan información suficiente para comprender y reproducir el problema, como:

- Descripción breve del error.
- Pasos para reproducirlo, cuando sean necesarios.
- Resultado esperado y resultado obtenido.
- Captura o evidencia, si está disponible.

El detalle dependerá de la complejidad del error.

Los problemas menores también pueden comunicarse mediante comentarios en el Pull Request correspondiente.

Los errores importantes que requieran seguimiento deben registrarse mediante una Issue para facilitar su corrección.

### 7.2. Seguimiento de errores

Se recomienda:

- Asignar los errores al responsable correspondiente.
- Mantener una descripción comprensible.
- Registrar las correcciones cuando sea necesario.
- Verificar que el problema se haya solucionado.
- Realizar pruebas de regresión cuando corresponda.

### 7.3. Buenas prácticas de colaboración

- Mantener las tareas claras y enfocadas.
- Utilizar nombres consistentes para ramas y commits.
- Evitar mezclar cambios que no estén relacionados.
- Mantener los Pull Requests comprensibles.
- Evitar modificar el trabajo de otros integrantes sin coordinación.
- No publicar información sensible.
- Documentar las decisiones relevantes cuando sea necesario.
- Revisar los cambios antes de integrarlos.
- Mantener la trazabilidad entre tareas y cambios realizados.
