<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

use Config;

/**
 * Typed reader over the plugin's configuration values.
 * Falls back to ConfigDefaults when a key is missing so the plugin
 * works even before install() runs (e.g. in tests).
 */
final class PluginConfig
{
    private array $values;

    public function __construct(?array $values = null)
    {
        $raw = $values ?? Config::getConfigurationValues(Installer::CONFIG_CONTEXT);
        $this->values = $raw + ConfigDefaults::all();
    }

    public function companyName(): string
    {
        return (string) $this->values['company_name'];
    }

    public function city(): string
    {
        return (string) $this->values['city'];
    }

    public function logoFilename(): string
    {
        return (string) $this->values['logo_filename'];
    }

    /** @return list<string> */
    public function equipmentKeywords(): array
    {
        return $this->jsonList('equipment_keywords');
    }

    /** @return list<string> */
    public function labelsDni(): array            { return $this->jsonList('labels_dni'); }

    /** @return list<string> */
    public function labelsStartWork(): array      { return $this->jsonList('labels_startwork'); }

    /** @return list<string> */
    public function labelsVacationStart(): array  { return $this->jsonList('labels_vacstart'); }

    /** @return list<string> */
    public function labelsVacationEnd(): array    { return $this->jsonList('labels_vacend'); }

    /** @return list<string> */
    public function labelsObservations(): array   { return $this->jsonList('labels_observations'); }

    /** @return list<string> */
    private function jsonList(string $key): array
    {
        $raw = $this->values[$key] ?? '[]';
        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
        return is_array($decoded) ? array_values(array_map('strval', $decoded)) : [];
    }
}
