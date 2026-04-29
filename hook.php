<?php
/**
 * vacationpdf — install/uninstall/upgrade lifecycle.
 * Business logic lives in src/Dispatcher.php.
 */

declare(strict_types=1);

function plugin_vacationpdf_install(): bool
{
    GlpiPlugin\Vacationpdf\Installer::install();
    return true;
}

function plugin_vacationpdf_uninstall(): bool
{
    GlpiPlugin\Vacationpdf\Installer::uninstall();
    return true;
}
