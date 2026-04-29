# vacationpdf — Plugin de GLPI para PDF de Vacaciones y Equipos

Plugin para **GLPI 11.x** (PHP 8.2+) que genera automáticamente documentos PDF (solicitudes de vacaciones, constancias y conformidades de equipo) cuando una validación de ticket cambia de estado, y los adjunta al ticket como Documentos GLPI.

Desarrollado por **Fabio Ochoa** · Licencia GPLv3 · Versión **2.0.0**

---

## Funcionalidades

- **Solicitud de Vacaciones** — PDF generado cuando la validación es rechazada o queda pendiente.
- **Constancia de Vacaciones** — PDF generado cuando la validación es aprobada.
- **Conformidad de Equipo** — PDF para tickets de asignación de PC / Monitor / Teléfono / Conformidades.
- Detección automática del tipo de ticket por palabras clave configurables en su nombre.
- Adjuntado automático del PDF al ticket como Documento GLPI.
- Logging integrado con `Toolbox::logInFile('vacationpdf', ...)` (respeta la rotación de logs de GLPI).
- Templates en **Twig**, separados del código PHP.
- Configuración almacenada en `glpi_configs` (contexto `plugin:vacationpdf`), sin tablas propias.

---

## Requisitos

| Componente | Versión mínima |
|---|---|
| GLPI | 11.0.0 |
| PHP  | 8.2 |
| TCPDF | incluido en GLPI (`vendor/tecnickcom/tcpdf`) |

---

## Instalación

1. Copia la carpeta `vacationpdf/` dentro de `<raíz_glpi>/plugins/`.
2. En GLPI, ve a **Configuración → Plugins**, haz clic en **Instalar** y luego en **Activar**.
3. (Opcional) Coloca el logotipo de tu empresa en:
   ```
   <GLPI_PLUGIN_DOC_DIR>/vacationpdf/logo.png
   ```
   Si el archivo no existe, se imprime el nombre de la empresa como texto.

La instalación **siembra valores por defecto** en `glpi_configs` (contexto `plugin:vacationpdf`). No se crean tablas adicionales.

---

## Estructura de archivos

```
vacationpdf/
├── setup.php                          # Metadatos + registro de hooks
├── hook.php                           # Callbacks install / uninstall
├── src/                               # Clases PSR-4 (namespace GlpiPlugin\Vacationpdf\*)
│   ├── Installer.php                  # Siembra / limpia configuración
│   ├── ConfigDefaults.php             # Valores por defecto
│   ├── PluginConfig.php               # Lectura tipada de configuración
│   ├── Dispatcher.php                 # Punto de entrada del hook item_update
│   ├── VacationPdfGenerator.php       # Orquestador de PDFs de vacaciones
│   ├── EquipmentPdfGenerator.php      # Orquestador de PDFs de equipo
│   ├── ContentParser.php              # Extrae campos del contenido del ticket
│   ├── ApprovalService.php            # Resume validaciones de un ticket
│   ├── PdfBuilder.php                 # Renderiza Twig → TCPDF → archivo
│   ├── DocumentAttacher.php           # Crea Document y lo enlaza al ticket
│   ├── UserInfo.php                   # Lookups de nombre y cargo del usuario
│   ├── DateUtils.php                  # Parseo y formateo de fechas
│   ├── Text.php                       # Utilidades de texto (HTML→plain, fold)
│   ├── Logger.php                     # Wrapper sobre Toolbox::logInFile
│   ├── PdfType.php                    # Enum de tipos de PDF
│   └── ApprovalStatus.php             # Enum de estado de aprobación
└── templates/                         # Plantillas Twig (namespace @vacationpdf)
    ├── _pdf_styles.html.twig
    ├── vacation_request.html.twig
    ├── vacation_certificate.html.twig
    └── equipment.html.twig
```

El autoload sigue PSR-4: `src/<Nombre>.php` ⇄ `GlpiPlugin\Vacationpdf\<Nombre>`. GLPI 11 resuelve el namespace automáticamente.

---

## Cómo funciona

### Flujo

```
TicketValidation se actualiza
        │
        ▼
Dispatcher::onValidationUpdate()        ← hook item_update
        │
        ├─ ¿Transición a ACCEPTED/REFUSED con cambio de estado?  → no: abortar
        │
        ├─ ¿Nombre contiene alguna palabra clave de equipo?
        │       └─ Sí → EquipmentPdfGenerator::generate()
        │
        └─ No  → VacationPdfGenerator::generate()
                        │
                        ├─ status = Approved  → PdfType::VacationCertificate
                        └─ status ≠ Approved  → PdfType::VacationRequest
```

La detección de palabras clave usa `Text::contains()`, que es **case-insensitive y acento-insensitive**: `"Asignación de PC"` matchea con `"asignacion de pc"`.

### Tipos de PDF

| Tipo | Cuándo se genera | Archivo |
|---|---|---|
| `PdfType::VacationCertificate` | validación aprobada | `constancia_vacaciones_{id}.pdf` |
| `PdfType::VacationRequest` | validación rechazada / pendiente | `solicitud_vacaciones_{id}.pdf` |
| `PdfType::Equipment` | el nombre del ticket matchea palabras clave de equipo | `conformidad_equipo_{id}.pdf` |

### Extracción de campos (solo vacaciones)

`ContentParser` lee el cuerpo del ticket y busca las siguientes etiquetas (todas configurables):

| Campo | Claves por defecto |
|---|---|
| DNI | `DNI` |
| Fecha de inicio de labores | `Fecha de Inicio de Labores`, `Inicio de Labores`, `Fecha de Ingreso`, `Ingreso` |
| Inicio de vacaciones | `Fecha inicio de vacaciones`, `Inicio de vacaciones`, `Fecha de inicio`, `Inicio` |
| Fin de vacaciones | `Fecha fin de vacaciones`, `Fin de vacaciones`, `Fecha de fin`, `Fin` |
| Observaciones | `Observaciones`, `Comentarios`, `Comentario` |

Formatos de fecha aceptados: `YYYY-MM-DD`, `DD/MM/YYYY`, `DD-MM-YYYY`.

---

## Configuración

Se persiste en `glpi_configs` con contexto `plugin:vacationpdf`. Valores por defecto:

| Clave | Default |
|---|---|
| `company_name` | `Comutel Perú SAC` |
| `city` | `Lima` |
| `logo_filename` | `logo.png` |
| `equipment_keywords` | `["Asignacion de PC", "Asignacion de Monitor", "Asignacion de Telefono", "Conformidad"]` |
| `labels_dni` | `["DNI"]` |
| `labels_startwork` | `["Fecha de Inicio de Labores", ...]` |
| `labels_vacstart` | `["Fecha inicio de vacaciones", ...]` |
| `labels_vacend` | `["Fecha fin de vacaciones", ...]` |
| `labels_observations` | `["Observaciones", "Comentarios", "Comentario"]` |

Los valores listados se guardan como JSON. Para modificarlos desde código o consola:

```php
Config::setConfigurationValues('plugin:vacationpdf', [
    'company_name' => 'Mi Empresa SAC',
    'city'         => 'Arequipa',
    'equipment_keywords' => json_encode(
        ['Asignacion de Laptop', 'Entrega de Equipo'],
        JSON_UNESCAPED_UNICODE
    ),
]);
```

> La desinstalación del plugin elimina todas estas claves.

---

## Tablas de base de datos

El plugin **no crea tablas propias**. Solo lee y escribe sobre:

| Tabla | Operación |
|---|---|
| `glpi_tickets` | Lectura |
| `glpi_users` | Lectura |
| `glpi_ticketvalidations` | Lectura |
| `glpi_usertitles` | Lectura |
| `glpi_documents` | Escritura (al adjuntar PDF) |
| `glpi_documents_items` | Escritura (al enlazar PDF con ticket) |
| `glpi_configs` (contexto `plugin:vacationpdf`) | Lectura/Escritura |

Todas las consultas usan `$DB->request([...])` con criterios array — sin SQL concatenado.

---

## Logging

Toda operación se registra vía `Toolbox::logInFile('vacationpdf', ...)`:

```
<GLPI_ROOT>/files/_log/vacationpdf.log
```

Niveles emitidos:
- `START vacation|equipment ticket_id={id} triggered_by={user_id}`
- `ATTACH ticket_id={id} type={...} result=OK|FAIL`
- `[ERROR] ...` (excepciones capturadas por el Dispatcher)

---

## Seguridad

- **CSRF compliant** — declarado vía `Hooks::CSRF_COMPLIANT`.
- **Escape automático** en plantillas Twig (no hay HTML concatenado).
- **Queries parametrizadas** mediante `$DB->request([...])`.
- `strict_types=1` en todos los archivos PHP.
- Archivos temporales eliminados inmediatamente tras adjuntarlos.

---

## Cambios respecto a la versión 1.3.0

- Reescrito para **GLPI 11 / PHP 8.2+**.
- Migración a **PSR-4** (`src/`) y namespace `GlpiPlugin\Vacationpdf`.
- Plantillas movidas a **Twig** (`templates/`).
- Configuración en `glpi_configs` — sin valores hardcoded.
- Cadena de `stripos` reemplazada por matching normalizado (`Text::fold`).
- SQL crudo reemplazado por `$DB->request([...])`.
- Logging propio migrado a `Toolbox::logInFile`.
- Responsabilidades separadas: parser, aprobaciones, PDF, adjuntado, orquestadores.
- Enums `PdfType` y `ApprovalStatus` para reemplazar strings mágicos.
- Install/uninstall hooks siembran/limpian configuración automáticamente.

---

## Licencia

GPLv3 — [https://www.gnu.org/licenses/gpl-3.0.html](https://www.gnu.org/licenses/gpl-3.0.html)
