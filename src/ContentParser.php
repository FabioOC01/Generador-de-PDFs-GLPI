<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

/**
 * Extracts structured fields from a ticket's free-form content.
 * All extractors match labels case-insensitively and tolerate trailing
 * whitespace / HTML stripped out beforehand by Text::htmlToPlain().
 */
final class ContentParser
{
    public function __construct(private readonly string $plainText)
    {
    }

    public static function fromHtml(string $html): self
    {
        return new self(Text::htmlToPlain($html));
    }

    /** Finds a 6–20 digit number. */
    public function integer(array $labels): string
    {
        foreach ($labels as $label) {
            $pattern = '/' . preg_quote($label, '/') . '\s*:\s*([0-9]{6,20})/i';
            if (preg_match($pattern, $this->plainText, $m)) {
                return trim($m[1]);
            }
        }
        return '';
    }

    /** Finds the first date after the label in any supported format. */
    public function date(array $labels): string
    {
        $date = '\d{4}\-\d{2}\-\d{2}|\d{2}\/\d{2}\/\d{4}|\d{2}\-\d{2}\-\d{4}';
        foreach ($labels as $label) {
            $pattern = '/' . preg_quote($label, '/') . '\s*:\s*.*?(' . $date . ')/i';
            if (preg_match($pattern, $this->plainText, $m)) {
                return trim($m[1]);
            }
        }
        return '';
    }

    /** Finds free text up to the next label or newline. */
    public function text(array $labels): string
    {
        foreach ($labels as $label) {
            $pattern = '/' . preg_quote($label, '/') . '\s*:\s*(.*?)(?=\n|\r|$|[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+\s*:\s*)/i';
            if (preg_match($pattern, $this->plainText, $m)) {
                $value = Text::sanitize($m[1]);
                if ($value !== '') {
                    return $value;
                }
            }
        }
        return '';
    }
}
