<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

use DateTimeImmutable;

final class DateUtils
{
    private const SUPPORTED_FORMATS = ['Y-m-d', 'd/m/Y', 'd-m-Y'];

    private const SPANISH_MONTHS = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    public static function parse(string $date): ?DateTimeImmutable
    {
        $date = trim($date);
        if ($date === '') {
            return null;
        }

        foreach (self::SUPPORTED_FORMATS as $format) {
            $dt = DateTimeImmutable::createFromFormat('!' . $format, $date);
            if ($dt instanceof DateTimeImmutable && $dt->format($format) === $date) {
                return $dt;
            }
        }

        return null;
    }

    /**
     * Inclusive day count between two dates. Returns '' when either is unparseable.
     */
    public static function daysInclusive(string $start, string $end): string
    {
        $s = self::parse($start);
        $e = self::parse($end);
        if ($s === null || $e === null) {
            return '';
        }

        return (string) ((int) $s->diff($e)->days + 1);
    }

    /**
     * "Lima 23 de Abril de 2026." — falls back to today when date is invalid.
     */
    public static function formatSpanishLongDate(string $ymd, string $city): string
    {
        $dt = self::parse($ymd) ?? new DateTimeImmutable('today');
        $month = self::SPANISH_MONTHS[(int) $dt->format('n')] ?? '';

        return sprintf('%s %d de %s de %s.', $city, (int) $dt->format('j'), $month, $dt->format('Y'));
    }
}
