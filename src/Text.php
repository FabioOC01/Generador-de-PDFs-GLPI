<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

/**
 * Pure text utilities: normalization and accent-insensitive matching.
 */
final class Text
{
    /**
     * Fold to lowercase ASCII (strips accents) so "Asignación" and
     * "asignacion" compare equal. Used for keyword detection.
     */
    public static function fold(string $input): string
    {
        $input = mb_strtolower($input, 'UTF-8');

        $map = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
        ];

        return strtr($input, $map);
    }

    /**
     * Case- and accent-insensitive "contains".
     */
    public static function contains(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return false;
        }
        return str_contains(self::fold($haystack), self::fold($needle));
    }

    /**
     * Convert HTML ticket content into plain text with preserved line breaks.
     */
    public static function htmlToPlain(string $html): string
    {
        $replacements = [
            '~<\s*br\s*/?\s*>~i' => "\n",
            '~</\s*p\s*>~i'      => "\n",
            '~</\s*div\s*>~i'    => "\n",
            '~</\s*tr\s*>~i'     => "\n",
            '~</\s*td\s*>~i'     => ' ',
        ];
        $text = preg_replace(array_keys($replacements), array_values($replacements), $html) ?? $html;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = str_replace("\xC2\xA0", ' ', $text);
        $text = (string) preg_replace("/\r\n|\r/", "\n", $text);
        $text = (string) preg_replace("/[ \t]+/", ' ', $text);
        $text = (string) preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }

    /**
     * Strip remaining HTML from an already-extracted value and collapse whitespace.
     */
    public static function sanitize(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
        $value = str_replace("\xC2\xA0", ' ', $value);
        $value = (string) preg_replace('~<[^>]+>~', ' ', $value);
        $value = (string) preg_replace('/\s{2,}/', ' ', $value);

        return trim($value);
    }
}
