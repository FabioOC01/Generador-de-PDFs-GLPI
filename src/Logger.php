<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

use Toolbox;

/**
 * Thin wrapper over Toolbox::logInFile so every log line is consistent
 * and lands in files/_log/vacationpdf*.log — participating in GLPI's
 * log rotation instead of writing our own.
 */
final class Logger
{
    private const CHANNEL = 'vacationpdf';

    public static function info(string $message): void
    {
        Toolbox::logInFile(self::CHANNEL, $message . PHP_EOL, false);
    }

    public static function error(string $message): void
    {
        Toolbox::logInFile(self::CHANNEL, '[ERROR] ' . $message . PHP_EOL, false);
    }
}
