<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

/**
 * Default configuration values seeded at install time.
 * Values are serialized as JSON when they are arrays so they fit in
 * GLPI's glpi_configs.value column.
 */
final class ConfigDefaults
{
    public static function all(): array
    {
        return [
            'company_name'      => 'Comutel Perú SAC',
            'city'              => 'Lima',
            'logo_filename'     => 'logo.png',
            'equipment_keywords' => json_encode([
                'Asignacion de PC',
                'Asignacion de Monitor',
                'Asignacion de Telefono',
                'Conformidad',
            ], JSON_UNESCAPED_UNICODE),
            'labels_dni' => json_encode(['DNI'], JSON_UNESCAPED_UNICODE),
            'labels_startwork' => json_encode([
                'Fecha de Inicio de Labores',
                'Fecha Inicio de Labores',
                'Inicio de Labores',
                'Fecha de Ingreso',
                'Ingreso',
            ], JSON_UNESCAPED_UNICODE),
            'labels_vacstart' => json_encode([
                'Fecha inicio de vacaciones',
                'Inicio de vacaciones',
                'Fecha de inicio',
                'Inicio',
            ], JSON_UNESCAPED_UNICODE),
            'labels_vacend' => json_encode([
                'Fecha fin de vacaciones',
                'Fin de vacaciones',
                'Fecha de fin',
                'Fin',
            ], JSON_UNESCAPED_UNICODE),
            'labels_observations' => json_encode([
                'Observaciones',
                'Comentarios',
                'Comentario',
            ], JSON_UNESCAPED_UNICODE),
        ];
    }
}
