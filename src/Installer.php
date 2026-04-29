<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

use Config;

/**
 * Handles install and uninstall lifecycle.
 * Stores configuration via GLPI's core Config (context = "plugin:vacationpdf").
 */
final class Installer
{
    public const CONFIG_CONTEXT = 'plugin:vacationpdf';

    public static function install(): void
    {
        $defaults = ConfigDefaults::all();
        $existing = Config::getConfigurationValues(self::CONFIG_CONTEXT);

        $toInsert = array_diff_key($defaults, $existing);
        if ($toInsert !== []) {
            Config::setConfigurationValues(self::CONFIG_CONTEXT, $toInsert);
        }
    }

    public static function uninstall(): void
    {
        Config::deleteConfigurationValues(
            self::CONFIG_CONTEXT,
            array_keys(ConfigDefaults::all())
        );
    }
}
