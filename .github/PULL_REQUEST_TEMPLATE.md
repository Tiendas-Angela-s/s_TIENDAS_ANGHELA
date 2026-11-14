## Título
<!-- Formato: <tipo>(<modulo>): <descripcion-corta> -->

## Contexto e Issue Relacionada
<!-- Ejemplo: Closes #12 -->
Closes #

## Alcance del Cambio
- [ ] Implementación / corrección en `src/`
- [ ] Pruebas unitarias / integración en `tests/`
- [ ] Documentación en `docs/`

## Criterios de Aceptación
- [ ] Cumple las reglas de negocio del caso de uso (`CU-011`).
- [ ] No expone credenciales ni archivos binarios locales (`*.sqlite`, `.env`).

## Pruebas Ejecutadas (Regresión)
- `php tests/unit/IngresoService.test.php`
- `php tests/unit/TransferenciaService.test.php`
- `php tests/integration/TransferenciaConcurrencia.test.php`

## Riesgos y Mitigación
- **Riesgo:** Inconsistencia de stock en transferencias concurrentes.
- **Mitigación:** Validación atómica y pruebas de concurrencia ejecutadas en CI.

## Evidencia
<!-- Adjuntar captura de pruebas en verde o check de GitHub Actions -->
