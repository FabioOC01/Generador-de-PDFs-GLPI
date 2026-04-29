<?php
/**
 * vacationpdf — GLPI plugin
 *
 * Generates vacation and equipment PDFs when ticket validations change state
 * and attaches them as GLPI Documents.
 *
 * @author  Fabio Ochoa
 * @license GPLv3
 */

declare(strict_types=1);

use Glpi\Plugin\Hooks;

const PLUGIN_VACATIONPDF_VERSION  = '2.0.0';
const PLUGIN_VACATIONPDF_MIN_GLPI = '11.0.0';
const PLUGIN_VACATIONPDF_MAX_GLPI = '11.0.99';

function plugin_init_vacationpdf(): void
{
    /** @var array $PLUGIN_HOOKS */
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['vacationpdf'] = true;

    $PLUGIN_HOOKS[Hooks::ITEM_UPDATE]['vacationpdf'] = [
        TicketValidation::class => [
            GlpiPlugin\Vacationpdf\Dispatcher::class,
            'onValidationUpdate',
        ],
    ];
}

function plugin_version_vacationpdf(): array
{
    return [
        'name'           => 'Vacation & Equipment PDF',
        'version'        => PLUGIN_VACATIONPDF_VERSION,
        'author'         => 'Fabio Ochoa',
        'license'        => 'GPLv3',
        'homepage'       => '',
        'minGlpiVersion' => PLUGIN_VACATIONPDF_MIN_GLPI,
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_VACATIONPDF_MIN_GLPI,
                'max' => PLUGIN_VACATIONPDF_MAX_GLPI,
            ],
            'php'  => [
                'min' => '8.2',
            ],
        ],
    ];
}

function plugin_vacationpdf_check_prerequisites(): bool
{
    return true;
}

function plugin_vacationpdf_check_config(): bool
{
    return true;
}
